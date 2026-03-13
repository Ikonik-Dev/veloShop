<?php

namespace App\Service;

use App\Entity\BikeVariant;
use App\Entity\Stock;
use App\Repository\StockRepository;
use App\Repository\BikeVariantRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockService
{
    public function __construct(
        private StockRepository $stockRepository,
        private BikeVariantRepository $variantRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Vérifie si une variante est en stock (tous entrepôts confondus)
     */
    public function isInStock(BikeVariant $variant): bool
    {
        return $variant->getTotalStock() > 0;
    }

    /**
     * Stock total d'une variante tous entrepôts confondus
     */
    public function getTotalStock(BikeVariant $variant): int
    {
        return $variant->getTotalStock();
    }

    /**
     * Détail du stock par entrepôt pour une variante
     * @return array<array{warehouse: string, quantity: int, isLow: bool, reorderLevel: ?int}>
     */
    public function getStockDetails(BikeVariant $variant): array
    {
        $details = [];

        foreach ($variant->getStocks() as $stock) {
            $details[] = [
                'warehouse' => $stock->getWarehouse(),
                'quantity' => $stock->getQuantity(),
                'isLow' => $stock->isLowStock(),
                'reorderLevel' => $stock->getReorderLevel(),
                'lastRestock' => $stock->getLastRestockDate(),
            ];
        }

        return $details;
    }

    /**
     * Ajouter du stock pour une variante dans un entrepôt
     */
    public function addStock(BikeVariant $variant, string $warehouse, int $quantity): Stock
    {
        $stock = $this->findOrCreateStock($variant, $warehouse);
        $stock->addQuantity($quantity);
        $stock->setLastRestockDate(new \DateTimeImmutable());

        $this->em->flush();

        return $stock;
    }

    /**
     * Retirer du stock
     */
    public function removeStock(BikeVariant $variant, string $warehouse, int $quantity): Stock
    {
        $stock = $this->findOrCreateStock($variant, $warehouse);
        $stock->removeQuantity($quantity);

        $this->em->flush();

        return $stock;
    }

    /**
     * Toutes les variantes en stock faible (sous le reorderLevel)
     * @return Stock[]
     */
    public function getLowStockAlerts(): array
    {
        return $this->stockRepository->createQueryBuilder('s')
            ->join('s.variant', 'v')
            ->join('v.bike', 'b')
            ->addSelect('v', 'b')
            ->where('s.reorderLevel IS NOT NULL')
            ->andWhere('s.quantity <= s.reorderLevel')
            ->orderBy('s.quantity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Variantes avec stock = 0 (rupture totale)
     * @return BikeVariant[]
     */
    public function getOutOfStockVariants(): array
    {
        return $this->variantRepository->createQueryBuilder('v')
            ->leftJoin('v.stocks', 's')
            ->join('v.bike', 'b')
            ->addSelect('b')
            ->where('v.isActive = true')
            ->groupBy('v.id')
            ->having('COALESCE(SUM(s.quantity), 0) = 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * Résumé du stock global
     */
    public function getStockSummary(): array
    {
        $totalVariants = $this->variantRepository->count(['isActive' => true]);

        $inStock = $this->variantRepository->createQueryBuilder('v')
            ->select('COUNT(DISTINCT v.id)')
            ->join('v.stocks', 's')
            ->where('v.isActive = true')
            ->andWhere('s.quantity > 0')
            ->getQuery()
            ->getSingleScalarResult();

        $lowStock = count($this->getLowStockAlerts());

        $totalUnits = $this->stockRepository->createQueryBuilder('s')
            ->select('COALESCE(SUM(s.quantity), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        $warehouseBreakdown = $this->stockRepository->createQueryBuilder('s')
            ->select('s.warehouse, SUM(s.quantity) AS totalQty, COUNT(DISTINCT s.variant) AS variantCount')
            ->groupBy('s.warehouse')
            ->orderBy('totalQty', 'DESC')
            ->getQuery()
            ->getResult();

        return [
            'totalVariants' => (int) $totalVariants,
            'inStock' => (int) $inStock,
            'outOfStock' => $totalVariants - (int) $inStock,
            'lowStock' => $lowStock,
            'totalUnits' => (int) $totalUnits,
            'warehouses' => $warehouseBreakdown,
        ];
    }

    /**
     * Historique de stock : variantes récemment réapprovisionnées
     * @return Stock[]
     */
    public function getRecentRestocks(int $days = 30): array
    {
        $since = new \DateTimeImmutable("-{$days} days");

        return $this->stockRepository->createQueryBuilder('s')
            ->join('s.variant', 'v')
            ->join('v.bike', 'b')
            ->addSelect('v', 'b')
            ->where('s.lastRestockDate >= :since')
            ->setParameter('since', $since)
            ->orderBy('s.lastRestockDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    private function findOrCreateStock(BikeVariant $variant, string $warehouse): Stock
    {
        foreach ($variant->getStocks() as $stock) {
            if ($stock->getWarehouse() === $warehouse) {
                return $stock;
            }
        }

        $stock = (new Stock())
            ->setVariant($variant)
            ->setWarehouse($warehouse)
            ->setQuantity(0);

        $this->em->persist($stock);

        return $stock;
    }
}
