<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormInterface;

interface FormErrorsSerializerInterface
{
    public function getErrors(FormInterface $form): array;
}
