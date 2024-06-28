<?php

namespace FormBuilderBundle\Tool\FriendlyCaptcha;

class Response
{
    private bool $success;
    private ?string $details;
    private ?array $errors;

    public static function fromJson(string $json): Response
    {
        $responseData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!$responseData) {
            return new self(false, 'invalid-json', null);
        }

        $success = $responseData['success'] ?? false;
        $details = $responseData['details'] ?? null;
        $errors = $responseData['errors'] ?? null;

        return new self($success, $details, $errors);
    }

    public function __construct(
        bool $success,
        ?string $details,
        ?array $errors
    ) {
        $this->success = $success;
        $this->details = $details;
        $this->errors = $errors;
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
