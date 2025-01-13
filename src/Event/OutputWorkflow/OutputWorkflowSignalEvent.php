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

namespace FormBuilderBundle\Event\OutputWorkflow;

use Symfony\Contracts\EventDispatcher\Event;

class OutputWorkflowSignalEvent extends Event
{
    public const NAME = 'form_builder.output_workflow.signal';

    public function __construct(
        protected string $name,
        protected mixed $data
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
