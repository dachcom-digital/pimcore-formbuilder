<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\PimcoreHrefType;

class EmailChannelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mailTemplate', PimcoreHrefType::class);
        $builder->add('ignoreFields', ChoiceType::class);
        $builder->add('allowAttachments', CheckboxType::class);
        $builder->add('forcePlainText', CheckboxType::class);
        $builder->add('disableDefaultMailBody', CheckboxType::class);
        $builder->add('mailLayoutData', TextType::class);

        $builder->get('ignoreFields')->resetViewTransformers();

        $builder->get('mailLayoutData')
            ->addModelTransformer(new CallbackTransformer(
                function ($mailLayout) {
                    return $mailLayout;
                },
                function ($mailLayout) {
                    if ($mailLayout === null) {
                        return $mailLayout;
                    }

                    $mailLayout = str_replace('&nbsp;', ' ', $mailLayout);
                    $mailLayout = preg_replace('/\s+/', ' ', $mailLayout);

                    return $mailLayout;
                }
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
