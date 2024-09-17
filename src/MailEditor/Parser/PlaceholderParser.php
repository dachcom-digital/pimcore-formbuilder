<?php

namespace FormBuilderBundle\MailEditor\Parser;

use FormBuilderBundle\MailEditor\Parser\TemplateParser\TemplateParserInterface;
use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
use FormBuilderBundle\MailEditor\AttributeBag;
use Symfony\Component\Form\FormInterface;

class PlaceholderParser implements PlaceholderParserInterface
{
    protected FormInterface $form;
    protected array $outputData;
    protected string $layoutType;

    public function __construct(
        protected MailEditorWidgetRegistry $mailEditorWidgetRegistry,
        protected iterable $templateParser,
        protected array $validHtmlTags,
        protected array $validTextTags,
    ) {
    }

    public function replacePlaceholderWithOutputData(string $layout, FormInterface $form, array $outputData, string $layoutType): string
    {
        $this->form = $form;
        $this->outputData = $outputData;
        $this->layoutType = $layoutType;

        // first, parse all container blocks
        $layout = preg_replace_callback($this->getPlaceholderRegex('fb-container-field'), [$this, 'parseContainerTag'], $layout);

        $layout = preg_replace_callback($this->getPlaceholderRegex('fb-field'), [$this, 'parseFieldTag'], $layout);

        return $this->parseLayoutTemplate($layout, $layoutType);
    }

    protected function parseContainerTag(array $tag): ?string
    {
        $type = $tag[1];

        $attributes = $this->parseAttributes($type, $tag[0]);
        $containerTemplate = $this->parseContainerTemplate($type, $tag[0]);

        $tagType = $attributes->get('type');
        $tagSubType = $attributes->get('sub_type');

        if ($tagType === null || !$this->mailEditorWidgetRegistry->has($tagType)) {
            return null;
        }

        $outputData = null;
        if ($tagSubType !== null && isset($this->outputData[$tagSubType])) {
            $outputData = $this->outputData[$tagSubType];
            $attributes->set('output_data', $outputData);
        }

        // no container data found. remove template.
        if ($outputData === null) {
            return '';
        }

        $widget = $this->mailEditorWidgetRegistry->get($tagType);

        $response = '';
        foreach ($outputData['fields'] as $containerBlock) {
            $response .= $widget->getValueForOutput($attributes, $this->layoutType);
            $response .= preg_replace_callback(
                $this->getPlaceholderRegex('fb-field'),
                function (array $tag) use ($containerBlock) {
                    return $this->parseFieldTag($tag, $containerBlock);
                },
                $containerTemplate
            );
        }

        return $response;
    }

    protected function parseLayoutTemplate(string $layout, string $layoutType): string
    {
        $allowedTags = $layoutType === 'html' ? $this->validHtmlTags : $this->validTextTags;

        $layout = strip_tags($layout, $allowedTags);

        /** @var TemplateParserInterface $templateParser */
        foreach ($this->templateParser as $templateParser) {
            if ($templateParser->supports($layoutType, $layout)) {
                return $templateParser->parse($layout);
            }
        }

        return $layout;
    }

    /**
     * @throws \Exception
     */
    protected function parseFieldTag(array $tag, ?array $containerData = null): ?string
    {
        $type = $tag[1];
        $attributes = $this->parseAttributes($type, $tag[0]);

        $tagType = $attributes->get('type');
        $tagSubType = $attributes->get('sub_type');

        if ($tagType === null || !$this->mailEditorWidgetRegistry->has($tagType)) {
            return null;
        }

        $widget = $this->mailEditorWidgetRegistry->get($tagType);

        if ($tagSubType !== null) {
            if ($containerData !== null) {
                $containerOutputDataIndex = array_search($tagSubType, array_column($containerData, 'name'), true);
                if ($containerOutputDataIndex !== false) {
                    $attributes->set('output_data', $containerData[$containerOutputDataIndex]);
                }
            } elseif (isset($this->outputData[$tagSubType])) {
                $attributes->set('output_data', $this->outputData[$tagSubType]);
            }
        }

        $attributes->set('form', $this->form);
        $attributes->set('raw_output_data', $this->outputData);

        return $widget->getValueForOutput($attributes, $this->layoutType);
    }

    protected function parseAttributes(string $type, string $text): AttributeBag
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($text);

        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query(sprintf('//%s/@*', $type));

        $attributes = [];
        foreach ($nodes as $node) {

            $value = $node->nodeValue;
            if ($value === 'true' || $value === 'false') {
                $value = $value === 'true';
            }

            $attributes[str_replace('data-', '', $node->nodeName)] = $value;
        }

        return new AttributeBag($attributes);
    }

    protected function parseContainerTemplate(string $type, string $text): string
    {
        preg_match('/<fb-container-field[^>]*>(.*)<\/fb-container-field>/', $text, $matches);

        return $matches[1];
    }

    protected function getPlaceholderRegex(string $type): string
    {
        $typeRegex = str_replace('-', '\-', $type);

        return sprintf('/<(%s)(.*?)<\/(%s)>/', $typeRegex, $typeRegex);
    }
}
