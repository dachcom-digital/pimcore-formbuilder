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

namespace FormBuilderBundle\Tool\CloudflareTurnstile;

class Response
{
    public static function fromJson(string $json): self
    {
        try {
            $responseData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return new self(false, ['invalid-json']);
        }

        return new self(
            $responseData['success'] ?? false,
            $responseData['error-codes'] ?? null,
            $responseData['challenge_ts'] ?? null,
            $responseData['hostname'] ?? null,
            $responseData['action'] ?? null,
            $responseData['cdata'] ?? null,
            $responseData['metadata'] ?? null,
        );
    }

    public function __construct(
        protected bool $success,
        protected ?array $errorCodes = [],
        protected ?string $challengeTs = null,
        protected ?string $hostname = null,
        protected ?string $action = null,
        protected ?string $cdata = null,
        protected ?array $metadata = null
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorCodes(): ?array
    {
        return $this->errorCodes;
    }

    public function getChallengeTs(): ?string
    {
        return $this->challengeTs;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getCdata(): ?string
    {
        return $this->cdata;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'success'     => $this->isSuccess(),
            'errorCodes'  => $this->getErrorCodes(),
            'challengeTs' => $this->getChallengeTs(),
            'hostname'    => $this->getHostname(),
            'action'      => $this->getAction(),
            'metadata'    => $this->getMetadata(),
        ];
    }
}
