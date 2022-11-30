<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Model\FunnelActionElement;
use FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action\FunnelActionElementStack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var FunnelActionElementStack $funnelActionElementStack */
        $funnelActionElementStack = $options['funnel_action_element_stack'];

        /** @var FunnelActionElement $element */
        foreach ($funnelActionElementStack->getAll() as $element) {

            if ($element->isDisabled()) {
                continue;
            }

            $builder->add($element->getFunnelActionDefinition()->getName(), SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'funnel_action_element_stack' => null,
            'workflow_name'               => null,
            'funnel_name'                 => null,
        ]);

        $resolver->setAllowedTypes('funnel_action_element_stack', ['null', FunnelActionElementStack::class]);
        $resolver->setAllowedTypes('workflow_name', ['null', 'string']);
        $resolver->setAllowedTypes('funnel_name', ['null', 'string']);
    }
}
