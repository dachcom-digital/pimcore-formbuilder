<?php

namespace DachcomBundle\Test\Unit\Transformer;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use FormBuilderBundle\Registry\OptionsTransformerRegistry;
use FormBuilderBundle\Transformer\DefaultValueTransformer;
use FormBuilderBundle\Transformer\HrefTransformer;

class DefaultValueTransformTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testTransformWithDefaultValueAndEmptyData()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var DefaultValueTransformer $defaultValueTransformer */
        $defaultValueTransformer = $optionsTransformerRegistry->get(DefaultValueTransformer::class);

        $config = [
            'default_value' => 'DEFAULT_VALUE',
        ];

        $expectedTransformedValues = 'DEFAULT_VALUE';

        $transformedValues = $defaultValueTransformer->transform('', $config);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testTransformWithDefaultValueAndNoData()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var DefaultValueTransformer $defaultValueTransformer */
        $defaultValueTransformer = $optionsTransformerRegistry->get(DefaultValueTransformer::class);

        $config = [
            'default_value' => 'DEFAULT_VALUE',
        ];

        $expectedTransformedValues = 'TEST_VALUE';

        $transformedValues = $defaultValueTransformer->transform('TEST_VALUE', $config);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testReverseTransformWithDefaultValueData()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var HrefTransformer $defaultValueTransformer */
        $defaultValueTransformer = $optionsTransformerRegistry->get(DefaultValueTransformer::class);

        $config = [
            'default_value' => 'DEFAULT_VALUE',
        ];

        $expectedTransformedValues = '';

        $transformedValues = $defaultValueTransformer->reverseTransform('DEFAULT_VALUE', $config);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testReverseTransformWithNoDefaultValueData()
    {
        /** @var OptionsTransformerRegistry $optionsTransformerRegistry */
        $optionsTransformerRegistry = $this->getContainer()->get(OptionsTransformerRegistry::class);
        /** @var HrefTransformer $defaultValueTransformer */
        $defaultValueTransformer = $optionsTransformerRegistry->get(DefaultValueTransformer::class);

        $config = [
            'default_value' => 'DEFAULT_VALUE',
        ];

        $expectedTransformedValues = 'TEST_VALUE';

        $transformedValues = $defaultValueTransformer->reverseTransform('TEST_VALUE', $config);
        $this->assertEquals($expectedTransformedValues, $transformedValues);
    }
}
