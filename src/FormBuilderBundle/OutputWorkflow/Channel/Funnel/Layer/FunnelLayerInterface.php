<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Model\FunnelActionDefinition;
use Symfony\Component\Form\FormBuilderInterface;

interface FunnelLayerInterface
{
    public function getName(): string;

    public function getFormType(): array;

    /**
     * @return array<int, FunnelActionDefinition>
     */
    public function getFunnelActionDefinitions(): array;

    public function buildResponse(FunnelLayerResponse $funnelLayerResponse, FormBuilderInterface $formBuilder): FunnelLayerResponse;
}
