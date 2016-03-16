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

use Neos\MarketPlace\Domain\Repository\PackageRepository;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;

/**
 * Packages
 *
 * @api
 */
class Packages
{
    /**
     * @var PackageRepository
     * @Flow\Inject
     */
    protected $packageRepository;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="typeMapping")
     */
    protected $packageTypes;

    /**
     * @return \Generator
     */
    public function packages()
    {
        $packageTypes = array_keys(array_filter($this->packageTypes));
        foreach ($packageTypes as $type) {
            $packages = $this->packageRepository->findByPackageType($type);
            foreach ($packages as $packageKey) {
                $package = $this->packageRepository->findByPackageKey($packageKey);
                yield $package;
            }
        }
    }
}
