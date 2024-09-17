<?php

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
