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

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\Node;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * Vendor
 *
 * @api
 */
class Vendor extends Node
{
    /**
     * @param string $packageName
     * @return NodeInterface
     */
    public function getPackage($packageName)
    {
        $identifier = Slug::create($packageName);
        return $this->getNode($identifier);
    }
}
