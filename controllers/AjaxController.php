<?php

use Formbuilder\Controller\Action;
use Formbuilder\Model\Form;
use Formbuilder\Lib\Form\Frontend as FormFrontEnd;
use Formbuilder\Lib\Processor;

use Formbuilder\Lib\Form\File\FileHandler;
use Formbuilder\Tool\Session;

class Formbuilder_AjaxController extends Action {

    /**
     * @var FileHandler
     */
    private $fileHandler = NULL;

    public function init()
    {
        parent::init();

        $this->fileHandler = new FileHandler();
    }

    public function addFromUploadAction()
    {
        $this->setPlainHeader();

        $method = $_SERVER['REQUEST_METHOD'];

        $formConfig = $this->getFormInfoFromRequest();
        $formId = $formConfig->formId;
        $fieldName = $this->getParam('_fieldName');

        if ($method === 'POST')
        {
            $result = $this->fileHandler->handleUpload();

            $result['uploadName'] = $this->fileHandler->getRealFileName();

            if( isset( $result['success']) && $result['success'] === TRUE )
            {
                Session::addToTmpSession($formId, $fieldName, $result['uuid'], $result['uploadName']);
            }

            echo json_encode( $result );
            exit;
        }
        else if ($method === 'DELETE')
        {
            $this->deleteFromUploadAction();
        }
        else
        {
            $this->getResponse()->setHttpResponseCode(405);
            exit;
        }

    }

    public function deleteFromUploadAction()
    {
        $this->setPlainHeader();

        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $tokens = explode('/', $url);
        $uuid = $tokens[sizeof($tokens)-1];

        $formConfig = $this->getFormInfoFromRequest();
        $formId = $formConfig->formId;
        $fieldName = $this->getParam('_fieldName');

        //remove tmp element from session!
        Session::removeFromTmpSession($formId, $fieldName, $uuid);

        $result = $this->fileHandler->handleDelete( $uuid );

        echo json_encode( $result );
    }

    public function chunkDoneAction()
    {
        $this->setPlainHeader();

        $formConfig = $this->getFormInfoFromRequest();
        $formId = $formConfig->formId;
        $fieldName = $this->getParam('_fieldName');

        $result = $this->fileHandler->combineChunks();

        // To return a name used for uploaded file you can use the following line.
        $result['uploadName'] = $this->fileHandler->getRealFileName();

        if( isset( $result['success']) && $result['success'] === TRUE )
        {
            //add uuid to session to find it again later!
            Session::addToTmpSession($formId, $fieldName, $result['uuid'], $result['uploadName']);
        }

        echo json_encode( $result );

    }

    public function parseAction()
    {
        $formConfig = $this->getFormInfoFromRequest();

        $formId = $formConfig->formId;
        $language = $formConfig->language;

        $mailTemplateId = $formConfig->mailTemplateId;
        $copyMailTemplateId = $formConfig->copyMailTemplateId;
        $sendCopy = $formConfig->sendCopy;
        $formPreset = $formConfig->formPreset;

        $valid = FALSE;
        $redirect = FALSE;
        $message = '';
        $validationData = FALSE;

        $formData = Form::getById( $formId );

        if( $formData instanceof Form )
        {
            $frontendLib = new FormFrontEnd();

            $form = $frontendLib->getForm($formData->getId(), $language);

            $frontendLib->addDefaultValuesToForm(
                $form,
                [
                    'formData'              => $formData,
                    'formPreset'            => $formPreset,
                    'locale'                => $language,
                    'mailTemplateId'        => $mailTemplateId,
                    'copyMailTemplateId'    => $copyMailTemplateId,
                    'sendCopy'              => $sendCopy
                ]

            );

            $params = $frontendLib->parseFormParams( $this->getAllParams(), $form );

            $formValid = TRUE;
            $valid = FALSE;

            if( $frontendLib->hasRecaptchaV2() )
            {
                $formValid = $form->isValid( $params, $frontendLib->getRecaptchaV2Key() );
            }

            if( $formValid === TRUE )
            {
                $valid = $form->isValid( $params );
            }

            if( $valid )
            {
                $processor = new Processor();
                $processor->setSendCopy( $sendCopy );

                $processor->parse( $form, $formData, $mailTemplateId, $copyMailTemplateId );

                $valid = $processor->isValid();
                $message = $processor->getMessages();

                if( $valid === TRUE )
                {
                    $return = $this->afterSend( $mailTemplateId );

                    $valid = $return['valid'];
                    $redirect = $return['redirect'];
                    $message = $valid === FALSE ? $return['message'] : $return['html'];
                }

            }
            else
            {
                $validationData = $form->getMessages();
            }
        }

        $this->_helper->json(
            array(
                'success'           => $valid,
                'message'           => $message,
                'validationData'    => $validationData,
                'redirect'          => $redirect
            )
        );

    }

    private function afterSend( $mailTemplateId )
    {
        $redirect = FALSE;
        $error = FALSE;

        $successMessage = '';
        $statusMessage = '';

        $mailTemplate = \Pimcore\Model\Document::getById( $mailTemplateId );

        $afterSuccess = $mailTemplate->getProperty('mail_successfully_sent');

        //get the content from a snippet
        if( $afterSuccess instanceof \Pimcore\Model\Document\Snippet )
        {
            $params['document'] = $afterSuccess;

            if( $this->view instanceof \Pimcore\View )
            {
                try
                {
                    $successMessage = $this->view->action($afterSuccess->getAction(), $afterSuccess->getController(), $afterSuccess->getModule(), $params);
                }
                catch(\Exception $e)
                {
                    $error = TRUE;
                    $statusMessage = $e->getMessage();
                }

            }

        }

        //it's a redirect!
        else if( $afterSuccess instanceof \Pimcore\Model\Document)
        {
            $redirect = TRUE;
            $successMessage = $afterSuccess->getFullPath();
        }

        //it's just a string!
        else if( is_string( $afterSuccess ) )
        {
            $successMessage = $afterSuccess;
        }

        return array(
            'valid'     => $error === FALSE,
            'message'   => $statusMessage,
            'redirect'  => $redirect,
            'html'      => $successMessage
        );

    }

    private function getFormInfoFromRequest()
    {
        $formConfig = $this->getParam('_formConfig');

        if( empty( $formConfig ) )
        {
            return new \stdClass();
        }

        try
        {
            $data = json_decode( html_entity_decode($formConfig) );
        }
        catch(\Exception $e)
        {
            $data =  new \stdClass();
        }

        return $data;
    }

    private function setPlainHeader()
    {
        $this->disableViewAutoRender();
        $this->getResponse()
            ->setHeader('Content-type', 'text/plain')
            ->setHeader('Cache-Control','no-cache');
    }

}