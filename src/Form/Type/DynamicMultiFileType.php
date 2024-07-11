<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\Form\FormTypeOptionsEvent;
use FormBuilderBundle\FormBuilderEvents;
use FormBuilderBundle\Registry\DynamicMultiFileAdapterRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DynamicMultiFileType extends AbstractType
{
    public function __construct(
        protected FormFactoryInterface $formFactory,
        protected Configuration $configuration,
        protected EventDispatcherInterface $eventDispatcher,
        protected DynamicMultiFileAdapterRegistry $dynamicMultiFileAdapterRegistry
    ) {
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

        $adapterFormFieldName = 'adapter';

        $dmfForm = $this->formFactory->createNamedBuilder(
            $adapterFormFieldName,
            $dmfAdapter->getForm(),
            null,
            $this->dispatchFormTypeOptionsEvent($adapterFormFieldName, $dmfAdapter->getForm(), $options)
        );

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

    private function dispatchFormTypeOptionsEvent(string $name, string $type, array $options): array
    {
        $event = new FormTypeOptionsEvent($name, $type, $options);

        $this->eventDispatcher->dispatch($event, FormBuilderEvents::FORM_TYPE_OPTIONS);

        return $event->getOptions();
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
