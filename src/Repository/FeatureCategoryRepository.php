<?php

namespace App\Repository;

use App\Entity\FeatureCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeatureCategory>
 *
 * @method FeatureCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeatureCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeatureCategory[]    findAll()
 * @method FeatureCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeatureCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeatureCategory::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('fc')
            ->andWhere('fc.isActive = true')
            ->leftJoin('fc.features', 'f')
            ->addSelect('f')
            ->orderBy('fc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
