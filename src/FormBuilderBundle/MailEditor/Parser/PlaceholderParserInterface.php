<?php

namespace FormBuilderBundle\MailEditor\Parser;

use Symfony\Component\Form\FormInterface;

interface PlaceholderParserInterface
{
    /**
     * @param string        $layout
     * @param FormInterface $form
     * @param array         $outputData
     *
     * @return string
     */
    public function replacePlaceholderWithOutputData(string $layout, FormInterface $form, array $outputData);
}
