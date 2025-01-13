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

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Form\AdvancedChoiceBuilderInterface;
use FormBuilderBundle\Form\ChoiceBuilderInterface;
use FormBuilderBundle\Registry\ChoiceBuilderRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicChoiceType extends AbstractType
{
    protected ChoiceBuilderRegistry $builderRegistry;
    protected array $services;

    public function __construct(ChoiceBuilderRegistry $builderRegistry)
    {
        $this->builderRegistry = $builderRegistry;
        $this->services = [];
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'service'                   => null,
            'conditionalLogic'          => null,
            'choice_translation_domain' => false,
            'choice_loader'             => function (Options $options) {
                $serviceName = $options['service'];
                $serviceKey = $this->getServiceClassKey($serviceName);
                $this->services[$serviceKey] = $this->builderRegistry->get($serviceName);

                return new CallbackChoiceLoader(function () use ($options) {
                    return $this->getServiceClassByOptions($options)->getList();
                });
            },
            'choice_label'              => function (Options $options, $previousValue) {
                $service = $this->getServiceClassByOptions($options);
                if ($service instanceof AdvancedChoiceBuilderInterface) {
                    return function ($choiceValue, $key, $value) use ($service) {
                        return call_user_func_array([$service, 'getChoiceLabel'], [$choiceValue, $key, $value]);
                    };
                }

                return $previousValue;
            },
            'choice_attr'               => function (Options $options, $previousValue) {
                $service = $this->getServiceClassByOptions($options);
                if ($service instanceof AdvancedChoiceBuilderInterface) {
                    return function ($element, $key, $value) use ($service) {
                        return call_user_func_array([$service, 'getChoiceAttributes'], [$element, $key, $value]);
                    };
                }

                return $previousValue;
            },
            'group_by'                  => function (Options $options, $previousValue) {
                $service = $this->getServiceClassByOptions($options);
                if ($service instanceof AdvancedChoiceBuilderInterface) {
                    return function ($element, $key, $value) use ($service) {
                        return call_user_func_array([$service, 'getGroupBy'], [$element, $key, $value]);
                    };
                }

                return $previousValue;
            },
            'preferred_choices'         => function (Options $options, $previousValue) {
                $service = $this->getServiceClassByOptions($options);
                if ($service instanceof AdvancedChoiceBuilderInterface) {
                    return function ($element, $key, $value) use ($service) {
                        return call_user_func_array([$service, 'getPreferredChoices'], [$element, $key, $value]);
                    };
                }

                return $previousValue;
            },
            'choice_value'              => function (Options $options, $previousValue) {
                $service = $this->getServiceClassByOptions($options);
                if ($service instanceof AdvancedChoiceBuilderInterface) {
                    return function ($element = null) use ($service) {
                        return call_user_func_array([$service, 'getChoiceValue'], [$element]);
                    };
                }

                return $previousValue;
            },
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->getServiceClassByArray($options)->setFormBuilder($builder);
    }

    protected function getServiceClassByOptions(Options $options): ChoiceBuilderInterface
    {
        return $this->getServiceClassByArray(['service' => $options->offsetGet('service')]);
    }

    protected function getServiceClassByArray(array $options): ChoiceBuilderInterface
    {
        $serviceKey = $this->getServiceClassKey($options['service']);

        return $this->services[$serviceKey];
    }

    protected function getServiceClassKey(string $serviceName): string
    {
        return md5($serviceName);
    }
}
