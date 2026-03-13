<?php

namespace App\Repository;

use App\Entity\BikeFeature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BikeFeature>
 *
 * @method BikeFeature|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeFeature|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeFeature[]    findAll()
 * @method BikeFeature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeFeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeFeature::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('bf')
            ->andWhere('bf.isActive = true')
            ->leftJoin('bf.category', 'fc')
            ->addSelect('fc')
            ->orderBy('fc.name', 'ASC')
            ->addOrderBy('bf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory($categoryId): array
    {
        return $this->createQueryBuilder('bf')
            ->andWhere('bf.isActive = true')
            ->andWhere('bf.category = :category')
            ->setParameter('category', $categoryId)
            ->orderBy('bf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
