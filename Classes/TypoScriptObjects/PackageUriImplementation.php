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

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Neos\Service\LinkingService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;
use TYPO3\Neos\Exception as NeosException;

/**
 * Package TypoScript Implementation
 *
 * @api
 */
class PackageUriImplementation extends AbstractTypoScriptObject
{
    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @return string
     */
    public function getPackageKey()
    {
        return $this->tsValue('packageKey');
    }

    /**
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->tsValue('node');
    }

    /**
     * @return string The rendered URI or NULL if no URI could be resolved for the given node
     * @throws NeosException
     */
    public function evaluate()
    {
        $packageKey = $this->getPackageKey();
        $packageKeyParts = explode('-', $packageKey);
        if (isset($packageKeyParts[0]) && $packageKeyParts[0] === 'ext' && isset($packageKeyParts[1])) {
            return sprintf('http://php.net/manual-lookup.php?pattern=%s&scope=quickref', urlencode($packageKeyParts[1]));
        }
        $query = new FlowQuery([$this->getNode()]);
        /** @var NodeInterface $packageNode */
        $packageNode = $query->find(sprintf('[instanceof Neos.MarketPlace:Package][title = "%s"]', $packageKey))->get(0);
        if ($packageNode !== null) {
            return $this->linkingService->createNodeUri(
                $this->tsRuntime->getControllerContext(),
                $packageNode,
                $this->getNode()
            );
        }
        return 'https://packagist.org/packages/' . $this->getPackageKey();
    }
}
