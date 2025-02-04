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
use FormBuilderBundle\Tool\FriendlyCaptcha\Response;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class FriendlyCaptchaProcessor implements FriendlyCaptchaProcessorInterface
{
    public function __construct(
        protected Configuration $configuration,
        protected RequestStack $requestStack
    ) {
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
