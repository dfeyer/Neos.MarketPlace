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

use TYPO3\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Eel;
use TYPO3\Flow\Utility\Arrays;

/**
 * ElasticSearchQueryBuilder
 */
class ElasticSearchQueryBuilder extends Eel\ElasticSearchQueryBuilder
{
    /**
     * @return array
     */
    public function getRequest()
    {
        $request = parent::getRequest();
        $request = $this->enforeFunctionScoring($request);
        return $request;
    }

    /**
     * @param array $request
     * @return array
     */
    protected function enforeFunctionScoring(array $request)
    {
        $request['query'] = [
            'function_score' => [
                'functions' => [
                    [
                        'filter' => [
                            'term' => [
                                '__typeAndSupertypes' => 'Neos.MarketPlace:Vendor'
                            ],
                        ],
                        'weight' => 1.2
                    ],
                    [
                        'script_score' => [
                            "script" => "(0.08 / ((3.16*pow(10,-11)) * abs(DateTime.now().getMillis() - doc['__versions.time'].date.getMillis()) + 0.05)) + 1.0"
                        ]
                    ]
                ],
                'query' => $request['query']
            ]
        ];
        return $request;
    }

}
