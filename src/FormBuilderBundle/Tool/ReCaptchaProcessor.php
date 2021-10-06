<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Tool\ReCaptcha\Response;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class ReCaptchaProcessor implements ReCaptchaProcessorInterface
{
    protected Configuration $configuration;
    protected string $url = 'https://www.google.com/recaptcha/api/siteverify';
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
        $reCaptchaConfig = $config['recaptcha_v3'];

        $response = $client->post(
            $this->url,
            [
                'form_params' => [
                    'secret'   => $reCaptchaConfig['secret_key'],
                    'remoteip' => $this->getClientIp(),
                    'response' => $value
                ]
            ]
        );

        return Response::fromJson($response->getBody());
    }

    protected function getClientIp(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        return $request->getClientIp();
    }
}
