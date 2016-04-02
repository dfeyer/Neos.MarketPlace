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

use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Eel;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Arrays;

/**
 * ElasticSearchQueryBuilder
 */
class ElasticSearchQueryBuilder extends Eel\ElasticSearchQueryBuilder
{
    /**
     * @var boolean
     */
    protected $hasFulltext = false;

    /**
     * @return array
     */
    public function getRequest()
    {
        $this->skipAbandonnedPackages();
        $request = parent::getRequest();
        $request = $this->enforeFunctionScoring($request);
        return $request;
    }

    /**
     * @param string $searchWord
     * @return $this
     */
    public function fulltext($searchWord)
    {
        $searchWord = trim($searchWord);
        if ($searchWord === '') {
            return $this;
        }
        $this->hasFulltext = true;

        $this->request = Arrays::setValueByPath($this->request, 'query.filtered.query.bool.must', []);
        $this->request = Arrays::setValueByPath($this->request, 'query.filtered.query.bool.should', []);
        $this->request = Arrays::setValueByPath($this->request, 'query.filtered.query.bool.minimum_should_match', 1);
        $this->appendAtPath('query.filtered.query.bool.should', [
            [
                'query_string' => [
                    'fields' => [
                        'title^10',
                        '__composerVendor^5',
                        '__maintainers.name^5',
                        '__maintainers.tag^8',
                        'description^2',
                        '__lastVersion.keywords.name^10',
                        '__lastVersion.keywords.tag^12',
                        '__fulltext.*'
                    ],
                    'query' => str_replace('/', '\\/', $searchWord),
                    'default_operator' => 'AND',
                    'use_dis_max' => true
                ]
            ]
        ]);

        return $this;
    }

    /**
     * return void
     */
    protected function skipAbandonnedPackages()
    {
        $this->appendAtPath('query.filtered.filter.bool.must_not', [
            'exists' => [
                'field' => 'abandoned'
            ]
        ]);
    }

    /**
     * @param array $request
     * @return array
     */
    protected function enforeFunctionScoring(array $request)
    {
        if ($this->hasFulltext !== false) {
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
                            'field_value_factor' => [
                                'field' => 'downloadDaily',
                                'factor' => 0.5,
                                'modifier' => 'sqrt',
                                'missing' => 1
                            ]
                        ],
                        [
                            'field_value_factor' => [
                                'field' => 'githubStargazers',
                                'factor' => 1,
                                'modifier' => 'sqrt',
                                'missing' => 1
                            ]
                        ],
                        [
                            'field_value_factor' => [
                                'field' => 'githubForks',
                                'factor' => 0.5,
                                'modifier' => 'sqrt',
                                'missing' => 1
                            ]
                        ],
                        [
                            'gauss' => [
                                '__lastVersion.time' => [
                                    'scale' => '60d',
                                    'offset' => '5d',
                                    'decay' => 0.5
                                ]
                            ]
                        ]
                    ],
                    'score_mode' => 'avg',
                    'boost_mode' => 'multiply',
                    'query' => $request['query']
                ]
            ];
        }
        return $request;
    }

}
