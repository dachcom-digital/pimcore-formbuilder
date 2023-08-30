<?php

namespace FormBuilderBundle\Manager;

use FormBuilderBundle\Configuration\Configuration;

class TemplateManager
{
    public function __construct(protected Configuration $configuration)
    {
    }

    public function getFieldTemplates(): array
    {
        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['field']['templates'];

        $templateData = [];
        foreach ($templates as $templateId => $template) {
            $template['id'] = $templateId;
            $templateData[] = $template;
        }

        return $templateData;
    }

    public function getDefaultFieldTemplate(): ?string
    {
        $defaultValue = null;

        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['field']['templates'];
        foreach ($templates as $template) {
            $defaultValue = $template['value'];

            break;
        }

        return $defaultValue;
    }

    public function getFormTemplates(bool $parseForExtJsStore = false): array
    {
        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['templates'];

        $templateData = [];
        foreach ($templates as $templateId => $template) {
            $template['id'] = $templateId;
            $templateData[] = $template;
        }

        if ($parseForExtJsStore) {
            $storeTemplates = [];
            foreach ($templateData as $template) {
                $storeTemplates[] = [$template['value'], $template['label']];
            }

            return $storeTemplates;
        }

        return $templateData;
    }

    public function getDefaultFormTemplate(): ?string
    {
        $defaultValue = null;

        $areaConfig = $this->configuration->getConfig('form');
        $templates = $areaConfig['templates'];
        foreach ($templates as $template) {
            $defaultValue = $template['value'];

            break;
        }

        return $defaultValue;
    }
}
