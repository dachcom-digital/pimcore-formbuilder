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

namespace FormBuilderBundle\Validator\EmailChecker;

use FormBuilderBundle\Configuration\Configuration;
use League\Flysystem\FilesystemOperator;
use function Symfony\Component\String\u;

final class DisposableEmailDomainChecker implements EmailCheckerInterface
{
    public const DISPOSABLE_EMAIL_DOMAIN_DATABASE_PATH = 'disposable_email_domains/domains.json';

    public function __construct(
        protected Configuration $configuration,
        protected FilesystemOperator $formBuilderEmailCheckerStorage
    ) {
    }

    public function isValid(string $email, array $context): bool
    {
        $config = $this->configuration->getConfig('spam_protection');
        $includeSubdomains = $config['email_checker']['disposable_email_domains']['include_subdomains'];

        if (!$this->formBuilderEmailCheckerStorage->fileExists(self::DISPOSABLE_EMAIL_DOMAIN_DATABASE_PATH)) {
            return true;
        }

        $domain = u($email)->after('@')->toString();

        if (!$domain) {
            return true;
        }

        $domains = json_decode(
            $this->formBuilderEmailCheckerStorage->read(self::DISPOSABLE_EMAIL_DOMAIN_DATABASE_PATH),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (in_array($domain, $domains, false)) {
            return false;
        }

        if ($includeSubdomains === false) {
            return true;
        }

        foreach ($domains as $root) {
            if (str_ends_with($domain, sprintf('.%s', $root))) {
                return false;
            }
        }

        return true;
    }
}
