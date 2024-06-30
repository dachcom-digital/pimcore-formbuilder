<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Validator\Constraints\CloudflareTurnstile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CloudflareTurnstileType extends AbstractType
{
    public function __construct(
        protected RequestStack $requestStack,
        protected Configuration $configuration
    ) {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $config = $this->configuration->getConfig('spam_protection');
        $turnstileConfig = $config['cloudflare_turnstile'];

        $locale = $options['lang'] ?? null;
        if ($locale === null) {
            $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
        }

        $friendlyCaptchaDataAttributes = array_filter([
            'sitekey'    => $turnstileConfig['site_key'],
            'lang'       => str_contains($locale, '_') ? explode('_', $locale)[0] : $locale,
            'theme'      => $options['theme'] ?? 'auto',
            'appearance' => $options['appearance'] ?? 'always',
            'size'       => $options['size'] ?? 'normal',
        ]);

        $view->vars['turnstile_attributes'] = $friendlyCaptchaDataAttributes;
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_cloudflare_turnstile_type';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped'      => false,
            'lang'        => false,
            'theme'       => 'auto',
            'appearance'  => 'always',
            'size'        => 'normal',
            'constraints' => [new CloudflareTurnstile()],
        ]);
    }
}
