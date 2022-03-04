<?php

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Stream\AttachmentStreamInterface;
use Pimcore\Model\Asset;
use Pimcore\Translation\Translator;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Storage\FormFieldSimpleInterface;
use Symfony\Component\Form\FormInterface;

class DynamicMultiFileTransformer implements OutputTransformerInterface
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var AttachmentStreamInterface
     */
    protected $attachmentStream;

    /**
     * @param Translator                $translator
     * @param AttachmentStreamInterface $attachmentStream
     */
    public function __construct(Translator $translator, AttachmentStreamInterface $attachmentStream)
    {
        $this->translator = $translator;
        $this->attachmentStream = $attachmentStream;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(FormFieldSimpleInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
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

        $asset = $this->attachmentStream->createAttachmentAsset($rawValue['data'], $fieldDefinition->getName(), $rootFormData->getFormDefinition()->getName());

        if ($asset instanceof Asset) {
            return sprintf('%s%s', \Pimcore\Tool::getHostUrl(), $asset->getRealFullPath());
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(FormFieldSimpleInterface $fieldDefinition, FormInterface $formField, $rawValue, $locale)
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
