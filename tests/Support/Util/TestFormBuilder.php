<?php

namespace DachcomBundle\Test\Support\Util;

class TestFormBuilder
{
    /**
     * @var string|null
     */
    protected $group = null;

    /**
     * @var string
     */
    protected $action = '/';

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var string
     */
    protected $enctype = 'multipart/form-data';

    /**
     * @var bool
     */
    protected $noValidate = true;

    /**
     * @var bool
     */
    protected $useAjax = false;

    /**
     * @var array
     */
    protected $formConfig;

    /**
     * @var array
     */
    protected $fieldTypeMapper;

    /**
     * @param string $formName
     */
    public function __construct(string $formName = '')
    {
        $this->fieldTypeMapper = [];
        $this->formConfig = [
            'form_name'              => $formName,
            'form_config'            => [
                'attributes' => []
            ],
            'form_fields'            => ['fields' => []],
            'form_conditional_logic' => []
        ];

    }

    /**
     * @param string $group
     *
     * @return $this
     */
    public function setGroup(string $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @param $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param $enctype
     *
     * @return $this
     */
    public function setEncType($enctype)
    {
        $this->enctype = $enctype;

        return $this;
    }

    /**
     * @param $noValidate
     *
     * @return $this
     */
    public function setNoValidate($noValidate)
    {
        $this->noValidate = $noValidate;

        return $this;
    }

    /**
     * @param $useAjax
     *
     * @return $this
     */
    public function setUseAjax($useAjax)
    {
        $this->useAjax = $useAjax;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function addFormAttributes($key, $value)
    {
        $this->formConfig['form_config']['attributes'][] = ['option' => $key, 'value' => $value];
        return $this;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldInput(string $name, array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'text';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldNumericInput(string $name, array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'integer';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldSingleCheckbox(string $name, array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'checkbox';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldTextArea(string $name, array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'textarea';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $choices
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldChoice(string $name, array $choices = [], array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'choice';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        if (!isset($options['placeholder'])) {
            $options['placeholder'] = false;
        }

        $options['expanded'] = false;
        $options['multiple'] = false;
        $options['choices'] = $choices;

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $choices
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldChoiceExpanded(string $name, array $choices = [], array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'choice';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        if (!isset($options['placeholder'])) {
            $options['placeholder'] = false;
        }

        $options['expanded'] = true;
        $options['multiple'] = false;
        $options['choices'] = $choices;

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $choices
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldChoiceMultiple(string $name, array $choices = [], array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'choice';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        if (!isset($options['placeholder'])) {
            $options['placeholder'] = false;
        }

        $options['expanded'] = false;
        $options['multiple'] = true;
        $options['choices'] = $choices;

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $choices
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldChoiceExpandedAndMultiple(string $name, array $choices = [], array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'choice';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        if (!isset($options['placeholder'])) {
            $options['placeholder'] = false;
        }

        $options['expanded'] = true;
        $options['multiple'] = true;
        $options['choices'] = $choices;

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param array  $optional
     * @param array  $constraints
     *
     * @return $this
     */
    public function addFormFieldSubmitButton(string $name, array $options = [], array $optional = [], array $constraints = [])
    {
        $type = 'submit';
        $displayName = ucfirst($name);

        if (!isset($options['label'])) {
            $options['label'] = $displayName;
        }

        $this->addFormField($type, $name, $displayName, $constraints, $options, $optional);
        return $this;
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $displayName
     * @param array  $constraints
     * @param array  $options
     * @param array  $optional
     *
     * @return $this
     */
    public function addFormField(string $type, string $name, string $displayName, array $constraints = [], array $options = [], array $optional = [])
    {
        $parsedConstraints = [];
        foreach ($constraints as $constraintType) {
            if (is_array($constraintType)) {
                $parsedConstraints[] = ['type' => $constraintType[0], 'config' => $constraintType[1]];
            } else {
                $parsedConstraints[] = ['type' => $constraintType];
            }
        }

        if (!isset($optional['template'])) {
            $optional['template'] = '';
        }

        $field = [
            'type'         => $type,
            'name'         => $name,
            'display_name' => $displayName,
            'constraints'  => $parsedConstraints,
            'options'      => $options,
            'optional'     => $optional,
        ];

        $this->fieldTypeMapper[$name] = $this->getSelectorFortype($type, $options);

        $this->formConfig['form_fields']['fields'][] = $field;

        return $this;
    }

    /**
     * @param string $subType
     * @param string $name
     * @param array  $configuration
     * @param array  $subFields
     *
     * @return $this
     */
    public function addFormFieldContainer(string $subType, string $name, array $configuration, array $subFields)
    {
        if (!isset($configuration['template'])) {
            $configuration['template'] = '';
        }

        $field = [
            'type'          => 'container',
            'sub_type'      => $subType,
            'name'          => $name,
            'display_name'  => ucfirst($subType),
            'configuration' => $configuration,
            'fields'        => $subFields
        ];

        $this->fieldTypeMapper[$name] = 'div';

        if ($subType === 'fieldset' && count($subFields) > 0) {
            foreach ($subFields as $i => $subField) {
                $this->fieldTypeMapper[sprintf('%s_%d_%s', $name, $i, $subField['name'])] = $this->getSelectorFortype($subField['type'], $subField['options'] ?? []);
            }
        }

        $this->formConfig['form_fields']['fields'][] = $field;

        return $this;
    }

    /**
     * @param string $fieldName
     *
     * @return $this
     */
    public function removeField(string $fieldName)
    {
        foreach ($this->formConfig['form_fields']['fields'] as $index => $field) {
            if ($field['name'] === $fieldName) {
                unset($this->formConfig['form_fields']['fields'][$index]);
                break;
            }
        }

        return $this;
    }

    /**
     * @param array $conditions
     * @param array $actions
     *
     * @return $this
     */
    public function addFormConditionBlock(array $conditions, array $actions)
    {
        $conditionalLogicBlock = [
            'condition' => $conditions,
            'action'    => $actions
        ];

        $this->formConfig['form_conditional_logic'][] = $conditionalLogicBlock;

        return $this;
    }

    /***
     * @param        $formId
     * @param string $fieldName
     * @param string $prefix
     * @param string $suffix
     *
     * @return string
     */
    public function getFormFieldSelector($formId, string $fieldName, string $prefix = '', string $suffix = '')
    {
        return sprintf('%s %s %s#formbuilder_%d_%s%s',
            $this->getFormSelector($formId),
            $prefix,
            $this->fieldTypeMapper[$fieldName],
            $formId,
            $fieldName,
            $suffix
        );
    }

    /**
     * @param $formId
     *
     * @return string
     */
    public function getFormFieldTokenSelector($formId)
    {
        return sprintf('%s input#formbuilder_%d__token',
            $this->getFormSelector($formId),
            $formId
        );
    }

    /**
     * @param $formId
     *
     * @return string
     */
    public function getFormSelector($formId)
    {
        return sprintf('form[name*="formbuilder_%d"]',
            $formId
        );
    }

    /**
     * @return array
     */
    public function build()
    {
        $config = $this->formConfig;

        $config['form_group'] = $this->group;
        $config['form_config']['action'] = $this->action;
        $config['form_config']['method'] = $this->method;
        $config['form_config']['enctype'] = $this->enctype;
        $config['form_config']['noValidate'] = $this->noValidate;
        $config['form_config']['useAjax'] = $this->useAjax;

        return $config;
    }

    /**
     * @param string $type
     * @param array  $options
     *
     * @return string
     */
    protected function getSelectorFortype($type, array $options = [])
    {
        switch ($type) {
            case 'choice':
                if (isset($options['expanded']) && $options['expanded'] === false) {
                    $selector = 'select';
                } else {
                    $selector = 'input';
                }
                break;
            case 'checkbox':
            case 'email':
            case 'text':
                $selector = 'input';
                break;
            case 'submit':
                $selector = 'button';
                break;
            case 'textarea':
                $selector = 'textarea';
                break;
            default:
                $selector = 'input';
        }

        return $selector;
    }

}
