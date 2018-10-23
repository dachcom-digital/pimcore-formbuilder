<?php

namespace DachcomBundle\Test\Helper\Traits;

use Codeception\Actor;
use DachcomBundle\Test\AcceptanceTester;
use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Util\FormHelper;
use DachcomBundle\Test\Util\TestFormBuilder;

trait FunctionalFormTrait
{
    /**
     * @param bool $useAjax
     *
     * @return TestFormBuilder
     */
    private function generateSimpleForm($useAjax = false)
    {
        return FormHelper::generateSimpleForm('dachcom_test', $useAjax);
    }

    /**
     * @param TestFormBuilder                         $testFormBuilder
     * @param AcceptanceTester|FunctionalTester|Actor $I
     */
    private function fillSimpleForm(TestFormBuilder $testFormBuilder, Actor $I)
    {
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_1'), 'TEST_FIRST_NAME');
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_2'), 'TEST_LAST_NAME');
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_3'), '+43 1234 67 89');
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_input_4'), 'test@test.com');
        $I->fillField($testFormBuilder->getFormFieldSelector(1, 'simple_text_area'), 'DUMMY LOREM IPSUM TEXT AREA TEXT');

        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'simple_dropdown'), 'simple_drop_down_value_1');
        $I->selectOption($testFormBuilder->getFormFieldSelector(1, 'radios', '', '_3'), 'radio3');

        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'checkboxes', '', '_3'));
        $I->checkOption($testFormBuilder->getFormFieldSelector(1, 'single_checkbox'));
    }

    /**
     * @param TestFormBuilder                         $testFormBuilder
     * @param AcceptanceTester|FunctionalTester|Actor $I
     */
    private function clickSimpleFormSubmit(TestFormBuilder $testFormBuilder, Actor $I)
    {
        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));
    }
}
