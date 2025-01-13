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

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;

interface DoubleOptInSessionRepositoryInterface
{
    public function getQueryBuilder(): QueryBuilder;

    public function find(string $token): ?DoubleOptInSessionInterface;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?DoubleOptInSessionInterface;

    public function findByNonAppliedFormAwareSessionToken(string $token, int $formDefinitionId): ?DoubleOptInSessionInterface;
}
