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
