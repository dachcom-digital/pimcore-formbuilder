<?php

namespace FormBuilderBundle\Form\Data\Connector;

use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;
use FormBuilderBundle\Model\Fragment\SubFieldsAwareInterface;
use Symfony\Component\Yaml\Yaml;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Factory\FormDefinitionFactoryInterface;

class FormDataConnector implements FormDataConnectorInterface
{
    /**
     * @var FormDefinitionFactoryInterface
     */
    protected $formDefinitionFactory;

    /**
     * @param FormDefinitionFactoryInterface $formDefinitionFactory
     */
    public function __construct(FormDefinitionFactoryInterface $formDefinitionFactory)
    {
        $this->formDefinitionFactory = $formDefinitionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function assignRelationDataToFormObject(FormDefinitionInterface $formDefinition)
    {
        $formPath = Configuration::STORE_PATH . '/main_' . $formDefinition->getId() . '.yml';

        if (!file_exists($formPath)) {
            throw new \Exception(sprintf('configuration yml file for form with id "%d" not found', $formDefinition->getId()));
        }

        $data = Yaml::parse(file_get_contents($formPath));

        if (!empty($data['config']) && is_array($data['config'])) {
            $formDefinition->setConfig($data['config']);
        }

        if (!empty($data['conditional_logic']) && is_array($data['conditional_logic'])) {
            $formDefinition->setConditionalLogic($data['conditional_logic']);
        }

        if ($formDefinition instanceof SubFieldsAwareInterface && !empty($data['fields'])) {
            $fields = [];
            foreach ($data['fields'] as $field) {
                if ($field['type'] === 'container') {
                    $formField = $this->formDefinitionFactory->createFormFieldContainerDefinition();
                    $this->populateFormField($formField, $field);
                    if ($formField instanceof SubFieldsAwareInterface && isset($field['fields']) && is_array($field['fields'])) {
                        $subFields = [];
                        foreach ($field['fields'] as $subField) {
                            $subFormField = $this->formDefinitionFactory->createFormFieldDefinition();
                            $subFields[] = $this->populateFormField($subFormField, $subField);
                        }
                        $formField->setFields($subFields);
                    }
                } else {
                    $formField = $this->formDefinitionFactory->createFormFieldDefinition();
                    $this->populateFormField($formField, $field);
                }

                $fields[$field['name']] = $formField;
            }

            $formDefinition->setFields($fields);
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
     * {@inheritdoc}
     */
    public function storeFormData(FormDefinitionInterface $formDefinition)
    {
        $data = [
            'config'            => $formDefinition->getConfig(),
            'conditional_logic' => $formDefinition->getConditionalLogic(),
            'fields'            => $this->getFormFieldData($formDefinition)
        ];

        $this->storeYmlData($formDefinition, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFormData(FormDefinitionInterface $formDefinition)
    {
        if (file_exists(Configuration::STORE_PATH . '/main_' . $formDefinition->getId() . '.yml')) {
            unlink(Configuration::STORE_PATH . '/main_' . $formDefinition->getId() . '.yml');
        }
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

    /**
     * @param FormDefinitionInterface $formDefinition
     *
     * @return array
     */
    protected function getFormFieldData(FormDefinitionInterface $formDefinition)
    {
        $formFields = [];

        /** @var FormFieldDefinitionInterface $field */
        foreach ($formDefinition->getFields() as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $formFields[] = $field->toArray();
            }
        }

        return $formFields;
    }

    /**
     * @param FormDefinitionInterface $formDefinition
     * @param mixed                   $data
     */
    protected function storeYmlData(FormDefinitionInterface $formDefinition, $data)
    {
        if (file_exists(Configuration::STORE_PATH . '/main_' . $formDefinition->getId() . '.yml')) {
            unlink(Configuration::STORE_PATH . '/main_' . $formDefinition->getId() . '.yml');
        }

        $yml = Yaml::dump($data);
        file_put_contents(Configuration::STORE_PATH . '/main_' . $formDefinition->getId() . '.yml', $yml);
    }
}
