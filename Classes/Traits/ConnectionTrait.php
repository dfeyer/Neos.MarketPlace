<?php
namespace Neos\MarketPlace\Traits;

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
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;

/**
 * Connection Trait
 *
 * @api
 */
trait ConnectionTrait
{
    /**
     * @var \TYPO3\Flow\Object\ObjectManager
     * @Flow\Inject
     */
    protected $_objectManager;

    protected function getConnection()
    {
        /** @var Connection $client */
        $client = $this->_objectManager->get(Connection::class);
        $client->connect(3600);
        return $client;
    }
}
