<?php

namespace FormBuilderBundle\Form\RuntimeData\Provider;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;
use FormBuilderBundle\Manager\DoubleOptInManager;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DoubleOptInSessionDataProvider implements RuntimeDataProviderInterface
{
    public const DOUBLE_OPT_IN_SESSION_RUNTIME_DATA_IDENTIFIER = 'double_opt_in_session';

    public function __construct(
        protected Configuration $configuration,
        protected RequestStack $requestStack
    ) {
    }

    public function getRuntimeDataId(): string
    {
        return self::DOUBLE_OPT_IN_SESSION_RUNTIME_DATA_IDENTIFIER;
    }

    public function hasRuntimeData(FormDefinitionInterface $formDefinition): bool
    {
        $doubleOptInConfig = $this->configuration->getConfig('double_opt_in');

        if ($doubleOptInConfig['enabled'] === false) {
            return false;
        }

        return $this->requestStack->getMainRequest()->query->has(DoubleOptInManager::DOUBLE_OPT_IN_SESSION_QUERY_IDENTIFIER);
    }

    public function getRuntimeData(FormDefinitionInterface $formDefinition): ?string
    {
        return $this->requestStack->getMainRequest()->query->get(DoubleOptInManager::DOUBLE_OPT_IN_SESSION_QUERY_IDENTIFIER);
    }
}
