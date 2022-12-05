<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Layer\SimpleLayoutLayerType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\LocalizedValuesCollectionType;
use FormBuilderBundle\Model\FunnelActionDefinition;
use Symfony\Component\Form\FormBuilderInterface;

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

    public function getFunnelActionDefinitions(): array
    {
        return [
            new FunnelActionDefinition('button1', 'Top Button')
        ];
    }

    public function buildResponse(FunnelLayerResponse $funnelLayerResponse, FormBuilderInterface $formBuilder): FunnelLayerResponse
    {
        $funnelConfiguration = $funnelLayerResponse->getFunnelWorkerData()->getChannel()->getConfiguration();
        $funnelLayerConfiguration = $funnelConfiguration['configuration'] ?? [];

        $layout = null;
        $locale = $funnelLayerResponse->getFunnelWorkerData()->getRequest()->getLocale();

        foreach (['default', $locale] as $layoutLocale) {
            if (isset($funnelLayerConfiguration[$layoutLocale]['layout']['path']) && !empty($funnelLayerConfiguration[$layoutLocale]['layout']['path'])) {
                $layout = $funnelLayerConfiguration[$layoutLocale]['layout']['path'];
                break;
            }
        }

        $funnelLayerResponse->setFunnelLayerView('@FormBuilder/funnel/layer/simple_layout_layer.html.twig');
        $funnelLayerResponse->setFunnelLayerViewArguments(['layout' => $layout]);
        $funnelLayerResponse->setRenderType(FunnelLayerResponse::RENDER_TYPE_PRERENDER);

        return $funnelLayerResponse;
    }
}
