<?php

namespace FormBuilderBundle\MailEditor\Parser;

use Symfony\Component\Form\FormInterface;

interface PlaceholderParserInterface
{
    public function replacePlaceholderWithOutputData(string $layout, FormInterface $form, array $outputData): string;
}
