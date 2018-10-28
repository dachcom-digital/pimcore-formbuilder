<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;

class DateTimeFieldCest extends AbstractFieldCest
{
    protected $type = 'date_time';

    protected $name = 'simple_date_time';

    protected $displayName = 'simple_date_time';

    /**
     * @param FunctionalTester $I
     */
    public function testDateTimeFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'label' => 'Date Time Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Date Time Selection', 'label');
    }

    /**
     * Help Text is not implemented!
     *
     * @param FunctionalTester $I
     */
    private function testDateTimeFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'help_text'   => 'This is a Helptext'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateTimeFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ];

        $optionals = [
            'email_label' => 'Date Time Email Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField('input#formbuilder_1_simple_date_time_date', '1983-06-21');
        $I->fillField('input#formbuilder_1_simple_date_time_time', '21:45');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Date Time Email Selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateTimeFieldWithSingleTextWidget(FunctionalTester $I)
    {
        $options = [
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_date_time_date', ['type' => 'date']);
        $I->seeElement('input#formbuilder_1_simple_date_time_time', ['type' => 'time']);

        $I->fillField('input#formbuilder_1_simple_date_time_date', '1983-06-21');
        $I->fillField('input#formbuilder_1_simple_date_time_time', '21:45');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_date_time' => 'Jun 21, 1983, 9:45:00 PM']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateTimeFieldWithChoiceWidget(FunctionalTester $I)
    {
        $options = [
            'date_widget' => 'choice',
            'time_widget' => 'choice',
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_date_time_date_month');
        $I->seeElement('select#formbuilder_1_simple_date_time_date_day');
        $I->seeElement('select#formbuilder_1_simple_date_time_date_year');

        $I->seeElement('select#formbuilder_1_simple_date_time_time_hour');
        $I->seeElement('select#formbuilder_1_simple_date_time_time_minute');

        $I->selectOption('select#formbuilder_1_simple_date_time_date_month', '6');
        $I->selectOption('select#formbuilder_1_simple_date_time_date_day', '21');
        $I->selectOption('select#formbuilder_1_simple_date_time_date_year', date('Y'));

        $I->selectOption('select#formbuilder_1_simple_date_time_time_hour', '21');
        $I->selectOption('select#formbuilder_1_simple_date_time_time_minute', '45');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_date_time' => sprintf('Jun 21, %s, 9:45:00 PM', date('Y'))]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateTimeFieldWithSecondsAndChoiceWidget(FunctionalTester $I)
    {
        $options = [
            'date_widget' => 'choice',
            'time_widget' => 'choice',
            'with_seconds' => true
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_date_time_date_month');
        $I->seeElement('select#formbuilder_1_simple_date_time_date_day');
        $I->seeElement('select#formbuilder_1_simple_date_time_date_year');

        $I->seeElement('select#formbuilder_1_simple_date_time_time_hour');
        $I->seeElement('select#formbuilder_1_simple_date_time_time_minute');
        $I->seeElement('select#formbuilder_1_simple_date_time_time_second');

        $I->selectOption('select#formbuilder_1_simple_date_time_date_month', '6');
        $I->selectOption('select#formbuilder_1_simple_date_time_date_day', '21');
        $I->selectOption('select#formbuilder_1_simple_date_time_date_year', date('Y'));

        $I->selectOption('select#formbuilder_1_simple_date_time_time_hour', '21');
        $I->selectOption('select#formbuilder_1_simple_date_time_time_minute', '45');
        $I->selectOption('select#formbuilder_1_simple_date_time_time_second', '12');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_date_time' => sprintf('Jun 21, %s, 9:45:12 PM', date('Y'))]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateTimeFieldWithTextWidget(FunctionalTester $I)
    {
        $options = [
            'date_widget' => 'text',
            'time_widget' => 'text',
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_date_time_date_month');
        $I->seeElement('input#formbuilder_1_simple_date_time_date_day');
        $I->seeElement('input#formbuilder_1_simple_date_time_date_year');

        $I->seeElement('input#formbuilder_1_simple_date_time_time_hour');
        $I->seeElement('input#formbuilder_1_simple_date_time_time_minute');

        $I->fillField('input#formbuilder_1_simple_date_time_date_month', '6');
        $I->fillField('input#formbuilder_1_simple_date_time_date_day', '21');
        $I->fillField('input#formbuilder_1_simple_date_time_date_year', date('Y'));

        $I->fillField('input#formbuilder_1_simple_date_time_time_hour', '21');
        $I->fillField('input#formbuilder_1_simple_date_time_time_minute', '45');

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_date_time' => sprintf('Jun 21, %s, 9:45:00 PM', date('Y'))]);
    }
}
