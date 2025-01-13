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

namespace FormBuilderBundle\Form\RuntimeData;

class RuntimeDataCollector
{
    protected array $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @throws \Exception
     */
    public function add($id, $data): void
    {
        if (array_key_exists($id, $this->data)) {
            throw new \Exception(sprintf('Runtime Data Block with "%s" already added.', $id));
        }

        $this->data[$id] = $data;
    }

    /**
     * @throws \Exception
     */
    public function find(string $id): mixed
    {
        if (!array_key_exists($id, $this->data)) {
            throw new \Exception(sprintf('Runtime Data Block with "%s" not found.', $id));
        }

        return $this->data[$id];
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @throws \Exception
     */
    public function __toString(): string
    {
        if (count($this->data) === 0) {
            return '';
        }

        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
