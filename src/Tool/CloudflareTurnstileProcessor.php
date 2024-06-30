<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Tool\CloudflareTurnstile\Response;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class CloudflareTurnstileProcessor implements CloudflareTurnstileProcessorInterface
{
    protected string $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        protected Configuration $configuration,
        protected RequestStack $requestStack
    ) {
    }

    public function verify(mixed $value): Response
    {
        $client = new Client();
        $config = $this->configuration->getConfig('spam_protection');
        $turnstileConfig = $config['cloudflare_turnstile'];

        $response = $client->post(
            $this->url,
            [
                'form_params' => [
                    'secret'   => $turnstileConfig['secret_key'],
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
