<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FixedCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['entries'] as $entry) {
            $entryType = $options['entry_type']($entry);
            $entryName = $options['entry_name']($entry);
            $entryOptions = $options['entry_options']($entry);

            $builder->add($entryName, $entryType, array_replace([
                'property_path' => '[' . $entryName . ']',
                'block_name'    => 'entry',
            ], $entryOptions));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('entries');
        $resolver->setAllowedTypes('entries', ['array', \Traversable::class]);

        $resolver->setRequired('entry_type');
        $resolver->setAllowedTypes('entry_type', ['string', 'callable']);
        $resolver->setNormalizer('entry_type', self::optionalCallableNormalizer());

        $resolver->setRequired('entry_name');
        $resolver->setAllowedTypes('entry_name', ['callable']);

        $resolver->setDefault('entry_options', function () {
            return [];
        });

        $resolver->setAllowedTypes('entry_options', ['array', 'callable']);
        $resolver->setNormalizer('entry_options', self::optionalCallableNormalizer());
    }

    /**
     * @return callable
     */
    public static function optionalCallableNormalizer(): callable
    {
        return function (Options $options, $value) {
            if (is_callable($value)) {
                return $value;
            }

            return function () use ($value) {
                return $value;
            };
        };
    }
}