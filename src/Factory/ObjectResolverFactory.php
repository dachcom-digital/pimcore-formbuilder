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

namespace FormBuilderBundle\Factory;

use FormBuilderBundle\Form\FormValuesOutputApplierInterface;
use FormBuilderBundle\OutputWorkflow\Channel\Object\ExistingObjectResolver;
use FormBuilderBundle\OutputWorkflow\Channel\Object\NewObjectResolver;
use FormBuilderBundle\Registry\DynamicObjectResolverRegistry;
use Pimcore\Model\FactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ObjectResolverFactory implements ObjectResolverFactoryInterface
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected DynamicObjectResolverRegistry $dynamicObjectResolverRegistry,
        protected FormValuesOutputApplierInterface $formValuesOutputApplier,
        protected EventDispatcherInterface $eventDispatcher,
        protected FactoryInterface $modelFactory
    ) {
    }

    public function createForNewObject(array $objectMappingData): NewObjectResolver
    {
        return new NewObjectResolver(
            $this->translator,
            $this->formValuesOutputApplier,
            $this->eventDispatcher,
            $this->modelFactory,
            $this->dynamicObjectResolverRegistry,
            $objectMappingData
        );
    }

    public function createForExistingObject(array $objectMappingData): ExistingObjectResolver
    {
        return new ExistingObjectResolver(
            $this->translator,
            $this->formValuesOutputApplier,
            $this->eventDispatcher,
            $this->modelFactory,
            $this->dynamicObjectResolverRegistry,
            $objectMappingData
        );
    }
}
