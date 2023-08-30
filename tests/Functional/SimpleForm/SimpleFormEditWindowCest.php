<?php

namespace DachcomBundle\Test\Functional\SimpleForm;

use DachcomBundle\Test\Support\Helper\Traits;
use DachcomBundle\Test\Support\FunctionalTester;

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
            'store' => [
                0 => [1, 'dachcom_test'],
            ],
            'width' => 250,
            'onChange' => 'formBuilderAreaWatcher.watchOutputWorkflow.bind(this)',
        ];

        $I->seeElement('template#template__pimcore_editable_bundleTestArea_1_formName');
        $I->seeAEditableConfiguration('formName', 'select', 'Form', $options, 1, 'script');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testConfigFieldFormTemplateSelector(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $options = [
            'defaultValue' => 'form_div_layout.html.twig',
            'width' => 250,
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
            ],
        ];

        $I->seeElement('template#template__pimcore_editable_bundleTestArea_1_formType');
        $I->seeAEditableConfiguration('formType', 'select', 'Form Template', $options, 'form_div_layout.html.twig', 'script');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testConfigFieldFormMailOutputWorkflowSelector(FunctionalTester $I)
    {
        $this->setupAdminForm($I);

        $options = [
            'defaultValue' => 'none',
            'width' => 250,
            'store'    => [
                 0 => [
                     0 => 'none',
                     1 => 'No Output Workflow available'
                 ],
            ],
            'class' => 'fb-output-workflow-selector',
        ];

        $I->seeElement('template#template__pimcore_editable_bundleTestArea_1_outputWorkflow');
        $I->seeAEditableConfiguration('outputWorkflow', 'select', 'Output Workflow', $options, null, 'script');
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
