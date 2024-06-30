<?php

namespace FormBuilderBundle\Tool\ReCaptcha;

class Response
{
    public static function fromJson(string $json): Response
    {
        try {
            $responseData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return new self(false, ['invalid-json']);
        }

        return new self(
            $responseData['success'] ?? false,
            $responseData['error-codes'] ?? null,
            $responseData['hostname'] ?? null,
            $responseData['challenge_ts'] ?? null,
            $responseData['apk_package_name'] ?? null,
            $responseData['score'] ?? null,
            $responseData['action'] ?? null
        );
    }

    public function __construct(
        protected bool $success,
        protected ?array $errorCodes = [],
        protected ?string $hostname = null,
        protected ?string $challengeTs = null,
        protected ?string $apkPackageName = null,
        protected mixed $score = null,
        protected ?string $action = null
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

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function getChallengeTs(): ?string
    {
        return $this->challengeTs;
    }

    public function getApkPackageName(): ?string
    {
        return $this->apkPackageName;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function toArray(): array
    {
        return [
            'success'          => $this->isSuccess(),
            'hostname'         => $this->getHostname(),
            'challenge_ts'     => $this->getChallengeTs(),
            'apk_package_name' => $this->getApkPackageName(),
            'score'            => $this->getScore(),
            'action'           => $this->getAction(),
            'error-codes'      => $this->getErrorCodes(),
        ];
    }
}
