<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;

class SnippetFieldCest extends AbstractFieldCest
{
    protected $type = 'snippet';

    protected $name = 'snippet_field';

    protected $displayName = 'snippet_field';

    /**
     * @param FunctionalTester $I
     */
    public function testSnippetField(FunctionalTester $I)
    {
        $snippet = $I->haveASnippetDocument('form-element-snippet');

        $options = [
            'path' => [
                'en' => [
                    'id'   => $snippet->getId(),
                    'type' => 'document'
                ],
                'de' => [
                    'id'   => null,
                    'type' => null
                ]
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('snippet content with id 2', '.form-builder-snippet-element h3');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testSnippetFieldSubmission(FunctionalTester $I)
    {
        $snippet = $I->haveASnippetDocument('form-element-snippet');

        $options = [
            'path' => [
                'en' => [
                    'id'   => $snippet->getId(),
                    'type' => 'document'
                ],
                'de' => [
                    'id'   => null,
                    'type' => null
                ]
            ]
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->cantSeePropertyKeysInEmail($adminEmail, ['snippet_field']);
    }
}
