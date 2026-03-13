<?php

namespace App\Repository;

use App\Entity\CustomerSegment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerSegment>
 *
 * @method CustomerSegment|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerSegment|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerSegment[]    findAll()
 * @method CustomerSegment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerSegmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerSegment::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('cs')
            ->andWhere('cs.isActive = true')
            ->orderBy('cs.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): ?CustomerSegment
    {
        return $this->findOneBy(['name' => $name, 'isActive' => true]);
    }

    public function findWithDiscount(): array
    {
        return $this->createQueryBuilder('cs')
            ->andWhere('cs.isActive = true')
            ->andWhere('cs.discountRate > 0')
            ->orderBy('cs.discountRate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
