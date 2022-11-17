<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\PimcoreHrefType;

class EmailChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('mailTemplate', PimcoreHrefType::class);
        $builder->add('ignoreFields', CollectionType::class, ['allow_add' => true, 'entry_type' => TextType::class]);
        $builder->add('allowAttachments', CheckboxType::class);
        $builder->add('forcePlainText', CheckboxType::class);
        $builder->add('disableDefaultMailBody', CheckboxType::class);
        $builder->add('disableMailLogging', CheckboxType::class);
        $builder->add('mailLayoutData', TextType::class);

        $builder->get('ignoreFields')->addEventListener(
            FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            if ($event->getData() === '') {
                $event->setData([]);
            }
        });

        $builder->get('mailLayoutData')
            ->addModelTransformer(new CallbackTransformer(
                function ($mailLayout) {
                    return $mailLayout;
                },
                function ($mailLayout) {
                    if ($mailLayout === null) {
                        return null;
                    }

                    $mailLayout = str_replace('&nbsp;', ' ', $mailLayout);

                    return preg_replace('/\s+/', ' ', $mailLayout);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
