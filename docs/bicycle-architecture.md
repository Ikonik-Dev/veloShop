# Architecture e-commerce Bicyclettes

Cette documentation décrit l'architecture complète implémentée pour gérer les ventes de vélos avec tous leurs variants, tarifications par segment et packages.

## 📋 Vue d'ensemble

17 entités + 17 repositories ont été créés pour gérer un catalogue de vélos professionnel et flexible.

## 🏗️ Architecture générale

```
CATÉGORIES & MARQUES
├── Category (VTT, Route, Gravel, Fat Bike, Cargo, Tandem, etc.)
└── Brand (Trek, Specialized, Giant, etc.)

MODÈLES DE VÉLOS
├── Bike (Modèle principal)
│   ├── BikeVariant (Couleur, Taille, Moteur)
│   │   ├── BikeSpecification (Specs techniques)
│   │   ├── BikePrice (Tarifications par segment)
│   │   └── Stock (Inventaire par dépôt)
│   ├── BikeImage (Galerie images)
│   ├── Review (Avis clients)
│   └── BikeFeature (Équipements - relation many-to-many)

COMPOSANTS & TECHNOLOGIES
├── BikeFeature (Équipements : Shimano, Sram, etc.)
│   └── FeatureCategory (Catégories d'équipements)
├── Motor (Moteurs VAE : Bosch, Shimano, etc.)
└── BikeCompatibility (Lier vélos compatibles)

SEGMENTS CLIENTS & TARIFICATIONS
├── CustomerSegment (Amateur, Semi-pro, Pro, Entreprise)
└── BikePrice (Prix différenciés par segment)

OFFRES GROUPÉES
├── Package (Offres : vélo + équipements)
└── PackageItem (Éléments d'une offre)
```

## 📊 Entités détaillées

### 1. **Category** - Catégories de vélos

```
- name: string (VTT, Route, Gravel, Fat Bike, Cargo, Tandem, etc.)
- slug: string (url-friendly)
- description: text
- icon: string (classe FontAwesome ou emoji)
- isActive: boolean
```

### 2. **Brand** - Marques

```
- name: string
- slug: string
- description: text
- logoUrl: string
- country: string
- website: string
- isActive: boolean
```

### 3. **Motor** - Moteurs électriques (VAE)

```
- name: string (Bosch Performance Line, Shimano Steps, etc.)
- brand: FK Brand
- wattage: int (250, 500, 750W)
- torque: int (Nm)
- batteryCapacity: int (Wh)
- range: int (km estimé)
- isActive: boolean
```

### 4. **FeatureCategory** & **BikeFeature** - Équipements

```
FeatureCategory (Catégories)
- name: string (Freins, Dérailleur, Selle, Pneus, etc.)
- features: OneToMany → BikeFeature

BikeFeature (Équipements spécifiques)
- name: string (Shimano Deore XT, Sram GX, etc.)
- category: FK FeatureCategory
- specification: string (Modèle/version)
- isActive: boolean
```

### 5. **Bike** - Modèle de vélo

```
- name: string (Trek Marlin 7, Trek Domane SLR, etc.)
- slug: string (url-friendly)
- category: FK Category
- brand: FK Brand
- description: text (détails marketing)
- features: text (liste à puces)
- modelYear: int (2024, 2025, etc.)
- segmentLevel: enum (none|semi-pro|pro|enterprise)
  → Permet de cibler des profils clients spécifiques
- isFeatured: boolean (mette en avant)
- variants: OneToMany → BikeVariant
- images: OneToMany → BikeImage
- reviews: OneToMany → Review
- bikeFeatures: ManyToMany → BikeFeature
```

### 6. **BikeVariant** - Configuration spécifique du vélo

```
⭐ Entité clé pour la personnalisation

- bike: FK Bike
- color: string (Noir, Bleu, Blanc, etc.)
- size: string (XS, S, M, L, XL ou 48cm, 50cm, 52cm, etc.)
- motor: FK Motor (null = non électrique, 40% du catalogue)
- basePrice: decimal (Prix HT base)
- weight: int (grammes)
- condition: enum (new|refurbished|used)
- isElectric(): boolean (helper)
- specifications: OneToMany → BikeSpecification
- prices: OneToMany → BikePrice
- stocks: OneToMany → Stock
```

### 7. **BikeSpecification** - Spécifications techniques

```
⭐ Permet de différencier les variantes

- variant: FK BikeVariant
- name: string (Cadre, Fourche, Freins, Dérailleur, Pneus...)
- value: string (Aluminium 6061, Suspension 100mm, Hydraulique...)
- unit: string (mm, kg, pouces, etc.)
- position: int (ordre d'affichage)
```

⚠️ **Important**: Les specs peuvent varier par taille/couleur

