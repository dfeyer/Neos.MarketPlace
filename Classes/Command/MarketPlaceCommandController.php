<?php
namespace Neos\MarketPlace\Command;

/*
 * This file is part of the Neos.MarketPlace package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\MarketPlace\Domain\Model\Packages;
use Neos\MarketPlace\Domain\Model\Storage;
use Neos\MarketPlace\Service\PackageImporterInterface;
use Packagist\Api\Client;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Search\Indexer\NodeIndexingManager;

/**
 * MarketPlace Command Controller
 */
class MarketPlaceCommandController extends CommandController
{
    /**
     * @var PackageImporterInterface
     * @Flow\Inject
     */
    protected $importer;

    /**
     * @var NodeIndexingManager
     * @Flow\Inject
     */
    protected $nodeIndexingManager;

    /**
     * @param string $package
     * @param boolean $disableIndexing
     * @return void
     */
    public function syncCommand($package = null, $disableIndexing = false)
    {
        $sync = function() use ($package) {
            $count = 0;
            $this->outputLine('Synchronize with Packagist ...');
            $storage = new Storage();
            $process = function (Package $package) use ($storage, &$count) {
                $count++;
                $this->outputLine(sprintf('  %d/ %s (%s)', $count, $package->getName(), $package->getTime()));
                $this->importer->process($package, $storage);
            };
            if ($package === null) {
                $packages = new Packages();
                foreach ($packages->packages() as $package) {
                    $process($package);
                }
                $this->cleanupPackages($storage);
                $this->cleanupVendors($storage);
            } else {
                $client = new Client();
                $package = $client->get($package);
                $process($package);
            }

            $this->outputLine();
            $this->outputLine(sprintf('%d package(s) imported with success', $this->importer->getProcessedPackagesCount()));
        };

        if ($disableIndexing === true) {
            $this->nodeIndexingManager->withoutIndexing($sync);
        } else {
            $sync();
        }
    }

    /**
     * @param Storage $storage
     */
    protected function cleanupPackages(Storage $storage)
    {
        $this->outputLine();
        $this->outputLine('Cleanup packages ...');
        $count = $this->importer->cleanupPackages($storage, function (NodeInterface $package) {
            $this->outputLine(sprintf('%s deleted', $package->getLabel()));
        });
        if ($count > 0) {
            $this->outputLine(sprintf('  Deleted %d package(s)', $count));
        }
    }

    /**
     * @param Storage $storage
     */
    protected function cleanupVendors(Storage $storage)
    {
        $this->outputLine();
        $this->outputLine('Cleanup vendors ...');
        $count = $this->importer->cleanupVendors($storage, function (NodeInterface $vendor) {
            $this->outputLine(sprintf('%s deleted', $vendor->getLabel()));
        });
        if ($count > 0) {
            $this->outputLine(sprintf('  Deleted %d vendor(s)', $count));
        }
    }
}
