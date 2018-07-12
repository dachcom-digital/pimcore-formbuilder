<?php

namespace FormBuilderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetType extends AbstractType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * DynamicChoiceType constructor.
     *
     * @param $requestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'path'      => NULL,
            'href_type' => NULL,
            'mapped'    => FALSE,
            'label'     => FALSE,
            'required'  => FALSE
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $vars = array_merge_recursive($view->vars, [
            'data' => '',
            'attr' => [
                'data-field-name' => $view->vars['name'],
                'class' => 'form-builder-snippet-element'
            ],
            'path' => $this->getSnippetPath($options['path'])
        ]);

        $vars['attr']['class'] = join(' ', (array)$vars['attr']['class']);
        $view->vars = $vars;
    }

    /**
     * @param $paths
     * @return string|null
     */
    private function getSnippetPath($paths)
    {
        // legacy
        if (is_string($paths)) {
            return $paths;
        }

        $path = null;
        $locale = $this->requestStack->getMasterRequest()->getLocale();

        // current locale found
        if (isset($paths[$locale]) && !empty($paths[$locale])) {
            return $paths[$locale];
        }

        // search for fallback locale
        $fallbackLanguages = \Pimcore\Tool::getFallbackLanguagesFor($locale);
        foreach ($fallbackLanguages as $fallbackLanguage) {
            if (isset($paths[$fallbackLanguage]) && !empty($paths[$fallbackLanguage])) {
                return $paths[$fallbackLanguage];
            }
        }

        // search for default locale
        $defaultLocale = \Pimcore\Tool::getDefaultLanguage();
        if (isset($paths[$defaultLocale]) && !empty($paths[$defaultLocale])) {
            return $paths[$defaultLocale];
        }

        //no locale found. use the first one.
        return reset($paths);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'form_builder_snippet_type';
    }
}