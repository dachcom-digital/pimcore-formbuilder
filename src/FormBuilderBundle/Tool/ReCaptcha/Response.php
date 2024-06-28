<?php

namespace FormBuilderBundle\Tool\ReCaptcha;

class Response
{
    private bool $success;
    private array $errorCodes;
    private ?string $hostname;
    private ?string $challengeTs;
    private ?string $apkPackageName;
    private ?float $score;
    private ?string $action;

    public static function fromJson(string $json): Response
    {
        $responseData = json_decode($json, true);

        if (!$responseData) {
            return new self(false, ['invalid-json']);
        }

        $hostname = $responseData['hostname'] ?? null;
        $challengeTs = $responseData['challenge_ts'] ?? null;
        $apkPackageName = $responseData['apk_package_name'] ?? null;
        $score = isset($responseData['score']) ? (float) $responseData['score'] : null;
        $action = $responseData['action'] ?? null;

        if (isset($responseData['success']) && $responseData['success'] == true) {
            return new self(true, [], $hostname, $challengeTs, $apkPackageName, $score, $action);
        }

        if (isset($responseData['error-codes']) && is_array($responseData['error-codes'])) {
            return new self(false, $responseData['error-codes'], $hostname, $challengeTs, $apkPackageName, $score, $action);
        }

        return new self(false, ['unknown-error'], $hostname, $challengeTs, $apkPackageName, $score, $action);
    }

    public function __construct(
        bool $success,
        array $errorCodes = [],
        ?string $hostname = null,
        ?string $challengeTs = null,
        ?string $apkPackageName = null,
        ?float $score = null,
        ?string $action = null
    ) {
        $this->success = $success;
        $this->hostname = $hostname;
        $this->challengeTs = $challengeTs;
        $this->apkPackageName = $apkPackageName;
        $this->score = $score;
        $this->action = $action;
        $this->errorCodes = $errorCodes;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorCodes(): array
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
