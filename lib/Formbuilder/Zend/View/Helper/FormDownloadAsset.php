<?php

namespace Formbuilder\Zend\View\Helper;

class FormDownloadAsset extends \Zend_View_Helper_FormElement
{
    public function formDownloadAsset($name, $value = null, $attribs = null, $options = null, $listsep = '')
    {
        $filePath = isset( $attribs['asset'] ) ? $attribs['asset'] : FALSE;

        $asset = \Pimcore\Model\Asset::getByPath( $filePath );

        $assetFile = NULL;

        $fileName = '';
        $fileSize = '';
        $fileExtension = '';

        if( $asset instanceof \Pimcore\Model\Asset )
        {
            $assetFile = $asset;

            $fileName = $assetFile->getMetadata('title') ? $assetFile->getMetadata('title') : $assetFile->getFilename();
            $fileSize = $assetFile->getFileSize('kb', 2);
            $fileExtension = \Pimcore\File::getFileExtension($assetFile->getFilename());
        }


        return $this->view->partial('formbuilder/form/elements/download/default.php', [
            'file' => $assetFile,
            'meta' => [
                'fileName'      => $fileName,
                'fileSize'      => $fileSize,
                'fileExtension' => $fileExtension
            ]
        ]);
    }
}