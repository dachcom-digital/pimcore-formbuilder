<?php

namespace FormBuilderBundle\Form\Type\Container;

use FormBuilderBundle\Form\Type\Container\Traits;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class RepeaterContainerType extends AbstractType
{
    use Traits\ContainerTrait;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {

                $minEntries = $options['formbuilder_configuration']['min'];

                if (!is_numeric($minEntries)) {
                    return;
                }

                $minEntries = (int)$minEntries;
                if ($minEntries === 0) {
                    return;
                }

                $globalEntryOptions = $event->getForm()->getConfig()->getOption('entry_options');
                $parsedEntryOptions = $this->getFormEntryOptions($options['formbuilder_configuration']);
                $entryOptions = array_merge($parsedEntryOptions, ['fields' => $globalEntryOptions['fields']]);
                $this->addEmptyCollections($event->getForm(), $entryOptions, $minEntries);

            })->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {

                $data = $event->getData();
                $minEntries = $options['formbuilder_configuration']['min'];
                $maxEntries = $options['formbuilder_configuration']['max'];

                if (!is_numeric($maxEntries)) {
                    return;
                }

                $maxEntries = (int)$maxEntries;
                if ($maxEntries === 0) {
                    return;
                }

                if (empty($data)) {
                    return;
                }

                if (count($data) < $minEntries) {
                    $label = $this->getContainerLabel($options);
                    if (is_string($label)) {
                        $label = $this->translator->trans($label);
                    }
                    $transMessage = $this->translator->trans('form_builder.form.container.repeater.min', ['%label%' => $label, '%items%' => $minEntries]);
                    $event->getForm()->addError(new FormError($transMessage));
                } elseif (count($data) > $maxEntries) {
                    $label = $this->getContainerLabel($options);
                    if (is_string($label)) {
                        $label = $this->translator->trans($label);
                    }
                    $transMessage = $this->translator->trans('form_builder.form.container.repeater.max', ['%label%' => $label, '%items%' => $maxEntries]);
                    $event->getForm()->addError(new FormError($transMessage));
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label'] = $this->getContainerLabel($options);
        $view->vars['attr']['data-label-add-block'] = $options['formbuilder_configuration']['label_add_block'];
        $view->vars['attr']['data-label-remove-block'] = $options['formbuilder_configuration']['label_remove_block'];
        $view->vars['attr']['data-repeater-min'] = $options['formbuilder_configuration']['min'];
        $view->vars['attr']['data-repeater-max'] = $options['formbuilder_configuration']['max'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add'          => true,
            'allow_delete'       => true,
            'delete_empty'       => true,
            'allow_extra_fields' => true
        ]);

        $entryOptionsNormalizer = function (Options $options, $globalEntryOptions) {
            return array_merge($globalEntryOptions, $this->getFormEntryOptions($options['formbuilder_configuration']));
        };

        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form_builder_container_repeater';
    }

    /**
     * @param $config
     *
     * @return array
     */
    private function getFormEntryOptions($config)
    {
        $options = [];

        if (empty($config['block_label'])) {
            $label = false;
        } else {
            $label = (string)$config['block_label'];
        }

        $options['label'] = $label;
        $options['add_block_counter'] = $config['add_block_counter'];

        return $options;
    }

    public function getParent()
    {
        return ContainerType::class;
    }
}