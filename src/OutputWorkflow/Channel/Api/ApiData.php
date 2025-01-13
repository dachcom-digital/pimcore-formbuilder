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

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\OutputWorkflow\Channel\ChannelContext;
use Symfony\Component\Form\FormInterface;

class ApiData
{
    public function __construct(
        protected string $apiProviderName,
        protected array $apiNodes,
        protected ?array $providerConfiguration,
        protected string $locale,
        protected array $formRuntimeData,
        protected FormInterface $form,
        protected ?ChannelContext $channelContext
    ) {
    }

    public function getApiProviderName(): string
    {
        return $this->apiProviderName;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function hasChannelContext(): bool
    {
        return $this->channelContext instanceof ChannelContext;
    }

    public function getChannelContext(): ChannelContext
    {
        if (!$this->hasChannelContext()) {
            throw new \RuntimeException('ChannelContext not available');
        }

        return $this->channelContext;
    }

    public function getFormRuntimeData(): array
    {
        return $this->formRuntimeData;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getProviderConfiguration(): ?array
    {
        return $this->providerConfiguration;
    }

    public function getProviderConfigurationNode(string $node): mixed
    {
        if (!is_array($this->providerConfiguration)) {
            return null;
        }

        return $this->providerConfiguration[$node] ?? null;
    }

    public function hasProviderConfigurationNode(string $node): bool
    {
        if (!is_array($this->providerConfiguration)) {
            return false;
        }

        return isset($this->providerConfiguration[$node]);
    }

    public function setApiNodes(array $apiNodes): void
    {
        $this->apiNodes = $apiNodes;
    }

    public function getApiNodes(): array
    {
        return $this->apiNodes;
    }

    public function getApiNode(string $node): mixed
    {
        return $this->apiNodes[$node] ?? null;
    }

    public function hasApiNode(string $node): bool
    {
        return isset($this->apiNodes[$node]);
    }
}
