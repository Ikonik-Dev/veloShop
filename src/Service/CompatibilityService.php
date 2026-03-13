<?php

namespace App\Service;

use App\Entity\Bike;
use App\Entity\BikeCompatibility;
use App\Repository\BikeCompatibilityRepository;

class CompatibilityService
{
    public function __construct(
        private BikeCompatibilityRepository $compatibilityRepository,
    ) {
    }

    /**
     * Récupère les upgrades possibles depuis un vélo
     * @return BikeCompatibility[]
     */
    public function getUpgrades(Bike $bike): array
    {
        return $this->compatibilityRepository->createQueryBuilder('bc')
            ->join('bc.bikeTo', 'bt')
            ->addSelect('bt')
            ->where('bc.bikeFrom = :bike')
            ->andWhere('bc.type = :type')
            ->andWhere('bc.isActive = true')
            ->andWhere('bt.isActive = true')
            ->setParameter('bike', $bike)
            ->setParameter('type', 'upgrade')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les downgrades (vélo moins cher) depuis un vélo
     * @return BikeCompatibility[]
     */
    public function getDowngrades(Bike $bike): array
    {
        return $this->compatibilityRepository->createQueryBuilder('bc')
            ->join('bc.bikeTo', 'bt')
            ->addSelect('bt')
            ->where('bc.bikeFrom = :bike')
            ->andWhere('bc.type = :type')
            ->andWhere('bc.isActive = true')
            ->andWhere('bt.isActive = true')
            ->setParameter('bike', $bike)
            ->setParameter('type', 'downgrade')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vélos similaires
     * @return BikeCompatibility[]
     */
    public function getSimilar(Bike $bike): array
    {
        return $this->compatibilityRepository->createQueryBuilder('bc')
            ->join('bc.bikeTo', 'bt')
            ->addSelect('bt')
            ->where('bc.bikeFrom = :bike')
            ->andWhere('bc.type IN (:types)')
            ->andWhere('bc.isActive = true')
            ->andWhere('bt.isActive = true')
            ->setParameter('bike', $bike)
            ->setParameter('types', ['similar', 'compatible'])
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les relations de compatibilité d'un vélo (dans les deux sens)
     */
    public function getAllRelations(Bike $bike): array
    {
        $outgoing = $this->compatibilityRepository->createQueryBuilder('bc')
            ->join('bc.bikeTo', 'bt')
            ->addSelect('bt')
            ->where('bc.bikeFrom = :bike')
            ->andWhere('bc.isActive = true')
            ->setParameter('bike', $bike)
            ->getQuery()
            ->getResult();

        $incoming = $this->compatibilityRepository->createQueryBuilder('bc')
            ->join('bc.bikeFrom', 'bf')
            ->addSelect('bf')
            ->where('bc.bikeTo = :bike')
            ->andWhere('bc.isActive = true')
            ->setParameter('bike', $bike)
            ->getQuery()
            ->getResult();

        return [
            'upgrades' => array_filter($outgoing, fn(BikeCompatibility $bc) => $bc->getType() === 'upgrade'),
            'downgrades' => array_filter($outgoing, fn(BikeCompatibility $bc) => $bc->getType() === 'downgrade'),
            'similar' => array_filter($outgoing, fn(BikeCompatibility $bc) => in_array($bc->getType(), ['similar', 'compatible'], true)),
            'incoming' => $incoming,
        ];
    }

    /**
     * Parcours d'upgrade complet : A → B → C (chaîne d'upgrades)
     * @return Bike[]
     */
    public function getUpgradePath(Bike $bike, int $maxDepth = 5): array
    {
        $path = [];
        $current = $bike;
        $visited = [$bike->getId()];

        for ($i = 0; $i < $maxDepth; $i++) {
            $upgrades = $this->getUpgrades($current);
            if (empty($upgrades)) {
                break;
            }

            $nextBike = $upgrades[0]->getBikeTo();
            if ($nextBike === null || in_array($nextBike->getId(), $visited, true)) {
                break;
            }

            $path[] = $nextBike;
            $visited[] = $nextBike->getId();
            $current = $nextBike;
        }

        return $path;
    }

    /**
     * Suggestions personnalisées basées sur catégorie + marque + segment
     * @return Bike[]
     */
    public function getSuggestions(Bike $bike): array
    {
        // Combiner les vélos compatibles + similaires via compatibilité
        $relations = $this->getAllRelations($bike);
        $suggestions = [];
        $seenIds = [$bike->getId()];

        foreach (['upgrades', 'similar', 'incoming'] as $type) {
            foreach ($relations[$type] as $compatibility) {
                $related = $compatibility instanceof BikeCompatibility
                    ? ($type === 'incoming' ? $compatibility->getBikeFrom() : $compatibility->getBikeTo())
                    : null;

                if ($related !== null && !in_array($related->getId(), $seenIds, true)) {
                    $suggestions[] = $related;
                    $seenIds[] = $related->getId();
                }
            }
        }

        return $suggestions;
    }
}
