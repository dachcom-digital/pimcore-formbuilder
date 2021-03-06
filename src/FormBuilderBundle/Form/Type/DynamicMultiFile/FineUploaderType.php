<?php

namespace FormBuilderBundle\Form\Type\DynamicMultiFile;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class FineUploaderType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_merge_recursive($view->vars, [
            'attr' => [
                'data-field-id'       => $view->parent->vars['id'],
                'data-engine-options' => json_encode([
                    'messages'           => $this->getInterfaceTranslations(),
                    'multiple'           => isset($options['multiple']) && $options['multiple'] === true ? 1 : 0,
                    'max_file_size'      => is_numeric($options['max_file_size']) ? (int) $options['max_file_size'] * 1024 * 1024 : 0,
                    'allowed_extensions' => is_array($options['allowed_extensions']) ? $options['allowed_extensions'] : [],
                    'item_limit'         => is_numeric($options['item_limit']) ? (int) $options['item_limit'] : 0
                ]),
                'class'               => join(' ', [
                    'dynamic-multi-file',
                    sprintf('element-%s', $view->vars['name'])
                ])
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'max_file_size'        => 0,
            'allowed_extensions'   => [],
            'item_limit'           => 0,
            'submit_as_attachment' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form_builder_dynamicmultifile_fine_uploader';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return array
     */
    private function getInterfaceTranslations()
    {
        $globalMessages = [
            'cannotDestroyActiveInstanceError' => $this->translator->trans('form_builder.dynamic_multi_file.global.cannot_destroy_active_instance')
        ];

        $coreMessages = [
            'typeError'                    => $this->translator->trans('form_builder.dynamic_multi_file.file_invalid_extension'),
            'sizeError'                    => $this->translator->trans('form_builder.dynamic_multi_file.file_is_too_large'),
            'minSizeError'                 => $this->translator->trans('form_builder.dynamic_multi_file.file_is_too_small'),
            'emptyError'                   => $this->translator->trans('form_builder.dynamic_multi_file.file_is_empty'),
            'noFilesError'                 => $this->translator->trans('form_builder.dynamic_multi_file.no_files_to_upload'),
            'tooManyItemsError'            => $this->translator->trans('form_builder.dynamic_multi_file.too_many_items'),
            'maxHeightImageError'          => $this->translator->trans('form_builder.dynamic_multi_file.image_too_tall'),
            'maxWidthImageError'           => $this->translator->trans('form_builder.dynamic_multi_file.image_too_wide'),
            'minHeightImageError'          => $this->translator->trans('form_builder.dynamic_multi_file.image_not_tall_enough'),
            'minWidthImageError'           => $this->translator->trans('form_builder.dynamic_multi_file.image_not_wide_enough'),
            'retryFailTooManyItems'        => $this->translator->trans('form_builder.dynamic_multi_file.retry_failed_limit'),
            'onLeave'                      => $this->translator->trans('form_builder.dynamic_multi_file.files_uploaded'),
            'unsupportedBrowserIos8Safari' => $this->translator->trans('form_builder.dynamic_multi_file.unrecoverable_error')
        ];

        $deleteMessages = [
            'confirmMessage'     => $this->translator->trans('form_builder.dynamic_multi_file.sure_to_delete'),
            'deletingStatusText' => $this->translator->trans('form_builder.dynamic_multi_file.deleting'),
            'deletingFailedText' => $this->translator->trans('form_builder.dynamic_multi_file.delete_failed')
        ];

        $interfacesText = [
            'formatProgress'     => $this->translator->trans('form_builder.dynamic_multi_file.percent_of_size'),
            'failUpload'         => $this->translator->trans('form_builder.dynamic_multi_file.upload_failed'),
            'waitingForResponse' => $this->translator->trans('form_builder.dynamic_multi_file.processing'),
            'paused'             => $this->translator->trans('form_builder.dynamic_multi_file.paused')
        ];

        return [
            'core'   => $coreMessages,
            'delete' => $deleteMessages,
            'text'   => $interfacesText,
            'global' => $globalMessages
        ];
    }
}
