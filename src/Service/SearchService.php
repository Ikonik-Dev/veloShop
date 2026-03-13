<?php

namespace App\Service;

use App\Entity\Bike;
use App\Repository\BikeRepository;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;

use function mb_strlen;

class SearchService
{
    public function __construct(
        private BikeRepository $bikeRepository,
        private BrandRepository $brandRepository,
        private CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * Recherche globale multi-entité
     *
     * @return array{bikes: Bike[], brands: array, categories: array, total: int}
     */
    public function search(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['bikes' => [], 'brands' => [], 'categories' => [], 'total' => 0];
        }

        $bikes = $this->searchBikes($query, $limit);
        $brands = $this->searchBrands($query, 5);
        $categories = $this->searchCategories($query, 5);

        return [
            'bikes' => $bikes,
            'brands' => $brands,
            'categories' => $categories,
            'total' => count($bikes) + count($brands) + count($categories),
        ];
    }

    /**
     * Recherche de vélos par nom, description, features
     * @return Bike[]
     */
    public function searchBikes(string $query, int $limit = 20): array
    {
        return $this->bikeRepository->createQueryBuilder('b')
            ->leftJoin('b.brand', 'br')
            ->leftJoin('b.category', 'c')
            ->leftJoin('b.variants', 'v')
            ->addSelect('br', 'c', 'v')
            ->where('b.isActive = true')
            ->andWhere('b.name LIKE :q OR b.description LIKE :q OR br.name LIKE :q OR b.features LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('b.isFeatured', 'DESC')
            ->addOrderBy('b.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Autocomplétion rapide (noms de vélos uniquement)
     * @return string[]
     */
    public function autocomplete(string $query, int $limit = 8): array
    {
        if (mb_strlen($query) < 2) {
            return [];
        }

        $results = $this->bikeRepository->createQueryBuilder('b')
            ->select('b.name')
            ->where('b.isActive = true')
            ->andWhere('b.name LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('b.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        // Ajouter aussi les marques qui matchent
        $brandResults = $this->brandRepository->createQueryBuilder('br')
            ->select('br.name')
            ->where('br.isActive = true')
            ->andWhere('br.name LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->setMaxResults(3)
            ->getQuery()
            ->getSingleColumnResult();

        return array_values(array_unique(array_merge($results, $brandResults)));
    }

    /**
     * Suggestions "Cherchiez-vous..." basées sur catégorie/marque populaire
     * @return Bike[]
     */
    public function getSuggestions(string $query, int $limit = 6): array
    {
        if ($query === '') {
            // Sans requête, retourner les featured
            return $this->bikeRepository->findFeatured();
        }

        // D'abord chercher les résultats directs
        $directResults = $this->searchBikes($query, $limit);

        if (!empty($directResults)) {
            return $directResults;
        }

        // Si aucun résultat, élargir la recherche aux mots individuels
        $words = explode(' ', $query);
        $qb = $this->bikeRepository->createQueryBuilder('b')
            ->leftJoin('b.brand', 'br')
            ->addSelect('br')
            ->where('b.isActive = true');

        $orConditions = [];
        foreach ($words as $i => $word) {
            if (mb_strlen($word) < 2) {
                continue;
            }
            $param = 'word' . $i;
            $orConditions[] = "b.name LIKE :{$param} OR b.description LIKE :{$param} OR br.name LIKE :{$param}";
            $qb->setParameter($param, '%' . $word . '%');
        }

        if (empty($orConditions)) {
            return [];
        }

        $qb->andWhere('(' . implode(' OR ', $orConditions) . ')')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche de marques
     * @return \App\Entity\Brand[]
     */
    private function searchBrands(string $query, int $limit = 5): array
    {
        return $this->brandRepository->createQueryBuilder('br')
            ->where('br.isActive = true')
            ->andWhere('br.name LIKE :q OR br.country LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de catégories
     * @return \App\Entity\Category[]
     */
    private function searchCategories(string $query, int $limit = 5): array
    {
        return $this->categoryRepository->createQueryBuilder('c')
            ->where('c.isActive = true')
            ->andWhere('c.name LIKE :q OR c.description LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
