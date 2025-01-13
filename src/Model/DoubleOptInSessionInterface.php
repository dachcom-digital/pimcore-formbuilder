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
