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

namespace FormBuilderBundle\Event\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Contracts\EventDispatcher\Event;

class PostSetDataEvent extends Event
{
    public function __construct(
        private readonly FormEvent $formEvent,
        private readonly array $formOptions
    ) {
    }

    public function getFormEvent(): FormEvent
    {
        return $this->formEvent;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }
}
