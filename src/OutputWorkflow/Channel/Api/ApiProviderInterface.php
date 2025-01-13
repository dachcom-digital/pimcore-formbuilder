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

namespace FormBuilderBundle\OutputWorkflow\Channel\Api;

use FormBuilderBundle\Model\FormDefinitionInterface;

interface ApiProviderInterface
{
    public function getName(): string;

    public function getProviderConfigurationFields(FormDefinitionInterface $formDefinition): array;

    public function getPredefinedApiFields(FormDefinitionInterface $formDefinition, array $providerConfiguration): array;

    public function process(ApiData $apiData): void;
}
