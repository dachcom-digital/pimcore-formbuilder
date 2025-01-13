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

use FormBuilderBundle\Stream\FileStack;
use Symfony\Component\Uid\Uuid;

class FormStorageData
{
    protected ?int $formId;
    protected array $formData;
    protected ?array $formRuntimeData;
    protected string $initiationPath;
    protected array $funnelRuntimeData = [];
    protected array $funnelErrors = [];
    protected array $attachmentSignals = [];
    protected bool $funnelRuntimeDataDirty = false;

    public function __construct(
        ?int $formId,
        array $formData,
        ?array $formRuntimeData,
        string $initiationPath,
        array $funnelRuntimeData = [],
        array $funnelErrors = [],
        array $attachmentSignals = []
    ) {
        $this->formId = $formId;
        $this->formData = $formData;
        $this->formRuntimeData = $formRuntimeData;
        $this->initiationPath = $initiationPath;
        $this->funnelRuntimeData = $funnelRuntimeData;
        $this->funnelErrors = $funnelErrors;
        $this->attachmentSignals = $attachmentSignals;
        $this->funnelRuntimeDataDirty = false;
    }

    public function getFormId(): ?int
    {
        return $this->formId;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getFormRuntimeData(): ?array
    {
        return $this->formRuntimeData;
    }

    public function getInitiationPath(): string
    {
        return $this->initiationPath;
    }

    public function funnelRuntimeDataDirty(): bool
    {
        return $this->funnelRuntimeDataDirty === true;
    }

    public function resetRuntimeDataDirty(): void
    {
        $this->funnelRuntimeDataDirty = false;
    }

    public function setFunnelRuntimeData(array $funnelRuntimeData): void
    {
        $this->funnelRuntimeData = $funnelRuntimeData;
    }

    public function getFunnelRuntimeData(): array
    {
        return $this->funnelRuntimeData;
    }

    public function getNamespacedFunnelRuntimeData(string $namespace): ?array
    {
        return $this->funnelRuntimeData[$namespace] ?? null;
    }

    public function hasFunnelRuntimeData(string $namespace): bool
    {
        return count($this->funnelRuntimeData) > 0;
    }

    public function hasNamespacedFunnelRuntimeData(string $namespace): bool
    {
        return array_key_exists($namespace, $this->funnelRuntimeData) && count($this->funnelRuntimeData[$namespace]) > 0;
    }

    public function addFunnelRuntimeData(string $namespace, array $data): void
    {
        if (count($data) === 0) {
            return;
        }

        $this->funnelRuntimeData[$namespace] = $data;
        $this->funnelRuntimeDataDirty = true;
    }

    public function addFunnelError(string $errorMessage): string
    {
        $errorToken = Uuid::v4()->toRfc4122();

        $this->funnelErrors[$errorToken] = $errorMessage;

        return $errorToken;
    }

    public function getFunnelErrors(): array
    {
        return $this->funnelErrors;
    }

    public function hasFunnelError(string $errorToken): bool
    {
        return array_key_exists($errorToken, $this->funnelErrors);
    }

    public function getFunnelError(string $errorToken)
    {
        return $this->funnelErrors[$errorToken] ?? null;
    }

    public function addAttachmentSignal(FileStack $fileStack): void
    {
        $this->attachmentSignals[] = $fileStack;
    }

    public function getAttachmentSignals(): array
    {
        return $this->attachmentSignals;
    }
}
