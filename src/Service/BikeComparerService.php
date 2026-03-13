<?php

namespace App\Service;

use App\Entity\Bike;
use App\Entity\BikeVariant;
use App\Service\PricingService;
use App\Service\ReviewService;

class BikeComparerService
{
    public function __construct(
        private PricingService $pricingService,
        private ReviewService $reviewService,
    ) {
    }

    /**
     * Comparer deux ou plusieurs vélos côte à côte
     *
     * @param Bike[] $bikes
     */
    public function compareBikes(array $bikes): array
    {
        $comparison = [];

        foreach ($bikes as $bike) {
            $variants = $bike->getVariants();
            $cheapestVariant = $this->getCheapestVariant($bike);
            $reviewStats = $this->reviewService->getReviewStats($bike);

            $comparison[] = [
                'bike' => $bike,
                'name' => $bike->getName(),
                'brand' => $bike->getBrand()?->getName(),
                'category' => $bike->getCategory()?->getName(),
                'modelYear' => $bike->getModelYear(),
                'segmentLevel' => $bike->getSegmentLevel(),
                'variantCount' => count($variants),
                'hasElectric' => $this->hasElectricVariant($bike),
                'cheapestPrice' => $cheapestVariant ? $this->pricingService->getCheapestPrice($cheapestVariant)?->getPriceHT() : null,
                'priceRange' => $this->getPriceRange($bike),
                'weightRange' => $this->getWeightRange($bike),
                'availableSizes' => $this->getAvailableSizes($bike),
                'availableColors' => $this->getAvailableColors($bike),
                'features' => $this->getFeatureNames($bike),
                'reviewStats' => $reviewStats,
                'isFeatured' => $bike->isFeatured(),
            ];
        }

        return $comparison;
    }

    /**
     * Comparer deux variantes spécifiques
     */
    public function compareVariants(BikeVariant $variant1, BikeVariant $variant2): array
    {
        $specs1 = $this->getSpecsMap($variant1);
        $specs2 = $this->getSpecsMap($variant2);

        $allSpecNames = array_unique(array_merge(array_keys($specs1), array_keys($specs2)));
        sort($allSpecNames);

        $specComparison = [];
        foreach ($allSpecNames as $name) {
            $specComparison[$name] = [
                'variant1' => $specs1[$name] ?? null,
                'variant2' => $specs2[$name] ?? null,
                'identical' => isset($specs1[$name], $specs2[$name]) && $specs1[$name] === $specs2[$name],
            ];
        }

        $price1 = $this->pricingService->getCheapestPrice($variant1);
        $price2 = $this->pricingService->getCheapestPrice($variant2);

        return [
            'variant1' => [
                'bike' => $variant1->getBike()?->getName(),
                'color' => $variant1->getColor(),
                'size' => $variant1->getSize(),
                'weight' => $variant1->getWeight(),
                'basePrice' => $variant1->getBasePrice(),
                'condition' => $variant1->getBikeCondition(),
                'isElectric' => $variant1->isElectric(),
                'motor' => $variant1->getMotor()?->getName(),
                'cheapestPrice' => $price1?->getPriceHT(),
            ],
            'variant2' => [
                'bike' => $variant2->getBike()?->getName(),
                'color' => $variant2->getColor(),
                'size' => $variant2->getSize(),
                'weight' => $variant2->getWeight(),
                'basePrice' => $variant2->getBasePrice(),
                'condition' => $variant2->getBikeCondition(),
                'isElectric' => $variant2->isElectric(),
                'motor' => $variant2->getMotor()?->getName(),
                'cheapestPrice' => $price2?->getPriceHT(),
            ],
            'specifications' => $specComparison,
            'priceDifference' => $price1 && $price2
                ? number_format(abs((float) $price1->getPriceHT() - (float) $price2->getPriceHT()), 2, '.', '')
                : null,
            'weightDifference' => $variant1->getWeight() !== null && $variant2->getWeight() !== null
                ? abs($variant1->getWeight() - $variant2->getWeight())
                : null,
        ];
    }

    /**
     * Score de similarité entre deux vélos (0 à 100)
     */
    public function getSimilarityScore(Bike $bike1, Bike $bike2): int
    {
        $score = 0;

        // Même catégorie : +30
        if ($bike1->getCategory()?->getId() === $bike2->getCategory()?->getId()) {
            $score += 30;
        }

        // Même marque : +20
        if ($bike1->getBrand()?->getId() === $bike2->getBrand()?->getId()) {
            $score += 20;
        }

        // Même année : +10
        if ($bike1->getModelYear() === $bike2->getModelYear()) {
            $score += 10;
        }

        // Même segment level : +10
        if ($bike1->getSegmentLevel() === $bike2->getSegmentLevel()) {
            $score += 10;
        }

        // Features en commun : +30 max
        $features1 = $bike1->getBikeFeatures()->map(fn($f) => $f->getId())->toArray();
        $features2 = $bike2->getBikeFeatures()->map(fn($f) => $f->getId())->toArray();

        if (!empty($features1) && !empty($features2)) {
            $intersection = array_intersect($features1, $features2);
            $union = array_unique(array_merge($features1, $features2));
            $featureScore = (count($intersection) / count($union)) * 30;
            $score += (int) $featureScore;
        }

        return min(100, $score);
    }

    private function getCheapestVariant(Bike $bike): ?BikeVariant
    {
        $cheapest = null;
        foreach ($bike->getVariants() as $variant) {
            if (!$variant->isActive()) {
                continue;
            }
            if ($cheapest === null || (float) $variant->getBasePrice() < (float) $cheapest->getBasePrice()) {
                $cheapest = $variant;
            }
        }
        return $cheapest;
    }

    private function hasElectricVariant(Bike $bike): bool
    {
        foreach ($bike->getVariants() as $variant) {
            if ($variant->isElectric()) {
                return true;
            }
        }
        return false;
    }

    private function getPriceRange(Bike $bike): array
    {
        $prices = [];
        foreach ($bike->getVariants() as $variant) {
            if ($variant->isActive()) {
                $prices[] = (float) $variant->getBasePrice();
            }
        }

        if (empty($prices)) {
            return ['min' => null, 'max' => null];
        }

        return ['min' => min($prices), 'max' => max($prices)];
    }

    private function getWeightRange(Bike $bike): array
    {
        $weights = [];
        foreach ($bike->getVariants() as $variant) {
            if ($variant->isActive() && $variant->getWeight() !== null) {
                $weights[] = $variant->getWeight();
            }
        }

        if (empty($weights)) {
            return ['min' => null, 'max' => null];
        }

        return ['min' => min($weights), 'max' => max($weights)];
    }

    private function getAvailableSizes(Bike $bike): array
    {
        $sizes = [];
        foreach ($bike->getVariants() as $variant) {
            if ($variant->isActive()) {
                $sizes[] = $variant->getSize();
            }
        }
        return array_values(array_unique($sizes));
    }

    private function getAvailableColors(Bike $bike): array
    {
        $colors = [];
        foreach ($bike->getVariants() as $variant) {
            if ($variant->isActive()) {
                $colors[] = $variant->getColor();
            }
        }
        return array_values(array_unique($colors));
    }

    private function getFeatureNames(Bike $bike): array
    {
        $names = [];
        foreach ($bike->getBikeFeatures() as $feature) {
            $names[] = $feature->getName();
        }
        return $names;
    }

    /**
     * @return array<string, string>
     */
    private function getSpecsMap(BikeVariant $variant): array
    {
        $map = [];
        foreach ($variant->getSpecifications() as $spec) {
            $map[$spec->getName()] = $spec->getDisplayValue();
        }
        return $map;
    }
}
