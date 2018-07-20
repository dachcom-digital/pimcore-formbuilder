<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Tool\HrefLocaleMapper;
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
     * @var HrefLocaleMapper
     */
    protected $hrefLocaleMapper;

    /**
     * DynamicChoiceType constructor.
     *
     * @param RequestStack     $requestStack
     * @param HrefLocaleMapper $hrefLocaleMapper
     */
    public function __construct(RequestStack $requestStack, HrefLocaleMapper $hrefLocaleMapper)
    {
        $this->requestStack = $requestStack;
        $this->hrefLocaleMapper = $hrefLocaleMapper;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'path'      => null,
            'href_type' => null,
            'mapped'    => false,
            'label'     => false,
            'required'  => false
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
                'class'           => 'form-builder-snippet-element'
            ],
            'path' => $this->getSnippetId($options['path'])
        ]);

        $vars['attr']['class'] = join(' ', (array)$vars['attr']['class']);
        $view->vars = $vars;
    }

    /**
     * @param string|array $data
     * @return string|null
     */
    private function getSnippetId($data)
    {
        // legacy
        if (is_string($data)) {
            return $data;
        }

        $locale = $this->requestStack->getMasterRequest()->getLocale();

        return $this->hrefLocaleMapper->map($locale, $data);
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