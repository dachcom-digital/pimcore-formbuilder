<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class DateFieldCest extends AbstractFieldCest
{
    protected $type = 'date';

    protected $name = 'simple_date';

    protected $displayName = 'simple_date';

    /**
     * @param FunctionalTester $I
     */
    public function testDateFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text',
            'label'  => 'Date Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Date Selection', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text',
            'help_text' => 'This is a Helptext'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text'
        ];

        $optionals = [
            'email_label' => 'Date Email Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_date'), '1983-06-21');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Date Email Selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateFieldWithSingleTextWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_date', ['type' => 'date']);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_date'), '1983-06-21');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_date' => 'Jun 21, 1983']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateFieldWithChoiceWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'choice'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_date_month');
        $I->seeElement('select#formbuilder_1_simple_date_day');
        $I->seeElement('select#formbuilder_1_simple_date_year');

        $I->selectOption('select#formbuilder_1_simple_date_month', '6');
        $I->selectOption('select#formbuilder_1_simple_date_day', '21');
        $I->selectOption('select#formbuilder_1_simple_date_year', date('Y'));
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_date' => sprintf('Jun 21, %s', date('Y'))]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testDateFieldWithTextWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'text'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_date_month');
        $I->seeElement('input#formbuilder_1_simple_date_day');
        $I->seeElement('input#formbuilder_1_simple_date_year');

        $I->fillField('input#formbuilder_1_simple_date_month', '6');
        $I->fillField('input#formbuilder_1_simple_date_day', '21');
        $I->fillField('input#formbuilder_1_simple_date_year', date('Y'));
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_date' => sprintf('Jun 21, %s', date('Y'))]);
    }
}
