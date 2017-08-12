<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Mapper\FormTypeOptionsMapper;
use FormBuilderBundle\Storage\FormFieldInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChoiceType extends AbstractType
{
    /**
     * @var string
     */
    protected $type = 'choice_type';

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

        $builder->add($field->getName(), SymfonyChoiceType::class, $options);
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
        $options['expanded'] = $typeOptions->getExpanded();
        $options['multiple'] = $typeOptions->getMultiple();
        $options['choices'] = $this->parseChoices($typeOptions->getChoices());

        return $options;
    }

    public function parseChoices($choices)
    {
        $parsedChoices = [];
        foreach($choices as $choice) {

            //groups
            if(isset($choice[0])) {
                $groupName = $choice[0]['name'];
                foreach($choice as $index => $choiceGroup) {
                    $parsedChoices[$groupName][$choiceGroup['option']] = $choiceGroup['value'];
                }
            } else {
                $parsedChoices[$choice['option']] = $choice['value'];
            }
        }

        return $parsedChoices;

    }
}