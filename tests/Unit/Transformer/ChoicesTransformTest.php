<?php

namespace DachcomBundle\Test\Unit\Transformer;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use FormBuilderBundle\Registry\OptionsTransformerRegistry;
use FormBuilderBundle\Transformer\ChoicesTransformer;

class ChoicesTransformTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testSimpleTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesTransformer $choicesTransformer */
        $choicesTransformer = $optionsTransformerRegistry->get(ChoicesTransformer::class);

        $data = [
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
            'OPTION1' => 'VALUE1',
            'OPTION2' => 'VALUE2',
        ];

        $transformedValues = $choicesTransformer->transform($data);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testGroupedTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesTransformer $choicesTransformer */
        $choicesTransformer = $optionsTransformerRegistry->get(ChoicesTransformer::class);

        $data = [
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
            'G1' => [
                'OPTION1' => 'VALUE1',
                'OPTION2' => 'VALUE2'
            ],
            'G2' => [
                'OPTION3' => 'VALUE3'
            ],
        ];

        $transformedValues = $choicesTransformer->transform($data);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testSimpleReverseTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesTransformer $choicesTransformer */
        $choicesTransformer = $optionsTransformerRegistry->get(ChoicesTransformer::class);

        $data = [
            'OPTION1' => 'VALUE1',
            'OPTION2' => 'VALUE2',
        ];

        $expectedTransformedValues = [
            [
                'option' => 'OPTION1',
                'value'  => 'VALUE1',
            ],
            [
                'option' => 'OPTION2',
                'value'  => 'VALUE2',
            ]
        ];

        $transformedValues = $choicesTransformer->reverseTransform($data);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testGroupedReverseTransform()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var ChoicesTransformer $choicesTransformer */
        $choicesTransformer = $optionsTransformerRegistry->get(ChoicesTransformer::class);

        $data = [
            'G1' => [
                'OPTION1' => 'VALUE1',
                'OPTION2' => 'VALUE2'
            ],
            'G2' => [
                'OPTION3' => 'VALUE3'
            ],
        ];

        $expectedTransformedValues = [
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

        $transformedValues = $choicesTransformer->reverseTransform($data);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }
}
