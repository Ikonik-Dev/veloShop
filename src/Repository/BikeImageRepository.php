<?php

namespace App\Repository;

use App\Entity\BikeImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BikeImage>
 *
 * @method BikeImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeImage[]    findAll()
 * @method BikeImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeImage::class);
    }

    public function findActiveByBike($bikeId): array
    {
        return $this->createQueryBuilder('bi')
            ->andWhere('bi.bike = :bike')
            ->andWhere('bi.isActive = true')
            ->setParameter('bike', $bikeId)
            ->orderBy('bi.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPrimaryImage($bikeId): ?BikeImage
    {
        return $this->createQueryBuilder('bi')
            ->andWhere('bi.bike = :bike')
            ->andWhere('bi.type = :type')
            ->andWhere('bi.isActive = true')
            ->setParameter('bike', $bikeId)
            ->setParameter('type', 'primary')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findThumbnail($bikeId): ?BikeImage
    {
        return $this->createQueryBuilder('bi')
            ->andWhere('bi.bike = :bike')
            ->andWhere('bi.type = :type')
            ->andWhere('bi.isActive = true')
            ->setParameter('bike', $bikeId)
            ->setParameter('type', 'thumbnail')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
