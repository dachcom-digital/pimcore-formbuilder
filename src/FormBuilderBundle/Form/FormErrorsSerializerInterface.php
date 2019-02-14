<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormInterface;

interface FormErrorsSerializerInterface
{
    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function getErrors(FormInterface $form);
}
