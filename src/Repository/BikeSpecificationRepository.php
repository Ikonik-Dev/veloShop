<?php

namespace App\Repository;

use App\Entity\BikeSpecification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BikeSpecification>
 *
 * @method BikeSpecification|null find($id, $lockMode = null, $lockVersion = null)
 * @method BikeSpecification|null findOneBy(array $criteria, array $orderBy = null)
 * @method BikeSpecification[]    findAll()
 * @method BikeSpecification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeSpecificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeSpecification::class);
    }

    public function findByVariant($variantId): array
    {
        return $this->createQueryBuilder('bs')
            ->andWhere('bs.variant = :variant')
            ->setParameter('variant', $variantId)
            ->orderBy('bs.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('bs')
            ->andWhere('bs.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
    }
}
