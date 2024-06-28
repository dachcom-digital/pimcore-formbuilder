<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Tool\FriendlyCaptcha\Response;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class FriendlyCaptchaProcessor implements FriendlyCaptchaProcessorInterface
{
    protected Configuration $configuration;
    protected RequestStack $requestStack;

    public function __construct(Configuration $configuration, RequestStack $requestStack)
    {
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
    }

    public function verify(mixed $value): Response
    {
        $client = new Client();
        $config = $this->configuration->getConfig('spam_protection');
        $friendlyCaptchaConfig = $config['friendly_captcha'];

        $verificationEndpoint = $friendlyCaptchaConfig['eu_only'] === true
            ? $friendlyCaptchaConfig['verification']['eu_endpoint']
            : $friendlyCaptchaConfig['verification']['global_endpoint'];

        $response = $client->post(
            $verificationEndpoint,
            [
                'form_params' => [
                    'secret'   => $friendlyCaptchaConfig['secret_key'],
                    'sitekey'  => $friendlyCaptchaConfig['site_key'],
                    'solution' => $value,
                ],
            ]
        );

        return Response::fromJson($response->getBody());
    }
}
