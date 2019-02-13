<?php

namespace FormBuilderBundle\Form\Type\Container;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldSetContainerType extends AbstractType
{
    use Traits\ContainerTrait;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $globalEntryOptions = $event->getForm()->getConfig()->getOption('entry_options');
            $parsedEntryOptions = $this->getFormEntryOptions();
            $entryOptions = array_merge($parsedEntryOptions, ['fields' => $globalEntryOptions['fields']]);
            $this->addEmptyCollections($event->getForm(), $entryOptions, 1);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label'] = $this->getContainerLabel($options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add'          => false,
            'allow_delete'       => false,
            'delete_empty'       => true,
            'allow_extra_fields' => false
        ]);

        $entryOptionsNormalizer = function (Options $options, $globalEntryOptions) {
            return array_merge($globalEntryOptions, $this->getFormEntryOptions());
        };

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form_builder_container_fieldset';
    }

    /**
     * @return array
     */
    private function getFormEntryOptions()
    {
        $options = [];
        $options['label'] = false;

        return $options;
    }

    public function getParent()
    {
        return ContainerType::class;
    }
}
