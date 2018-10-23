<?php

namespace DachcomBundle\Test\Helper\Browser;

use Codeception\Module;
use DachcomBundle\Test\Util\FormHelper;

class WebDriver extends Module\WebDriver
{
    /**
     * Actor Function to see a page with enabled edit-mode
     *
     * @param string $page
     */
    public function amOnPageInEditMode(string $page)
    {
        $this->amOnPage(sprintf('%s?pimcore_editmode=true', $page));
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param null   $data
     * @param null   $selector
     */
    public function seeAEditableConfiguration(string $name, string $type, array $options, $data = null, $selector = null)
    {
        $this->see(FormHelper::generateEditableConfiguration($name, $type, $options, $data), $selector);
    }
}
