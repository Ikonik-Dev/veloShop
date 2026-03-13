<?php

namespace App\Repository;

use App\Entity\Motor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Motor>
 *
 * @method Motor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Motor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Motor[]    findAll()
 * @method Motor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Motor::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isActive = true')
            ->orderBy('m.brand', 'ASC')
            ->addOrderBy('m.wattage', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByWattageRange(int $minWattage, int $maxWattage): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isActive = true')
            ->andWhere('m.wattage >= :minWattage')
            ->andWhere('m.wattage <= :maxWattage')
            ->setParameter('minWattage', $minWattage)
            ->setParameter('maxWattage', $maxWattage)
            ->orderBy('m.wattage', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findHighEnd(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isActive = true')
            ->andWhere('m.wattage >= 750')
            ->orderBy('m.wattage', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
