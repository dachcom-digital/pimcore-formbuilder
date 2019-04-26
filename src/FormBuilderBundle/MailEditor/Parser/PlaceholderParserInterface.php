<?php

namespace FormBuilderBundle\MailEditor\Parser;

interface PlaceholderParserInterface
{
    /**
     * @param string $layout
     * @param array  $outputData
     *
     * @return string
     */
    public function replacePlaceholderWithOutputData(string $layout, array $outputData);
}
