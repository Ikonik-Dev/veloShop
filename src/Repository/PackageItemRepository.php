<?php

namespace App\Repository;

use App\Entity\PackageItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PackageItem>
 *
 * @method PackageItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackageItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackageItem[]    findAll()
 * @method PackageItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PackageItem::class);
    }

    public function findByPackage($packageId): array
    {
        return $this->createQueryBuilder('pi')
            ->andWhere('pi.package = :package')
            ->setParameter('package', $packageId)
            ->leftJoin('pi.variant', 'v')
            ->addSelect('v')
            ->orderBy('pi.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByVariant($variantId): array
    {
        return $this->createQueryBuilder('pi')
            ->andWhere('pi.variant = :variant')
            ->setParameter('variant', $variantId)
            ->leftJoin('pi.package', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();
    }
}
