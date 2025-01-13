<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
