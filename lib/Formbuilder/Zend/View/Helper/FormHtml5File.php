<?php

namespace Formbuilder\Zend\View\Helper;

class FormHtml5File extends \Zend_View_Helper_FormElement
{
    public function formHtml5File($name, $value = null, $attribs = null, $options = null, $listsep = '')
    {
        $allowedExtensions = array();
        $sizeLimit = 0;

        if( isset( $attribs['allowedExtensions'] ) && is_array( $attribs['allowedExtensions'] ) )
        {
            $allowedExtensions = $attribs['allowedExtensions'];
        }

        //transform MB to bytes!
        if( isset( $attribs['maxFileSize'] ) && is_numeric( $attribs['maxFileSize'] ) )
        {
            $sizeLimit = ( $attribs['maxFileSize'] * 1024 * 1024 );
        }

        $coreMessages = array(
            'typeError'                     => $this->view->translate('{file} has an invalid extension. Valid extension(s): {extensions}.'),
            'sizeError'                     => $this->view->translate('{file} is too large, maximum file size is {sizeLimit}.'),
            'minSizeError'                  => $this->view->translate('{file} is too small, minimum file size is {minSizeLimit}.'),
            'emptyError'                    => $this->view->translate('{file} is empty, please select files again without it.'),
            'noFilesError'                  => $this->view->translate('No files to upload.'),
            'tooManyItemsError'             => $this->view->translate('Too many items ({netItems}) would be uploaded.  Item limit is {itemLimit}.'),
            'maxHeightImageError'           => $this->view->translate('Image is too tall.'),
            'maxWidthImageError'            => $this->view->translate('Image is too wide.'),
            'minHeightImageError'           => $this->view->translate('Image is not tall enough.'),
            'minWidthImageError'            => $this->view->translate('Image is not wide enough.'),
            'retryFailTooManyItems'         => $this->view->translate('Retry failed - you have reached your file limit.'),
            'onLeave'                       => $this->view->translate('The files are being uploaded, if you leave now the upload will be canceled.'),
            'unsupportedBrowserIos8Safari'  => $this->view->translate('Unrecoverable error - this browser does not permit file uploading of any kind due to serious bugs in iOS8 Safari. Please use iOS8 Chrome until Apple fixes these issues.')
        );

        $deleteMessages = array(
            'confirmMessage'                => $this->view->translate('Are you sure you want to delete {filename}?'),
            'deletingStatusText'            => $this->view->translate('Deleting...'),
            'deletingFailedText'            => $this->view->translate('Delete failed')
        );

        $interfacesText = array(
            'formatProgress'                => $this->view->translate('{percent}% of {total_size}'),
            'failUpload'                    => $this->view->translate('Upload failed'),
            'waitingForResponse'            => $this->view->translate('Processing...'),
            'paused'                        => $this->view->translate('Paused')
        );

        $messages = array( 'core' => $coreMessages, 'delete' => $deleteMessages, 'text' => $interfacesText );

        return $this->view->partial('formbuilder/form/elements/html5file/default.php', array( 'fieldName' => $attribs['realName'], 'message' => $messages, 'sizeLimit' => $sizeLimit, 'allowedExtensions' => $allowedExtensions ));
    }
}