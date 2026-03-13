<?php

namespace App\Service;

use App\Entity\Bike;
use App\Repository\BikeRepository;
use App\Repository\BikeVariantRepository;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;

class BikeFilterService
{
    public function __construct(
        private BikeRepository $bikeRepository,
        private BikeVariantRepository $variantRepository,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
    ) {
    }

    /**
     * Filtrage avancé multi-critères
     *
     * @param array{
     *     category?: string,
     *     brand?: string,
     *     minPrice?: string,
     *     maxPrice?: string,
     *     size?: string,
     *     color?: string,
     *     condition?: string,
     *     electric?: bool,
     *     segmentLevel?: string,
     *     modelYear?: int,
     *     featured?: bool,
     *     search?: string,
     *     sortBy?: string,
     *     sortOrder?: string,
     *     page?: int,
     *     limit?: int
     * } $criteria
     * @return array{bikes: Bike[], total: int, page: int, limit: int, totalPages: int}
     */
    public function filter(array $criteria = []): array
    {
        $qb = $this->bikeRepository->createQueryBuilder('b')
            ->leftJoin('b.variants', 'v')
            ->leftJoin('b.category', 'c')
            ->leftJoin('b.brand', 'br')
            ->addSelect('v', 'c', 'br')
            ->andWhere('b.isActive = true');

        // Filtre par catégorie (slug)
        if (!empty($criteria['category'])) {
            $qb->andWhere('c.slug = :category')
                ->setParameter('category', $criteria['category']);
        }

        // Filtre par marque (slug)
        if (!empty($criteria['brand'])) {
            $qb->andWhere('br.slug = :brand')
                ->setParameter('brand', $criteria['brand']);
        }

        // Filtre par fourchette de prix
        if (!empty($criteria['minPrice'])) {
            $qb->andWhere('v.basePrice >= :minPrice')
                ->setParameter('minPrice', $criteria['minPrice']);
        }
        if (!empty($criteria['maxPrice'])) {
            $qb->andWhere('v.basePrice <= :maxPrice')
                ->setParameter('maxPrice', $criteria['maxPrice']);
        }

        // Filtre par taille
        if (!empty($criteria['size'])) {
            $qb->andWhere('v.size = :size')
                ->setParameter('size', $criteria['size']);
        }

        // Filtre par couleur
        if (!empty($criteria['color'])) {
            $qb->andWhere('v.color = :color')
                ->setParameter('color', $criteria['color']);
        }

        // Filtre par condition
        if (!empty($criteria['condition'])) {
            $qb->andWhere('v.bikeCondition = :condition')
                ->setParameter('condition', $criteria['condition']);
        }

        // Filtre électrique / mécanique
        if (isset($criteria['electric'])) {
            if ($criteria['electric']) {
                $qb->andWhere('v.motor IS NOT NULL');
            } else {
                $qb->andWhere('v.motor IS NULL');
            }
        }

        // Filtre par niveau de segment
        if (!empty($criteria['segmentLevel'])) {
            $qb->andWhere('b.segmentLevel = :segmentLevel')
                ->setParameter('segmentLevel', $criteria['segmentLevel']);
        }

        // Filtre par année modèle
        if (!empty($criteria['modelYear'])) {
            $qb->andWhere('b.modelYear = :modelYear')
                ->setParameter('modelYear', $criteria['modelYear']);
        }

        // Filtre featured uniquement
        if (!empty($criteria['featured'])) {
            $qb->andWhere('b.isFeatured = true');
        }

        // Recherche textuelle
        if (!empty($criteria['search'])) {
            $qb->andWhere('b.name LIKE :search OR b.description LIKE :search')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        // Tri
        $sortBy = $criteria['sortBy'] ?? 'name';
        $sortOrder = strtoupper($criteria['sortOrder'] ?? 'ASC');
        $sortOrder = in_array($sortOrder, ['ASC', 'DESC'], true) ? $sortOrder : 'ASC';

        $sortMap = [
            'name' => 'b.name',
            'price' => 'v.basePrice',
            'year' => 'b.modelYear',
            'created' => 'b.createdAt',
            'weight' => 'v.weight',
        ];

        $sortField = $sortMap[$sortBy] ?? 'b.name';
        $qb->orderBy($sortField, $sortOrder);

        // Total count avant pagination
        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT b.id)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        // Pagination
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $limit = min(100, max(1, (int) ($criteria['limit'] ?? 12)));

        $qb->groupBy('b.id')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'bikes' => $qb->getQuery()->getResult(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int) ceil($total / $limit),
        ];
    }

    /**
     * Récupère les filtres disponibles (facets) pour l'affichage sidebar
     */
    public function getAvailableFilters(): array
    {
        $categories = $this->categoryRepository->findBy(['isActive' => true]);
        $brands = $this->brandRepository->findBy(['isActive' => true]);

        $sizes = $this->variantRepository->createQueryBuilder('v')
            ->select('DISTINCT v.size')
            ->where('v.isActive = true')
            ->orderBy('v.size', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $colors = $this->variantRepository->createQueryBuilder('v')
            ->select('DISTINCT v.color')
            ->where('v.isActive = true')
            ->orderBy('v.color', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $priceRange = $this->variantRepository->createQueryBuilder('v')
            ->select('MIN(v.basePrice) AS minPrice, MAX(v.basePrice) AS maxPrice')
            ->where('v.isActive = true')
            ->getQuery()
            ->getSingleResult();

        $years = $this->bikeRepository->createQueryBuilder('b')
            ->select('DISTINCT b.modelYear')
            ->where('b.isActive = true')
            ->andWhere('b.modelYear IS NOT NULL')
            ->orderBy('b.modelYear', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        return [
            'categories' => $categories,
            'brands' => $brands,
            'sizes' => $sizes,
            'colors' => $colors,
            'priceRange' => $priceRange,
            'conditions' => ['new', 'refurbished', 'used'],
            'segmentLevels' => ['none', 'semi-pro', 'pro', 'enterprise'],
            'years' => $years,
        ];
    }

    /**
     * Comptage rapide par catégorie
     * @return array<string, array{name: string, count: int}>
     */
    public function countByCategory(): array
    {
        $results = $this->bikeRepository->createQueryBuilder('b')
            ->select('c.name, c.slug, COUNT(b.id) AS bikeCount')
            ->join('b.category', 'c')
            ->where('b.isActive = true')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['slug']] = [
                'name' => $row['name'],
                'count' => (int) $row['bikeCount'],
            ];
        }

        return $counts;
    }

    /**
     * Comptage rapide par marque
     * @return array<string, array{name: string, count: int}>
     */
    public function countByBrand(): array
    {
        $results = $this->bikeRepository->createQueryBuilder('b')
            ->select('br.name, br.slug, COUNT(b.id) AS bikeCount')
            ->join('b.brand', 'br')
            ->where('b.isActive = true')
            ->groupBy('br.id')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['slug']] = [
                'name' => $row['name'],
                'count' => (int) $row['bikeCount'],
            ];
        }

        return $counts;
    }
}
