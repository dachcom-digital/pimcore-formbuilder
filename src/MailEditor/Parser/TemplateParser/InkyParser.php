<?php

namespace FormBuilderBundle\MailEditor\Parser\TemplateParser;

class InkyParser implements TemplateParserInterface
{
    public function __construct(
        protected bool $useEmailizr,
        protected array $replaces
    ) {
    }

    public function supports(string $layoutType, string $layout): bool
    {
        return $layoutType === 'html' && $this->useEmailizr === true;
    }

    public function parse(string $template): string
    {
        return str_replace(array_keys($this->replaces), array_values($this->replaces), $template);
    }
}
