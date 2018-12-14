<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

class FormErrorsSerializer implements FormErrorsSerializerInterface
{
    /**
     * @inheritdoc
     */
    public function getErrors(FormInterface $form)
    {
        return $this->recursiveFormErrors($form->getErrors(true, false), [$form->getName()]);
    }

    /**
     * @param FormErrorIterator $formErrors
     * @param array             $prefixes
     *
     * @return array
     */
    private function recursiveFormErrors(FormErrorIterator $formErrors, array $prefixes)
    {
        $errors = [];
        foreach ($formErrors as $formError) {
            if ($formError instanceof FormErrorIterator) {
                $errors = array_merge($errors, $this->recursiveFormErrors($formError, array_merge($prefixes, [$formError->getForm()->getName()])));
            } elseif ($formError instanceof FormError) {
                $index = count($prefixes) === 1 ? 'general' : implode('_', $prefixes);
                /** @scrutinizer ignore-call */
                $errors[$index][] = $formError->getMessage();
            }
        }

        return $errors;
    }
}