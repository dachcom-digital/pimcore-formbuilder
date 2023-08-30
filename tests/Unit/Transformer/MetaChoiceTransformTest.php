<?php

namespace DachcomBundle\Test\Unit\Transformer;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Registry\OptionsTransformerRegistry;
use FormBuilderBundle\Transformer\ChoicesMetaTransformer;
use Pimcore\Tests\Util\TestHelper;

class MetaChoiceTransformTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testSimpleTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesMetaTransformer $choicesMetaTransformer */
        $choicesMetaTransformer = $optionsTransformerRegistry->getDynamic(ChoicesMetaTransformer::class);

        $document = TestHelper::createEmptyDocumentPage();

        $rawData = [
            [
                'option'      => 'OPTION1',
                'value'       => 'VALUE1',
                'choice_meta' => json_encode([
                    'tooltip' => 'MY_TOOLTIP'
                ]),
            ],
            [
                'option'      => 'OPTION2',
                'value'       => 'VALUE2',
                'choice_meta' => json_encode([
                    'tooltip'     => 'MY_TOOLTIP',
                    'relation.en' => [
                        'id'      => $document->getId(),
                        'path'    => $document->getFullPath(),
                        'type'    => 'document',
                        'subtype' => $document->getType()
                    ]
                ]),
            ]
        ];

        $transformedData = [
            'optionA' => 'valueA',
            'optionB' => 'valueB',
        ];

        $expectedTransformedValues = [
            'OPTION1' => [
                'data-meta-tooltip' => 'MY_TOOLTIP'
            ],
            'OPTION2' => [
                'data-meta-tooltip'            => 'MY_TOOLTIP',
                'data-meta-relation-en-id'     => $document->getId(),
                'data-meta-relation-en-type'   => 'document',
                'data-meta-relation-en-locale' => 'en',
            ],
        ];

        $transformedValues = $choicesMetaTransformer->transform($rawData, $transformedData);

        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testGroupedTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesMetaTransformer $choicesMetaTransformer */
        $choicesMetaTransformer = $optionsTransformerRegistry->getDynamic(ChoicesMetaTransformer::class);

        $document = TestHelper::createEmptyDocumentPage();

        $rawData = [
            [
                [
                    'name'        => 'G1',
                    'option'      => 'OPTION1',
                    'value'       => 'VALUE1',
                    'choice_meta' => json_encode([
                        'tooltip' => 'MY_TOOLTIP'
                    ]),
                ],
                [
                    'name'        => 'G1',
                    'option'      => 'OPTION2',
                    'value'       => 'VALUE2',
                    'choice_meta' => json_encode([
                        'tooltip'     => 'MY_TOOLTIP',
                        'relation.en' => [
                            'id'      => $document->getId(),
                            'path'    => $document->getFullPath(),
                            'type'    => 'document',
                            'subtype' => $document->getType()
                        ]
                    ]),
                ]
            ],
            [
                [
                    'name'        => 'G2',
                    'option'      => 'OPTION3',
                    'value'       => 'VALUE3',
                    'choice_meta' => json_encode([
                        'tooltip'     => 'MY_OTHER_TOOLTIP',
                        'relation.de' => [
                            'id'      => $document->getId(),
                            'path'    => $document->getFullPath(),
                            'type'    => 'document',
                            'subtype' => $document->getType()
                        ]
                    ]),
                ],
            ]
        ];

        $transformedData = [
            'G1' => [
                'OPTION1' => 'VALUE1',
                'OPTION2' => 'VALUE2'
            ],
            'G2' => [
                'OPTION3' => 'VALUE3'
            ],
        ];

        $expectedTransformedValues = [
            'OPTION1' => [
                'data-meta-tooltip' => 'MY_TOOLTIP'
            ],
            'OPTION2' => [
                'data-meta-tooltip'            => 'MY_TOOLTIP',
                'data-meta-relation-en-id'     => $document->getId(),
                'data-meta-relation-en-type'   => 'document',
                'data-meta-relation-en-locale' => 'en',
            ],
            'OPTION3' => [
                'data-meta-tooltip'            => 'MY_OTHER_TOOLTIP',
                'data-meta-relation-de-id'     => $document->getId(),
                'data-meta-relation-de-type'   => 'document',
                'data-meta-relation-de-locale' => 'de',
            ],
        ];

        $transformedValues = $choicesMetaTransformer->transform($rawData, $transformedData);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testSimpleReverseTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesMetaTransformer $choicesMetaTransformer */
        $choicesMetaTransformer = $optionsTransformerRegistry->getDynamic(ChoicesMetaTransformer::class);

        $document1 = TestHelper::createEmptyDocumentPage();
        $document2 = TestHelper::createEmptyDocumentPage();

        $rawData = [
            'OPTION1' => [
                'data-meta-tooltip'            => 'MY_TOOLTIP',
                'data-meta-relation-en-id'     => $document1->getId(),
                'data-meta-relation-en-type'   => 'document',
                'data-meta-relation-en-locale' => 'en',
            ],
            'OPTION2' => [
                'data-meta-tooltip'            => 'MY_SECOND_TOOLTIP',
                'data-meta-relation-de-id'     => $document2->getId(),
                'data-meta-relation-de-type'   => 'document',
                'data-meta-relation-de-locale' => 'de',
            ]
        ];

        $transformedData = [
            [
                'option' => 'OPTION1',
                'value'  => 'VALUE1',
            ],
            [
                'option' => 'OPTION2',
                'value'  => 'VALUE2',
            ]
        ];

        $expectedTransformedValues = [
            [
                'option'      => 'OPTION1',
                'value'       => 'VALUE1',
                'choice_meta' => json_encode([
                    'tooltip'     => 'MY_TOOLTIP',
                    'relation.en' => [
                        'id'      => $document1->getId(),
                        'path'    => $document1->getFullPath(),
                        'type'    => 'document',
                        'subtype' => $document1->getType()
                    ]
                ]),
            ],
            [
                'option'      => 'OPTION2',
                'value'       => 'VALUE2',
                'choice_meta' => json_encode([
                    'tooltip'     => 'MY_SECOND_TOOLTIP',
                    'relation.de' => [
                        'id'      => $document2->getId(),
                        'path'    => $document2->getFullPath(),
                        'type'    => 'document',
                        'subtype' => $document2->getType()
                    ]
                ]),
            ]
        ];

        $transformedValues = $choicesMetaTransformer->reverseTransform($rawData, $transformedData);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testGroupedReverseTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesMetaTransformer $choicesMetaTransformer */
        $choicesMetaTransformer = $optionsTransformerRegistry->getDynamic(ChoicesMetaTransformer::class);

        $document1 = TestHelper::createEmptyDocumentPage();
        $document2 = TestHelper::createEmptyDocumentPage();

        $rawData = [
            'OPTION1' => [
                'data-meta-tooltip'            => 'MY_TOOLTIP',
            ],
            'OPTION2' => [
                'data-meta-tooltip'            => 'MY_SECOND_TOOLTIP',
                'data-meta-relation-en-id'     => $document1->getId(),
                'data-meta-relation-en-type'   => 'document',
                'data-meta-relation-en-locale' => 'en',
            ],
            'OPTION3' => [
                'data-meta-tooltip'            => 'MY_THIRD_TOOLTIP',
                'data-meta-relation-de-id'     => $document2->getId(),
                'data-meta-relation-de-type'   => 'document',
                'data-meta-relation-de-locale' => 'de',
            ]
        ];

        $transformedData = [
            [
                [
                    'name'   => 'G1',
                    'option' => 'OPTION1',
                    'value'  => 'VALUE1',
                ],
                [
                    'name'   => 'G1',
                    'option' => 'OPTION2',
                    'value'  => 'VALUE2',
                ]
            ],
            [
                [
                    'name'   => 'G2',
                    'option' => 'OPTION3',
                    'value'  => 'VALUE3',
                ]
            ]
        ];

        $expectedTransformedValues = [
            [
                [
                    'name'        => 'G1',
                    'option'      => 'OPTION1',
                    'value'       => 'VALUE1',
                    'choice_meta' => json_encode([
                        'tooltip' => 'MY_TOOLTIP'
                    ]),
                ],
                [
                    'name'        => 'G1',
                    'option'      => 'OPTION2',
                    'value'       => 'VALUE2',
                    'choice_meta' => json_encode([
                        'tooltip'     => 'MY_SECOND_TOOLTIP',
                        'relation.en' => [
                            'id'      => $document1->getId(),
                            'path'    => $document1->getFullPath(),
                            'type'    => 'document',
                            'subtype' => $document1->getType()
                        ]
                    ]),
                ]
            ],
            [
                [
                    'name'        => 'G2',
                    'option'      => 'OPTION3',
                    'value'       => 'VALUE3',
                    'choice_meta' => json_encode([
                        'tooltip'     => 'MY_THIRD_TOOLTIP',
                        'relation.de' => [
                            'id'      => $document2->getId(),
                            'path'    => $document2->getFullPath(),
                            'type'    => 'document',
                            'subtype' => $document2->getType()
                        ]
                    ]),
                ],
            ]
        ];

        $transformedValues = $choicesMetaTransformer->reverseTransform($rawData, $transformedData);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }
}
