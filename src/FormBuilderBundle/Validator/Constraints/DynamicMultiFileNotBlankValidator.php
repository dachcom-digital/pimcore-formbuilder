<?php

namespace FormBuilderBundle\Validator\Constraints;

use FormBuilderBundle\Form\Data\FormDataInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
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
        $formData = $this->context->getRoot()->getData();
        if (!$formData instanceof FormDataInterface) {
            return;
        }

        $formDefinition = $formData->getFormDefinition();
        if (!$formDefinition instanceof FormDefinitionInterface) {
            return;
        }

        if (!$constraint instanceof DynamicMultiFileNotBlank) {
            throw new UnexpectedTypeException($constraint, DynamicMultiFileNotBlank::class);
        }

        $object = $this->context->getObject();
        if (!$object instanceof FormInterface) {
            return;
        }

        $fieldId = $this->buildId($object);
        $field = $this->context->getObject()->getConfig();

        if (!$field instanceof FormConfigBuilderInterface) {
            return;
        }

        /** @var NamespacedAttributeBag $sessionBag */
        $sessionBag = $this->session->getBag('form_builder_session');

        $counter = 0;
        foreach ($sessionBag->getIterator() as $key => $sessionValue) {
            $formKey = 'file_' . $formDefinition->getId();
            if (substr($key, 0, strlen($formKey)) !== $formKey) {
                continue;
            }

            if (isset($sessionValue['fieldId']) && $sessionValue['fieldId'] === $fieldId) {
                $counter++;
            }
        }

        if ($counter === 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    private function buildId(FormInterface $form): string
    {
        $id = $form->getName();

        if ($form->getParent() !== null) {
            $id = sprintf('%s_%s', $this->buildId($form->getParent()), $id);
        }

        return $id;
    }
}
