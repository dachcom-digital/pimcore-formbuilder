<?php

namespace FormBuilderBundle\Form\RuntimeData\Provider;

use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestDataProvider implements RuntimeDataProviderInterface
{
    protected string $expr;
    protected string $runtimeId;
    protected RequestStack $requestStack;
    protected ExpressionLanguage $expressionLanguage;

    public function __construct(RequestStack $requestStack, string $expr, string $runtimeId)
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->requestStack = $requestStack;
        $this->expr = $expr;
        $this->runtimeId = $runtimeId;
    }

    public function getRuntimeDataId(): string
    {
        return $this->runtimeId;
    }

    public function hasRuntimeData(FormDefinitionInterface $formDefinition): bool
    {
        $data = $this->expressionLanguage->evaluate($this->expr, ['request' => $this->requestStack->getMainRequest()]);

        return $data !== null;
    }

    public function getRuntimeData(FormDefinitionInterface $formDefinition): mixed
    {
        try {
            return $this->expressionLanguage->evaluate($this->expr, ['request' => $this->requestStack->getMainRequest()]);
        } catch (SyntaxError $e) {
            return null;
        }
    }
}
