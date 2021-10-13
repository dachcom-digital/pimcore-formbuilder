<?php

namespace FormBuilderBundle\MailEditor\Parser;

use FormBuilderBundle\Registry\MailEditorWidgetRegistry;
use Symfony\Component\Form\FormInterface;

class PlaceholderParser implements PlaceholderParserInterface
{
    protected array $outputData;
    protected FormInterface $form;
    protected MailEditorWidgetRegistry $mailEditorWidgetRegistry;

    public function __construct(MailEditorWidgetRegistry $mailEditorWidgetRegistry)
    {
        $this->mailEditorWidgetRegistry = $mailEditorWidgetRegistry;
    }

    public function replacePlaceholderWithOutputData(string $layout, FormInterface $form, array $outputData): string
    {
        $this->outputData = $outputData;
        $this->form = $form;

        return preg_replace_callback($this->getPlaceholderRegex(), [$this, 'parseSquareBracketsTag'], $layout);
    }

    /**
     * @throws \Exception
     */
    protected function parseSquareBracketsTag(array $tag): ?string
    {
        $type = $tag[1];
        $config = $this->parseSquareBracketsAttributes($tag[2]);

        if (!$this->mailEditorWidgetRegistry->has($type)) {
            return null;
        }

        $widget = $this->mailEditorWidgetRegistry->get($type);

        // add field value to widget.
        if (isset($config['sub-type'], $this->outputData[$config['sub-type']])) {
            $config['outputData'] = $this->outputData[$config['sub-type']];
        }

        $cleanConfig = [
            'form' => $this->form
        ];

        foreach ($config as $key => $value) {
            if ($value === 'true' || $value === 'false') {
                $value = $value === 'true';
            }

            $cleanConfig[$key] = $value;
        }

        return $widget->getValueForOutput($cleanConfig);
    }

    protected function parseSquareBracketsAttributes(string $text): array
    {
        $attributes = [];
        $pattern = $this->getSquareBracketsAttributeRegex();
        $text = preg_replace('/[\x{00a0}\x{200b}]+/u', ' ', $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $attributes[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $attributes[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $attributes[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) && strlen($m[7])) {
                    $attributes[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $attributes[] = stripcslashes($m[8]);
                }
            }
            foreach ($attributes as &$value) {
                if (str_contains($value, '<')) {
                    if (preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value) !== 1) {
                        $value = '';
                    }
                }
            }
        } else {
            $attributes = [ltrim($text)];
        }

        return $attributes;
    }

    protected function getSquareBracketsAttributeRegex(): string
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    }

    protected function getPlaceholderRegex(): string
    {
        $allowedRex = implode('|', $this->mailEditorWidgetRegistry->getAllIdentifier());

        return '/\\[\\[(' . $allowedRex . ')(.*?)\\]\\]/';
    }
}
