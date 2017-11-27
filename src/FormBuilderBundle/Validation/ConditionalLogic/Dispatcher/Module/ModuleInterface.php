<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher\Module;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface ModuleInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * @param $options
     * @return mixed
     */
    public function apply($options);

}