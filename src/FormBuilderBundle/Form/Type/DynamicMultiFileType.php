<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Registry\DynamicMultiFileAdapterRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicMultiFileType extends AbstractType
{
    protected FormFactoryInterface $formFactory;
    protected Configuration $configuration;
    protected DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry;

    public function __construct(
        FormFactoryInterface $formFactory,
        Configuration $configuration,
        DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry
    ) {
        $this->formFactory = $formFactory;
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

        $options['compound'] = true;
        $options['label'] = empty($options['label']) ? false : $options['label'];
        $options['attr']['data-dynamic-multi-file-instance'] = 'true';
        $options['attr']['data-js-handler'] = $dmfAdapter->getJsHandler();

        $dmfForm = $this->formFactory->createNamedBuilder('adapter', $dmfAdapter->getForm(), null, $options);

        $dmfForm->add('data', HiddenType::class, []);
        $dmfForm->get('data')->addModelTransformer(new CallbackTransformer(
            function ($identifier) {
                return $identifier === null ? null : json_encode($identifier, JSON_THROW_ON_ERROR);
            },
            function ($identifier) {
                return $identifier === null ? [] : json_decode($identifier, true, 512, JSON_THROW_ON_ERROR);
            }
        ));

        $builder->add($dmfForm);
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
