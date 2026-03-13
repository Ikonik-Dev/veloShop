<?php

namespace App\Repository;

use App\Entity\BikeVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BikeVariant>
 *
 * @method BikeVariant|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeVariant|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeVariant[]    findAll()
 * @method BikeVariant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeVariant::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isActive = true')
            ->leftJoin('v.specifications', 's')
            ->leftJoin('v.prices', 'p')
            ->addSelect('s', 'p')
            ->getQuery()
            ->getResult();
    }

    public function findInStock(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isActive = true')
            ->leftJoin('v.stocks', 'stock')
            ->andWhere('stock.quantity > 0')
            ->addSelect('stock')
            ->getQuery()
            ->getResult();
    }

    public function findElectric(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isActive = true')
            ->leftJoin('v.motor', 'm')
            ->andWhere('m.id IS NOT NULL')
            ->addSelect('m')
            ->getQuery()
            ->getResult();
    }

    public function findByColor(string $color): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isActive = true')
            ->andWhere('v.color = :color')
            ->setParameter('color', $color)
            ->getQuery()
            ->getResult();
    }

    public function findBySize(string $size): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isActive = true')
            ->andWhere('v.size = :size')
            ->setParameter('size', $size)
            ->getQuery()
            ->getResult();
    }

    public function findByCondition(string $condition): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isActive = true')
            ->andWhere('v.bikeCondition = :condition')
            ->setParameter('condition', $condition)
            ->getQuery()
            ->getResult();
    }

    public function findLowWeight(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.isActive = true')
            ->andWhere('v.weight IS NOT NULL')
            ->andWhere('v.weight < 13000') // < 13 kg
            ->orderBy('v.weight', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
