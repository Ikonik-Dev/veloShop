<?php

namespace App\Service;

use App\Entity\Bike;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReviewService
{
    public function __construct(
        private ReviewRepository $reviewRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Créer un avis (non approuvé par défaut)
     */
    public function createReview(
        Bike $bike,
        string $authorName,
        int $rating,
        string $title,
        string $content,
        ?string $authorEmail = null,
    ): Review {
        $review = (new Review())
            ->setBike($bike)
            ->setAuthorName($authorName)
            ->setRating(min(5, max(1, $rating)))
            ->setTitle($title)
            ->setContent($content)
            ->setAuthorEmail($authorEmail)
            ->setIsApproved(false);

        $this->em->persist($review);
        $this->em->flush();

        return $review;
    }

    /**
     * Approuver un avis
     */
    public function approve(Review $review): Review
    {
        $review->approve();
        $this->em->flush();

        return $review;
    }

    /**
     * Rejeter (supprimer) un avis
     */
    public function reject(Review $review): void
    {
        $this->em->remove($review);
        $this->em->flush();
    }

    /**
     * Liste des avis en attente de modération
     * @return Review[]
     */
    public function getPendingReviews(): array
    {
        return $this->reviewRepository->findBy(
            ['isApproved' => false],
            ['createdAt' => 'DESC']
        );
    }

    /**
     * Avis approuvés d'un vélo, triés par date récente
     * @return Review[]
     */
    public function getApprovedReviews(Bike $bike, string $sortBy = 'recent'): array
    {
        $qb = $this->reviewRepository->createQueryBuilder('r')
            ->where('r.bike = :bike')
            ->andWhere('r.isApproved = true')
            ->setParameter('bike', $bike);

        match ($sortBy) {
            'rating_high' => $qb->orderBy('r.rating', 'DESC'),
            'rating_low' => $qb->orderBy('r.rating', 'ASC'),
            'oldest' => $qb->orderBy('r.createdAt', 'ASC'),
            default => $qb->orderBy('r.createdAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques des avis d'un vélo
     */
    public function getReviewStats(Bike $bike): array
    {
        $reviews = $this->getApprovedReviews($bike);
        $total = count($reviews);

        if ($total === 0) {
            return [
                'average' => 0,
                'total' => 0,
                'distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'recommendation_rate' => 0,
            ];
        }

        $sum = 0;
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        foreach ($reviews as $review) {
            $rating = $review->getRating();
            $sum += $rating;
            $distribution[$rating]++;
        }

        $average = $sum / $total;
        $positiveCount = $distribution[4] + $distribution[5];

        return [
            'average' => round($average, 1),
            'total' => $total,
            'distribution' => $distribution,
            'recommendation_rate' => round(($positiveCount / $total) * 100, 1),
        ];
    }

    /**
     * Les N meilleurs avis (note >= 4, approuvés)
     * @return Review[]
     */
    public function getTopReviews(int $limit = 5): array
    {
        return $this->reviewRepository->createQueryBuilder('r')
            ->where('r.isApproved = true')
            ->andWhere('r.rating >= 4')
            ->orderBy('r.rating', 'DESC')
            ->addOrderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Les vélos les mieux notés
     * @return array<array{bike: Bike, average: float, count: int}>
     */
    public function getTopRatedBikes(int $limit = 10): array
    {
        return $this->reviewRepository->createQueryBuilder('r')
            ->select('IDENTITY(r.bike) AS bikeId, AVG(r.rating) AS avgRating, COUNT(r.id) AS reviewCount')
            ->where('r.isApproved = true')
            ->groupBy('r.bike')
            ->having('COUNT(r.id) >= 1')
            ->orderBy('avgRating', 'DESC')
            ->addOrderBy('reviewCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre total d'avis en attente
     */
    public function getPendingCount(): int
    {
        return $this->reviewRepository->count(['isApproved' => false]);
    }
}
