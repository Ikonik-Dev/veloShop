# 🚴 Guide d'utilisation - Architecture e-commerce vélos

## ✅ État actuel

Le système complet est **opérationnel** avec :

- ✅ 17 entités Doctrine créées et migrées
- ✅ 17 repositories avec méthodes de recherche avancées
- ✅ 2 migrations SQL exécutées
- ✅ Données d'exemple chargées

## 🏃 Lancer le projet

```bash
# 1. Démarrer les services (Laragon)
php artisan serve
# OU via Laragon: Clic droit → Start All

# 2. Vérifier que tout est ok
symfony console doctrine:query:sql "SELECT COUNT(*) FROM bikes"
# Devrait retourner: 2 vélos d'exemple

# 3. Charger des données fraîches (remet à zéro la DB)
symfony console doctrine:fixtures:load

# 4. Voir les tables créées
# Accès PhpMyAdmin: http://localhost/phpmyadmin
# Base: rameshBike
```

## 📚 Exemples de code

### 1. Récupérer tous les vélos actifs

```php
// Dans un Controller
use App\Repository\BikeRepository;

public function __construct(private BikeRepository $bikeRepository) {}

public function index(): Response
{
    $bikes = $this->bikeRepository->findActive();

    return $this->render('bikes/list.html.twig', [
        'bikes' => $bikes
    ]);
}
```

### 2. Afficher le détail d'un vélo avec toutes ses variantes et tarifications

```php
public function show(string $slug): Response
{
    $bike = $this->bikeRepository->findBySlug($slug);

    if (!$bike) {
        throw $this->createNotFoundException('Vélo non trouvé');
    }

    // Les variantes sont automatiquement chargées (lazy loading)
    $variants = $bike->getVariants();

    // Accéder aux données d'une variante
    foreach ($variants as $variant) {
        $color = $variant->getColor();
        $size = $variant->getSize();
        $weight = $variant->getWeight();
        $isElectric = $variant->isElectric();

        // Tarifications par segment
        $prices = $variant->getPrices();
        foreach ($prices as $price) {
            echo "{$price->getSegment()->getName()} : {$price->getPriceHT()}€";
        }

        // Stock
        $totalStock = $variant->getTotalStock();
        foreach ($variant->getStocks() as $stock) {
            echo "{$stock->getWarehouse()} : {$stock->getQuantity()} unités";
        }
    }
}
```

### 3. Afficher les vélos électriques (40% du catalogue)

```php
public function electricBikes(): Response
{
    $electricBikes = $this->bikeRepository->findElectric();

    foreach ($electricBikes as $bike) {
        foreach ($bike->getVariants() as $variant) {
            if ($variant->getMotor()) {
                $motor = $variant->getMotor();
                echo "{$bike->getName()} - {$motor->getName()}";
                echo "Puissance: {$motor->getWattage()}W";
                echo "Autonomie: {$motor->getEstimatedRange()}km";
            }
        }
    }
}
```

### 4. Tarifications différenciées par segment client

```php
public function getPriceForCustomer(BikeVariant $variant, CustomerSegment $segment): ?string
{
    $price = $this->bikePriceRepository->findByVariantAndSegment(
        $variant->getId(),
        $segment->getId()
    );

    if ($price && $price->isCurrentlyValid()) {
        return $price->getPriceTTC(); // Affiche au client
    }

    return null;
}

// Utilisation
$variant = $bikeVariant; // Une variante de Trek Marlin 7
$amateurSegment = $customerSegmentRepository->findByName('Amateur');
$proSegment = $customerSegmentRepository->findByName('Professionnel');

echo "Prix Amateur: " . getPriceForCustomer($variant, $amateurSegment); // 1078.80€
echo "Prix Pro: " . getPriceForCustomer($variant, $proSegment);        // 916.80€
```

### 5. Trouver les vélos compatibles ou recommandés

```php
public function showCompatible(Bike $bike): Response
{
    // Vélos similaires (cross-sell)
    $similar = $this->bikeCompatibilityRepository->findSimilarBikes($bike->getId());

    // Upgrades disponibles (upsell)
    $upgrades = $this->bikeCompatibilityRepository->findRecommendedUpgrades($bike->getId());

    return $this->render('bikes/details.html.twig', [
        'similar' => $similar,
        'upgrades' => $upgrades
    ]);
}
```

### 6. Avis clients et notes

