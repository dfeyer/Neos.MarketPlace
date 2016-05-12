<?php
namespace Neos\MarketPlace\Review\Handler;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MarketPlace\Review\Contract\MessageHandlerInterface;
use Neos\MarketPlace\Review\Contract\ReviewPluginInterface;
use Neos\MarketPlace\Review\Message\ReviewPluginRequestedMessage;
use Neos\MarketPlace\Traits\ConnectionTrait;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Flow\Object\ObjectManager;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * Review Requested Message
 *
 * @api
 */
class ReviewRequestedHandler implements MessageHandlerInterface, ReviewPluginInterface
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
     * @param array $payload
     */
    public function process(array $payload)
    {
        $client = $this->getConnection();
        foreach (self::getReviewPlugins($this->objectManager) as $plugin) {
            $this->logger->log($plugin, LOG_DEBUG);
            $message = new ReviewPluginRequestedMessage($plugin, $payload);
            $client->publish($message::SUBJECT, $message->payload());
        }
    }

    /**
     * @param ObjectManagerInterface $objectManager
     * @return array Array of method parameters by action name
     * @Flow\CompileStatic
     */
    static public function getReviewPlugins($objectManager)
    {
        $reflectionService = $objectManager->get(ReflectionService::class);
        return $reflectionService->getAllImplementationClassNamesForInterface(ReviewPluginInterface::class);
    }
}
