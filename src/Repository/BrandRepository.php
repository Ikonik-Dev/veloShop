<?php

namespace App\Repository;

use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Brand>
 *
 * @method Brand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brand[]    findAll()
 * @method Brand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = true')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Brand
    {
        return $this->findOneBy(['slug' => $slug, 'isActive' => true]);
    }

    public function findActiveWithBikes(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.bikes', 'bike')
            ->andWhere('b.isActive = true')
            ->andWhere('bike.isActive = true')
            ->addSelect('bike')
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
