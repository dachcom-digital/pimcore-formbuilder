<?php

namespace FormBuilderBundle\Form\Type\DynamicMultiFile;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class DropZoneType extends AbstractType
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
        $vars = array_merge_recursive($view->vars, [
            'attr' => [
                'data-field-id'       => $view->parent->vars['id'],
                'data-engine-options' => json_encode([
                    'translations'       => $this->getInterfaceTranslations(),
                    'instance_error'      => $this->translator->trans('form_builder.dynamic_multi_file.global.cannot_destroy_active_instance'),
                    'multiple'           => isset($options['multiple']) && is_bool($options['multiple']) ? $options['multiple'] : false,
                    'max_file_size'      => is_numeric($options['max_file_size']) && $options['max_file_size'] > 0 ? (int) $options['max_file_size'] : null,
                    'allowed_extensions' => is_array($options['allowed_extensions']) ? join(',', $options['allowed_extensions']) : null,
                    'item_limit'         => is_numeric($options['item_limit']) && $options['item_limit'] > 0 ? (int) $options['item_limit'] : null
                ]),
                'class'               => [
                    'dynamic-multi-file',
                    sprintf('element-%s', $view->vars['name'])
                ]
            ]
        ]);

        $vars['attr']['class'] = join(' ', (array) $vars['attr']['class']);

        $view->vars = $vars;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'max_file_size'        => null,
            'allowed_extensions'   => [],
            'item_limit'           => null,
            'submit_as_attachment' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'form_builder_dynamicmultifile_drop_zone';
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
        return [
            'dictDefaultMessage'           => $this->translator->trans('form_builder.dynamic_multi_file.drop_files_here'),
            'dictFileTooBig'               => $this->translator->trans('form_builder.dynamic_multi_file.file_is_too_large'),
            'dictInvalidFileType'          => $this->translator->trans('form_builder.dynamic_multi_file.file_invalid_extension'),
            'dictResponseError'            => $this->translator->trans('form_builder.dynamic_multi_file.upload_failed'),
            'dictCancelUpload'             => $this->translator->trans('form_builder.dynamic_multi_file.cancel'),
            'dictUploadCanceled'           => $this->translator->trans('form_builder.dynamic_multi_file.canceled'),
            'dictCancelUploadConfirmation' => $this->translator->trans('form_builder.dynamic_multi_file.sure_to_cancel'),
            'dictRemoveFile'               => $this->translator->trans('form_builder.dynamic_multi_file.remove'),
            'dictMaxFilesExceeded'         => $this->translator->trans('form_builder.dynamic_multi_file.too_many_items'),
        ];
    }
}
