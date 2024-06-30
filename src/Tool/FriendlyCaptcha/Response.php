<?php

namespace FormBuilderBundle\Tool\FriendlyCaptcha;

class Response
{
    public static function fromJson(string $json): Response
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
