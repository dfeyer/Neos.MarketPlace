<?php
namespace Neos\MarketPlace\Review\Contract;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;

/**
 * Package Reviewer
 *
 * @api
 */
interface ReviewPluginInterface
{
    /**
     * @param array $payload
     */
    public function process(array $payload);
}
