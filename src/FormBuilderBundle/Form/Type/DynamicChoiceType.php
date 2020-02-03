<?php

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
    /**
     * @var ChoiceBuilderRegistry
     */
    protected $builderRegistry;

    /**
     * @var ChoiceBuilderInterface[]
     */
    protected $services;

    /**
     * @param ChoiceBuilderRegistry $builderRegistry
     */
    public function __construct(ChoiceBuilderRegistry $builderRegistry)
    {
        $this->builderRegistry = $builderRegistry;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
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

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->getServiceClassByArray($options)->setFormBuilder($builder);
    }

    /**
     * @param Options $options
     *
     * @return ChoiceBuilderInterface
     */
    protected function getServiceClassByOptions(Options $options)
    {
        return $this->getServiceClassByArray(['service' => $options->offsetGet('service')]);
    }

    /**
     * @param array $options
     *
     * @return ChoiceBuilderInterface
     */
    protected function getServiceClassByArray(array $options)
    {
        $serviceKey = $this->getServiceClassKey($options['service']);

        return $this->services[$serviceKey];
    }

    /**
     * @param string $serviceName
     *
     * @return string
     */
    protected function getServiceClassKey(string $serviceName)
    {
        return md5($serviceName);
    }
}
