<?php

namespace FormBuilderBundle\Resolver;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Model\FormStorageData;
use FormBuilderBundle\OutputWorkflow\FunnelData;
use FormBuilderBundle\Registry\StorageProviderRegistry;
use FormBuilderBundle\Storage\StorageProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class FunnelDataResolver
{
    public const FUNNEL_DATA_NAME = 'form_builder_funnel_data';

    public const FUNNEL_STORAGE_TOKEN_FRAGMENT = 'funnel_data_token';
    public const FUNNEL_ERROR_TOKEN_FRAGMENT = 'funnel_error_token';
    public const FUNNEL_FUNNEL_FINISHED_FRAGMENT = 'funnel_finished';

    protected Configuration $configuration;
    protected StorageProviderRegistry $storageProviderRegistry;
    protected bool $isFunnelAware;

    public function __construct(
        Configuration $configuration,
        StorageProviderRegistry $storageProviderRegistry
    ) {
        $this->configuration = $configuration;
        $this->storageProviderRegistry = $storageProviderRegistry;
        $this->isFunnelAware = $this->isFunnelAware();
    }

    public function buildFunnelData(Request $request): void
    {
        if (!$this->isFunnelProcessRequest($request)) {
            return;
        }

        if ($request->attributes->has(self::FUNNEL_DATA_NAME)) {
            return;
        }

        $funnelId = '';
        $channelId = '';
        $storageToken = null;

        if ($this->isInFunnelRoute($request)) {
            $funnelId = $request->attributes->get('funnelId');
            $channelId = $request->attributes->get('channelId');
            $storageToken = $request->attributes->get('storageToken');
        } elseif ($request->query->has(self::FUNNEL_STORAGE_TOKEN_FRAGMENT)) {
            $storageToken = $request->query->get(self::FUNNEL_STORAGE_TOKEN_FRAGMENT);
        }

        $formStorageData = $this->getFunnelStorageData($request, $storageToken);

       // dump($storageToken, $formStorageData); exit;

        if (!$formStorageData instanceof FormStorageData) {
            throw new \Exception('Could not resolve funnel data. No form storage data found');
        }

        $request->attributes->set(
            self::FUNNEL_DATA_NAME,
            new FunnelData(
                $request,
                $formStorageData,
                $funnelId,
                $channelId,
                $storageToken
            )
        );
    }

    public function getFunnelData(Request $request): FunnelData
    {
        if (!$request->attributes->has(self::FUNNEL_DATA_NAME)) {
            throw new \Exception('Could not resolve submission funnel data');
        }

        return $request->attributes->get(self::FUNNEL_DATA_NAME);
    }

    public function flushFunnelData(Request $request): void
    {
        if (!$request->attributes->has(self::FUNNEL_DATA_NAME)) {
            return;
        }

        $funnelData = $this->getFunnelData($request);

        if ((int) $request->query->get(self::FUNNEL_FUNNEL_FINISHED_FRAGMENT) !== $funnelData->getFormStorageData()->getFormId()) {
            return;
        }

        $this->getStorageProvider()->flush($request, $funnelData->getStorageToken());
    }

    public function isFunnelProcessRequest(Request $request): bool
    {
        if (!$this->isFunnelAware) {
            return false;
        }

        if ($this->isInFunnelRoute($request)) {
            return true;
        }

        if ($request->query->has(self::FUNNEL_STORAGE_TOKEN_FRAGMENT)) {
            return true;
        }

        return false;
    }

    public function isActiveFunnelProcessRequest(Request $request): bool
    {
        if (!$this->isFunnelProcessRequest($request)) {
            return false;
        }

        return $request->attributes->has(self::FUNNEL_DATA_NAME);
    }

    public function isFunnelShutdownRequest(Request $request): bool
    {
        if (!$this->isActiveFunnelProcessRequest($request)) {
            return false;
        }

        if (!$request->query->has(self::FUNNEL_FUNNEL_FINISHED_FRAGMENT)) {
            return false;
        }

        return true;
    }

    public function isFunnelErroredRequest(Request $request): bool
    {
        if (!$this->isActiveFunnelProcessRequest($request)) {
            return false;
        }

        if (!$request->query->has(self::FUNNEL_ERROR_TOKEN_FRAGMENT)) {
            return false;
        }

        return true;
    }

    public function getFunnelErrorToken(Request $request): ?string
    {
        if (!$this->isFunnelErroredRequest($request)) {
            return null;
        }

        return $request->query->get(self::FUNNEL_ERROR_TOKEN_FRAGMENT);
    }

    private function isInFunnelRoute(Request $request): bool
    {
        return $request->attributes->has('_route') && $request->attributes->get('_route') === 'form_builder.controller.funnel.dispatch';
    }

    private function isFunnelAware(): bool
    {
        $funnelConfiguration = $this->configuration->getConfig('funnel');

        return $funnelConfiguration['enabled'] === true;
    }

    private function getFunnelStorageData(Request $request, string $storageToken): ?FormStorageData
    {
        $data = $this->getStorageProvider()->fetch($request, $storageToken);

        if (!$data instanceof FormStorageData) {
            return null;
        }

        // @todo?
        //if ($data->getFormId() !== $formId) {
        //    return null;
        //}

        return $data;
    }

    protected function getStorageProvider(): StorageProviderInterface
    {
        $funnelConfiguration = $this->configuration->getConfig('funnel');

        return $this->storageProviderRegistry->get($funnelConfiguration['storage_provider']);
    }
}
