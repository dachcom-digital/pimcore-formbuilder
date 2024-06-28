<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Validator\Constraints\FriendlyCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FriendlyCaptchaType extends AbstractType
{
    protected Configuration $configuration;
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, Configuration $configuration)
    {
        $this->requestStack = $requestStack;
        $this->configuration = $configuration;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $config = $this->configuration->getConfig('spam_protection');
        $friendlyCaptchaConfig = $config['friendly_captcha'];

        if (!empty($options['callback'])) {
            $view->vars['attr']['data-callback'] = $options['callback'];
        }

        if (!empty($options['callback'])) {
            $view->vars['attr']['data-callback'] = $options['callback'];
        }

        $locale = $options['lang'] ?? null;
        if ($locale === null) {
            $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
        }

        $friendlyCaptchaDataAttributes = array_filter([
            'sitekey'         => $friendlyCaptchaConfig['site_key'],
            'lang'            => str_contains($locale, '_') ? explode('_', $locale)[0] : $locale,
            'start'           => $options['start'] ?? 'focus',
            'callback'        => $options['callback'] ?? null,
            'puzzle-endpoint' => $friendlyCaptchaConfig['eu_only'] === true
                ? $friendlyCaptchaConfig['puzzle']['eu_endpoint']
                : $friendlyCaptchaConfig['puzzle']['global_endpoint'],
        ]);

        $view->vars['friendly_captcha_attributes'] = $friendlyCaptchaDataAttributes;
        $view->vars['darkmode'] = $options['darkmode'] ?? false;
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_friendly_captcha_type';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'lang'        => null,
            'start'       => 'focus',
            'callback'    => null,
            'mapped'      => false,
            'darkmode'    => false,
            'constraints' => [new FriendlyCaptcha()],
        ]);

        $resolver->setAllowedValues('start', ['auto', 'focus', 'none']);
    }
}
