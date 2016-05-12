<?php
namespace Neos\MarketPlace\Command;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Nats\Connection;
use Neos\MarketPlace\Review\Contract\MessageHandlerInterface;
use Neos\MarketPlace\Traits\ConnectionTrait;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Flow\Object\ObjectManager;

/**
 * MarketPlace Command Controller
 */
class MarketPlaceWorkerCommandController extends CommandController
{
    use ConnectionTrait;

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $logger;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="worker")
     */
    protected $configuration;

    /**
     * Run Worker Process
     *
     * @param string $subject
     * @param string $queue
     */
    public function runCommand($subject, $queue = null)
    {
        $this->outputLine('MarketPlace Worker running ...', [], LOG_DEBUG);

        /** @var Connection $client */
        $client = $this->getConnection();

        $callback = function ($payload) {
            $rawPayload = $payload;
            $payload = json_decode($payload, true);
            if (!isset($payload['action'])) {
                $this->outputLine('Unable to process "%s", missing action', [$rawPayload]);
                return;
            }
            /** @var MessageHandlerInterface $action */
            $action = $this->objectManager->get($payload['action']);
            $action->process($payload);
            $this->outputLine('Processed with success "%s"', [$rawPayload]);
        };

        if ($queue === null) {
            $sid = $client->subscribe($subject, $callback);
        } else {
            $sid = $client->queueSubscribe($subject, $queue, $callback);
        }
        $client->wait(1);

        $client->unsubscribe($sid);
    }

    /**
     * @param string $text
     * @param array $arguments
     * @param integer $severity
     */
    protected function outputLine($text = '', array $arguments = [], $severity = LOG_INFO)
    {
        if ($severity !== null) {
            $message = vsprintf($text, $arguments);
            $this->logger->log($message, $severity);
        }
        parent::outputLine($text, $arguments);
    }
}
