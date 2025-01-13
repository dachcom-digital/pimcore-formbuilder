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

namespace FormBuilderBundle\Tool\FriendlyCaptcha;

class Response
{
    public static function fromJson(string $json): self
    {
        try {
            $responseData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return new self(false, 'invalid-json');
        }

        return new self(
            $responseData['success'] ?? false,
            $responseData['details'] ?? null,
            $responseData['errors'] ?? null
        );
    }

    public function __construct(
        protected bool $success,
        protected ?string $details,
        protected ?array $errors = null
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'details' => $this->getDetails(),
            'errors'  => $this->getErrors(),
        ];
    }
}
