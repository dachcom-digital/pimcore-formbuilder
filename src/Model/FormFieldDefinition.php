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

class FormFieldDefinition implements FormFieldDefinitionInterface, EntityToArrayAwareInterface
{
    protected string $name;
    private string $display_name;
    private string $type;
    private int $order;
    private array $constraints = [];
    private array $options = [];
    private array $optional = [];

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

    public function setOptions(array $options = []): void
    {
        $this->options = array_filter($options, static function ($option) {
            return $option !== '';
        });
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptional(array $options = []): void
    {
        $this->optional = array_filter($options, static function ($option) {
            return $option !== '';
        });
    }

    public function getOptional(): array
    {
        return $this->optional;
    }

    public function setConstraints(array $constraints = []): void
    {
        $this->constraints = $constraints;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $array = [];
        foreach ($vars as $key => $value) {
            $array[ltrim($key, '_')] = $value;
        }

        $removeKeys = [];

        return array_diff_key($array, array_flip($removeKeys));
    }
}
