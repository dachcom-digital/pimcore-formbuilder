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
use FormBuilderBundle\OutputWorkflow\FunnelData;
use FormBuilderBundle\Storage\StorageProviderInterface;
use FormBuilderBundle\Stream\AttachmentStream;
use FormBuilderBundle\Stream\FileStack;

class FormDataSignalStorage implements SignalStorageInterface, ProviderAwareStorageInterface
{
    protected FunnelData $funnelData;
    protected StorageProviderInterface $storageProvider;
    protected array $context;

    public function __construct(array $context = [])
    {
        $this->context = $context;

        if (!array_key_exists('funnelData', $this->context)) {
            throw new \Exception('Cannot store signal without funnelData context');
        }

        $this->funnelData = $this->context['funnelData'];
    }

    public function setStorageProvider(StorageProviderInterface $storageProvider): void
    {
        $this->storageProvider = $storageProvider;
    }

    public function getStorageProvider(): StorageProviderInterface
    {
        return $this->storageProvider;
    }

    public function storeSignal(OutputWorkflowSignalEvent $signal): void
    {
        $storageToken = $this->funnelData->getStorageToken();
        $formStorageData = $this->funnelData->getFormStorageData();

        if ($signal->getName() !== AttachmentStream::SIGNAL_CLEAN_UP) {
            throw new \Exception(sprintf('FormDataSignalStorage only supports signal events of type "%s"', AttachmentStream::SIGNAL_CLEAN_UP));
        }

        if (!$signal->getData() instanceof FileStack) {
            throw new \Exception(sprintf('Data of Signal "%s" needs to be type of "%s"', AttachmentStream::SIGNAL_CLEAN_UP, FileStack::class));
        }

        $formStorageData->addAttachmentSignal($signal->getData());

        $this->getStorageProvider()->update($this->funnelData->getRequest(), $storageToken, $formStorageData);
    }

    public function getSignals(): array
    {
        $formStorageData = $this->funnelData->getFormStorageData();

        $restoredAttachmentSignalEvents = [];
        /** @var FileStack $fileStack */
        foreach ($formStorageData->getAttachmentSignals() as $fileStack) {
            $restoredAttachmentSignalEvents[] = new OutputWorkflowSignalEvent(AttachmentStream::SIGNAL_CLEAN_UP, $fileStack);
        }

        return $restoredAttachmentSignalEvents;
    }
}
