<?php

namespace DachcomBundle\Test\Helper\Browser;

use Codeception\Module;
use DachcomBundle\Test\Util\FileGeneratorHelper;
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
     * @param null $path
     */
    public function setDownloadPathForWebDriver($path = null)
    {
        if (is_null($path)) {
            $path = FileGeneratorHelper::getDownloadPath();
        }

        $url = $this->webDriver->getCommandExecutor()->getAddressOfRemoteServer();
        $uri = '/session/' . $this->webDriver->getSessionID() . '/chromium/send_command';
        $body = [
            'cmd'    => 'Page.setDownloadBehavior',
            'params' => ['behavior' => 'allow', 'downloadPath' => $path]
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->post($url . $uri, ['body' => json_encode($body)]);

        try {
            $responseData = json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $responseData = [];
        }

        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals(0, $responseData['status']);

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
