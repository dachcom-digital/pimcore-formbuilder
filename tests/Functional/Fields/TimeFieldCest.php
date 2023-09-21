<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class TimeFieldCest extends AbstractFieldCest
{
    protected $type = 'time';

    protected $name = 'simple_time';

    protected $displayName = 'simple_time';

    /**
     * @param FunctionalTester $I
     */
    public function testTimeFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text',
            'label'  => 'Time Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Time Selection', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTimeFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'widget'    => 'single_text',
            'help_text' => 'This is a Helptext'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testTimeFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text'
        ];

        $optionals = [
            'email_label' => 'Time Email Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_time'), '21:45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Time Email Selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTimeFieldWithSingleTextWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_time', ['type' => 'time']);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_time'), '21:45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => '9:45:00 PM']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTimeFieldWithChoiceWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'choice'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_time_hour');
        $I->seeElement('select#formbuilder_1_simple_time_minute');

        $I->selectOption('select#formbuilder_1_simple_time_hour', '21');
        $I->selectOption('select#formbuilder_1_simple_time_minute', '45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => '9:45:00 PM']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTimeFieldWithSecondsAndChoiceWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'choice',
            'with_seconds' => true
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_time_hour');
        $I->seeElement('select#formbuilder_1_simple_time_minute');
        $I->seeElement('select#formbuilder_1_simple_time_second');

        $I->selectOption('select#formbuilder_1_simple_time_hour', '21');
        $I->selectOption('select#formbuilder_1_simple_time_minute', '45');
        $I->selectOption('select#formbuilder_1_simple_time_second', '12');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => '9:45:12 PM']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTimeFieldWithTextWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'text'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_time_hour');
        $I->seeElement('input#formbuilder_1_simple_time_minute');

        $I->fillField('input#formbuilder_1_simple_time_hour', '21');
        $I->fillField('input#formbuilder_1_simple_time_minute', '45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_time' => '9:45:00 PM']);
    }
}
