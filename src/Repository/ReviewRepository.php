<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findApprovedByBike($bikeId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.bike = :bike')
            ->andWhere('r.isApproved = true')
            ->setParameter('bike', $bikeId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPending(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.isApproved = false')
            ->orderBy('r.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findHighRated(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.isApproved = true')
            ->andWhere('r.rating >= 4')
            ->orderBy('r.rating', 'DESC')
            ->addOrderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getAverageRatingByBike($bikeId): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avg_rating')
            ->andWhere('r.bike = :bike')
            ->andWhere('r.isApproved = true')
            ->setParameter('bike', $bikeId)
            ->getQuery()
            ->getSingleResult();

        return $result['avg_rating'] ? (float) $result['avg_rating'] : null;
    }

    public function getReviewCountByBike($bikeId): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id) as cnt')
            ->andWhere('r.bike = :bike')
            ->andWhere('r.isApproved = true')
            ->setParameter('bike', $bikeId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
