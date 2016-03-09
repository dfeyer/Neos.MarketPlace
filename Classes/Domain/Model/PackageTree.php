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
 * Package Tree by vendor
 *
 * @api
 */
class PackageTree
{
    /**
     * @var PackageRepository
     * @Flow\Inject
     */
    protected $packageRepository;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="types")
     */
    protected $packageTypes;

    /**
     * @var integer
     */
    protected $count;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return int
     */
    public function count()
    {
        $this->load();
        return $this->count;
    }

    /**
     * @param string $vendor
     * @return \Generator
     */
    public function packages($vendor = null)
    {
        $this->load();
        if ($vendor === null) {
            foreach ($this->data as $packages) {
                foreach ($packages as $package) {
                    yield $package;
                }
            }
        } else {
            if (!isset($this->data[$vendor])) {
                return;
            }
            foreach ($this->data[$vendor] as $package) {
                yield $package;
            }
        }
    }

    /**
     * Load data from packagist and build the tree
     */
    protected function load()
    {
        if ($this->data !== []) {
            return;
        }
        $this->count = 0;
        $packageTypes = array_keys(array_filter($this->packageTypes));
        $this->data = [];
        foreach ($packageTypes as $type) {
            $packages = $this->packageRepository->findByPackageType($type);
            foreach ($packages as $packageKey) {
                $package = $this->packageRepository->findByPackageKey($packageKey);
                $vendor = explode('/', $packageKey)[0];
                if (!isset($this->data[$vendor])) {
                    $this->data[$vendor] = [];
                }
                $this->data[$vendor][$packageKey] = $package;
            }
            $this->count += count($packages);
        }
    }
}
