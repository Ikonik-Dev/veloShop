<?php

namespace App\Repository;

use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Package>
 *
 * @method Package|null find($id, $lockMode = null, $lockVersion = null)
 * @method Package|null findOneBy(array $criteria, array $orderBy = null)
 * @method Package[]    findAll()
 * @method Package[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = true')
            ->leftJoin('p.items', 'pi')
            ->addSelect('pi')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFeatured(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = true')
            ->andWhere('p.isFeatured = true')
            ->leftJoin('p.items', 'pi')
            ->addSelect('pi')
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCurrent(): array
    {
        $now = new \DateTimeImmutable();
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = true')
            ->andWhere('(p.validFrom IS NULL OR p.validFrom <= :now)')
            ->andWhere('(p.validUntil IS NULL OR p.validUntil >= :now)')
            ->setParameter('now', $now)
            ->leftJoin('p.items', 'pi')
            ->addSelect('pi')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Package
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isActive = true')
            ->setParameter('slug', $slug)
            ->leftJoin('p.items', 'pi')
            ->leftJoin('pi.variant', 'v')
            ->addSelect('pi', 'v')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
