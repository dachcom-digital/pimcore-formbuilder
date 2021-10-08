<?php

namespace DachcomBundle\Test\Util;

use Codeception\Util\Debug;
use FormBuilderBundle\Configuration\Configuration;
use Pimcore\Db;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FormHelper
{
    public const AREA_TEST_NAMESPACE = 'dachcomBundleTest';

    public static function removeAllForms(): void
    {
        $formPath = Configuration::STORE_PATH;

        $finder = new Finder();
        $fileSystem = new Filesystem();

        foreach ($finder->in($formPath)->name('*.yml') as $file) {
            $fileSystem->remove($file);
        }

        try {
            $db = Db::get();
            $db->exec('SET FOREIGN_KEY_CHECKS = 0;');
            $db->exec('TRUNCATE TABLE formbuilder_output_workflow_channel;');
            $db->exec('TRUNCATE TABLE formbuilder_output_workflow;');
            $db->exec('TRUNCATE TABLE formbuilder_forms;');
            $db->exec('SET FOREIGN_KEY_CHECKS = 1;');
        } catch (\Exception $e) {
            Debug::debug(sprintf('[FORMBUILDER ERROR] error while removing forms. message was: ' . $e->getMessage()));
        }
    }

    public static function generateSimpleForm(string $formName = 'dachcom_test', bool $useAjax = false): TestFormBuilder
    {
        return (new TestFormBuilder($formName))
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
    }
}
