<?php
namespace Neos\MarketPlace\Property\TypeConverter;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MarketPlace\Domain\Model\Slug;
use Neos\MarketPlace\Domain\Model\Storage;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Property\Exception\InvalidPropertyMappingConfigurationException;
use TYPO3\Flow\Property\Exception\TypeConverterException;
use TYPO3\Flow\Property\PropertyMappingConfigurationInterface;
use TYPO3\Flow\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;

/**
 * Convert package from packagist to node
 *
 * @api
 */
class PackageConverter extends AbstractTypeConverter
{
    /**
     * @var string
     */
    const STORAGE = 'storage';

    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    /**
     * @var array<string>
     */
    protected $sourceTypes = [Package::class];

    /**
     * @var string
     */
    protected $targetType = NodeInterface::class;

    /**
     * Converts $source to a node
     *
     * @param string|integer|array $source the string to be converted to a \DateTime object
     * @param string $targetType must be "DateTime"
     * @param array $convertedChildProperties not used currently
     * @param PropertyMappingConfigurationInterface $configuration
     * @return NodeInterface
     * @throws TypeConverterException
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        /** @var Package $package */
        $package = $source;
        $storage = $this->getStorage($configuration);
        $vendor = explode('/', $package->getName())[0];
        $identifier = Slug::create($package->getName());
        $vendorNode = $storage->createVendor($vendor);
        $packageNode = $vendorNode->getNode($identifier);
        if ($packageNode === null) {
            $node = $this->create($package, $vendorNode);
        } else {
            $node = $this->update($package, $packageNode);
        }
        return $node;
    }

    /**
     * @param Package $package
     * @param NodeInterface $parentNode
     * @return NodeInterface
     */
    protected function create(Package $package, NodeInterface $parentNode)
    {
        $name = Slug::create($package->getName());
        $nodeTemplate = new NodeTemplate();
        $time = \DateTime::createFromFormat(\DateTime::ATOM, $package->getTime());
        $nodeTemplate->setName($name);
        $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Neos.MarketPlace:Package'));
        $nodeTemplate->setProperty('uriPathSegment', $name);
        $nodeTemplate->setProperty('title', $package->getName());
        $nodeTemplate->setProperty('description', $package->getDescription());
        $nodeTemplate->setProperty('time', $time);
        $nodeTemplate->setProperty('type', $package->getType());
        $nodeTemplate->setProperty('repository', $package->getRepository());
        $nodeTemplate->setProperty('favers', $package->getFavers());
        $node = $parentNode->createNodeFromTemplate($nodeTemplate);
        $this->createOrUpdateMaintainers($package, $node);
        return $node;
    }

    /**
     * @param Package $package
     * @param NodeInterface $node
     * @return NodeInterface
     */
    protected function update(Package $package, NodeInterface $node)
    {
        $this->updateNodeProperties($node, [
            'description' => $package->getDescription(),
            'time' => \DateTime::createFromFormat(\DateTime::ATOM, $package->getTime()),
            'type' => $package->getType(),
            'repository' => $package->getRepository(),
            'favers' => $package->getFavers()
        ]);
        $this->createOrUpdateMaintainers($package, $node);
        return $node;
    }

    /**
     * @param Package $package
     * @param NodeInterface $node
     */
    protected function createOrUpdateMaintainers(Package $package, NodeInterface $node)
    {
        $maintainerStorage = $node->getNode('maintainers');
        foreach ($package->getMaintainers() as $maintainer) {
            /** @var Package\Maintainer $maintainer */
            $name = Slug::create($maintainer->getName());
            $node = $maintainerStorage->getNode($name);
            if ($node === null) {
                $nodeTemplate = new NodeTemplate();
                $nodeTemplate->setNodeType($this->nodeTypeManager->getNodeType('Neos.MarketPlace:Maintainer'));
                $nodeTemplate->setName($name);
                $nodeTemplate->setProperty('title', $maintainer->getName());
                $nodeTemplate->setProperty('email', $maintainer->getEmail());
                $nodeTemplate->setProperty('homepage', $maintainer->getHomepage());
                $maintainerStorage->createNodeFromTemplate($nodeTemplate);
            } else {
                $this->updateNodeProperties($node, [
                    'title' => $maintainer->getName(),
                    'email' => $maintainer->getEmail(),
                    'homepage' => $maintainer->getHomepage()
                ]);
            }
        }
    }

    /**
     * @param NodeInterface $node
     * @param array $data
     */
    protected function updateNodeProperties(NodeInterface $node, array $data)
    {
        foreach ($data as $propertyName => $propertyValue) {
            $this->updateNodeProperty($node, $propertyName, $propertyValue);
        }
    }

    /**
     * @param NodeInterface $node
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    protected function updateNodeProperty(NodeInterface $node, $propertyName, $propertyValue)
    {
        if ($node->getProperties()[$propertyName] === $propertyValue) {
            return;
        }
        $node->setProperty($propertyName, $propertyValue);
    }

    /**
     * Determines the default date format to use for the conversion.
     * If no format is specified in the mapping configuration DEFAULT_DATE_FORMAT is used.
     *
     * @param PropertyMappingConfigurationInterface $configuration
     * @return Storage
     * @throws InvalidPropertyMappingConfigurationException
     */
    protected function getStorage(PropertyMappingConfigurationInterface $configuration = null)
    {
        if ($configuration === null) {
            throw new InvalidPropertyMappingConfigurationException('Missing property configuration', 1457516367);
        }
        $storage = $configuration->getConfigurationValue(PackageConverter::class, self::STORAGE);
        if (!$storage instanceof Storage) {
            throw new InvalidPropertyMappingConfigurationException('Storage must be a NodeInterface instances', 1457516377);
        }
        return $storage;
    }
}