- Ex: Taille L pèse 13.2kg vs Taille M pèse 12.8kg
- Ex: Édition limitée couleur "rouge racing" a des freins upscale

### 8. **CustomerSegment** - Segments clients

```
- name: string (Amateur, Semi-pro, Pro, Entreprise Vélib, B2C Occasion)
- description: text
- discountRate: decimal (0-100%) → Réduction applicable

Segments prédéfinis courants:
├── Amateur (0% discount)
├── Semi-pro (5-10% discount)
├── Pro (15-25% discount)
├── Entreprise Vélib (30-40% discount)
└── B2C Occasion (5-15% discount)
```

### 9. **BikePrice** - Tarifications flexibles

```
⭐ Gère les prix différenciés par segment

- variant: FK BikeVariant
- segment: FK CustomerSegment
- priceHT: decimal
- priceTTC: decimal (calculé)
- marginRate: decimal (%) → Permet l'  analyse commerciale
- validFrom / validUntil: datetime → Gestion des promos
- isCurrentlyValid(): boolean (helper)

Cas d'usage:
├── Même vélo, prix amateur vs pro
├── Soldes temporaires (dates limites)
└── Prix spécifiques par channel de vente
```

### 10. **Stock** - Inventaire

```
- variant: FK BikeVariant
- warehouse: string (Dépôt principal, Magasin Paris, Dépôt Lyon...)
- quantity: int
- reorderLevel: int (Seuil d'alerte)
- lastRestockDate: datetime
- isLowStock(): boolean (helper)

Cas d'usage:
├── Stock par dépôt (multi-localisation)
├── Alertes réapprovisionnement
└── Historique restockage
```

### 11. **BikeImage** - Galerie images

```
- bike: FK Bike
- filename: string
- altText: string
- type: enum (thumbnail|primary|secondary|gallery)
- position: int (ordre d'affichage)
- uploadedAt: datetime
```

### 12. **BikeCompatibility** - Compatibilités entre vélos

```
⭐ Recommande des produits similaires/upgrades

- bikeFrom: FK Bike (vélo source)
- bikeTo: FK Bike (vélo compatible)
- type: enum (compatible|similar|upgrade|downgrade)
  ├── compatible: Alternative directe
  ├── similar: Géométrie/utilisation proche
  ├── upgrade: Modèle supérieur
  └── downgrade: Modèle d'entrée de gamme
- reason: text (Pourquoi c'est compatible)

Cas d'usage:
├── Trek Marlin 5 → Trek Marlin 7 (upgrade)
├── Trek Marlin 7 → Specialized Rockhopper (similar)
└── Trek Marlin 7 (electrique) → Trek Marlin 7 (mécanique) (compatible)
```

### 13. **Package** - Offres groupées

```
⭐ Vendre des bundles : vélo + équipements

- name: string (VTT Complet, Vélo Route Casual, Promo E-Bike...)
- slug: string
- description: text
- totalPriceHT: decimal
- packageDiscount: decimal (réduction globale)
- validFrom / validUntil: datetime (promos limitées)
- isFeatured: boolean
- items: OneToMany → PackageItem

Cas d'usage:
├── VTT Marlin 7 + casque + chambre secours → -15€
├── Gravel + bidon + porte-bagage → -25€
└── E-Bike Bosch + batterie rechange → -200€
```

### 14. **PackageItem** - Éléments d'un package

```
- package: FK Package
- variant: FK BikeVariant
- quantity: int
- priceOverride: decimal (prix spécial du package)
- position: int (ordre d'affichage)
```

### 15. **Review** - Avis clients

```
- bike: FK Bike
- authorName: string
- authorEmail: string
- rating: int (1-5)
- title: string
- content: text
- isApproved: boolean (modération)
- approvedAt: datetime
- approve(): void (helper)

Méthodes utiles:
├── getAverageRatingByBike() → Moyenne des notes
└── getReviewCountByBike() → Nombre d'avis
```

## 🔑 Relations clés

### Hiérarchie produit

```
Category
  ↓
Bike (N bikes par catégorie)
  ↓
BikeVariant (N variantes par bike : couleurs, tailles)
  ↓
BikePrice + Stock (N prix/stocks par variante)
```

### Exemple concret : Trek Marlin 7

```
Category: VTT
  └── Bike: Trek Marlin 7
      ├── BikeVariant #1: Noir, Taille M, Mécanique, 899€
      │   ├── BikePrice: Amateur 899€ / Pro 799€
      │   └── Stock: Dépôt A (5), Dépôt B (3)
      ├── BikeVariant #2: Noir, Taille L, Mécanique, 909€
      │   ├── BikePrice: Amateur 909€ / Pro 809€
      │   └── Stock: Dépôt A (2), Dépôt B (8)
      └── BikeVariant #3: Bleu, Taille M, Électrique Bosch, 1899€
          ├── BikePrice: Amateur 1899€ / Pro 1599€ / Entreprise 1400€
          └── Stock: Dépôt A (1), Dépôt B (1)
```

