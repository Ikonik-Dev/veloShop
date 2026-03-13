<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 *
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function findLowStock(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.reorderLevel IS NOT NULL')
            ->andWhere('s.quantity <= s.reorderLevel')
            ->leftJoin('s.variant', 'v')
            ->addSelect('v')
            ->orderBy('s.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOutOfStock(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.quantity = 0')
            ->leftJoin('s.variant', 'v')
            ->addSelect('v')
            ->getQuery()
            ->getResult();
    }

    public function findByWarehouse(string $warehouse): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.warehouse = :warehouse')
            ->setParameter('warehouse', $warehouse)
            ->leftJoin('s.variant', 'v')
            ->addSelect('v')
            ->orderBy('s.quantity', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByVariant($variantId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.variant = :variant')
            ->setParameter('variant', $variantId)
            ->getQuery()
            ->getResult();
    }

    public function getTotalStock($variantId): int
    {
        $result = $this->createQueryBuilder('s')
            ->select('SUM(s.quantity) as total')
            ->andWhere('s.variant = :variant')
            ->setParameter('variant', $variantId)
            ->getQuery()
            ->getSingleResult();

        return (int) ($result['total'] ?? 0);
    }
}
