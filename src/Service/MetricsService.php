<?php

namespace App\Service;

use App\Repository\BikeRepository;
use App\Repository\BikeVariantRepository;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ReviewRepository;

class MetricsService
{
    public function __construct(
        private BikeRepository $bikeRepository,
        private BikeVariantRepository $variantRepository,
        private BrandRepository $brandRepository,
        private CategoryRepository $categoryRepository,
        private ReviewRepository $reviewRepository,
        private StockService $stockService,
    ) {
    }

    /**
     * Dashboard global : chiffres clés
     */
    public function getDashboard(): array
    {
        return [
            'catalog' => $this->getCatalogStats(),
            'stock' => $this->stockService->getStockSummary(),
            'reviews' => $this->getReviewGlobalStats(),
            'pricing' => $this->getPricingStats(),
        ];
    }

    /**
     * Statistiques du catalogue
     */
    public function getCatalogStats(): array
    {
        $totalBikes = $this->bikeRepository->count(['isActive' => true]);
        $totalVariants = $this->variantRepository->count(['isActive' => true]);
        $totalBrands = $this->brandRepository->count(['isActive' => true]);
        $totalCategories = $this->categoryRepository->count(['isActive' => true]);

        $electricCount = count($this->bikeRepository->findElectric());
        $featuredCount = count($this->bikeRepository->findFeatured());

        $byCondition = $this->variantRepository->createQueryBuilder('v')
            ->select('v.bikeCondition, COUNT(v.id) AS cnt')
            ->where('v.isActive = true')
            ->groupBy('v.bikeCondition')
            ->getQuery()
            ->getResult();

        $bySegmentLevel = $this->bikeRepository->createQueryBuilder('b')
            ->select('b.segmentLevel, COUNT(b.id) AS cnt')
            ->where('b.isActive = true')
            ->groupBy('b.segmentLevel')
            ->getQuery()
            ->getResult();

        return [
            'totalBikes' => $totalBikes,
            'totalVariants' => $totalVariants,
            'totalBrands' => $totalBrands,
            'totalCategories' => $totalCategories,
            'electricCount' => $electricCount,
            'featuredCount' => $featuredCount,
            'byCondition' => $byCondition,
            'bySegmentLevel' => $bySegmentLevel,
        ];
    }

    /**
     * Statistiques globales des avis
     */
    public function getReviewGlobalStats(): array
    {
        $totalReviews = $this->reviewRepository->count([]);
        $approvedReviews = $this->reviewRepository->count(['isApproved' => true]);
        $pendingReviews = $this->reviewRepository->count(['isApproved' => false]);

        $avgRating = $this->reviewRepository->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->where('r.isApproved = true')
            ->getQuery()
            ->getSingleScalarResult();

        $ratingDistribution = $this->reviewRepository->createQueryBuilder('r')
            ->select('r.rating, COUNT(r.id) AS cnt')
            ->where('r.isApproved = true')
            ->groupBy('r.rating')
            ->orderBy('r.rating', 'ASC')
            ->getQuery()
            ->getResult();

        $recentReviews = $this->reviewRepository->createQueryBuilder('r')
            ->where('r.createdAt >= :since')
            ->setParameter('since', new \DateTimeImmutable('-30 days'))
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $totalReviews,
            'approved' => $approvedReviews,
            'pending' => $pendingReviews,
            'averageRating' => $avgRating !== null ? round((float) $avgRating, 1) : 0,
            'distribution' => $ratingDistribution,
            'recentCount' => (int) $recentReviews,
        ];
    }

    /**
     * Statistiques de prix
     */
    public function getPricingStats(): array
    {
        $stats = $this->variantRepository->createQueryBuilder('v')
            ->select(
                'MIN(v.basePrice) AS minPrice',
                'MAX(v.basePrice) AS maxPrice',
                'AVG(v.basePrice) AS avgPrice'
            )
            ->where('v.isActive = true')
            ->getQuery()
            ->getSingleResult();

        $priceRanges = $this->variantRepository->createQueryBuilder('v')
            ->select(
                "CASE 
                    WHEN CAST(v.basePrice AS float) < 500 THEN 'budget'
                    WHEN CAST(v.basePrice AS float) < 1000 THEN 'milieu'
                    WHEN CAST(v.basePrice AS float) < 2000 THEN 'premium'
                    ELSE 'luxe'
                END AS priceRange",
                'COUNT(v.id) AS cnt'
            )
            ->where('v.isActive = true')
            ->groupBy('priceRange')
            ->getQuery()
            ->getResult();

        $electricVsMechanical = $this->variantRepository->createQueryBuilder('v')
            ->select(
                "CASE WHEN v.motor IS NOT NULL THEN 'electric' ELSE 'mechanical' END AS type",
                'AVG(v.basePrice) AS avgPrice',
                'COUNT(v.id) AS cnt'
            )
            ->where('v.isActive = true')
            ->groupBy('type')
            ->getQuery()
            ->getResult();

        return [
            'minPrice' => $stats['minPrice'],
            'maxPrice' => $stats['maxPrice'],
            'avgPrice' => $stats['avgPrice'] !== null ? round((float) $stats['avgPrice'], 2) : 0,
            'priceRanges' => $priceRanges,
            'electricVsMechanical' => $electricVsMechanical,
        ];
    }

    /**
     * Top des vélos par nombre de variantes
     */
    public function getTopBikesByVariants(int $limit = 10): array
    {
        return $this->bikeRepository->createQueryBuilder('b')
            ->select('b.name, br.name AS brand, COUNT(v.id) AS variantCount')
            ->join('b.variants', 'v')
            ->join('b.brand', 'br')
            ->where('b.isActive = true')
            ->groupBy('b.id')
            ->orderBy('variantCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Marques classées par nombre de vélos
     */
    public function getBrandRanking(): array
    {
        return $this->bikeRepository->createQueryBuilder('b')
            ->select('br.name, br.slug, br.country, COUNT(b.id) AS bikeCount')
            ->join('b.brand', 'br')
            ->where('b.isActive = true')
            ->groupBy('br.id')
            ->orderBy('bikeCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Catégories classées par nombre de vélos
     */
    public function getCategoryRanking(): array
    {
        return $this->bikeRepository->createQueryBuilder('b')
            ->select('c.name, c.slug, c.icon, COUNT(b.id) AS bikeCount')
            ->join('b.category', 'c')
            ->where('b.isActive = true')
            ->groupBy('c.id')
            ->orderBy('bikeCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Évolution du catalogue dans le temps (par modelYear)
     */
    public function getCatalogTimeline(): array
    {
        return $this->bikeRepository->createQueryBuilder('b')
            ->select('b.modelYear, COUNT(b.id) AS bikeCount')
            ->where('b.isActive = true')
            ->andWhere('b.modelYear IS NOT NULL')
            ->groupBy('b.modelYear')
            ->orderBy('b.modelYear', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
