<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Stream\AttachmentStreamInterface;
use Pimcore\Model\Asset;
use Pimcore\Translation\Translator;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use Symfony\Component\Form\FormInterface;

class DynamicMultiFileTransformer implements OutputTransformerInterface
{
    protected Translator $translator;
    protected AttachmentStreamInterface $attachmentStream;

    public function __construct(Translator $translator, AttachmentStreamInterface $attachmentStream)
    {
        $this->translator = $translator;
        $this->attachmentStream = $attachmentStream;
    }

    public function getValue(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): mixed
    {
        if (!$fieldDefinition instanceof FormFieldDefinitionInterface) {
            return null;
        }

        $attachmentData = $rawValue['adapter']['data'];

        $options = $fieldDefinition->getOptions();
        /** @var FormDataInterface $rootFormData */
        $rootFormData = $formField->getRoot()->getData();

        if (isset($options['submit_as_attachment']) && $options['submit_as_attachment'] === true) {
            $attachmentLinks = $this->attachmentStream->createAttachmentLinks($attachmentData, $fieldDefinition->getName());
            foreach ($attachmentLinks as $attachment) {
                $rootFormData->addAttachment($attachment);
            }

            // attachment is not required to get displayed in mail body

            return null;
        }

        $asset = $this->attachmentStream->createAttachmentAsset($attachmentData, $fieldDefinition->getName(), $rootFormData->getFormDefinition()->getName());

        if ($asset instanceof Asset) {
            $path = $asset->getFrontendFullPath();

            if (str_starts_with($path, 'http')) {
                return $path;
            }

            return sprintf('%s%s', \Pimcore\Tool::getHostUrl(), $asset->getRealFullPath());
        }

        return null;
    }

    public function getLabel(FieldDefinitionInterface $fieldDefinition, FormInterface $formField, mixed $rawValue, ?string $locale): ?string
    {
        if (!$fieldDefinition instanceof FormFieldDefinitionInterface) {
            return null;
        }

        $fieldOptions = $fieldDefinition->getOptions();
        $optionalOptions = $fieldDefinition->getOptional();

        $emailLabel = isset($optionalOptions['email_label']) && !empty($optionalOptions['email_label'])
            ? $this->translator->trans($optionalOptions['email_label'], [], null, $locale)
            : null;

        if (!empty($emailLabel)) {
            return $emailLabel;
        }

        return isset($fieldOptions['label']) && !empty($fieldOptions['label'])
            ? $this->translator->trans($fieldOptions['label'], [], null, $locale)
            : $fieldDefinition->getName();
    }
}
