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
