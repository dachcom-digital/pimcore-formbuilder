<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Transformer\Output;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FieldDefinitionInterface;
use FormBuilderBundle\Model\FormFieldDefinitionInterface;
use FormBuilderBundle\Stream\AttachmentStreamInterface;
use Pimcore\Model\Asset;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DynamicMultiFileTransformer implements OutputTransformerInterface
{
    public function __construct(
        protected RouterInterface $router,
        protected TranslatorInterface $translator,
        protected AttachmentStreamInterface $attachmentStream
    ) {
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
            $hostUrl = \Pimcore\Tool::getHostUrl();

            if (isset($options['submit_as_admin_deep_link']) && $options['submit_as_admin_deep_link'] === true) {
                return sprintf('%s%s?%s_%d_%s', $hostUrl, $this->router->generate('pimcore_admin_login_deeplink'), 'asset', $asset->getId(), $asset->getType());
            }

            $path = $asset->getFrontendFullPath();
            if (str_starts_with($path, 'http')) {
                return $path;
            }

            return sprintf('%s%s', $hostUrl, $asset->getRealFullPath());
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
