<?php

namespace DachcomBundle\Test\Functional\Constraints;

use DachcomBundle\Test\Support\FunctionalTester;
use DachcomBundle\Test\Support\Util\TestFormBuilder;

abstract class AbstractConstraintCest
{
    /**
     * @var array
     */
    protected $validFieldsForCurrentConstraint = [];

    /**
     * File Upload Constraint is acceptance tested
     *
     * @var array
     */
    protected $fieldsToTest = [
        [
            'config'   => ['date', ['placeholder' => 'Please Choose']],
            'selector' => [
                '//div[@id="formbuilder_1_date"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_month"]', 'value' => '1'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_day"]', 'value' => '1'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_year"]', 'value' => 'CURRENT_YEAR'],
            ]
        ],
        [
            'config'   => ['date_time', ['placeholder' => 'Please Choose']],
            'selector' => [
                '//div[@id="formbuilder_1_date_time"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_time_date_month"]', 'value' => '1'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_time_date_day"]', 'value' => '1'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_time_date_year"]', 'value' => 'CURRENT_YEAR'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_time_time_hour"]', 'value' => '22'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_date_time_time_minute"]', 'value' => '20'],
            ]
        ],
        [
            'config'   => ['time', ['placeholder' => 'Please Choose']],
            'selector' => [
                '//div[@id="formbuilder_1_time"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_time_hour"]', 'value' => '22'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_time_minute"]', 'value' => '20'],
            ]
        ],
        [
            'config'   => ['birthday', ['placeholder' => 'Please Choose']],
            'selector' => [
                '//div[@id="formbuilder_1_birthday"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_birthday_month"]', 'value' => '6'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_birthday_day"]', 'value' => '21'],
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_birthday_year"]', 'value' => '1983'],
            ]
        ],
        [
            'config'   => ['text'],
            'selector' => [
                '//input[@id="formbuilder_1_text"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'fillField', 'selector' => '//input[@id="formbuilder_1_text"]', 'value' => 'INPUT_TEXT'],
            ]
        ],
        [
            'config'   => ['textarea'],
            'selector' => [
                '//textarea[@id="formbuilder_1_textarea"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'fillField', 'selector' => '//textarea[@id="formbuilder_1_textarea"]', 'value' => 'TEXTAREA_TEXT'],
            ]
        ],
        [
            'config'   => ['integer'],
            'selector' => [
                '//input[@id="formbuilder_1_integer"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'fillField', 'selector' => '//input[@id="formbuilder_1_integer"]', 'value' => 667],
            ]
        ],
        [
            'config'   => ['checkbox'],
            'selector' => [
                '//input[@id="formbuilder_1_checkbox"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'checkOption', 'selector' => '//input[@id="formbuilder_1_checkbox"]', 'value' => '1'],
            ]
        ],
        [
            'config'   => ['country', ['placeholder' => 'Please Choose']],
            'selector' => [
                '//select[@id="formbuilder_1_country"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_country"]', 'value' => 'AT'],

            ]
        ],
        [
            'config'   => ['choice', ['choices' => ['Choice A' => 'choice_a', 'Choice B' => 'choice_b'], 'placeholder' => 'Please Choose']],
            'selector' => [
                '//select[@id="formbuilder_1_choice"]//preceding-sibling::ul//li'
            ],
            'fill'     => [
                ['type' => 'selectOption', 'selector' => '//select[@id="formbuilder_1_choice"]', 'value' => 'choice_b'],
            ]
        ],
    ];

    /**
     * @param FunctionalTester $I
     * @param array            $constraints
     * @param string           $formTemplate
     *
     * @return array
     */
    protected function setupForm(FunctionalTester $I, $constraints = [], $formTemplate = 'form_div_layout.html.twig')
    {
        $document = $I->haveAPageDocument('form-test');
        $adminEmail = $I->haveAEmailDocumentForAdmin();

        $testFormBuilder = (new TestFormBuilder('dachcom_test'))
            ->setUseAjax(false)
            ->addFormFieldSubmitButton('submit');

        foreach ($this->getFieldsToTest() as $field) {
            $testFormBuilder->addFormField(
                $field['config'][0],
                $field['config'][0],
                ucfirst($field['config'][0]),
                $constraints,
                isset($field['config'][1]) ? $field['config'][1] : [],
                []
            );
        }

        $testFormBuilder->addFormFieldSubmitButton('submit');

        $form = $I->haveAForm($testFormBuilder);

        $I->seeAFormAreaElementPlacedOnDocument($document, $form, $adminEmail, null, $formTemplate);
        $I->amOnPage('/form-test');

        return [$testFormBuilder, $form];
    }

    /**
     * @param FunctionalTester $I
     * @param array            $overrideValues
     */
    protected function fillForm(FunctionalTester $I, array $overrideValues = [])
    {
        foreach ($this->getFieldsToTest() as $field) {
            foreach ($field['fill'] as $index => $fillData) {
                $name = $field['config'][0] . '_' . $index;
                switch ($fillData['type']) {
                    case 'fillField' :
                        $I->fillField($fillData['selector'], $this->parseValue($fillData['value'], $name, $overrideValues));
                        break;
                    case 'selectOption' :
                        $I->selectOption($fillData['selector'], $this->parseValue($fillData['value'], $name, $overrideValues));
                        break;
                    case 'checkOption' :
                        $I->selectOption($fillData['selector'], $this->parseValue($fillData['value'], $name, $overrideValues));
                        break;
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function getFieldsToTest()
    {
        $fields = [];
        foreach ($this->fieldsToTest as $field) {

            if (!empty($this->validFieldsForCurrentConstraint) &&
                !in_array($field['config'][0], $this->validFieldsForCurrentConstraint)) {
                continue;
            }

            $fields[] = $field;

        }

        return $fields;
    }

    /**
     * @param        $value
     * @param string $fieldName
     * @param array  $overrideValues
     *
     * @return false|string
     */
    protected function parseValue($value, string $fieldName, array $overrideValues = [])
    {
        if (isset($overrideValues[$fieldName])) {
            return $overrideValues[$fieldName];
        }

        if (empty($value)) {
            return $value;
        }

        if ($value === 'CURRENT_YEAR') {
            return date('Y');
        }

        return $value;
    }

}
