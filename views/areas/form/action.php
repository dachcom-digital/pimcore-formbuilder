<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

use Formbuilder\Lib\Processor;
use Formbuilder\Lib\Form\Frontend;
use Formbuilder\Tool\Preset;
use Formbuilder\Model\Configuration;
use Formbuilder\Model\Form as FormModel;

class Form extends Document\Tag\Area\AbstractArea {

    public function action()
    {
        if ($this->view->editmode)
        {
            $mainList = new FormModel();
            $mains = $mainList->getAll();

            $store = array();

            if( !empty( $mains ) )
            {
                foreach( $mains as $form)
                {
                    $store[] = [ $form['id'], $form['name'] ];
                }
            }

            $typeStore = [
                [ 'horizontal', 'Horizontal' ],
                [ 'vertical', 'Vertical' ]
            ];

            $this->view->availableForms = $store;
            $this->view->availableFormTypes = $typeStore;

            $formPresets = Preset::getAvailablePresets();
            $formPresetsStore = [];

            if( !empty( $formPresets ) )
            {
                $formPresetsStore[] = [ 'custom', $this->view->translateAdmin('no form preset') ];

                foreach( $formPresets as $presetName => $preset )
                {
                    $formPresetsStore[] = [ $presetName, $preset['niceName'] ];
                }

                if($this->view->select('formPreset')->isEmpty() )
                {
                    $this->view->select('formPreset')->setDataFromResource( 'custom' );
                }

                $this->view->availableFormPresets = $formPresetsStore;
            }

        }

        $formData = NULL;
        $formId = NULL;
        $formHtml = NULL;
        $messageHtml = NULL;
        $messages = [];

        $noteMessage = '';
        $noteError = FALSE;

        $horizontalForm = TRUE;
        $sendCopy = $this->view->checkbox('userCopy')->isChecked() === '1';
        $formPreset = $this->view->select('formPreset')->getData();

        if( empty( $formPreset ) || is_null( $formPreset ) )
        {
            $formPreset = 'custom';
        }

        if (!$this->view->select('formName')->isEmpty())
        {
            $formId = $this->view->select('formName')->getData();
        }

        if( $this->view->select('formType')->getData() == 'vertical')
        {
            $horizontalForm = FALSE;
        }

        $copyMailTemplate = NULL;

        if( !empty( $formId ) )
        {
            try
            {
                $formData = FormModel::getById( $formId );

                if( !$formData instanceof FormModel)
                {
                    $noteMessage = 'Form (' . $formId . ') is not a valid FormBuilder Element.';
                    $noteError = TRUE;
                }

            } catch( \Exception $e )
            {
                $noteMessage = $e->getMessage();
                $noteError = TRUE;
            }
        }
        else
        {
            $noteMessage = 'No valid form selected.';
            $noteError = TRUE;
        }

        if( $noteError === TRUE )
        {
            $this->view->assign(
                [
                    'form'          => NULL,
                    'messages'      => NULL,
                    'formId'        => NULL,
                    'formPreset'    => NULL,
                    'notifications' => ['error' => $noteError, 'message' => $noteMessage ],
                ]
            );

            return FALSE;
        }

        $frontendLib = new Frontend();

        $form = $frontendLib->getTwitterForm($formData->getId(), $this->view->language, $horizontalForm);

        $_mailTemplate = NULL;
        $_copyMailTemplate = NULL;

        if( $formPreset === 'custom')
        {
            $_mailTemplate = $this->view->href('sendMailTemplate')->getElement();
            $_copyMailTemplate = $this->view->href('sendCopyMailTemplate')->getElement();
        }
        else
        {
            $language = isset( $this->view->language ) ? $this->view->language : FALSE;
            $presetInfo = Preset::getPresetConfig( $formPreset, $language );

            if( !empty( $presetInfo ) )
            {
                if( $presetInfo['mail'] !== FALSE )
                {
                    $_mailTemplate = \Pimcore\Model\Document\Email::getByPath( $presetInfo['mail'] );
                }

                if( $presetInfo['mailCopy'] !== FALSE )
                {
                    $sendCopy = TRUE;
                    $_copyMailTemplate = \Pimcore\Model\Document\Email::getByPath( $presetInfo['mailCopy'] );
                }
            }

        }

        $mailTemplateId = NULL;
        $copyMailTemplateId = NULL;

        if( $_mailTemplate instanceof \Pimcore\Model\Document\Email )
        {
            $mailTemplateId = $_mailTemplate->getId();
        }

        if( $sendCopy === TRUE && $_copyMailTemplate instanceof \Pimcore\Model\Document\Email )
        {
            $copyMailTemplateId = $_copyMailTemplate->getId();
        }
        else //disable copy!
        {
            $sendCopy = FALSE;
        }

        if( $form !== FALSE )
        {
            $frontendLib->addDefaultValuesToForm(
                $form,
                [
                    'formData'              => $formData,
                    'formPreset'            => $formPreset,
                    'formId'                => $formId,
                    'locale'                => $this->view->language,
                    'mailTemplateId'        => $mailTemplateId,
                    'copyMailTemplateId'    => $copyMailTemplateId,
                    'sendCopy'              => $sendCopy
                ]
            );

            if( \Zend_Controller_Front::getInstance()->getRequest()->isPost() )
            {
                $valid = $form->isValid( $frontendLib->parseFormParams( $this->getAllParams(), $form ) );

                if( $valid )
                {
                    $processor = new Processor();
                    $processor->setSendCopy( $sendCopy );

                    $processor->parse( $form, $formData, $mailTemplateId, $copyMailTemplateId );

                    $valid = $processor->isValid();
                    $messages = $processor->getMessages();

                    if( $valid === TRUE )
                    {
                        $messages = [ $this->view->translate('form has been successfully sent') ];
                    }

                    if ( !empty($messages) )
                    {
                        $messageHtml = $this->view->partial('formbuilder/form/partials/notifications.php', ['valid' => $valid, 'messages' => $messages]);
                    }

                    $form->reset();
                }

            }

            $formHtml = $form->render( $this->view );

        }

        $this->view->assign(
            [
                'form'          => $formHtml,
                'messages'      => $messageHtml,
                'formId'        => $formId,
                'formPreset'    => $formPreset,
                'notifications' => [],
            ]
        );

    }

}