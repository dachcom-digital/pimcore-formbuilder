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

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\MailEditor\Widget\MailEditorFieldDataWidgetInterface;
use FormBuilderBundle\MailEditor\Widget\MailEditorWidgetInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailEditorWidgetRegistry
{
    protected array $provider = [];
    protected ?OptionsResolver $widgetOptionsResolver = null;

    public function register(string $identifier, mixed $service): void
    {
        if (!in_array(MailEditorWidgetInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    MailEditorWidgetInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $widgetConfig = $service instanceof MailEditorFieldDataWidgetInterface
            ? $service->getWidgetConfigByField([]) : $service->getWidgetConfig();

        if (count($widgetConfig) > 0) {
            foreach ($widgetConfig as $config) {
                $this->getOptionsResolver()->resolve($config);
            }
        }

        $this->provider[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->provider[$identifier]);
    }

    /**
     * @throws \Exception
     */
    public function get(string $identifier): MailEditorWidgetInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" widget service does not exist.');
        }

        return $this->provider[$identifier];
    }

    public function getAll(): array
    {
        return $this->provider;
    }

    public function getAllIdentifier(): array
    {
        return array_keys($this->provider);
    }

    protected function getOptionsResolver(): OptionsResolver
    {
        if ($this->widgetOptionsResolver !== null) {
            return $this->widgetOptionsResolver;
        }

        $optionsResolver = new OptionsResolver();

        $optionsResolver->setRequired(['defaultValue', 'type', 'label']);
        $optionsResolver->setAllowedTypes('label', ['string']);
        $optionsResolver->setAllowedTypes('defaultValue', ['null', 'string', 'bool']);
        $optionsResolver->setAllowedValues('type', ['checkbox', 'input', 'read-only']);

        $this->widgetOptionsResolver = $optionsResolver;

        return $this->widgetOptionsResolver;
    }
}
