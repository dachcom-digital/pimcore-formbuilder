<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module\Data\DataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ModuleInterface
{
    public function configureOptions(OptionsResolver $resolver): void;

    public function apply(array $options): DataInterface;
}
