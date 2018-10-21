<?php

namespace DachcomBundle\Test\Helper\Traits;

use Codeception\Actor;

trait FunctionalFormTrait
{
    /**
     * @param Actor $I
     */
    private function fillForm(Actor $I)
    {
        $I->fillField('form input#formbuilder_1_prename', 'TEST');
        $I->fillField('form input#formbuilder_1_lastname', 'MAN');
        $I->fillField('form input#formbuilder_1_phone', '123456789');
        $I->fillField('form input#formbuilder_1_email', 'test@test.com');
        $I->fillField('form textarea#formbuilder_1_comment', 'DUMMY TEXT');
        $I->selectOption('form select#formbuilder_1_salutation', 'mr');
        $I->selectOption('form input#formbuilder_1_radios_3', 'radio_d');
        $I->checkOption('form input#formbuilder_1_checkbox_3');
    }
}
