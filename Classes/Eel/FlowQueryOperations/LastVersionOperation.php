<?php
namespace Neos\MarketPlace\Eel\FlowQueryOperations;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MarketPlace\Service\PackageVersion;
use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Eel\FlowQuery\FlowQuery;

/**
 * EEL operation to get last version for a given package
 */
class LastVersionOperation extends AbstractOperation {

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    static protected $shortName = 'lastVersion';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    static protected $priority = 100;

    /**
     * @var PackageVersion
     * @Flow\Inject
     */
    protected $packageVersion;

    /**
     * {@inheritdoc}
     *
     * We can only handle TYPO3CR Nodes.
     *
     * @param mixed $context
     * @return boolean
     */
    public function canEvaluate($context) {
        if (isset($context[0]) && ($context[0] instanceof NodeInterface)) {
            /** @var NodeInterface $node */
            $node = $context[0];
            return $node->getNodeType()->isOfType('Neos.MarketPlace:Package');
        }
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return mixed
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments) {
        $context = $flowQuery->getContext();
        $newContext = [];
        /** @var NodeInterface $package */
        foreach ($context as $package) {
            if (!$package->getNodeType()->isOfType('Neos.MarketPlace:Package')) {
                continue;
            }
            $version = $this->packageVersion->extractLastVersion($package);
            if ($version === null) {
                continue;
            }
            $newContext[$version->getIdentifier()] = $version;
        }
        $flowQuery->setContext(array_values($newContext));
        return $flowQuery;
    }
}
