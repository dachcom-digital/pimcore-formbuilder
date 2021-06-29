<?php

namespace FormBuilderBundle\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class FormErrorsSerializer implements FormErrorsSerializerInterface
{
    public function getErrors(FormInterface $form): array
    {
        $errors = [];
        $formName = $form->getName();

        foreach ($form->getErrors(true, true) as $formError) {

            $name = '';
            $origin = $formError->getOrigin();
            $currentFieldName = $origin->getName();

            if (!$formError->getCause() instanceof ConstraintViolationInterface) {

                if (!isset($errors['general'])) {
                    $errors['general'] = [];
                }

                $errors['general'][] = $formError->getMessage();

                continue;
            }

            while ($origin = $origin->getParent()) {
                if ($formName !== $currentFieldName) {
                    $name = sprintf('%s_%s', $origin->getName(), $name);
                }
            }

            $fieldName = sprintf('%s%s', $name, $currentFieldName);

            if (!in_array($fieldName, $errors)) {
                $errors[$fieldName] = [];
            }

            $errors[$fieldName][] = $formError->getMessage();
        }

        return $errors;
    }
}
