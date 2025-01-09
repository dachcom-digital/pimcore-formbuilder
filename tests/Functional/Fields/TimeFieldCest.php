<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class TimeFieldCest extends AbstractFieldCest
{
    protected $type = 'time';
    protected $name = 'simple_time';
    protected $displayName = 'simple_time';

    public function testTimeFieldOptionsWithLabel(FunctionalTester $I): void
    {
        $options = [
            'widget' => 'single_text',
            'label'  => 'Time Selection'
        ];

        [$adminEmail, $testFormBuilder, $form] = $this->setupField($I, $options);

        $I->see('Time Selection', 'label');
    }

    public function testTimeFieldOptionsWithHelpText(FunctionalTester $I): void
    {
        $options = [
            'widget'    => 'single_text',
            'help_text' => 'This is a Helptext'
        ];

        [$adminEmail, $testFormBuilder, $form] = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    public function testTimeFieldOptionalsWithEmailLabel(FunctionalTester $I): void
    {
        $options = [
            'widget' => 'single_text'
        ];

        $optionals = [
            'email_label' => 'Time Email Selection'
        ];

        [$adminEmail, $testFormBuilder, $form] = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_time'), '21:45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Time Email Selection');
    }

    public function testTimeFieldWithSingleTextWidget(FunctionalTester $I): void
    {
        $options = [
            'widget' => 'single_text'
        ];

        [$adminEmail, $testFormBuilder, $form] = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_time', ['type' => 'time']);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_time'), '21:45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => sprintf('9:45:00%sPM', "\u{202F}")]);
    }

    public function testTimeFieldWithChoiceWidget(FunctionalTester $I): void
    {
        $options = [
            'widget' => 'choice'
        ];

        [$adminEmail, $testFormBuilder, $form] = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_time_hour');
        $I->seeElement('select#formbuilder_1_simple_time_minute');

        $I->selectOption('select#formbuilder_1_simple_time_hour', '21');
        $I->selectOption('select#formbuilder_1_simple_time_minute', '45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => sprintf('9:45:00%sPM', "\u{202F}")]);
    }

    public function testTimeFieldWithSecondsAndChoiceWidget(FunctionalTester $I): void
    {
        $options = [
            'widget' => 'choice',
            'with_seconds' => true
        ];

        [$adminEmail, $testFormBuilder, $form] = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_time_hour');
        $I->seeElement('select#formbuilder_1_simple_time_minute');
        $I->seeElement('select#formbuilder_1_simple_time_second');

        $I->selectOption('select#formbuilder_1_simple_time_hour', '21');
        $I->selectOption('select#formbuilder_1_simple_time_minute', '45');
        $I->selectOption('select#formbuilder_1_simple_time_second', '12');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => sprintf('9:45:12%sPM', "\u{202F}")]);
    }

    public function testTimeFieldWithTextWidget(FunctionalTester $I): void
    {
        $options = [
            'widget' => 'text'
        ];

        [$adminEmail, $testFormBuilder, $form] = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_time_hour');
        $I->seeElement('input#formbuilder_1_simple_time_minute');

        $I->fillField('input#formbuilder_1_simple_time_hour', '21');
        $I->fillField('input#formbuilder_1_simple_time_minute', '45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => sprintf('9:45:00%sPM', "\u{202F}")]);
    }
}
