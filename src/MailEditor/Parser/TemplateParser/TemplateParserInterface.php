<?php

namespace FormBuilderBundle\MailEditor\Parser\TemplateParser;

interface TemplateParserInterface
{
    public function supports(string $layoutType, string $layout): bool;

    public function parse(string $template): string;
}
