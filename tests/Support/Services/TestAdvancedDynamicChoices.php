<?php

namespace DachcomBundle\Test\Support\Services;

use FormBuilderBundle\Form\AdvancedChoiceBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class TestAdvancedDynamicChoices implements AdvancedChoiceBuilderInterface
{
    protected FormBuilderInterface $builder;

    private function getFakeEntities(): array
    {
        return [
            1 => 'Entity 1',
            2 => 'Entity 2',
            3 => 'Entity 3',
            4 => 'Entity 4',
            5 => 'Entity 5',
        ];
    }

    public function setFormBuilder(FormBuilderInterface $builder): void
    {
        $this->builder = $builder;

        // transform data back to string (to display the product name in the email for example)
        $builder->addModelTransformer(new CallbackTransformer(
            function ($entries) {
                return $entries;
            },
            function ($entries) {
                if (empty($entries)) {
                    return $entries;
                }

                $fakeEntities = $this->getFakeEntities();
                if (is_array($entries)) {
                    $data = [];
                    foreach ($entries as $id) {
                        $data[] = $fakeEntities[$id];
                    }
                    return implode(', ', $data);
                }

                return $fakeEntities[$entries];

            }
        ));
    }

    public function getChoiceValue(mixed $value = null): string
    {
        return $value . '-custom-value';
    }

    public function getChoiceLabel(mixed $choiceValue, string $key, mixed $value): string
    {
        return $key . ' Custom Label';
    }

    public function getChoiceAttributes(mixed $element, string $key, mixed $value): array
    {
        return ['class' => 'special-choice-class'];
    }

    public function getGroupBy($element, string $key, mixed $value): string
    {
        return 'Group A';
    }

    public function getPreferredChoices($element, $key, mixed $value): bool
    {
        return $key === 'Entity 5';
    }

    public function getList(): array
    {
        $data = [];
        foreach ($this->getFakeEntities() as $entityId => $entityName) {
            $data[$entityName] = $entityId;
        }

        return $data;
    }
}
