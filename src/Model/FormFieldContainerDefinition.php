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

namespace FormBuilderBundle\Model;

use FormBuilderBundle\Model\Fragment\EntityToArrayAwareInterface;

class FormFieldContainerDefinition implements FormFieldContainerDefinitionInterface, EntityToArrayAwareInterface
{
    protected string $name;
    private string $display_name;
    private string $type;
    private string $sub_type;
    private int $order;
    private array $configuration = [];
    private array $fields = [];

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDisplayName(string $name): void
    {
        $this->display_name = $name;
    }

    public function getDisplayName(): string
    {
        return $this->display_name;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setSubType(string $subType): void
    {
        $this->sub_type = $subType;
    }

    public function getSubType(): string
    {
        return $this->sub_type;
    }

    public function setConfiguration(array $configuration = []): void
    {
        $this->configuration = array_filter($configuration, static function ($configElement) {
            return $configElement !== '';
        });
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setFields(array $fields = []): void
    {
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $array = [];
        foreach ($vars as $key => $value) {
            $array[ltrim($key, '_')] = $value;
        }

        $removeKeys = ['fields'];
        $data = array_diff_key($array, array_flip($removeKeys));

        // parse fields
        $fieldData = [];
        foreach ($this->getFields() as $field) {
            if ($field instanceof EntityToArrayAwareInterface) {
                $fieldData[] = $field->toArray();
            }
        }

        $data['fields'] = $fieldData;

        return $data;
    }
}
