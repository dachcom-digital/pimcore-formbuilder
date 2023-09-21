<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class IntegerFieldCest extends AbstractFieldCest
{
    protected $type = 'integer';

    protected $name = 'integer_field';

    protected $displayName = 'integer_field';

    /**
     * @param FunctionalTester $I
     */
    public function testIntegerField(FunctionalTester $I)
    {
        $options = [];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_integer_field');

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'integer_field'), 42);
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['integer_field' => '42']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testIntegerFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'label' => 'Integer Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Integer Field', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testIntegerFieldOptionsWithData(FunctionalTester $I)
    {
        $options = [
            'data' => 50
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('input#formbuilder_1_integer_field', ['value' => '50']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testIntegerFieldOptionsWithHelpText(FunctionalTester $I)
    {
        $options = [
            'help_text' => 'This is a Helptext'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, []);

        $I->see('This is a Helptext', 'span');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testIntegerFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [];

        $optionals = [
            'email_label' => 'Integer Email Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'integer_field'), 42);
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'Integer Email Field');
    }
}
