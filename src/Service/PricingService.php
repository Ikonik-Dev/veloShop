<?php

namespace App\Service;

use App\Entity\BikePrice;
use App\Entity\BikeVariant;
use App\Entity\CustomerSegment;
use App\Entity\Package;
use App\Repository\CustomerSegmentRepository;

class PricingService
{
    private const DEFAULT_TVA_RATE = 20.0;

    public function __construct(
        private CustomerSegmentRepository $segmentRepository,
    ) {
    }

    /**
     * Récupère le prix actif d'une variante pour un segment donné
     */
    public function getPriceForSegment(BikeVariant $variant, CustomerSegment $segment): ?BikePrice
    {
        foreach ($variant->getPrices() as $price) {
            if ($price->getSegment() === $segment && $price->isCurrentlyValid()) {
                return $price;
            }
        }

        return null;
    }

    /**
     * Récupère le prix le moins cher parmi tous les segments actifs
     */
    public function getCheapestPrice(BikeVariant $variant): ?BikePrice
    {
        $cheapest = null;

        foreach ($variant->getPrices() as $price) {
            if (!$price->isCurrentlyValid()) {
                continue;
            }
            if ($cheapest === null || (float) $price->getPriceHT() < (float) $cheapest->getPriceHT()) {
                $cheapest = $price;
            }
        }

        return $cheapest;
    }

    /**
     * Récupère le prix le plus élevé (prix public / amateur)
     */
    public function getHighestPrice(BikeVariant $variant): ?BikePrice
    {
        $highest = null;

        foreach ($variant->getPrices() as $price) {
            if (!$price->isCurrentlyValid()) {
                continue;
            }
            if ($highest === null || (float) $price->getPriceHT() > (float) $highest->getPriceHT()) {
                $highest = $price;
            }
        }

        return $highest;
    }

    /**
     * Calcule le prix TTC à partir du HT avec un taux de TVA
     */
    public function calculateTTC(string $priceHT, float $tvaRate = self::DEFAULT_TVA_RATE): string
    {
        $ht = (float) $priceHT;
        $ttc = $ht * (1 + $tvaRate / 100);

        return number_format($ttc, 2, '.', '');
    }

    /**
     * Calcule le prix HT à partir du TTC
     */
    public function calculateHT(string $priceTTC, float $tvaRate = self::DEFAULT_TVA_RATE): string
    {
        $ttc = (float) $priceTTC;
        $ht = $ttc / (1 + $tvaRate / 100);

        return number_format($ht, 2, '.', '');
    }

    /**
     * Calcule la réduction entre le prix de base et le prix segment
     */
    public function calculateDiscount(BikeVariant $variant, CustomerSegment $segment): ?array
    {
        $segmentPrice = $this->getPriceForSegment($variant, $segment);
        if ($segmentPrice === null) {
            return null;
        }

        $basePrice = (float) $variant->getBasePrice();
        $segmentHT = (float) $segmentPrice->getPriceHT();

        if ($basePrice <= 0) {
            return null;
        }

        $discountAmount = $basePrice - $segmentHT;
        $discountPercent = ($discountAmount / $basePrice) * 100;

        return [
            'original_ht' => $variant->getBasePrice(),
            'discounted_ht' => $segmentPrice->getPriceHT(),
            'discounted_ttc' => $segmentPrice->getPriceTTC(),
            'discount_amount' => number_format($discountAmount, 2, '.', ''),
            'discount_percent' => round($discountPercent, 1),
            'segment' => $segment->getName(),
        ];
    }

    /**
     * Grille tarifaire complète d'une variante pour tous les segments
     */
    public function getPricingGrid(BikeVariant $variant): array
    {
        $segments = $this->segmentRepository->findAll();
        $grid = [];

        foreach ($segments as $segment) {
            $price = $this->getPriceForSegment($variant, $segment);
            $grid[] = [
                'segment' => $segment,
                'segment_name' => $segment->getName(),
                'discount_rate' => $segment->getDiscountRate(),
                'price' => $price,
                'price_ht' => $price?->getPriceHT(),
                'price_ttc' => $price?->getPriceTTC(),
                'margin_rate' => $price?->getMarginRate(),
                'is_available' => $price !== null,
            ];
        }

        return $grid;
    }

    /**
     * Calcule le prix total d'un package avec remise
     */
    public function calculatePackagePrice(Package $package, ?CustomerSegment $segment = null): array
    {
        $totalWithoutDiscount = 0.0;
        $itemDetails = [];

        foreach ($package->getItems() as $item) {
            $variant = $item->getVariant();
            if ($variant === null) {
                continue;
            }

            $unitPrice = $item->getPriceOverride();
            if ($unitPrice === null && $segment !== null) {
                $segmentPrice = $this->getPriceForSegment($variant, $segment);
                $unitPrice = $segmentPrice?->getPriceHT() ?? $variant->getBasePrice();
            } elseif ($unitPrice === null) {
                $unitPrice = $variant->getBasePrice();
            }

            $lineTotal = (float) $unitPrice * $item->getQuantity();
            $totalWithoutDiscount += $lineTotal;

            $itemDetails[] = [
                'variant' => $variant,
                'quantity' => $item->getQuantity(),
                'unit_price_ht' => $unitPrice,
                'line_total_ht' => number_format($lineTotal, 2, '.', ''),
            ];
        }

        $discount = (float) ($package->getPackageDiscount() ?? '0');
        $totalAfterDiscount = $totalWithoutDiscount - $discount;

        return [
            'items' => $itemDetails,
            'subtotal_ht' => number_format($totalWithoutDiscount, 2, '.', ''),
            'discount' => number_format($discount, 2, '.', ''),
            'total_ht' => number_format(max(0, $totalAfterDiscount), 2, '.', ''),
            'total_ttc' => $this->calculateTTC(number_format(max(0, $totalAfterDiscount), 2, '.', '')),
        ];
    }

    /**
     * Compare le prix d'un vélo entre différents segments
     */
    public function comparePricesAcrossSegments(BikeVariant $variant): array
    {
        $segments = $this->segmentRepository->findAll();
        $comparisons = [];

        foreach ($segments as $segment) {
            $discount = $this->calculateDiscount($variant, $segment);
            if ($discount !== null) {
                $comparisons[] = $discount;
            }
        }

        usort($comparisons, fn(array $a, array $b) => (float) $a['discounted_ht'] <=> (float) $b['discounted_ht']);

        return $comparisons;
    }
}
