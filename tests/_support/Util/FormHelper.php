<?php

namespace DachcomBundle\Test\Util;

use FormBuilderBundle\Configuration\Configuration;
use Symfony\Component\Finder\Finder;

class FormHelper
{
    const AREA_TEST_NAMESPACE = 'dachcomBundleTest';

    /**
     * Delete all Forms from db / filesystem
     */
    public static function removeAllForms()
    {
        $formPath = Configuration::STORE_PATH;

        $finder = new Finder();
        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();

        foreach ($finder->in($formPath)->name('*.yml') as $file) {
            $fileSystem->remove($file);
        }

        try {
            $db = \Pimcore\Db::get();
            $db->exec('TRUNCATE TABLE formbuilder_forms');
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while removing forms. message was: ' . $e->getMessage()));
        }
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param null   $data
     *
     * @return string
     */
    public static function generateEditableConfiguration(string $name, string $type, array $options, $data = null)
    {
        $editableConfig = [
            'id'        => sprintf('pimcore_editable_%s:1.%s', FormHelper::AREA_TEST_NAMESPACE, $name),
            'name'      => sprintf('%s:1.%s', FormHelper::AREA_TEST_NAMESPACE, $name),
            'realName'  => $name,
            'options'   => $options,
            'data'      => $data,
            'type'      => $type,
            'inherited' => false,
        ];

        $data = sprintf('editableConfigurations.push(%s);', json_encode($editableConfig));

        return $data;

    }

    /**
     * @param string $formName
     * @param bool   $useAjax
     *
     * @return TestFormBuilder
     */
    public static function generateSimpleForm(string $formName = 'dachcom_test', $useAjax = false)
    {
        $testFormBuilder = (new TestFormBuilder($formName))
            ->setUseAjax($useAjax)
            ->addFormFieldChoice('simple_dropdown', ['Simple DropDown Value 0' => 'simple_drop_down_value_0', 'Simple DropDown Value 1' => 'simple_drop_down_value_1'])
            ->addFormFieldInput('simple_text_input_1', [], [], ['not_blank'])
            ->addFormFieldInput('simple_text_input_2', [], [], ['not_blank'])
            ->addFormFieldInput('simple_text_input_3')
            ->addFormFieldInput('simple_text_input_4', [], [], ['not_blank', 'not_blank'])
            ->addFormFieldChoiceExpandedAndMultiple('checkboxes', [
                'Check 0' => 'check0',
                'Check 1' => 'check1',
                'Check 2' => 'check2',
                'Check 3' => 'check3',
            ], [], [], ['not_blank'])
            ->addFormFieldChoiceExpanded('radios', [
                'Radio 0' => 'radio0',
                'Radio 1' => 'radio1',
                'Radio 2' => 'radio2',
                'Radio 3' => 'radio3',
            ], [], [], ['not_blank'])
            ->addFormFieldTextArea('simple_text_area', [], [], ['not_blank'])
            ->addFormFieldSingleCheckbox('single_checkbox', [], [], ['not_blank'])
            ->addFormFieldSubmitButton('submit');

        return $testFormBuilder;
    }
}
