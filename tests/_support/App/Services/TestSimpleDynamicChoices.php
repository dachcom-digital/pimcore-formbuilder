<?php

namespace DachcomBundle\Test\App\Services;

use FormBuilderBundle\Form\ChoiceBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class TestSimpleDynamicChoices implements ChoiceBuilderInterface
{
    protected $builder;

    private function getFakeEntities()
    {
        return [
            1 => 'Entity 1',
            2 => 'Entity 2',
            3 => 'Entity 3',
            4 => 'Entity 4',
            5 => 'Entity 5',
        ];
    }

    public function setFormBuilder(FormBuilderInterface $builder)
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
                } else {
                    return $fakeEntities[$entries];
                }
            }
        ));
    }

    public function getList()
    {
        $data = [];
        foreach ($this->getFakeEntities() as $entityId => $entityName) {
            $data[$entityName] = $entityId;
        }

        return $data;
    }
}
