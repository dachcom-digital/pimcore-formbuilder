<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

use Formbuilder\Model\Form as FormModel;
use Formbuilder\Lib\Frontend;

class Form extends Document\Tag\Area\AbstractArea {

    public function action() {

        if ($this->view->editmode)
        {
            $mainList = new FormModel();
            $mains = $mainList->getAll();

            $store = array();

            if( !empty( $mains ) )
            {
                foreach( $mains as $form)
                {
                    $store[] = array($form['name'], $form['name'] );
                }
            }

            $typeStore = array(
                array('horizontal', 'Horizontal'),
                array('vertical', 'Vertical')
            );

            $this->view->availableForms = $store;
            $this->view->availableFormTypes = $typeStore;

        }

        $formName = NULL;
        $formHtml = NULL;

        $horizontalForm = TRUE;

        if (!$this->view->select("formName")->isEmpty())
        {
            $formName = $this->view->select("formName")->getData();
        }

        if( $this->view->select("formType")->getData() == 'vertical')
        {
            $horizontalForm = FALSE;
        }

        if( $formName !== NULL )
        {
            $frontendLib = new Frontend();

            $form = $frontendLib->getTwitterForm($formName, $this->view->language, $horizontalForm);

            $valid = $form->isValid( $this->getAllParams() );

            //var_dump( $form->getErrorMessages( ) );

            $isSubmit = !is_null( $this->getParam('submit') );

            if( $valid && $isSubmit ) {

                $mailTemplate = $this->view->href('sendMailTemplate')->getElement();
                $this->sendMail( $mailTemplate, $form->getValues() );

                $successMessage = $mailTemplate->getProperty('mail_successfully_sent');

                if( !empty( $successMessage ) )
                {
                    echo '<div class="row"><div class="col-xs-12"><div class="alert alert-success">' . $successMessage . '</div></div></div>';
                }

                $form->reset();

            }

            $formHtml = $form->render( $this->view );

        }

        $this->view->form = $formHtml;

    }

    public function postRenderAction(){

    }

    private function sendMail( $mailTemplate, $data ) {

        $mail = new \Pimcore\Mail();
        $mail->setParam('body', $this->parseHtml( $data ) );
        $mail->setDocument( $mailTemplate );
        $mail->send();

    }

    private function parseHtml( $data )
    {

        $html = '<table>';

        foreach( $data as $label => $field ) {

            $data = '';

            if( is_array( $field ) )
            {
                foreach( $field as $f )
                {
                    $data .= $f . '<br>';

                }
            }
            else
            {
                $data = $field;
            }

            $html .= '<tr>';

                $html .= '<td><strong>' . $label . '</strong></td>';
                $html .= '<td>' . $data . '</td>';

            $html .= '</tr>';

        }

        $html .= '</table>';

        return $html;

    }
}