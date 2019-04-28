<?php

namespace FormBuilderBundle\MailEditor\Parser;

use FormBuilderBundle\Registry\MailEditorWidgetRegistry;

class PlaceholderParser implements PlaceholderParserInterface
{
    /**
     * @var array
     */
    protected $outputData;

    /**
     * @var MailEditorWidgetRegistry
     */
    protected $mailEditorWidgetRegistry;

    /**
     * @param MailEditorWidgetRegistry $mailEditorWidgetRegistry
     */
    public function __construct(MailEditorWidgetRegistry $mailEditorWidgetRegistry)
    {
        $this->mailEditorWidgetRegistry = $mailEditorWidgetRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function replacePlaceholderWithOutputData(string $layout, array $outputData)
    {
        $this->outputData = $outputData;

        $data = preg_replace_callback($this->getPlaceholderRegex(), [$this, 'parseSquareBracketsTag'], $layout);

        return $data;
    }

    /**
     * @param array $tag
     *
     * @return null|string
     *
     * @throws \Exception
     */
    protected function parseSquareBracketsTag($tag)
    {
        $type = $tag[1];
        $config = $this->parseSquareBracketsAttributes($tag[2]);

        if (!$this->mailEditorWidgetRegistry->has($type)) {
            return null;
        }

        $widget = $this->mailEditorWidgetRegistry->get($type);

        // add field value to widget.
        if (isset($config['sub-type']) && isset($this->outputData[$config['sub-type']])) {
            $config['outputData'] = $this->outputData[$config['sub-type']];
        }

        $cleanConfig = [];
        foreach ($config as $key => $value) {

            if ($value === 'true' || $value === 'false') {
                $value = $value === 'true';
            }

            $cleanConfig[$key] = $value;
        }

        return $widget->getValueForOutput($cleanConfig);
    }

    /**
     * @param string $text
     *
     * @return array
     */
    protected function parseSquareBracketsAttributes($text)
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
                if (strpos($value, '<') !== false) {
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

    /**
     * @return string
     */
    protected function getSquareBracketsAttributeRegex()
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    }

    /**
     * @return string
     */
    protected function getPlaceholderRegex()
    {
        $allowedRex = join('|', $this->mailEditorWidgetRegistry->getAllIdentifier());

        return '/\\[\\[(' . $allowedRex . ')(.*?)\\]\\]/';
    }
}
