<?php

namespace App\Repository;

use App\Entity\BikeCompatibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BikeCompatibility>
 *
 * @method BikeCompatibility|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeCompatibility|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeCompatibility[]    findAll()
 * @method BikeCompatibility[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeCompatibilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeCompatibility::class);
    }

    public function findCompatibleBikes($bikeId): array
    {
        return $this->createQueryBuilder('bc')
            ->andWhere('bc.bikeFrom = :bike')
            ->andWhere('bc.isActive = true')
            ->setParameter('bike', $bikeId)
            ->leftJoin('bc.bikeTo', 'b')
            ->addSelect('b')
            ->getQuery()
            ->getResult();
    }

    public function findRecommendedUpgrades($bikeId): array
    {
        return $this->createQueryBuilder('bc')
            ->andWhere('bc.bikeFrom = :bike')
            ->andWhere('bc.type = :type')
            ->andWhere('bc.isActive = true')
            ->setParameter('bike', $bikeId)
            ->setParameter('type', 'upgrade')
            ->leftJoin('bc.bikeTo', 'b')
            ->addSelect('b')
            ->getQuery()
            ->getResult();
    }

    public function findSimilarBikes($bikeId): array
    {
        return $this->createQueryBuilder('bc')
            ->andWhere('bc.bikeFrom = :bike')
            ->andWhere('bc.type IN (:types)')
            ->andWhere('bc.isActive = true')
            ->setParameter('bike', $bikeId)
            ->setParameter('types', ['similar', 'compatible'])
            ->leftJoin('bc.bikeTo', 'b')
            ->addSelect('b')
            ->getQuery()
            ->getResult();
    }
}
