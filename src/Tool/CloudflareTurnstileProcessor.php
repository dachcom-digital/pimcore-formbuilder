<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
