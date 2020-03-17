<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class LocalizedValuesCollectionType extends AbstractType
{
    /**
     * @var string
     */
    protected $defaultLocaleCode = 'default';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entryOptions = $options['entry_options']($this->defaultLocaleCode);

        if (!isset($entryOptions['constraints'])) {
            $entryOptions['constraints'] = [];
        }

        $entryOptions['constraints'][] = new Valid();
        $entryOptions['label'] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $websiteLocales = \Pimcore\Tool::getValidLanguages();

        $resolver->setDefaults([
            'entries'     => array_merge(['default'], $websiteLocales),
            'entry_name'  => function (string $localeCode): string {
                return $localeCode;
            },
            'constraints' => [
                new Valid(),
            ],
        ])->setNormalizer('entry_options', function ($options, $additionalValues) {
            return function (string $localeCode) use ($additionalValues): array {
                $entryOptions = [
                    'required' => $localeCode === $this->defaultLocaleCode,
                    'label'    => null,
                ];

                if (is_array($additionalValues)) {
                    return array_merge($entryOptions, $additionalValues);
                }

                return $entryOptions;
            };
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return FixedCollectionType::class;
    }
}
