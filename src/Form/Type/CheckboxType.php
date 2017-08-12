<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Mapper\FormTypeOptionsMapper;
use FormBuilderBundle\Storage\FormFieldInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType as SymfonyCheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckboxType extends AbstractType
{
    use SimpleTypeTrait;

    /**
     * @var string
     */
    protected $type = 'checkbox_type';

    /**
     * @var string
     */
    protected $template = 'FormBuilderBundle:forms:fields/types/checkbox.html.twig';

    /**
     * @param FormBuilderInterface $builder
     * @param FormFieldInterface   $field
     */
    public function build(FormBuilderInterface $builder, FormFieldInterface $field)
    {
        $options = $this->parseOptions($field->getOptions());
        $options['attr']['field-template'] = $field->getTemplate();

        $builder->add($field->getName(), SymfonyCheckboxType::class, $options);
    }

    /**
     * @param FormTypeOptionsMapper $typeOptions
     *
     * @return array
     */
    public function parseOptions(FormTypeOptionsMapper $typeOptions)
    {
        $options = [
            'attr'        => [],
            'constraints' => []
        ];

        // required
        $isRequired = $typeOptions->hasRequired() ? $typeOptions->isRequired() : FALSE;

        if ($isRequired) {
            $options['constraints'][] = new NotBlank();
        }

        $options['required'] = $isRequired;
        $options['label'] = $typeOptions->hasLabel(TRUE) ? $typeOptions->getLabel(TRUE) : FALSE;

        return $options;
    }
}