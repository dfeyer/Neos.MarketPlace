<?php
namespace Neos\MarketPlace\Domain\Model;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Cocur\Slugify\Slugify;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;

/**
 * @api
 */
class StabilityLabel
{
    const DEV_VERSION_PREFIX = 'dev-';
    const DEV_VERSION_SUFFIX = '-dev';

    /**
     * @param string $version
     * @return string
     */
    public static function get($version)
    {
        if (self::isDev($version)) {
            return 'dev';
        }
        return 'released';
    }

    /**
     * @param string $version
     * @return boolean
     */
    public static function isDev($version)
    {
        return (substr($version, 0, 4) === self::DEV_VERSION_PREFIX || substr($version, -4) === self::DEV_VERSION_SUFFIX);
    }
}
