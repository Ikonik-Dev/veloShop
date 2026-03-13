<?php

namespace App\Service;

use App\Entity\BikeFeature;
use App\Entity\FeatureCategory;
use App\Repository\BikeFeatureRepository;
use App\Repository\BikeRepository;
use App\Repository\FeatureCategoryRepository;

class FeatureService
{
    public function __construct(
        private BikeFeatureRepository $featureRepository,
        private FeatureCategoryRepository $featureCategoryRepository,
        private BikeRepository $bikeRepository,
    ) {
    }

    /**
     * Liste toutes les features groupées par catégorie
     * @return array<string, array{category: FeatureCategory, features: BikeFeature[]}>
     */
    public function getFeaturesGroupedByCategory(): array
    {
        $categories = $this->featureCategoryRepository->findBy(
            ['isActive' => true],
            ['name' => 'ASC']
        );

        $grouped = [];
        foreach ($categories as $category) {
            $features = $this->featureRepository->findBy(
                ['category' => $category, 'isActive' => true],
                ['name' => 'ASC']
            );

            if (!empty($features)) {
                $grouped[$category->getName()] = [
                    'category' => $category,
                    'features' => $features,
                ];
            }
        }

        return $grouped;
    }

    /**
     * Combien de vélos utilisent chaque feature ?
     * @return array<int, array{feature: string, category: string, bikeCount: int}>
     */
    public function getFeaturePopularity(): array
    {
        return $this->bikeRepository->createQueryBuilder('b')
            ->select('f.name AS feature, fc.name AS category, COUNT(b.id) AS bikeCount')
            ->join('b.bikeFeatures', 'f')
            ->join('f.category', 'fc')
            ->where('b.isActive = true')
            ->andWhere('f.isActive = true')
            ->groupBy('f.id')
            ->orderBy('bikeCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les vélos qui ont une feature spécifique
     * @return \App\Entity\Bike[]
     */
    public function getBikesWithFeature(BikeFeature $feature): array
    {
        return $this->bikeRepository->createQueryBuilder('b')
            ->join('b.bikeFeatures', 'f')
            ->where('f = :feature')
            ->andWhere('b.isActive = true')
            ->setParameter('feature', $feature)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les vélos qui possèdent TOUTES les features données
     * @param BikeFeature[] $features
     * @return \App\Entity\Bike[]
     */
    public function getBikesWithAllFeatures(array $features): array
    {
        if (empty($features)) {
            return [];
        }

        $qb = $this->bikeRepository->createQueryBuilder('b')
            ->where('b.isActive = true');

        foreach ($features as $i => $feature) {
            $alias = 'f' . $i;
            $qb->join('b.bikeFeatures', $alias)
                ->andWhere("{$alias} = :feature{$i}")
                ->setParameter("feature{$i}", $feature);
        }

        return $qb->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Features communes entre plusieurs vélos
     * @param \App\Entity\Bike[] $bikes
     * @return BikeFeature[]
     */
    public function getCommonFeatures(array $bikes): array
    {
        if (count($bikes) < 2) {
            return [];
        }

        $featureSets = [];
        foreach ($bikes as $bike) {
            $ids = [];
            foreach ($bike->getBikeFeatures() as $feature) {
                $ids[] = $feature->getId();
            }
            $featureSets[] = $ids;
        }

        $commonIds = array_intersect(...$featureSets);

        if (empty($commonIds)) {
            return [];
        }

        return $this->featureRepository->findBy(['id' => $commonIds]);
    }

    /**
     * Features uniques d'un vélo par rapport à un autre
     * @return BikeFeature[]
     */
    public function getUniqueFeatures(\App\Entity\Bike $bike, \App\Entity\Bike $comparedTo): array
    {
        $bikeFeatureIds = [];
        foreach ($bike->getBikeFeatures() as $f) {
            $bikeFeatureIds[] = $f->getId();
        }

        $comparedFeatureIds = [];
        foreach ($comparedTo->getBikeFeatures() as $f) {
            $comparedFeatureIds[] = $f->getId();
        }

        $uniqueIds = array_diff($bikeFeatureIds, $comparedFeatureIds);

        if (empty($uniqueIds)) {
            return [];
        }

        return $this->featureRepository->findBy(['id' => $uniqueIds]);
    }
}
