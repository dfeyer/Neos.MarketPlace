<?php
namespace Neos\MarketPlace\TypoScriptObjects;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Exception as TypoScriptException;
use Neos\Fusion\FusionObjects\AbstractCollectionImplementation;

/**
 * Render a TypoScript collection of nodes
 *
 * todo can be remove when https://github.com/neos/neos-development-collection/pull/407 is merged
 */
class CollectionImplementation extends AbstractCollectionImplementation
{

    /**
     * @return string
     */
    public function getItemKey()
    {
        return $this->tsValue('itemKey');
    }

    /**
     * Evaluate the collection nodes
     *
     * @return string
     * @throws TypoScriptException
     */
    public function evaluate()
    {
        $collection = $this->getCollection();
        $output = '';
        if ($collection === null) {
            return '';
        }
        $this->numberOfRenderedNodes = 0;
        $itemName = $this->getItemName();
        if ($itemName === null) {
            throw new \Neos\Fusion\Exception('The Collection needs an itemName to be set.', 1344325771);
        }
        $itemKey = $this->getItemKey();
        $iterationName = $this->getIterationName();
        $collectionTotalCount = count($collection);
        foreach ($collection as $collectionKey => $collectionElement) {
            $context = $this->tsRuntime->getCurrentContext();
            $context[$itemName] = $collectionElement;
            if ($itemKey !== null) {
                $context[$itemKey] = $collectionKey;
            }
            if ($iterationName !== null) {
                $context[$iterationName] = $this->prepareIterationInformation($collectionTotalCount);
            }
            $this->tsRuntime->pushContextArray($context);
            $output .= $this->tsRuntime->render($this->path . '/itemRenderer');
            $this->tsRuntime->popContext();
            $this->numberOfRenderedNodes++;
        }
        return $output;
    }
}
