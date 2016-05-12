<?php
namespace Neos\MarketPlace\Review\Message;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MarketPlace\Domain\Model\Storage;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Utility\Arrays;

/**
 * Review Plugin Requested Message
 *
 * @api
 */
class ReviewPluginRequestedMessage
{
    const SUBJECT = 'Neos.MarketPlace:ReviewRequested';

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @param string $action
     * @param array $payload
     */
    public function __construct($action, array $payload)
    {
        $this->action = $action;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function payload()
    {
        $payload = Arrays::arrayMergeRecursiveOverrule($this->payload, [
            'action' => $this->action
        ]);
        return json_encode($payload);
    }
}
