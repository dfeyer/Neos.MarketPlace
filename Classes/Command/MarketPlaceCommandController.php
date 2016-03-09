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

use Neos\MarketPlace\Domain\Model\PackageTree;
use Neos\MarketPlace\Domain\Model\Storage;
use Neos\MarketPlace\Service\PackageImporterInterface;
use Packagist\Api\Client;
use Packagist\Api\Result\Package;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

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
     * @param string $package
     * @return void
     */
    public function syncCommand($package = null)
    {
        $storage = new Storage();
        if ($package === null) {
            $packageTree = new PackageTree();
            $this->outputLine('Synchronize with Packagist ...');
            foreach ($packageTree->packages() as $package) {
                $this->outputLine(sprintf('  - %s (%s)', $package->getName(), $package->getTime()));
                $this->importer->process($package, $storage);
            }
            $this->outputLine();
            $this->outputLine(sprintf('%d packages imported with success', $packageTree->count()));
        } else {
            $client = new Client();
            $package = $client->get($package);
            \TYPO3\Flow\var_dump($package);
            $this->importer->process($package, $storage);
            $this->outputLine(sprintf('Package "%s" imported with success', $package->getName()));
        }
    }

}
