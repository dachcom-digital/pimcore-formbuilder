<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Tool\LocaleDataMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetType extends AbstractType
{
    protected RequestStack $requestStack;
    protected LocaleDataMapper $localeDataMapper;

    public function __construct(RequestStack $requestStack, LocaleDataMapper $localeDataMapper)
    {
        $this->requestStack = $requestStack;
        $this->localeDataMapper = $localeDataMapper;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'path'      => null,
            'href_type' => null,
            'mapped'    => false,
            'label'     => false,
            'required'  => false
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $vars = array_merge_recursive($view->vars, [
            'data' => '',
            'attr' => [
                'data-field-name' => $view->vars['name'],
                'data-field-id'   => $view->vars['id'],
                'class'           => 'form-builder-snippet-element'
            ],
            'path' => $this->getSnippetId($options['path'])
        ]);

        $vars['attr']['class'] = join(' ', (array) $vars['attr']['class']);
        $view->vars = $vars;
    }

    /**
     * @param string|array $data
     */
    private function getSnippetId($data): ?string
    {
        // legacy
        if (is_string($data)) {
            return $data;
        }

        $locale = $this->requestStack->getMainRequest()->getLocale();

        return $this->localeDataMapper->mapHref($locale, $data);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'form_builder_snippet_type';
    }
}
