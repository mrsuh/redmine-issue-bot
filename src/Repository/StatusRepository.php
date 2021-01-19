<?php

namespace App\Repository;

use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Status|null find($id, $lockMode = null, $lockVersion = null)
 * @method Status|null findOneBy(array $criteria, array $orderBy = null)
 * @method Status[]    findAll()
 * @method Status[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function findOneByRedmineId(int $id): ?Status
    {
        return $this->findOneBy(['redmineId' => $id]);
    }

    public function findOneByType(string $type): ?Status
    {
        return $this->findOneBy(['type' => $type]);
    }

    /**
     * @return Status[]
     */
    public function findForReview(): array
    {
        return $this->findBy(['forReview' => true]);
    }
}
