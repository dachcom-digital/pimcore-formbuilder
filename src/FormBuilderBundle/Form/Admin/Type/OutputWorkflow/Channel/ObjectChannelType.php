<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component\PimcoreHrefType;
use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Object\ObjectMappingElementCollectionType;

class ObjectChannelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('resolveStrategy', ChoiceType::class, ['choices' => ['existingObject' => 'existingObject', 'newObject' => 'newObject']]);
        $builder->add('objectMappingData', ObjectMappingElementCollectionType::class, []);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['resolveStrategy'])) {
                return;
            }

            // reset old form data to allow conditional switches
            $form->setData(null);

            if ($data['resolveStrategy'] === 'existingObject') {
                $form->add('resolvingObject', PimcoreHrefType::class);
                $form->add('dynamicObjectResolver', TextType::class);
            } elseif ($data['resolveStrategy'] === 'newObject') {
                $form->add('storagePath', PimcoreHrefType::class);
                $form->add('resolvingObjectClass', TextType::class);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
