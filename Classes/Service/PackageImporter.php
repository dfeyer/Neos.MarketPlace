<?php
namespace Neos\MarketPlace\Service;

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
use Neos\MarketPlace\Domain\Repository\PackageRepository;
use Neos\MarketPlace\Property\TypeConverter\PackageConverter;
use Packagist\Api\Result\Package;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Property\PropertyMapper;
use TYPO3\Flow\Property\PropertyMappingConfigurationBuilder;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * Package Importer
 *
 * @Flow\Scope("singleton")
 * @api
 */
class PackageImporter implements PackageImporterInterface
{
    /**
     * @var PackageRepository
     * @Flow\Inject
     */
    protected $packageRepository;

    /**
     * @var PropertyMappingConfigurationBuilder
     * @Flow\Inject
     */
    protected $configurationBuilder;

    /**
     * @var PropertyMapper
     * @Flow\Inject
     */
    protected $propertyMapper;

    /**
     * @var array
     */
    protected $processedPackages = [];

    /**
     * @param Package $package
     * @param Storage $storage
     * @return NodeInterface
     */
    public function process(Package $package, Storage $storage)
    {
        $configuration = $this->configurationBuilder->build();
        $configuration->setTypeConverterOption(
            PackageConverter::class,
            PackageConverter::STORAGE,
            $storage
        );
        $node = $this->propertyMapper->convert($package, NodeInterface::class, $configuration);
        $this->processedPackages[$package->getName()] = true;
        return $node;
    }

    /**
     * Remove local package not preset in the processed packages list
     *
     * @param Storage $storage
     * @return integer
     */
    public function cleanupPackages(Storage $storage)
    {
        $count = 0;
        $storageNode = $storage->node();
        $query = new FlowQuery([$storageNode]);
        $query = $query->find('[instanceof Neos.MarketPlace:Package]');
        $upstreamPackages = $this->getProcessedPackages();
        foreach ($query as $package) {
            /** @var NodeInterface $package */
            if (in_array($package->getProperty('title'), $upstreamPackages)) {
                continue;
            }
            $package->remove();
            $this->emitPackageDeleted($package);
            $count++;
        }
        return $count;
    }

    /**
     * Remove vendors without packages
     *
     * @param Storage $storage
     * @return integer
     */
    public function cleanupVendors(Storage $storage)
    {
        $count = 0;
        $storageNode = $storage->node();
        $query = new FlowQuery([$storageNode]);
        $query = $query->find('[instanceof Neos.MarketPlace:Vendor]');
        foreach ($query as $vendor) {
            /** @var NodeInterface $vendor */
            $hasPackageQuery = new FlowQuery([$vendor]);
            $packageCount = $hasPackageQuery->find('[instanceof Neos.MarketPlace:Package]')->count();
            if ($packageCount > 0) {
                continue;
            }
            $vendor->remove();
            $this->emitVendorDeleted($vendor);
            $count++;
        }
        return $count;
    }

    /**
     * @return array
     */
    public function getProcessedPackages() {
        return array_keys(array_filter($this->processedPackages));
    }

    /**
     * @return integer
     */
    public function getProcessedPackagesCount() {
        return count($this->getProcessedPackages());
    }

    /**
     * Signals that a package node was deleted.
     *
     * @Flow\Signal
     * @param NodeInterface $node
     * @return void
     */
    protected function emitPackageDeleted(NodeInterface $node)
    {
    }

    /**
     * Signals that a package node was deleted.
     *
     * @Flow\Signal
     * @param NodeInterface $node
     * @return void
     */
    protected function emitVendorDeleted(NodeInterface $node)
    {
    }
}
