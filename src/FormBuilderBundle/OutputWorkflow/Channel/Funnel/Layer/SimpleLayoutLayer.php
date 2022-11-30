<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Layer\SimpleLayoutLayerType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\LocalizedValuesCollectionType;
use FormBuilderBundle\Model\FunnelActionDefinition;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Templating\EngineInterface;

class SimpleLayoutLayer implements FunnelLayerInterface
{
    protected EngineInterface $templating;
    protected SerializerInterface $serializer;

    public function __construct(
        EngineInterface $templating,
        SerializerInterface $serializer
    ) {
        $this->templating = $templating;
        $this->serializer = $serializer;
    }

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

    public function getFunnelActionDefinitions(): array
    {
        return [
            new FunnelActionDefinition('button1', 'Top Button')
        ];
    }

    public function buildResponse(FunnelLayerResponse $funnelLayerResponse, FormBuilderInterface $formBuilder): FunnelLayerResponse
    {
        $layout = null;
        $locale = $funnelLayerResponse->getFunnelWorkerData()->getRequest()->getLocale();

        foreach (['default', $locale] as $layoutConfig) {
            if (isset($funnelConfiguration[$layoutConfig]['layout']['path']) && !empty($funnelConfiguration[$layoutConfig]['layout']['path'])) {
                $layout = $funnelConfiguration[$layoutConfig]['layout']['path'];
                break;
            }
        }

        $funnelLayerResponse->setFunnelLayerView('@FormBuilder/funnel/layer/simple_layout_layer.html.twig');
        $funnelLayerResponse->setFunnelLayerViewArguments(['layout' => $layout]);

        return $funnelLayerResponse;
    }
}
