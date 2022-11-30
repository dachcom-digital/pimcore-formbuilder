<?php

namespace FormBuilderBundle\Model;

use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Uid\Uuid;

class FormStorageData
{
    protected ?int $formId;
    protected array $formData;
    protected ?array $formRuntimeData;
    protected string $initiationPath;
    protected array $funnelData = [];
    protected array $funnelErrors = [];

    public function __construct(?int $formId, array $formData, ?array $formRuntimeData, string $initiationPath, array $funnelData = [], array $funnelErrors = [])
    {
        $this->formId = $formId;
        $this->formData = $formData;
        $this->formRuntimeData = $formRuntimeData;
        $this->initiationPath = $initiationPath;
        $this->funnelData = $funnelData;
        $this->funnelErrors = $funnelErrors;
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

    public function addFunnelFormData(string $namespace, array $data): void
    {
        if (count($data) === 0) {
            return;
        }

        $this->funnelData[$namespace] = $data;
    }

    public function getFunnelData(): array
    {
        return $this->funnelData;
    }

    #[Ignore]
    public function hasFunnelFormData(string $namespace): bool
    {
        return array_key_exists($namespace, $this->funnelData) && count($this->funnelData[$namespace]) > 0;
    }

    #[Ignore]
    public function getFunnelFormData(string $namespace): ?array
    {
        return $this->funnelData[$namespace] ?? null;
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

    #[Ignore]
    public function hasFunnelError(string $errorToken): bool
    {
        return array_key_exists($errorToken, $this->funnelErrors);
    }

    #[Ignore]
    public function getFunnelError(string $errorToken)
    {
        return $this->funnelErrors[$errorToken] ?? null;
    }
}
