<?php

namespace FormBuilderBundle\Form\DataInjector;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExpressionDataInjector implements DataInjectorInterface
{
    protected ExpressionLanguage $expressionLanguage;

    public function __construct(protected RequestStack $requestStack)
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function getName(): string
    {
        return 'Expression';
    }

    public function getDescription(): ?string
    {
        return 'Inject data from request object via expression language';
    }

    public function parseData(array $config): mixed
    {
        if (!array_key_exists('expression', $config)) {
            return null;
        }

        $request = $this->requestStack->getMainRequest();

        if (!$request instanceof Request) {
            return null;
        }

        return $this->expressionLanguage->evaluate($config['expression'], ['request' => $request]);
    }
}
