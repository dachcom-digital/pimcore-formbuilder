<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Model\FunnelActionElement;

class FunnelActionElementStack
{
    protected array $funnelActionElements = [];

    public function add(FunnelActionElement $element): void
    {
        $this->funnelActionElements[] = $element;
    }

    public function getAll(): array
    {
        return $this->funnelActionElements;
    }

    public function hasByName(string $name, bool $allowDisabled = false): bool
    {
        $element = $this->getByName($name);

        if (!$element instanceof FunnelActionElement) {
            return false;
        }

        if ($allowDisabled === true) {
            return true;
        }

        return $element->isDisabled() === false;
    }

    public function getByName(string $name): ?FunnelActionElement
    {
        /** @var FunnelActionElement $funnelActionElement */
        foreach ($this->funnelActionElements as $funnelActionElement) {
            if ($funnelActionElement->getFunnelActionDefinition()->getName() === $name) {
                return $funnelActionElement;
            }
        }

        return null;
    }
}
