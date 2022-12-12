<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Model\FunnelActionDefinition;
use Symfony\Component\Form\FormBuilderInterface;

interface FunnelLayerInterface
{
    public function getName(): string;

    public function getFormType(): array;

    public function dynamicFunnelActionAware(): bool;

    /**
     * @return array<int, FunnelActionDefinition>
     */
    public function getFunnelActionDefinitions(): array;

    public function buildForm(FunnelLayerData $funnelLayerData, FormBuilderInterface $formBuilder): void;

    public function handleFormData(FunnelLayerData $funnelLayerData, array $formData): array;

    public function buildView(FunnelLayerData $funnelLayerData): void;
}
