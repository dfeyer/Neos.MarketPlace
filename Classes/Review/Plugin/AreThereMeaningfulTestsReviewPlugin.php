<?php
namespace Neos\MarketPlace\Review\Plugin;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MarketPlace\Review\Contract\ReviewPluginInterface;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;

/**
 * Are There Meaningful Tests Review Plugin
 *
 * @api
 */
class AreThereMeaningfulTestsReviewPlugin implements ReviewPluginInterface
{
    /**
     * @param array $payload
     */
    public function process(array $payload) {

    }
}
