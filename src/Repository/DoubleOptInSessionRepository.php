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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FormBuilderBundle\Model\DoubleOptInSession;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;
use Symfony\Component\Uid\Uuid;

class DoubleOptInSessionRepository implements DoubleOptInSessionRepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(DoubleOptInSession::class);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('s');
    }

    public function find(string $token): ?DoubleOptInSessionInterface
    {
        return $this->repository->find(Uuid::fromString($token)->toBinary());
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?DoubleOptInSessionInterface
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    public function findByNonAppliedFormAwareSessionToken(string $token, int $formDefinitionId): ?DoubleOptInSessionInterface
    {
        if (!Uuid::isValid($token)) {
            return null;
        }

        return $this->repository->findOneBy([
            'token'          => Uuid::fromString($token)->toBinary(),
            'formDefinition' => $formDefinitionId,
            'applied'        => false
        ]);
    }
}
