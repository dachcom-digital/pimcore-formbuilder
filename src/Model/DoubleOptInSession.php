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

namespace FormBuilderBundle\Model;

use Symfony\Component\Uid\Uuid;

class DoubleOptInSession implements DoubleOptInSessionInterface
{
    protected Uuid $token;
    protected string $email;
    protected array $additionalData;
    protected string $dispatchLocation;
    protected bool $applied;
    protected \DateTime $creationDate;
    protected FormDefinitionInterface $formDefinition;

    public function getToken(): Uuid
    {
        return $this->token;
    }

    public function getTokenAsString(): string
    {
        return $this->token->toRfc4122();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    public function getDispatchLocation(): string
    {
        return $this->dispatchLocation;
    }

    public function setDispatchLocation(string $dispatchLocation): void
    {
        $this->dispatchLocation = $dispatchLocation;
    }

    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $date): void
    {
        $this->creationDate = $date;
    }

    public function getFormDefinition(): FormDefinitionInterface
    {
        return $this->formDefinition;
    }

    public function setFormDefinition(FormDefinitionInterface $formDefinition): void
    {
        $this->formDefinition = $formDefinition;
    }

    public function isApplied(): bool
    {
        return $this->applied;
    }

    public function setApplied(bool $applied): void
    {
        $this->applied = $applied;
    }
}
