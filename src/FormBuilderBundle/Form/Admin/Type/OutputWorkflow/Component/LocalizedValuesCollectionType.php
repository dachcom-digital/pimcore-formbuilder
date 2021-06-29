<?php

namespace FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Component;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class LocalizedValuesCollectionType extends AbstractType
{
    protected string $defaultLocaleCode = 'default';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entryOptions = $options['entry_options']($this->defaultLocaleCode);

        if (!isset($entryOptions['constraints'])) {
            $entryOptions['constraints'] = [];
        }

        $entryOptions['constraints'][] = new Valid();
        $entryOptions['label'] = false;
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getParent(): string
    {
        return FixedCollectionType::class;
    }
}
