<?php

namespace App\DataFixtures;

use App\Entity\{
    Category,
    Brand,
    Motor,
    FeatureCategory,
    BikeFeature,
    Bike,
    BikeVariant,
    BikeSpecification,
    BikePrice,
    Stock,
    BikeImage,
    CustomerSegment,
    Review,
    BikeCompatibility
};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BicycleFixtures extends Fixture
{
    /**
     * Exemple de données pour tester l'architecture
     * 
     * Utiliser: php bin/console doctrine:fixtures:load
     */
    public function load(ObjectManager $manager): void
    {
        // ==================== CATEGOIRES ====================
        $categoryVTT = (new Category())
            ->setName('VTT')
            ->setSlug('vtt')
            ->setDescription('Vélos tout terrain pour la montagne')
            ->setIcon('🚵');
        $manager->persist($categoryVTT);

        $categoryRoute = (new Category())
            ->setName('Route')
            ->setSlug('route')
            ->setDescription('Vélos de route rapides et légers')
            ->setIcon('🚴');
        $manager->persist($categoryRoute);

        // ==================== MARQUES ====================
        $brandTrek = (new Brand())
            ->setName('Trek')
            ->setSlug('trek')
            ->setCountry('USA')
            ->setLogoUrl('trek-logo.png');
        $manager->persist($brandTrek);

        // ==================== MOTEURS VAE ====================
        $motorBosch = (new Motor())
            ->setName('Bosch Performance Line')
            ->setBrand($brandTrek)
            ->setWattage(250)
            ->setTorque(65)
            ->setBatteryCapacity(625)
            ->setEstimatedRange(120)
            ->setDescription('Moteur robuste Bosch pour VTT/Gravel');
        $manager->persist($motorBosch);

        // ==================== SEGMENTS CLIENTS ====================
        $segmentAmateur = (new CustomerSegment())
            ->setName('Amateur')
            ->setDescription('Cyclistes occasionnels')
            ->setDiscountRate('0');
        $manager->persist($segmentAmateur);

        $segmentPro = (new CustomerSegment())
            ->setName('Professionnel')
            ->setDescription('Compétiteurs, clubs, instructeurs')
            ->setDiscountRate('15');
        $manager->persist($segmentPro);

        $segmentEntreprise = (new CustomerSegment())
            ->setName('Entreprise Vélib')
            ->setDescription('Vélo-partage, flottes d\'entreprise')
            ->setDiscountRate('35');
        $manager->persist($segmentEntreprise);

        // ==================== CATEGORIES EQUIPEMENTS ====================
        $featureCategoryBrakes = (new FeatureCategory())
            ->setName('Freins')
            ->setDescription('Systèmes de freinage');
        $manager->persist($featureCategoryBrakes);

        $featureCategoryDerailleur = (new FeatureCategory())
            ->setName('Dérailleur')
            ->setDescription('Systèmes de changement de vitesses');
        $manager->persist($featureCategoryDerailleur);

        // ==================== EQUIPEMENTS ====================
        $featureShimanoDeore = (new BikeFeature())
            ->setName('Shimano Deore XT')
            ->setCategory($featureCategoryDerailleur)
            ->setSpecification('3x12 speed');
        $manager->persist($featureShimanoDeore);

        $featureHydraulicBrakes = (new BikeFeature())
            ->setName('Freins à disque hydrauliques')
            ->setCategory($featureCategoryBrakes)
            ->setSpecification('Shimano Hydraulic');
        $manager->persist($featureHydraulicBrakes);

        // ==================== VELOS ====================
        $bikeTrekMarlin7 = (new Bike())
            ->setName('Trek Marlin 7')
            ->setSlug('trek-marlin-7')
            ->setCategory($categoryVTT)
            ->setBrand($brandTrek)
            ->setDescription('Excellent VTT d\'entrée/intermédiaire avec géométrie moderne')
            ->setModelYear(2024)
            ->setSegmentLevel('semi-pro')
            ->addBikeFeature($featureShimanoDeore)
            ->addBikeFeature($featureHydraulicBrakes);
        $manager->persist($bikeTrekMarlin7);

        // ==================== VARIANTES ====================
        // Variante 1: Mécanique, Taille M, Noir
        $variantMarlinM = (new BikeVariant())
            ->setBike($bikeTrekMarlin7)
            ->setColor('Noir')
            ->setSize('M')
            ->setBasePrice('899.00')
            ->setWeight(13200) // 13.2 kg
            ->setBikeCondition('new');
        $manager->persist($variantMarlinM);

        // Ajouter spécifications
        (new BikeSpecification())
            ->setVariant($variantMarlinM)
            ->setName('Cadre')
            ->setValue('Aluminium Alpha 6061')
            ->setUnit(null)
            ->setPosition(1);
        $manager->persist($variantMarlinM);

        (new BikeSpecification())
            ->setVariant($variantMarlinM)
            ->setName('Fourche')
            ->setValue('SR Suntour XCM 100mm Travel')
            ->setUnit('mm')
            ->setPosition(2);
        $manager->persist($variantMarlinM);

        // Tarifications par segment
        (new BikePrice())
            ->setVariant($variantMarlinM)
            ->setSegment($segmentAmateur)
            ->setPriceHT('899.00')
            ->setPriceTTC('1078.80') // 20% TVA
            ->setMarginRate('35');
        $manager->persist($variantMarlinM);

        (new BikePrice())
            ->setVariant($variantMarlinM)
            ->setSegment($segmentPro)
            ->setPriceHT('764.00') // -15%
            ->setPriceTTC('916.80')
            ->setMarginRate('35');
        $manager->persist($variantMarlinM);

        (new BikePrice())
            ->setVariant($variantMarlinM)
            ->setSegment($segmentEntreprise)
            ->setPriceHT('584.00') // -35%
            ->setPriceTTC('700.80')
            ->setMarginRate('25');
        $manager->persist($variantMarlinM);

        // Stock
        (new Stock())
            ->setVariant($variantMarlinM)
            ->setWarehouse('Dépôt principal')
            ->setQuantity(15)
            ->setReorderLevel(5);
        $manager->persist($variantMarlinM);

        // Variante 2: Électrique Bosch, Taille M, Bleu
        $variantMarlinMElectric = (new BikeVariant())
            ->setBike($bikeTrekMarlin7)
            ->setColor('Bleu')
            ->setSize('M')
            ->setMotor($motorBosch)
            ->setBasePrice('2199.00')
            ->setWeight(24500) // 24.5 kg (batterie)
            ->setBikeCondition('new');
        $manager->persist($variantMarlinMElectric);

        (new BikePrice())
            ->setVariant($variantMarlinMElectric)
            ->setSegment($segmentAmateur)
            ->setPriceHT('2199.00')
            ->setPriceTTC('2638.80')
            ->setMarginRate('28');
        $manager->persist($variantMarlinMElectric);

        (new BikePrice())
            ->setVariant($variantMarlinMElectric)
            ->setSegment($segmentEntreprise)
            ->setPriceHT('1429.00') // -35%
            ->setPriceTTC('1714.80')
            ->setMarginRate('18');
        $manager->persist($variantMarlinMElectric);

        (new Stock())
            ->setVariant($variantMarlinMElectric)
            ->setWarehouse('Dépôt principal')
            ->setQuantity(5)
            ->setReorderLevel(3);
        $manager->persist($variantMarlinMElectric);

        // ==================== IMAGES ====================
        (new BikeImage())
            ->setBike($bikeTrekMarlin7)
            ->setFilename('trek-marlin-7-primary.jpg')
            ->setAltText('Trek Marlin 7 noir')
            ->setType('primary')
            ->setPosition(1);
        $manager->persist($bikeTrekMarlin7);

        // ==================== AVIS ====================
        (new Review())
            ->setBike($bikeTrekMarlin7)
            ->setAuthorName('Jean D.')
            ->setAuthorEmail('jean@example.com')
            ->setRating(5)
            ->setTitle('Excellent rapport qualité-prix')
            ->setContent('Vélo très bien conçu, parfait pour débuter en VTT. Les freins hydrauliques font vraiment la différence.')
            ->setIsApproved(true)
            ->setApprovedAt(new \DateTimeImmutable('-1 day'));
        $manager->persist($bikeTrekMarlin7);

        // ==================== COMPATIBILITES ====================
        $bikeTrekMarlin5 = (new Bike())
            ->setName('Trek Marlin 5')
            ->setSlug('trek-marlin-5')
            ->setCategory($categoryVTT)
            ->setBrand($brandTrek)
            ->setDescription('VTT d\'entrée de gamme')
            ->setModelYear(2024);
        $manager->persist($bikeTrekMarlin5);

        (new BikeCompatibility())
            ->setBikeFrom($bikeTrekMarlin5)
            ->setBikeTo($bikeTrekMarlin7)
            ->setType('upgrade')
            ->setReason('Version supérieure avec meilleurs composants');
        $manager->persist($bikeTrekMarlin5);

        // ==================== FLUSH ====================
        $manager->flush();

        echo "\n✅ Fixtures chargées avec succès!\n";
        echo "- 2 Catégories\n";
        echo "- 1 Marque\n";
        echo "- 1 Moteur VAE\n";
        echo "- 3 Segments clients\n";
        echo "- 2 Vélos avec 3 variantes\n";
        echo "- 6 Tarifications différentes\n";
        echo "- Spécifications, images, avis\n";
    }
}
