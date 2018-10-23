<?php

namespace DachcomBundle\Test\functional\SimpleForm;

use DachcomBundle\Test\Helper\Traits;
use DachcomBundle\Test\FunctionalTester;

class SimpleFormEditWindowCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     */
    public function testPresetSelectorIsNotAvailable(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $I->dontSee('Form Preset', '.form-config-window .fb-row .fb-col-half .fb-form-group label');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testConfigFieldFormSelector(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $options = [
            'width' => 240,
            'store' => [
                0 => ['1', 'dachcom_test'],
            ]
        ];

        $I->see('Form', '.form-config-window .fb-row .fb-col-half .fb-form-group label');
        $I->seeAEditableConfiguration('formName', 'select', $options, '1', 'script');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testConfigFieldFormTemplateSelector(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $options = [
            'width' => 240,
            'store' => [
                0 => [
                    0 => 'form_div_layout.html.twig',
                    1 => 'Form Div Layout',
                ],
                1 => [
                    0 => 'bootstrap_3_layout.html.twig',
                    1 => 'Bootstrap 3 Layout',
                ],
                2 => [
                    0 => 'bootstrap_3_horizontal_layout.html.twig',
                    1 => 'Bootstrap 3 Horizontal Layout',
                ],
                3 => [
                    0 => 'bootstrap_4_layout.html.twig',
                    1 => 'Bootstrap 4 Layout',
                ],
                4 => [
                    0 => 'bootstrap_4_horizontal_layout.html.twig',
                    1 => 'Bootstrap 4 Horizontal Layout',
                ],
            ]
        ];

        $I->see('Form Template', '.form-config-window .fb-row .fb-col-half .fb-form-group label');
        $I->seeAEditableConfiguration('formType', 'select', $options, 'form_div_layout.html.twig', 'script');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testConfigFieldFormMailTemplateHref(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $options = [
            'width'    => 505,
            'types'    => ['document'],
            'subtypes' => ['document' => ['email']]
        ];

        $I->see('Mail Template', '.form-config-window .fb-row .fb-col-full .fb-form-group label');
        $I->seeAEditableConfiguration('sendMailTemplate', 'href', $options, null, 'script');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testConfigFieldFormMailCopyTemplateHref(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $options = [
            'width'    => 240,
            'types'    => ['document'],
            'subtypes' => ['document' => ['email']]
        ];

        $I->see('Copy Mail Template', '.form-config-window .fb-row .fb-col-half .fb-form-group label');
        $I->seeAEditableConfiguration('sendCopyMailTemplate', 'href', $options, null, 'script');

    }

    /**
     * @param FunctionalTester $I
     */
    public function testConfigFieldFormMailCopyCheckbox(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $I->see('Send Copy to User', '.form-config-window .fb-row .fb-col-half .fb-form-group label');
        $I->seeAEditableConfiguration('userCopy', 'checkbox', [], false, 'script');
    }

    /**
     * @param FunctionalTester $I
     */
    protected function setupAdminForm(FunctionalTester $I)
    {
        $testFormBuilder = $this->generateSimpleForm();

        $user = $I->haveAUser('dachcom_test');
        $form = $I->haveAForm($testFormBuilder);
        $document = $I->haveAPageDocument('form-test');

        $I->seeAFormAreaElementPlacedOnDocument($document, $form);
        $I->amLoggedInAs('dachcom_test');
        $I->amOnPageInEditMode('/form-test');
    }
}
