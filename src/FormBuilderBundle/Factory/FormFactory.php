<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Storage\FormFieldContainer;
use FormBuilderBundle\Storage\FormInterface;
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

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormById($id)
    {
        try {
            $formEntity = Form::getById($id);
            $formEntity->setTranslator($this->translator);
            if (!$formEntity instanceof FormInterface) {
                return null;
            }
            $this->assignRelationDataToFormObject($formEntity);
        } catch (\Exception $e) {
            return null;
        }

        return $formEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormIdByName(string $name)
    {
        try {
            $formEntity = Form::getByName($name);
            $formEntity->setTranslator($this->translator);
            if (!$formEntity instanceof FormInterface) {
                return null;
            }
            $this->assignRelationDataToFormObject($formEntity);
        } catch (\Exception $e) {
            return null;
        }

        return $formEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllForms()
    {
        $formEntities = Form::getAll();

        $objects = [];
        foreach ($formEntities as $entity) {
            $form = $this->getFormById($entity['id']);
            if ($form instanceof FormInterface) {
                $objects[] = $this->getFormById($entity['id']);
            }
        }

        return $objects;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormField()
    {
        $formFieldEntity = new FormField();
        $formFieldEntity->setTranslator($this->translator);

        return $formFieldEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function createFormFieldContainer()
    {
        $formFieldContainerEntity = new FormFieldContainer();
        $formFieldContainerEntity->setTranslator($this->translator);

        return $formFieldContainerEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function assignRelationDataToFormObject(FormInterface $formEntity)
    {
        $formPath = Configuration::STORE_PATH . '/main_' . $formEntity->getId() . '.yml';

        if (!file_exists($formPath)) {
            throw new \Exception(sprintf('configuration yml file for form with id "%d" not found', $formEntity->getId()));
        }

        $data = Yaml::parse(file_get_contents($formPath));

        if (!empty($data['config']) && is_array($data['config'])) {
            $formEntity->setConfig($data['config']);
        }

        if (!empty($data['conditional_logic']) && is_array($data['conditional_logic'])) {
            $formEntity->setConditionalLogic($data['conditional_logic']);
        }

        if (!empty($data['fields'])) {
            $fields = [];
            foreach ($data['fields'] as $field) {
                if ($field['type'] === 'container') {
                    $formField = $this->createFormFieldContainer();
                    $this->populateFormField($formField, $field);
                    if (isset($field['fields']) && is_array($field['fields'])) {
                        $subFields = [];
                        foreach ($field['fields'] as $subField) {
                            $subFormField = $this->createFormField();
                            $subFields[] = $this->populateFormField($subFormField, $subField);
                        }
                        $formField->setFields($subFields);
                    }
                } else {
                    $formField = $this->createFormField();
                    $this->populateFormField($formField, $field);
                }

                $fields[$field['name']] = $formField;
            }

            $formEntity->setFields($fields);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function populateFormField($formField, array $field)
    {
        foreach ($field as $fieldName => $fieldValue) {
            $setter = 'set' . $this->camelize($fieldName);
            if (!is_callable([$formField, $setter])) {
                continue;
            }
            $formField->$setter($fieldValue);
        }

        return $formField;
    }

    /**
     * {@inheritdoc}
     */
    public function formHasAvailableConfigurationFile(int $formId)
    {
        return file_exists($this->getConfigurationPathOfForm($formId));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationPathOfForm(int $formId)
    {
        return Configuration::STORE_PATH . '/main_' . $formId . '.yml';
    }

    /**
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    protected function camelize($input, $separator = '_')
    {
        return ucfirst(str_replace($separator, '', ucwords($input, $separator)));
    }
}