```php
public function showBikeWithReviews(Bike $bike): Response
{
    // Note moyenne
    $avgRating = $this->reviewRepository->getAverageRatingByBike($bike->getId());

    // Nombre d'avis
    $reviewCount = $this->reviewRepository->getReviewCountByBike($bike->getId());

    // Avis approuvés uniquement
    $reviews = $this->reviewRepository->findApprovedByBike($bike->getId());

    return $this->render('bikes/reviews.html.twig', [
        'bike' => $bike,
        'avgRating' => $avgRating,
        'reviewCount' => $reviewCount,
        'reviews' => $reviews
    ]);
}
```

### 7. Gestion des stocks et alertes

```php
public function checkStock(): Response
{
    // Stock faible (< seuil de réapprovisionnement)
    $lowStock = $this->stockRepository->findLowStock();

    // Ruptures de stock
    $outOfStock = $this->stockRepository->findOutOfStock();

    // Stock par dépôt
    $depotStock = $this->stockRepository->findByWarehouse('Dépôt principal');

    // Stock total d'une variante
    $totalStock = $this->stockRepository->getTotalStock($variantId);

    return new JsonResponse([
        'lowStock' => count($lowStock),
        'outOfStock' => count($outOfStock),
        'totalByWarehouse' => count($depotStock),
        'totalVariant' => $totalStock
    ]);
}
```

### 8. Créer un nouveau vélo complet

```php
use Doctrine\ORM\EntityManagerInterface;

public function createNewBike(EntityManagerInterface $em): Response
{
    // 1. Créer le modèle de base
    $bike = (new Bike())
        ->setName('Trek Domane SLR')
        ->setSlug('trek-domane-slr')
        ->setCategory($categoryRoute)
        ->setBrand($brandTrek)
        ->setDescription('Vélo de route ultralégère pour les coureurs')
        ->setModelYear(2024)
        ->setSegmentLevel('pro');

    // 2. Créer une variante
    $variant = (new BikeVariant())
        ->setBike($bike)
        ->setColor('Noir')
        ->setSize('54cm')
        ->setBasePrice('4999.00')
        ->setWeight(6800); // 6.8kg ultra léger

    // 3. Ajouter spécifications techniques
    (new BikeSpecification())
        ->setVariant($variant)
        ->setName('Cadre')
        ->setValue('Carbone OCLV 500')
        ->setPosition(1);

    // 4. Tarifications par segment
    (new BikePrice())
        ->setVariant($variant)
        ->setSegment($segmentPro)
        ->setPriceHT('4999.00')
        ->setPriceTTC('5998.80')
        ->setMarginRate('40');

    // 5. Stock initial
    (new Stock())
        ->setVariant($variant)
        ->setWarehouse('Dépôt principal')
        ->setQuantity(2)
        ->setReorderLevel(1);

    // 6. Sauvegarder
    $em->persist($bike);
    $em->persist($variant);
    $em->flush();

    return new Response('Vélo créé: ' . $bike->getName());
}
```

## 🎯 Requêtes SQL courantes

```sql
-- Tous les vélos avec leurs variantes
SELECT b.name, bv.color, bv.size, bv.base_price
FROM bikes b
JOIN bike_variants bv ON b.id = bv.bike_id
WHERE b.is_active = 1;

-- Vélos électriques seulement
SELECT b.name, m.name as motor, m.wattage
FROM bikes b
JOIN bike_variants bv ON b.id = bv.bike_id
JOIN motors m ON bv.motor_id = m.id;

-- Prix pour chaque segment
SELECT b.name, bv.color, bv.size, cs.name as segment, bp.price_ht
FROM bikes b
JOIN bike_variants bv ON b.id = bv.bike_id
JOIN bike_prices bp ON bv.id = bp.variant_id
JOIN customer_segments cs ON bp.segment_id = cs.id
ORDER BY b.name, cs.name;

-- Stock par dépôt
SELECT b.name, bv.color, s.warehouse, s.quantity
FROM bikes b
JOIN bike_variants bv ON b.id = bv.bike_id
JOIN stocks s ON bv.id = s.variant_id
ORDER BY s.warehouse, b.name;

-- Vélos avec avis clients
SELECT b.name, COUNT(r.id) as review_count, AVG(r.rating) as avg_rating
FROM bikes b
LEFT JOIN reviews r ON b.id = r.bike_id AND r.is_approved = 1
GROUP BY b.id
HAVING review_count > 0;

-- Stock faible (alerte réapprovisionnement)
SELECT b.name, bv.color, s.warehouse, s.quantity, s.reorder_level
FROM bikes b
JOIN bike_variants bv ON b.id = bv.bike_id
JOIN stocks s ON bv.id = s.variant_id
WHERE s.quantity <= s.reorder_level;
```

