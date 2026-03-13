#!/bin/bash

# Script de vérification complète du projet

echo "======================================"
echo "🔍 VÉRIFICATIONS COMPLÈTES DU PROJET"
echo "======================================"
echo ""

echo "📊 PHPStan - Analyse statique"
php vendor/bin/phpstan analyse --no-progress 2>&1
PHPSTAN_EXIT=$?

echo ""
echo "🗄️  Doctrine - Validation du schéma"
php bin/console doctrine:schema:validate 2>&1
DOCTRINE_EXIT=$?

echo ""
echo "📋 Doctrine - Liste des entités"
php bin/console doctrine:orm:info 2>&1 | head -30

echo ""
echo "🚀 Sintaxe PHP"
php -l src/Entity/Bike.php > /dev/null 2>&1 && echo "✅ Bike.php: OK" || echo "❌ Bike.php: ERREUR"
php -l src/Entity/BikeVariant.php > /dev/null 2>&1 && echo "✅ BikeVariant.php: OK" || echo "❌ BikeVariant.php: ERREUR"
php -l src/DataFixtures/BicycleFixtures.php > /dev/null 2>&1 && echo "✅ BicycleFixtures.php: OK" || echo "❌ BicycleFixtures.php: ERREUR"

echo ""
echo "======================================"
if [ $PHPSTAN_EXIT -eq 0 ] && [ $DOCTRINE_EXIT -eq 0 ]; then
    echo "✅ TOUS LES CONTRÔLES SONT PASSÉS"
else
    echo "⚠️  VÉRIFIER LES ERREURS CI-DESSUS"
fi
echo "======================================"
