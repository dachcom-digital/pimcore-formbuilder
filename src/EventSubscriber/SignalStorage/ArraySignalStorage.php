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

namespace FormBuilderBundle\EventSubscriber\SignalStorage;

use FormBuilderBundle\Event\OutputWorkflow\OutputWorkflowSignalEvent;

class ArraySignalStorage implements SignalStorageInterface
{
    protected array $context;
    protected array $signals = [];

    public function __construct(array $context = [])
    {
        $this->context = $context;
    }

    public function storeSignal(OutputWorkflowSignalEvent $signal): void
    {
        $this->signals[] = $signal;
    }

    public function getSignals(): array
    {
        return $this->signals;
    }
}
