<?php

namespace DachcomBundle\Test\Unit\MailEditor;

use DachcomBundle\Test\Support\Test\DachcomBundleTestCase;
use FormBuilderBundle\MailEditor\Parser\PlaceholderParser;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;

class PlaceholderParserTest extends DachcomBundleTestCase
{
    public function testNewFormFieldContainerRepeaterField(): void
    {
        $placeholder = $this->getContainer()->get(PlaceholderParser::class);

        $config = new FormConfigBuilder('name', '\stdClass',  new EventDispatcher());
        $form = new Form($config);

        $data = $placeholder->replacePlaceholderWithOutputData($this->getTestString(), $form, $this->getTestFields(), 'text');

        $this->assertEquals($this->getExpectedString(), $data);
    }

    private function getTestFields(): array
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
    private function getTestString(): string
    {
        return '<fb-field data-type="fb_field" data-sub_type="last_name" data-render_type="L">last_name</fb-field>: <fb-field data-type="fb_field" data-sub_type="last_name" data-render_type="V">last_name</fb-field><br />
                <fb-field data-type="fb_field" data-sub_type="first_name" data-render_type="L">first_name</fb-field>: <fb-field data-type="fb_field" data-sub_type="first_name" show_label="true" data-render_type="V">first_name</fb-field><br />
                ===<br />
                Date: <fb-field data-type="date" data-sub_type="null" format="d.m.Y">date</fb-field>';
    }

    /**
     * @return string
     */
    private function getExpectedString(): string
    {
        return sprintf('Last Name: The Tester<br />
                First Name: Frank<br />
                ===<br />
                Date: %s', date('d.m.Y'));
    }

}
