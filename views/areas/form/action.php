<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

use Formbuilder\Model\Form as FormModel;
use Formbuilder\Lib\Frontend;
use Formbuilder\Lib\Mailer;

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

        $mailTemplate = $this->view->href('sendMailTemplate')->getElement();

        if( $formName !== NULL )
        {
            $form = new FormModel();
            $formId = $form->getIdByName($formName);

            $frontendLib = new Frontend();

            $form = $frontendLib->getTwitterForm($formId, $this->view->language, $horizontalForm);

            if( $form !== FALSE )
            {
                $frontendLib->addDefaultValuesToForm(
                    $form,
                    array(
                        'formId' => $formId,
                        'locale' => $this->view->language,
                        'mailTemplate' => $mailTemplate
                    )
                );

                $isSubmit = !is_null( $this->getParam('submit') );

                if( $isSubmit )
                {
                    $valid = $form->isValid( $frontendLib->parseFormParams( $this->getAllParams(), $form ) );

                    if( $valid )
                    {
                        Mailer::sendForm( $mailTemplate->getId(), array('data' => $form->getValues() ) );

                        $successMessages = Mailer::getMessages();

                        if (!empty($successMessages))
                        {
                            echo '<div class="row"><div class="col-xs-12"><div class="alert alert-success">';

                                foreach( $successMessages as $message )
                                {
                                    echo $message . '<br>';
                                }

                            echo '</div></div></div>';

                        }

                        $form->reset();
                    }

                }

                $formHtml = $form->render( $this->view );

            }

        }

        $this->view->form = $formHtml;

    }

}