<?php

namespace FormBuilderBundle\MailEditor\Parser\TemplateParser;

class PlainTextParser implements TemplateParserInterface
{
    public function supports(string $layoutType, string $layout): bool
    {
        return $layoutType === 'text';
    }

    public function parse(string $template): string
    {
        return str_replace(['<br />', '<br>'], "\n", $template);
    }
}
