<?php

namespace App\Repository;

use App\Entity\BikePrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BikePrice>
 *
 * @method BikePrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikePrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikePrice[]    findAll()
 * @method BikePrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikePriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikePrice::class);
    }

    public function findCurrentPrices(): array
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('bp')
            ->andWhere('bp.isActive = true')
            ->andWhere('(bp.validFrom IS NULL OR bp.validFrom <= :now)')
            ->andWhere('(bp.validUntil IS NULL OR bp.validUntil >= :now)')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function findByVariantAndSegment($variantId, $segmentId): ?BikePrice
    {
        return $this->createQueryBuilder('bp')
            ->andWhere('bp.variant = :variant')
            ->andWhere('bp.segment = :segment')
            ->andWhere('bp.isActive = true')
            ->setParameter('variant', $variantId)
            ->setParameter('segment', $segmentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySegment($segmentId): array
    {
        return $this->createQueryBuilder('bp')
            ->andWhere('bp.segment = :segment')
            ->andWhere('bp.isActive = true')
            ->setParameter('segment', $segmentId)
            ->leftJoin('bp.variant', 'v')
            ->addSelect('v')
            ->getQuery()
            ->getResult();
    }

    public function findHighMarginPrices(): array
    {
        return $this->createQueryBuilder('bp')
            ->andWhere('bp.isActive = true')
            ->andWhere('bp.marginRate > 30')
            ->orderBy('bp.marginRate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
