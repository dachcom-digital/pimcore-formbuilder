<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Layer\SimpleLayoutLayerType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\LocalizedValuesCollectionType;
use FormBuilderBundle\Model\FunnelActionElement;

class SimpleLayoutLayer implements FunnelLayerInterface
{
    public function getName(): string
    {
        return 'Simple Layout Layer';
    }

    public function getFormType(): array
    {
        return [
            'type'    => LocalizedValuesCollectionType::class,
            'options' => [
                'entry_type' => SimpleLayoutLayerType::class,
            ]
        ];
    }

    public function getFunnelActions(): array
    {
        return [
            new FunnelActionElement('button1', 'Top Button')
        ];
    }
}