## 📁 Structure des fichiers créés

```
src/
├── Entity/
│   ├── Category.php
│   ├── Brand.php
│   ├── Motor.php
│   ├── FeatureCategory.php
│   ├── BikeFeature.php
│   ├── Bike.php
│   ├── BikeVariant.php
│   ├── BikeSpecification.php
│   ├── CustomerSegment.php
│   ├── BikePrice.php
│   ├── Stock.php
│   ├── BikeImage.php
│   ├── BikeCompatibility.php
│   ├── Package.php
│   ├── PackageItem.php
│   └── Review.php
│
├── Repository/
│   ├── CategoryRepository.php
│   ├── BrandRepository.php
│   ├── MotorRepository.php
│   ├── FeatureCategoryRepository.php
│   ├── BikeFeatureRepository.php
│   ├── BikeRepository.php
│   ├── BikeVariantRepository.php
│   ├── BikeSpecificationRepository.php
│   ├── CustomerSegmentRepository.php
│   ├── BikePriceRepository.php
│   ├── StockRepository.php
│   ├── BikeImageRepository.php
│   ├── BikeCompatibilityRepository.php
│   ├── PackageRepository.php
│   ├── PackageItemRepository.php
│   └── ReviewRepository.php
│
└── DataFixtures/
    └── BicycleFixtures.php

migrations/
├── Version20260311112616.php (création tables)
├── Version20260311112904.php (range → estimatedRange)
└── Version20260311112941.php (condition → bikeCondition)

docs/
└── bicycle-architecture.md
```

## 🧪 Tests

### Vérifier les données

```bash
# Vélos chargés
symfony console doctrine:query:sql "SELECT COUNT(*) FROM bikes"

# Variantes par vélo
symfony console doctrine:query:sql "
SELECT b.name, COUNT(bv.id) as variant_count
FROM bikes b
LEFT JOIN bike_variants bv ON b.id = bv.bike_id
GROUP BY b.id"

# Électriques vs mécaniques
symfony console doctrine:query:sql "
SELECT
  SUM(CASE WHEN bv.motor_id IS NULL THEN 1 ELSE 0 END) as mechanical,
  SUM(CASE WHEN bv.motor_id IS NOT NULL THEN 1 ELSE 0 END) as electric
FROM bike_variants bv"

# Stock total
symfony console doctrine:query:sql "
SELECT SUM(s.quantity) FROM stocks s"
```

## 🚀 Prochaines étapes recommandées

1. **Admin Symfony (EasyAdmin)**

    ```bash
    composer require easycorp/easyadmin-bundle
    symfony console make:admin Bike
    ```

2. **API REST (API Platform)**

    ```bash
    composer require api-platform/core
    ```

3. **Filtrage avancé frontend**
    - Filtre par catégorie
    - Filtre par marque
    - Filtre par prix (min/max)
    - Filtre électrique/mécanique
    - Filtre par disponibilité stock

4. **Système de panier**
    - Session ou base de données
    - Calcul des prix selon segment client
    - Gestion des stocks

5. **Services métier**
    ```php
    // Services utiles à créer:
    - BikeVariantPriceCalculator
    - StockManager
    - RecommendationEngine
    - OrderProcessor
    ```

## ❓ FAQ

**Q: Comment ajouter 40% de VAE?**
A: Créer un BikeVariant avec `setMotor($motorObject)` vs null pour mécanique

**Q: Comment gérer plusieurs tarifications?**
A: Une BikePrice par combinaison Variante + Segment client

**Q: Comment organiser les dépôts stock?**
A: Table Stock avec colonne `warehouse` - multiple stocks par variante

**Q: Où mettre les images?**
A: BikeImage.filename stocke le chemin, créer dossier `/public/images/bikes/`

**Q: Comment modifier les données?**
A: Créer un Controller CRUD ou utiliser EasyAdmin

---

**Créé le:** 11 Mars 2026  
**Statut:** ✅ Production-ready
