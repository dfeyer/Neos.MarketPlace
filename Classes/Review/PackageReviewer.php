<?php
namespace Neos\MarketPlace\Review;

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
use Neos\MarketPlace\Review\Message\ReviewRequestedMessage;
use Neos\MarketPlace\Traits\ConnectionTrait;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;

/**
 * Package Reviewer
 *
 * @Flow\Scope("singleton")
 * @api
 */
class PackageReviewer implements PackageReviewerInterface
{
    use ConnectionTrait;

    /**
     * @param Storage $storage
     * @param Package $package
     * @param string $version
     * @throws Exception
     */
    public function process(Storage $storage, Package $package, $version)
    {
        $client = $this->getConnection();
        $message = new ReviewRequestedMessage($package, $storage, $version);
        $client->publish($message::SUBJECT, $message->payload());
    }
}
