<?php

namespace DachcomBundle\Test\unit\MailEditor;

use DachcomBundle\Test\Test\DachcomBundleTestCase;
use FormBuilderBundle\MailEditor\Parser\PlaceholderParser;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;

class PlaceholderParserTest extends DachcomBundleTestCase
{
    /**
     * @throws \Codeception\Exception\ModuleException
     */
    public function testNewFormFieldContainerRepeaterField()
    {
        $placeholder = $this->getContainer()->get(PlaceholderParser::class);

        $config = new FormConfigBuilder('name', '\stdClass',  new EventDispatcher());
        $form = new Form($config);

        $data = $placeholder->replacePlaceholderWithOutputData($this->getTestString(), $form, $this->getTestFields());

        $this->assertEquals($this->getExpectedString(), $data);
    }

    /**
     * @return array
     */
    private function getTestFields()
    {
        return [
            'last_name'  => [
                'value' => 'The Tester',
                'label' => 'Last Name'
            ],
            'first_name' => [
                'value' => 'Frank',
                'label' => 'First Name'
            ]
        ];
    }

    /**
     * @return string
     */
    private function getTestString()
    {
        return '[[fb_field sub-type="last_name"]]<br />
                [[fb_field sub-type="first_name" show_label="true"]]<br />
                ===<br />
                Date: [[date format="d.m.Y"]]';
    }

    /**
     * @return string
     */
    private function getExpectedString()
    {
        return sprintf('Last Name: The Tester<br />
                First Name: Frank<br />
                ===<br />
                Date: %s', date('d.m.Y'));
    }

}
