<?php

namespace FormBuilderBundle\Stream;

use Pimcore\Model\Asset;

interface AttachmentStreamInterface
{
    public function createAttachmentAsset(array $data, string $fieldName, string $formName): ?Asset;

    public function createAttachmentLinks(array $data, string $formName): array;

    public function removeAttachmentByFileInfo(array $fileInfo): void;
}
