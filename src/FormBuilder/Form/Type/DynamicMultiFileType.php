<?php

namespace FormBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicMultiFileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_merge_recursive($view->vars, [
            'type'           => 'text',
            'value'          => '',
            'attr'           => [
                'class' => 'formbuilder-dynamic-multifile'
            ],
            'engine_options' => [
                'messages'           => $this->getInterfaceTranslations(),
                'multiple'           => $options['multiple'] ? 1 : 0,
                'max_file_size'      => is_numeric($options['max_file_size']) ? (int)$options['max_file_size'] * 1024 * 1024 : 0,
                'allowed_extensions' => is_array($options['allowed_extensions']) ? $options['allowed_extensions'] : []
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'max_file_size'      => FALSE,
            'allowed_extensions' => FALSE,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'dynamicmultifile';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }

    private function getInterfaceTranslations()
    {
        $coreMessages = [
            'typeError'                    => '{file} has an invalid extension. Valid extension(s): {extensions}.',
            'sizeError'                    => '{file} is too large, maximum file size is {sizeLimit}.',
            'minSizeError'                 => '{file} is too small, minimum file size is {minSizeLimit}.',
            'emptyError'                   => '{file} is empty, please select files again without it.',
            'noFilesError'                 => 'No files to upload.',
            'tooManyItemsError'            => 'Too many items ({netItems}) would be uploaded.  Item limit is {itemLimit}.',
            'maxHeightImageError'          => 'Image is too tall.',
            'maxWidthImageError'           => 'Image is too wide.',
            'minHeightImageError'          => 'Image is not tall enough.',
            'minWidthImageError'           => 'Image is not wide enough.',
            'retryFailTooManyItems'        => 'Retry failed - you have reached your file limit.',
            'onLeave'                      => 'The files are being uploaded, if you leave now the upload will be canceled.',
            'unsupportedBrowserIos8Safari' => 'Unrecoverable error - this browser does not permit file uploading of any kind due to serious bugs in iOS8 Safari. Please use iOS8 Chrome until Apple fixes these issues.'
        ];

        $deleteMessages = [
            'confirmMessage'     => 'Are you sure you want to delete {filename}?',
            'deletingStatusText' => 'Deleting...',
            'deletingFailedText' => 'Delete failed'
        ];

        $interfacesText = [
            'formatProgress'     => '{percent}% of {total_size}',
            'failUpload'         => 'Upload failed',
            'waitingForResponse' => 'Processing...',
            'paused'             => 'Paused'
        ];

        $messages = [
            'core'   => $coreMessages,
            'delete' => $deleteMessages,
            'text'   => $interfacesText
        ];

        return $messages;
    }
}