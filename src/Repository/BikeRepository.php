<?php

namespace App\Repository;

use App\Entity\Bike;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bike>
 *
 * @method Bike|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bike|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bike[]    findAll()
 * @method Bike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bike::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = true')
            ->leftJoin('b.variants', 'v')
            ->addSelect('v')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Bike
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.slug = :slug')
            ->andWhere('b.isActive = true')
            ->setParameter('slug', $slug)
            ->leftJoin('b.variants', 'v')
            ->leftJoin('b.images', 'img')
            ->leftJoin('b.reviews', 'r')
            ->addSelect('v', 'img', 'r')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.category = :category')
            ->andWhere('b.isActive = true')
            ->setParameter('category', $category)
            ->leftJoin('b.variants', 'v')
            ->addSelect('v')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFeatured(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = true')
            ->andWhere('b.isFeatured = true')
            ->leftJoin('b.variants', 'v')
            ->addSelect('v')
            ->orderBy('b.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findElectric(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = true')
            ->leftJoin('b.variants', 'v')
            ->leftJoin('v.motor', 'm')
            ->andWhere('m.id IS NOT NULL')
            ->addSelect('v', 'm')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findForSegment(string $segmentLevel): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = true')
            ->andWhere('b.segmentLevel IN (:level, :none)')
            ->setParameter('level', $segmentLevel)
            ->setParameter('none', 'none')
            ->leftJoin('b.variants', 'v')
            ->addSelect('v')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $query): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = true')
            ->andWhere('b.name LIKE :query OR b.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->leftJoin('b.variants', 'v')
            ->addSelect('v')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
