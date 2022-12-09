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
            new FunnelActionDefinition('button1', 'Top Button'),
        ];
    }

    public function buildForm(FunnelLayerData $funnelLayerData, FormBuilderInterface $formBuilder): void
    {

    }

    public function handleFormData(FunnelLayerData $funnelLayerData, array $formData): array
    {
        return $formData;
    }

    public function buildView(FunnelLayerData $funnelLayerData): void
    {
        $funnelLayerConfiguration = $funnelLayerData->getFunnelLayerConfiguration();

        $layout = null;
        $locale = $funnelLayerData->getRequest()->getLocale();

        foreach (['default', $locale] as $layoutLocale) {
            if (isset($funnelLayerConfiguration[$layoutLocale]['layout']['path']) && !empty($funnelLayerConfiguration[$layoutLocale]['layout']['path'])) {
                $layout = $funnelLayerConfiguration[$layoutLocale]['layout']['path'];
                break;
            }
        }

        $funnelLayerData->setFunnelLayerView('@FormBuilder/funnel/layer/simple_layout_layer.html.twig');
        $funnelLayerData->setFunnelLayerViewArguments(['layout' => $layout]);
        $funnelLayerData->setRenderType(FunnelLayerData::RENDER_TYPE_PRERENDER);
    }
}
