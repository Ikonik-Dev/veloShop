<?php

namespace App\Service;

use App\Entity\BikeVariant;
use App\Entity\CustomerSegment;
use App\Entity\Package;
use App\Entity\PackageItem;
use App\Repository\PackageRepository;
use App\Service\PricingService;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;

class BundleService
{
    public function __construct(
        private PackageRepository $packageRepository,
        private PricingService $pricingService,
        private StockService $stockService,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Récupère les packages actifs et valides
     * @return Package[]
     */
    public function getActivePackages(): array
    {
        return $this->packageRepository->createQueryBuilder('p')
            ->leftJoin('p.items', 'i')
            ->leftJoin('i.variant', 'v')
            ->leftJoin('v.bike', 'b')
            ->addSelect('i', 'v', 'b')
            ->where('p.isActive = true')
            ->andWhere('p.validFrom IS NULL OR p.validFrom <= :now')
            ->andWhere('p.validUntil IS NULL OR p.validUntil >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Packages mis en avant
     * @return Package[]
     */
    public function getFeaturedPackages(): array
    {
        return $this->packageRepository->createQueryBuilder('p')
            ->leftJoin('p.items', 'i')
            ->leftJoin('i.variant', 'v')
            ->addSelect('i', 'v')
            ->where('p.isActive = true')
            ->andWhere('p.isFeatured = true')
            ->andWhere('p.validFrom IS NULL OR p.validFrom <= :now')
            ->andWhere('p.validUntil IS NULL OR p.validUntil >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcul détaillé d'un package (prix unitaires vs prix bundle)
     */
    public function getPackageDetails(Package $package, ?CustomerSegment $segment = null): array
    {
        $pricing = $this->pricingService->calculatePackagePrice($package, $segment);

        // Vérifier la disponibilité de chaque item
        $allAvailable = true;
        $availability = [];

        foreach ($package->getItems() as $item) {
            $variant = $item->getVariant();
            if ($variant === null) {
                continue;
            }

            $inStock = $this->stockService->isInStock($variant);
            $totalStock = $this->stockService->getTotalStock($variant);
            $hasEnough = $totalStock >= $item->getQuantity();

            if (!$hasEnough) {
                $allAvailable = false;
            }

            $availability[] = [
                'variant' => $variant,
                'bike_name' => $variant->getBike()?->getName(),
                'color' => $variant->getColor(),
                'size' => $variant->getSize(),
                'requested' => $item->getQuantity(),
                'available' => $totalStock,
                'in_stock' => $inStock,
                'has_enough' => $hasEnough,
            ];
        }

        // Calcul de l'économie par rapport aux prix individuels
        $individualTotal = 0.0;
        foreach ($package->getItems() as $item) {
            $variant = $item->getVariant();
            if ($variant !== null) {
                $individualTotal += (float) $variant->getBasePrice() * $item->getQuantity();
            }
        }

        $bundleTotal = (float) $pricing['total_ht'];
        $savings = $individualTotal - $bundleTotal;

        return [
            'package' => $package,
            'pricing' => $pricing,
            'availability' => $availability,
            'allAvailable' => $allAvailable,
            'individualTotal' => number_format($individualTotal, 2, '.', ''),
            'bundleTotal' => $pricing['total_ht'],
            'savings' => number_format(max(0, $savings), 2, '.', ''),
            'savingsPercent' => $individualTotal > 0
                ? round(($savings / $individualTotal) * 100, 1)
                : 0,
        ];
    }

    /**
     * Packages contenant une variante spécifique
     * @return Package[]
     */
    public function getPackagesForVariant(BikeVariant $variant): array
    {
        return $this->packageRepository->createQueryBuilder('p')
            ->join('p.items', 'i')
            ->where('i.variant = :variant')
            ->andWhere('p.isActive = true')
            ->setParameter('variant', $variant)
            ->getQuery()
            ->getResult();
    }

    /**
     * Créer un package à partir de variantes
     *
     * @param array<array{variant: BikeVariant, quantity: int, priceOverride?: string}> $items
     */
    public function createPackage(
        string $name,
        string $slug,
        string $description,
        array $items,
        ?string $discount = null,
    ): Package {
        $package = (new Package())
            ->setName($name)
            ->setSlug($slug)
            ->setDescription($description)
            ->setPackageDiscount($discount);

        $totalHT = 0.0;
        $position = 1;

        foreach ($items as $itemData) {
            $item = (new PackageItem())
                ->setPackage($package)
                ->setVariant($itemData['variant'])
                ->setQuantity($itemData['quantity'])
                ->setPriceOverride($itemData['priceOverride'] ?? null)
                ->setPosition($position++);

            $price = $itemData['priceOverride'] ?? $itemData['variant']->getBasePrice();
            $totalHT += (float) $price * $itemData['quantity'];

            $package->addItem($item);
        }

        $package->setTotalPriceHT(number_format($totalHT - (float) ($discount ?? '0'), 2, '.', ''));

        $this->em->persist($package);
        $this->em->flush();

        return $package;
    }
}
