<?php
namespace Neos\MarketPlace\Eel;

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
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Search\Eel;

/**
 * IndexingHelper
 */
class IndexingHelper extends Eel\IndexingHelper
{

    /**
     * @var array
     * @Flow\InjectConfiguration(path="typeMapping")
     */
    protected $packageTypes;

    /**
     * @param string $packageType
     * @return string
     */
    public function packageTypeMapping($packageType)
    {
        if (isset($this->packageTypes[$packageType])) {
            return $this->packageTypes[$packageType];
        }
        return $packageType;
    }

    /**
     * @param NodeInterface $node
     * @return array
     */
    public function extractVersions(NodeInterface $node)
    {
        $data = [];
        $query = new FlowQuery([$node]);
        $query = $query
            ->find('versions')
            ->find('[instanceof Neos.MarketPlace:Version]');

        foreach ($query as $versionNode) {
            /** @var \DateTime $time */
            $time = $versionNode->getProperty('time');
            /** @var NodeInterface $versionNode */
            $data[] = [
                'name' => $versionNode->getProperty('name'),
                'description' => $versionNode->getProperty('description'),
                'keywords' => $this->trimExplode($versionNode->getProperty('keywords')),
                'homepage' => $versionNode->getProperty('homepage'),
                'version' => $versionNode->getProperty('version'),
                'versionNormalized' => $versionNode->getProperty('versionNormalized'),
                'stability' => $versionNode->getProperty('stability'),
                'stabilityLevel' => $versionNode->getProperty('stabilityLevel'),
                'time' => $time ? $time->format('Y-m-d\TH:i:sP') : null,
                'timestamp' => $time ? $time->getTimestamp() : 0,
            ];
        }

        return $data;
    }

    /**
     * @param NodeInterface $node
     * @return array
     */
    public function extractLastVersion(NodeInterface $node)
    {
        $versions = $this->extractVersions($node);
        usort($versions, function($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });
        $stableVersion = array_filter($versions, function($version) {
            return $version['stability'] === true;
        });
        if (count($stableVersion) > 0) {
            return reset($stableVersion);
        } else {
            return reset($versions);
        }
    }

    /**
     * @param NodeInterface $node
     * @return array
     */
    public function extractMaintainers(NodeInterface $node)
    {
        $data = [];
        $query = new FlowQuery([$node]);
        $query = $query
            ->find('maintainers')
            ->find('[instanceof Neos.MarketPlace:Maintainer]');

        foreach ($query as $maintainerNode) {
            /** @var NodeInterface $maintainerNode */
            $data[] = [
                'name' => $maintainerNode->getProperty('title'),
                'email' => $maintainerNode->getProperty('email'),
                'homepage' => $maintainerNode->getProperty('homepage')
            ];
        }

        return $data;
    }

    /**
     * @param string $value
     * @return array
     */
    public function trimExplode($value)
    {
        return array_filter(array_map('trim', explode(',', $value)));
    }

}
