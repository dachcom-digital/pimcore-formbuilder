<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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

            if (!array_key_exists($fieldName, $errors)) {
                $errors[$fieldName] = [];
            }

            $errors[$fieldName][] = $formError->getMessage();
        }

        return $errors;
    }
}
