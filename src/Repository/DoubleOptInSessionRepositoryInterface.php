<?php

namespace FormBuilderBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;

interface DoubleOptInSessionRepositoryInterface
{
    public function getQueryBuilder(): QueryBuilder;

    public function findByNonAppliedFormAwareSessionToken(string $token, int $formDefinitionId): ?DoubleOptInSessionInterface;
}
