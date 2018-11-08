<?php

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Configuration\Configuration;
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
     * FormFactory constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     */
    public function createForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        return $form;
    }

    /**
     * @inheritdoc
     */
    public function getFormById($id, bool $ignoreMissingConfigurationFile = true)
    {
        $formEntity = Form::getById($id);
        $formEntity->setTranslator($this->translator);

        try {
            $this->assignRelationDataToFormObject($formEntity);
        } catch (\Exception $e) {
            if ($ignoreMissingConfigurationFile === false) {
                return null;
            }
        }

        return $formEntity;
    }

    /**
     * @inheritdoc
     */
    public function getFormIdByName(string $name, bool $ignoreMissingConfigurationFile = true)
    {
        $formEntity = Form::getByName($name);
        $formEntity->setTranslator($this->translator);

        try {
            $this->assignRelationDataToFormObject($formEntity);
        } catch (\Exception $e) {
            if ($ignoreMissingConfigurationFile === false) {
                return null;
            }
        }

        return $formEntity;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function createFormField()
    {
        $formFieldEntity = new FormField();
        $formFieldEntity->setTranslator($this->translator);
        return $formFieldEntity;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function formHasAvailableConfigurationFile(int $formId)
    {
        return file_exists($this->getConfigurationPathOfForm($formId));
    }

    /**
     * @inheritdoc
     */
    public function getConfigurationPathOfForm(int $formId)
    {
        return Configuration::STORE_PATH . '/main_' . $formId . '.yml';
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