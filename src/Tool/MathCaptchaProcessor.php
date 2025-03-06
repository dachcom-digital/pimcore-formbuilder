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

use Carbon\Carbon;
use FormBuilderBundle\Configuration\Configuration;

class MathCaptchaProcessor implements MathCaptchaProcessorInterface
{
    public const VALIDATION_STATE_VALID = 'valid';
    public const VALIDATION_STATE_INVALID_VALUE = 'invalid_value';
    public const VALIDATION_STATE_EXPIRED = 'expired';

    public function __construct(
        protected ?string $pimcoreEncryptionSecret,
        protected Configuration $configuration
    ) {
    }

    public function generateChallenge(string $difficulty, string $stamp): array
    {
        $numbers = match ($difficulty) {
            default => [
                random_int(1, 5),
                random_int(1, 4)
            ],
            'normal' => [
                random_int(10, 20),
                random_int(1, 10)
            ],
            'hard' => [
                random_int(100, 200),
                random_int(10, 20),
                random_int(1, 10)
            ],
        };

        $challenge = sprintf('%s = ', implode(' + ', $numbers));

        return [
            'user_challenge' => $challenge,
            'hash'           => $this->encryptChallenge(array_sum($numbers), $stamp)
        ];
    }

    public function generateStamp(): string
    {
        return Carbon::now()->toIso8601String();
    }

    public function verify(int $challenge, string $hash, string $stamp): string
    {
        $now = Carbon::now();
        $date = Carbon::parse($stamp)->addMinutes($this->getHashTtl());

        if ($now->isAfter($date)) {
            return self::VALIDATION_STATE_EXPIRED;
        }

        return $hash === $this->encryptChallenge($challenge, $stamp)
            ? self::VALIDATION_STATE_VALID
            : self::VALIDATION_STATE_INVALID_VALUE;
    }

    public function encryptChallenge(int $challenge, string $stamp): ?string
    {
        $encryptionSecret = $this->getEncryptionSecret();

        if ($encryptionSecret === null) {
            return null;
        }

        return hash_hmac(
            'sha256',
            (string) $challenge,
            sprintf('%s%s', $encryptionSecret, $stamp)
        );
    }

    private function getEncryptionSecret(): ?string
    {
        $encryptionSecret = $this->pimcoreEncryptionSecret;
        $config = $this->configuration->getConfig('spam_protection');
        $mathCaptchaConfig = $config['math_captcha'];

        if (empty($encryptionSecret)) {
            $encryptionSecret = $mathCaptchaConfig['encryption_secret'];
        }

        return $encryptionSecret;
    }

    private function getHashTtl(): int
    {
        $config = $this->configuration->getConfig('spam_protection');
        $mathCaptchaConfig = $config['math_captcha'];

        return $mathCaptchaConfig['hash_ttl'];
    }
}
