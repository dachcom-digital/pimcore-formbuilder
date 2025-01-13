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

class FunnelActionDefinition
{
    protected string $name;
    protected string $label;
    protected array $parameters;

    public function __construct(string $name, string $label, array $parameters = [])
    {
        $this->name = $name;
        $this->label = $label;
        $this->parameters = $parameters;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
