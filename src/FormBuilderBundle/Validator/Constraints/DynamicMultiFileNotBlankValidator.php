<?php

namespace FormBuilderBundle\Validator\Constraints;

use FormBuilderBundle\Storage\Form;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DynamicMultiFileNotBlankValidator extends ConstraintValidator
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $formEntity = $this->context->getRoot()->getData();
        if (!$formEntity instanceof Form) {
            return;
        }

        if (!$constraint instanceof DynamicMultiFileNotBlank) {
            throw new UnexpectedTypeException($constraint, DynamicMultiFileNotBlank::class);
        }

        $field = $this->context->getObject()->getConfig();
        if (!$field instanceof FormConfigBuilderInterface) {
            return;
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');

        $counter = 0;
        foreach ($sessionBag->getIterator() as $key => $sessionValue) {
            $formKey = 'file_' . $formEntity->getId();
            if (substr($key, 0, strlen($formKey)) !== $formKey) {
                continue;
            }

            if (isset($sessionValue['fieldName']) &&
                $sessionValue['fieldName'] == $field->getName()) {
                $counter++;
            }
        }

        if ($counter === 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
