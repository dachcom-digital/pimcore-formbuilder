<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Mapper\FormTypeOptionsMapper;
use FormBuilderBundle\Storage\FormField;
use FormBuilderBundle\Storage\FormFieldInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType as SymfonyTextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;

class TextAreaType extends AbstractType
{
    use SimpleTypeTrait;

    /**
     * @var string
     */
    protected $type = 'textarea_type';

    /**
     * @var string
     */
    protected $template = 'FormBuilderBundle:forms:fields/types/text.html.twig';

    /**
     * @param FormBuilderInterface $builder
     * @param FormFieldInterface   $field
     */
    public function build(FormBuilderInterface $builder, FormFieldInterface $field)
    {
        $options = $this->parseOptions($field->getOptions());
        $options['attr']['field-template'] = $field->getTemplate();

        $builder->add($field->getName(), SymfonyTextareaType::class, $options);
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
        $options['label'] = $typeOptions->hasLabel(TRUE) ? $typeOptions->getLabel() : FALSE;
        $options['attr']['placeholder'] = $typeOptions->hasPlaceholder() ? $typeOptions->getPlaceholder() : '';

        return $options;
    }
}