<?php

namespace DachcomBundle\Test\Functional\Fields;

use DachcomBundle\Test\Support\FunctionalTester;

class TextAreaFieldCest extends AbstractFieldCest
{
    protected $type = 'textarea';

    protected $name = 'simple_textarea';

    protected $displayName = 'simple_textarea';

    /**
     * @param FunctionalTester $I
     */
    public function testTextAreaField(FunctionalTester $I)
    {
        $options = [];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeElement('textarea#formbuilder_1_simple_textarea');

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_textarea'), 'Simple Text Data');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seePropertiesInEmail($adminEmail, ['simple_textarea' => 'Simple Text Data']);
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTextAreaFieldOptionsWithLabel(FunctionalTester $I)
    {
        $options = [
            'label' => 'TextArea Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('TextArea Field', 'label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTextAreaFieldOptionsWithData(FunctionalTester $I)
    {
        $options = [
            'data' => 'PRE_FILLED'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->seeInField('textarea#formbuilder_1_simple_textarea', 'PRE_FILLED');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testTextAreaFieldOptionsWithHelpText(FunctionalTester $I)
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
    public function testTextAreaFieldOptionalsWithEmailLabel(FunctionalTester $I)
    {
        $options = [];

        $optionals = [
            'email_label' => 'TextArea Email Field'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options, $optionals);

        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_textarea'), 'simple_textarea');
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->seeInRenderedEmailBody($adminEmail, 'TextArea Email Field');
    }
}
