<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class BirthdayFieldCest extends AbstractFieldCest
{
    protected $type = 'birthday';

    protected $name = 'simple_birthday';

    protected $displayName = 'simple_birthday';

    /**
     * @param FunctionalTester $I
     */
    public function testBirthdayFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text',
            'label'  => 'Birthday Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Birthday Selection', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testBirthdayFieldOptionsWithHelpText(FunctionalTester $I)
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
    public function testBirthdayFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text'
        ];

        $optionals = [
            'email_label' => 'Birthday Email Selection'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_birthday'), '1983-06-21');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Birthday Email Selection');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testBirthdayFieldWithSingleTextWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'single_text'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_birthday', ['type' => 'date']);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_birthday'), '1983-06-21');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_birthday' => 'Jun 21, 1983']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testBirthdayFieldWithChoiceWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'choice'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('select#formbuilder_1_simple_birthday_month');
        $I->seeElement('select#formbuilder_1_simple_birthday_day');
        $I->seeElement('select#formbuilder_1_simple_birthday_year');

        $I->selectOption('select#formbuilder_1_simple_birthday_month', '6');
        $I->selectOption('select#formbuilder_1_simple_birthday_day', '21');
        $I->selectOption('select#formbuilder_1_simple_birthday_year', date('Y'));
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_birthday' => sprintf('Jun 21, %s', date('Y'))]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testBirthdayFieldWithTextWidget(FunctionalTester $I)
    {
        $options = [
            'widget' => 'text'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_simple_birthday_month');
        $I->seeElement('input#formbuilder_1_simple_birthday_day');
        $I->seeElement('input#formbuilder_1_simple_birthday_year');

        $I->fillField('input#formbuilder_1_simple_birthday_month', '6');
        $I->fillField('input#formbuilder_1_simple_birthday_day', '21');
        $I->fillField('input#formbuilder_1_simple_birthday_year', date('Y'));
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_birthday' => sprintf('Jun 21, %s', date('Y'))]);
    }
}
