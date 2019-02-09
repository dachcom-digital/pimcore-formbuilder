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
     * @var CallbackChoiceLoader
     */
    protected $choiceBuilder;

    /**
     * @var ChoiceBuilderInterface
     */
    protected $service;

    /**
     * DynamicChoiceType constructor.
     *
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
                $initialChoiceBuilder = false;
                if (!$this->service) {
                    $serviceName = $options['service'];
                    $this->service = $this->builderRegistry->get($serviceName);
                    $initialChoiceBuilder = true;
                }

                //if conditional logic is available, we need to re-add the CallbackChoiceLoader
                if (!is_null($options['conditionalLogic']) || $initialChoiceBuilder) {
                    $this->choiceBuilder = new CallbackChoiceLoader(function () {
                        return $this->service->getList();
                    });
                }

                return $this->choiceBuilder;
            },
            'choice_label'              => function ($choiceValue, $key, $value) {
                if ($this->service instanceof AdvancedChoiceBuilderInterface) {
                    return $this->service->getChoiceLabel($choiceValue, $key, $value);
                }

                return $key;
            },
            'choice_attr'               => function ($element, $key, $value) {
                if ($this->service instanceof AdvancedChoiceBuilderInterface) {
                    return $this->service->getChoiceAttributes($element, $key, $value);
                }

                return [];
            },
            'group_by'                  => function ($element, $key, $value) {
                if ($this->service instanceof AdvancedChoiceBuilderInterface) {
                    return $this->service->getGroupBy($element, $key, $value);
                }

                return null;
            },
            'preferred_choices'         => function ($element, $key, $value) {
                if ($this->service instanceof AdvancedChoiceBuilderInterface) {
                    return $this->service->getPreferredChoices($element, $key, $value);
                }

                return null;
            },
            'choice_value'              => function ($element = null) {
                if ($this->service instanceof AdvancedChoiceBuilderInterface) {
                    return $this->service->getChoiceValue($element);
                }

                return $element;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->service->setFormBuilder($builder);
    }
}