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

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Layer\DynamicLayoutLayerType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\LocalizedValuesCollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class DynamicLayoutLayer implements FunnelLayerInterface
{
    public function getName(): string
    {
        return 'Dynamic Layout Layer';
    }

    public function getFormType(): array
    {
        return [
            'type'    => LocalizedValuesCollectionType::class,
            'options' => [
                'entry_type' => DynamicLayoutLayerType::class,
            ]
        ];
    }

    public function dynamicFunnelActionAware(): bool
    {
        return true;
    }

    public function getFunnelActionDefinitions(): array
    {
        return [];
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
    }
}
