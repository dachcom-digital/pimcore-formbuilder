<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
