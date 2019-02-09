<?php

namespace DachcomBundle\Test\functional\Fields;

use DachcomBundle\Test\FunctionalTester;

class HtmlTagFieldCest extends AbstractFieldCest
{
    protected $type = 'html_tag';

    protected $name = 'html_tag_field';

    protected $displayName = 'html_tag_field';

    /**
     * @param FunctionalTester $I
     */
    public function testHtmlTagField(FunctionalTester $I)
    {
        $options = [
            'tag'   => 'label',
            'label' => 'Tag Element Content'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->see('Tag Element Content', 'label.form-builder-html-tag-element');
    }

    /**
     * @param FunctionalTester $I
     */
    public function testHtmlTagFieldSubmission(FunctionalTester $I)
    {
        $options = [
            'tag'   => 'label',
            'label' => 'Tag Element Content'
        ];

        list($adminEmail, $testFormBuilder, $form) = $this->setupField($I, $options);

        $I->click($testFormBuilder->getFormFieldSelector(1, 'submit'));

        $I->cantSeePropertyKeysInEmail($adminEmail, ['html_tag_field']);
    }
}
