<?php

namespace FormBuilderBundle\Stream;

use Pimcore\Model\Asset;

interface AttachmentStreamInterface
{
    /**
     * @param array  $data
     * @param string $formName
     *
     * @return null|Asset
     */
    public function createAttachmentAsset($data, $formName);

    /**
     * @param array  $data
     * @param string $formName
     *
     * @return array
     */
    public function createAttachmentLinks($data, $formName);

    /**
     * @param array $fileInfo
     */
    public function removeAttachmentByFileInfo(array $fileInfo);
}
