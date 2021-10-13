<?php

namespace DachcomBundle\Test\functional\Preset;

use DachcomBundle\Test\FunctionalTester;
use DachcomBundle\Test\Helper\Traits;

class FormWithPresetsCest
{
    use Traits\FunctionalFormTrait;

    /**
     * @param FunctionalTester $I
     */
    public function testAdminFormWithPresets(FunctionalTester $I)
    {
        $I->haveABootedSymfonyConfiguration('config_presets.yml');

        $user = $I->haveAUser('dachcom_test');
        $document = $I->haveAPageDocument('form-test');
        $form = $I->haveAForm($this->generateSimpleForm());

        $I->seeAFormAreaElementPlacedOnDocument($document, $form);

        $I->amLoggedInAs('dachcom_test');
        $I->amOnPageInEditMode('/form-test');

        $options = [
            'defaultValue' => 'custom',
            'width'        => 250,
            'store'        => [
                0 => [
                    0 => 'custom',
                    1 => 'No Form Preset',
                ],
                1 => [
                    0 => 'preset1',
                    1 => 'Preset A',
                ],
            ],
            'onchange'     => 'formBuilderAreaWatcher.watchPresets.bind(this)',
        ];

        $I->seeElement('template#template__pimcore_editable_bundleTestArea_1_formPreset');
        $I->seeAEditableConfiguration('formPreset', 'select', 'Form Preset', $options, 'custom', 'script');
    }
}
