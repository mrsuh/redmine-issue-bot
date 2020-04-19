<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneById(int $id): ?User
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @return User[]
     */
    public function findByIds(array $ids): array
    {
        return $this->findBy(['id' => $ids]);
    }

    /**
     * @return User[]
     */
    public function findActive(): array
    {
        return $this->findBy(['active' => true]);
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('u')
             ->delete()
             ->getQuery()
             ->getResult();
    }
}
