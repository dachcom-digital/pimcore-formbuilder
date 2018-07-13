<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Storage\FormInterface;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Storage\Form;
use FormBuilderBundle\Storage\FormField;
use Pimcore\Translation\Translator;
use Symfony\Component\Yaml\Yaml;

class FormFactory implements FormFactoryInterface
{
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return Form
     */
    public function createForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        return $form;
    }

    /**
     * @param $id
     *
     * @return FormInterface
     */
    public function getFormById($id)
    {
        $formEntity = Form::getById($id);
        $formEntity->setTranslator($this->translator);
        $this->assignRelationDataToFormObject($formEntity);
        return $formEntity;
    }

    /**
     * @return array
     */
    public function getAllForms()
    {
        $formEntities = Form::getAll();

        $objects = [];
        foreach ($formEntities as $entity) {
            $objects[] = $this->getFormById($entity['id']);
        }

        return $objects;
    }

    /**
     * @param $name
     *
     * @return FormInterface
     */
    public function getFormIdByName($name)
    {
        $formEntity = Form::getByName($name);
        $formEntity->setTranslator($this->translator);
        $this->assignRelationDataToFormObject($formEntity);
        return $formEntity;
    }

    /**
     * @return FormFieldInterface
     */
    public function createFormField()
    {
        $formFieldEntity = new FormField();
        $formFieldEntity->setTranslator($this->translator);
        return $formFieldEntity;
    }

    /**
     * @param FormInterface $formEntity
     */
    public function assignRelationDataToFormObject($formEntity)
    {
        $formPath = Configuration::STORE_PATH . '/main_' . $formEntity->getId() . '.yml';

        if (!file_exists($formPath)) {
            return;
        }

        $data = Yaml::parse(file_get_contents($formPath));

        if (!empty($data['config'])) {
            $formEntity->setConfig($data['config']);
        }

        if (!empty($data['conditional_logic'])) {
            $formEntity->setConditionalLogic($data['conditional_logic']);
        }

        if (!empty($data['fields'])) {
            $fields = [];
            foreach ($data['fields'] as $field) {
                $formField = $this->createFormField();
                foreach ($field as $fieldName => $fieldValue) {

                    $setter = 'set' . $this->camelize($fieldName);
                    if (!is_callable([$formField, $setter])) {
                        continue;
                    }

                    $formField->$setter($fieldValue);
                }

                $fields[$field['name']] = $formField;
            }

            $formEntity->setFields($fields);
        }
    }

    /**
     * @param        $input
     * @param string $separator
     *
     * @return string
     */
    protected function camelize($input, $separator = '_')
    {
        return ucfirst(str_replace($separator, '', ucwords($input, $separator)));
    }
}