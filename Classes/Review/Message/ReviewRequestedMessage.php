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

/**
 * Review Requested Message
 *
 * @api
 */
class ReviewRequestedMessage
{
    const SUBJECT = 'Neos.MarketPlace:ReviewRequested';
    const ACTION = 'Neos\MarketPlace\Review\Handler\ReviewRequestedHandler';

    /**
     * @var Package
     */
    protected $package;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var string
     */
    protected $version;

    /**
     * @param Package $package
     * @param Storage $storage
     * @param string $version
     */
    public function __construct(Package $package, Storage $storage, $version)
    {
        $this->package = $package;
        $this->storage = $storage;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function payload()
    {
        return json_encode([
            'action' => self::ACTION,
            'packageName' => $this->package->getName(),
            'version' => $this->version,
            'storageIdentifier' => $this->storage->getIdentifer()
        ]);
    }
}
