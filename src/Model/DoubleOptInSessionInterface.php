<?php

namespace FormBuilderBundle\Model;

use Symfony\Component\Uid\Uuid;

interface DoubleOptInSessionInterface
{
    public function getToken(): Uuid;

    public function getTokenAsString(): string;

    public function getEmail(): string;

    public function getAdditionalData(): array;

    public function getDispatchLocation(): string;

    public function getCreationDate(): \DateTime;

    public function getFormDefinition(): FormDefinitionInterface;

    public function isApplied(): bool;

    public function setApplied(bool $applied): void;
}
