#!/bin/bash

echo "🚀 INSTALLATION AUTOMATIQUE - Module PhotoFloue v1.0.1"
echo "========================================================"

# Vérifier qu'on est dans le bon répertoire
if [ ! -f "application/settings/database.php" ]; then
    echo "❌ ERREUR: Vous devez être dans le répertoire racine de SocialEngine"
    echo "Utilisez: cd ~/preprodml/ puis relancez ce script"
    exit 1
fi

echo "✅ Répertoire SocialEngine détecté"

# Télécharger le module
echo "📦 Téléchargement du module..."
wget -q https://github.com/giraud-bardoux/Mondelib/raw/photoblur/releases/module-PhotoFloue-1.01-FINAL.tar.gz

if [ $? -eq 0 ]; then
    echo "✅ Module téléchargé"
else
    echo "❌ Erreur de téléchargement"
    exit 1
fi

# Décompresser
echo "📂 Décompression..."
tar -xzf module-PhotoFloue-1.01-FINAL.tar.gz

# Installer
echo "🔧 Installation des fichiers..."
cp -r module-PhotoFloue-1.01/PhotoFloue application/modules/

# Traductions
echo "🌍 Installation des traductions..."
mkdir -p application/languages/fr
mkdir -p application/languages/en
cp module-PhotoFloue-1.01/languages/photofloue-fr.csv application/languages/fr/photofloue.csv
cp module-PhotoFloue-1.01/languages/photofloue-en.csv application/languages/en/photofloue.csv

# Permissions
echo "🔐 Ajustement des permissions..."
chmod -R 755 application/modules/PhotoFloue

# Test
echo "🧪 Test de validation..."
if [ -f "module-PhotoFloue-1.01/test_photofloue_simple.php" ]; then
    php module-PhotoFloue-1.01/test_photofloue_simple.php
else
    echo "⚠️ Script de test non trouvé, mais installation OK"
fi

# Nettoyage
echo "🧹 Nettoyage..."
rm -f module-PhotoFloue-1.01-FINAL.tar.gz
rm -rf module-PhotoFloue-1.01/

echo ""
echo "🎉 INSTALLATION TERMINÉE !"
echo ""
echo "📋 PROCHAINES ÉTAPES :"
echo "1. Aller dans Admin Panel > Packages"
echo "2. Chercher 'PhotoFloue Module v1.0.1'"
echo "3. Cliquer 'Install' puis 'Enable'"
echo "4. Tester en mode visiteur non connecté"
echo ""
echo "✅ Module prêt à être activé via l'interface d'administration"