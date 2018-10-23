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

        $I->see('Form Preset', '.form-config-window .fb-form-group label');
        $I->see('Form Preset Info', '.preview-fields h5');

        $I->seeElement('.preview-fields .preview-field[data-name="preset1"] .description');
        $I->see('This is a description of Preset A', '.preview-fields .preview-field[data-name="preset1"] .description');

        $options = [
            'width' => 240,
            'store' => [
                0 => [
                    0 => 'custom',
                    1 => 'No Form Preset',
                ],
                1 => [
                    0 => 'preset1',
                    1 => 'Preset A',
                ],
            ]
        ];

        $I->seeAEditableConfiguration('formPreset', 'select', $options, 'custom', 'script');
    }
}
