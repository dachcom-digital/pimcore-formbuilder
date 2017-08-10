<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Configuration\Configuration;

class TemplateManager
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * TemplateManager constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getFieldTemplates()
    {
        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['field']['templates'];

        $templateData = [];
        foreach($templates as $templateId => $template) {
            $template['id'] = $templateId;
            $templateData[] = $template;
        }

        return $templateData;
    }

    /**
     * @return null
     */
    public function getDefaultFieldTemplate()
    {
        $defaultValue = NULL;

        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['field']['templates'];
        foreach($templates as $template) {
            $defaultValue = $template['value'];
            break;
        }

        return $defaultValue;
    }

    /**
     * @param bool $parseForExtJsStore
     *
     * @return mixed
     */
    public function getFormTemplates($parseForExtJsStore = FALSE)
    {
        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['templates'];

        $templateData = [];
        foreach($templates as $templateId => $template) {
            $template['id'] = $templateId;
            $templateData[] = $template;
        }

        if($parseForExtJsStore) {
            $storeTemplates = [];
            foreach($templateData as $template) {
                $storeTemplates[] = [$template['value'], $template['label']];
            }
            return $storeTemplates;
        }

        return $templateData;
    }

    /**
     * @return null
     */
    public function getDefaultFormTemplate()
    {
        $defaultValue = NULL;

        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['templates'];
        foreach($templates as $template) {
            $defaultValue = $template['value'];
            break;
        }

        return $defaultValue;
    }
}
