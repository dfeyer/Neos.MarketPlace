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

use TYPO3\Eel\FlowQuery\FlowQueryException;
use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\TYPO3CR\Domain\Model\Node;

/**
 * EEL operation to get last activity for a given package or a vendor
 */
class LastActivityOperation extends AbstractOperation {

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    static protected $shortName = 'lastActivity';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    static protected $priority = 100;

    /**
     * @var boolean
     */
    protected static $final = true;

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
            return $node->getNodeType()->isOfType('Neos.MarketPlace:Package') || $node->getNodeType()->isOfType('Neos.MarketPlace:Vendor');
        }
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return \DateTime
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments) {
        $query = $flowQuery->find('[instanceof Neos.MarketPlace:Version]');
        /** @var \DateTime $lastActivity */
        $lastActivity = null;
        /** @var NodeInterface $version */
        foreach ($query as $version) {
            /** @var \DateTime $time */
            $time = $version->getProperty('time');
            if ($lastActivity === null || $lastActivity->getTimestamp() < $time->getTimestamp()) {
                $lastActivity = $time;
            }
        }
        return $lastActivity;
    }
}
