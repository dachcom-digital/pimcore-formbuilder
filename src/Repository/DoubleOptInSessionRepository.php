<?php

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
