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
        return $node;
    }
}
