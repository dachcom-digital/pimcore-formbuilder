<?php

namespace FormBuilderBundle\Form\RuntimeData\Provider;

use FormBuilderBundle\Form\RuntimeData\RuntimeDataProviderInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestDataProvider implements RuntimeDataProviderInterface
{
    /**
     * @var string
     */
    protected $expr;

    /**
     * @var string
     */
    protected $runtimeId;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @param RequestStack $requestStack
     * @param string       $expr
     * @param string       $runtimeId
     */
    public function __construct(RequestStack $requestStack, string $expr, string $runtimeId)
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->requestStack = $requestStack;
        $this->expr = $expr;
        $this->runtimeId = $runtimeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuntimeDataId()
    {
        return $this->runtimeId;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRuntimeData(FormDefinitionInterface $formDefinition)
    {
        $data = $this->expressionLanguage->evaluate($this->expr, ['request' => $this->requestStack->getMasterRequest()]);

        return $data !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuntimeData(FormDefinitionInterface $formDefinition)
    {
        try {
            return $this->expressionLanguage->evaluate($this->expr, ['request' => $this->requestStack->getMasterRequest()]);
        } catch (SyntaxError $e) {
            return null;
        }
    }
}
