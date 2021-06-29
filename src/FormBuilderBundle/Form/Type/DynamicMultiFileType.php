<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Registry\DynamicMultiFileAdapterRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicMultiFileType extends AbstractType
{
    protected Configuration $configuration;
    protected DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry;

    public function __construct(
        Configuration $configuration,
        DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry
    ) {
        $this->configuration = $configuration;
        $this->dynamicMultiFileAdapterRegistry = $dynamicMultiFileAdapterRegistry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound'             => true,
            'max_file_size'        => 0,
            'allowed_extensions'   => [],
            'item_limit'           => 0,
            'submit_as_attachment' => false
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dmfAdapterName = $this->configuration->getConfig('dynamic_multi_file_adapter');
        $dmfAdapter = $this->dynamicMultiFileAdapterRegistry->get($dmfAdapterName);

        $options['mapped'] = false;
        $options['label'] = empty($options['label']) ? false : $options['label'];
        $options['attr']['data-dynamic-multi-file-instance'] = 'true';
        $options['attr']['data-js-handler'] = $dmfAdapter->getJsHandler();

        $builder->add('adapter', $dmfAdapter->getForm(), $options);
        $builder->add('data', HiddenType::class, []);

        $builder->get('data')->addModelTransformer(new CallbackTransformer(
            function ($identifier) {
                return $identifier === null ? null : json_encode($identifier);
            },
            function ($identifier) {
                return $identifier === null ? [] : json_decode($identifier, true);
            }
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['id'] = $view->vars['id'];
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_dynamicmultifile';
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
