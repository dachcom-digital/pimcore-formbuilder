<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\PimcoreHrefType;

class EmailChannelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mailTemplate', PimcoreHrefType::class);
        $builder->add('ignoreFields', ChoiceType::class);
        $builder->add('allowAttachments', CheckboxType::class);
        $builder->add('forcePlainText', CheckboxType::class);
        $builder->add('disableDefaultMailBody', CheckboxType::class);

        $builder->get('ignoreFields')->resetViewTransformers();
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
