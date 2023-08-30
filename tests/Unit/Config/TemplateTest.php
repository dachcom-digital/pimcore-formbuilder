<?php

namespace DachcomBundle\Test\Unit\Config;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\Configuration\Configuration;

class TemplateTest extends DachcomBundleTestCase
{
    public function testFormTemplates()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $formConfig = $configuration->getConfig('form');

        $this->assertArrayHasKey('templates', $formConfig);
    }

    public function testFormTemplatesConfig()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $formConfig = $configuration->getConfig('form');

        $this->assertArrayHasKey('form_div_layout', $formConfig['templates']);
        $this->assertArrayHasKey('bootstrap_3_layout', $formConfig['templates']);
        $this->assertArrayHasKey('bootstrap_3_horizontal_layout', $formConfig['templates']);
        $this->assertArrayHasKey('bootstrap_4_layout', $formConfig['templates']);
        $this->assertArrayHasKey('bootstrap_4_horizontal_layout', $formConfig['templates']);
        $this->assertCount(5, $formConfig['templates']);
    }

    public function testFormFieldTemplates()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $formConfig = $configuration->getConfig('form');
        $fieldConfig = $formConfig['field'];

        $this->assertArrayHasKey('templates', $fieldConfig);
    }

    public function testFormFieldTemplatesConfig()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $formConfig = $configuration->getConfig('form');
        $fieldConfig = $formConfig['field'];

        $this->assertArrayHasKey('default', $fieldConfig['templates']);
        $this->assertArrayHasKey('full', $fieldConfig['templates']);
        $this->assertArrayHasKey('half', $fieldConfig['templates']);
    }

    public function testFormFieldTemplatesDefaultConfig()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $formConfig = $configuration->getConfig('form');
        $fieldConfig = $formConfig['field']['templates']['default'];

        $this->assertEquals('default', $fieldConfig['value']);
        $this->assertEquals('form_builder_type_template.default', $fieldConfig['label']);
        $this->assertEquals(false, $fieldConfig['default']);
    }

    public function testFormFieldTemplatesFullConfig()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $formConfig = $configuration->getConfig('form');
        $fieldConfig = $formConfig['field']['templates']['full'];

        $this->assertEquals('col-12', $fieldConfig['value']);
        $this->assertEquals('form_builder_type_template.full', $fieldConfig['label']);
        $this->assertEquals(true, $fieldConfig['default']);
    }

    public function testFormFieldTemplatesHalfConfig()
    {
        $configuration = $this->getContainer()->get(Configuration::class);
        $formConfig = $configuration->getConfig('form');
        $fieldConfig = $formConfig['field']['templates']['half'];

        $this->assertEquals('col-6', $fieldConfig['value']);
        $this->assertEquals('form_builder_type_template.half', $fieldConfig['label']);
        $this->assertEquals(false, $fieldConfig['default']);
    }

}