## 🔄 Flux de données

### Créer un produit complet

```
1. Créer Category (VTT)
2. Créer Brand (Trek)
3. Créer Bike (Trek Marlin 7)
4. Créer 3 x BikeVariant (noir M/L/L electrique)
5. Pour chaque BikeVariant:
   - Ajouter BikeSpecification (cadre, fourche, etc.)
   - Ajouter BikePrice pour chaque CustomerSegment
   - Ajouter Stock par dépôt
6. Ajouter BikeImage (photos)
7. Ajouter BikeFeature (équipements : freins, dérailleur...)
```

### Créer un package

```
1. Créer Package ("VTT Marlin + Équipement")
2. Ajouter PackageItem #1: BikeVariant (Trek Marlin 7)
3. Ajouter PackageItem #2: BikeVariant (Casque)
4. Ajouter PackageItem #3: BikeVariant (Chambre de secours)
5. Fixer totalPriceHT et packageDiscount
```

## 🔍 Méthodes de recherche utiles (repositories)

### BikeRepository

```
findActive()              // Vélos actifs
findBySlug($slug)         // Détail produit
findByCategory($cat)      // Listing par catégorie
findFeatured()            // Vélos mis en avant
findElectric()            // Filtrer VAE
findForSegment($level)    // Ciblage commercial (pro/amateur)
search($query)            // Recherche texte
```

### BikeVariantRepository

```
findActive()              // Variantes disponibles
findInStock()             // Stock > 0
findElectric()            // Variantes VAE
findByColor/Size/Condition()
findLowWeight()           // < 13kg
```

### BikePriceRepository

```
findCurrentPrices()       // Respectant validFrom/Until
findByVariantAndSegment() // Prix spécifique
findHighMarginPrices()    // Analyse commerciale
```

### StockRepository

```
findLowStock()            // Alerte réappro
findOutOfStock()          // Ruptures de stock
findByWarehouse()         // Inventaire par dépôt
getTotalStock()           // Stock total tous dépôts
```

### BikeCompatibilityRepository

```
findCompatibleBikes()     // Achats complémentaires
findRecommendedUpgrades() // Upsell suggestions
findSimilarBikes()        // Cross-sell
```

## 📈 Cas d'usage commerciaux

### 1. Vendre 40% de VAE

```
Motor entity → Stocker Bosch 250W, 500W, 750W, Shimano Steps
BikeVariant.motor = null (mécanique) vs Motor (électrique)
BikeVariant.isElectric() → Identifier VAE
```

### 2. Tarifs différenciés par profil client

```
Amateur veut le meilleur prix
Pro/Entreprise ont des tarifs et volumes spécifiques
BikePrice.segment → Afficher le bon prix selon client
BikePrice.marginRate → Analyser profitabilité par segment
```

### 3. Promotions temporaires

```
BikePrice.validFrom/Until → Gérer soldes
PackageDiscount → Offres groupées limitées
```

### 4. Multi-localisation

```
Stock.warehouse → Gérer plusieurs dépôts
StockRepository.getTotalStock() → Stock global
StockRepository.findByWarehouse() → Disponibilité locale
```

### 5. Recommandations de vente

```
BikeCompatibility → Montrer upgrade (Marlin 5→7)
RelatedProducts → Casques, pneus, selles (BikeFeature)
RecentReviews → Avis positifs pour influencer
```

## 🛠️ À implémenter (bonus)

1. **Services métier**

```
BikeVariantCalculator → Calculer prix TTC, marge
StockManager → Réserver stock, alerte réappro
RecommendationEngine → Suggestion produits
```

2. **Admin CRUD**

```
bin/console make:admin (EasyAdmin)
Gestion complète des entités
```

3. **API REST**

```
bin/console make:controller (API Platform)
Endpoints: GET /bikes, /bikes/{id}, /variantes
```

4. **Frontend**

```
Listing/détail produits
Filtrage (catégorie, prix, électrique)
Avis clients
Compatibilité/recommendations
```

## 📋 Migration créée

```
Version20260311112616.php
```

Contient 36 requêtes SQL créant:

- 15 tables principales
- 2 tables de relations many-to-many (bikes_equipments, bike_prices FK)
- Indexes pour performances
- Contraintes uniques

## 🚀 Prochaines étapes

1. Générer Admin Symfony (EasyAdmin)
2. Créer quelques fixtures (sample data)
3. Développer les controllers/API
4. Frontend avancé (filtres, recherche)
5. Ajouter un panier e-commerce
