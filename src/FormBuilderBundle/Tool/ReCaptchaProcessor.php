<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Tool\ReCaptcha\Response;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class ReCaptchaProcessor implements ReCaptchaProcessorInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $url = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param Configuration $configuration
     * @param RequestStack  $requestStack
     */
    public function __construct(Configuration $configuration, RequestStack $requestStack)
    {
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function verify($value)
    {
        $client = new Client();
        $config = $this->configuration->getConfig('recaptcha_v3');

        $response = $client->post(
            $this->url,
            [
                'form_params' => [
                    'secret'   => $config['secret_key'],
                    'remoteip' => $this->getClientIp(),
                    'response' => $value
                ]
            ]
        );

        return Response::fromJson($response->getBody());
    }

    /**
     * @return string|null
     */
    protected function getClientIp()
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        return $request->getClientIp();
    }
}
