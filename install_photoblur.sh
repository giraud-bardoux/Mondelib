#!/bin/bash

# Script d'installation du module PhotoBlur pour SocialEngine 7.4
# Utilisation : ./install_photoblur.sh

echo "=== Installation du module PhotoBlur pour SocialEngine 7.4 ==="
echo ""

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "index.php" ] || [ ! -d "application/modules" ]; then
    echo "❌ Erreur : Ce script doit être exécuté depuis la racine de votre installation SocialEngine"
    echo "   Assurez-vous d'être dans le répertoire contenant index.php et application/"
    exit 1
fi

echo "✅ Répertoire SocialEngine détecté"

# Vérifier que le module existe
if [ ! -d "application/modules/PhotoBlur" ]; then
    echo "❌ Erreur : Le module PhotoBlur n'a pas été trouvé dans application/modules/"
    echo "   Assurez-vous d'avoir copié le dossier PhotoBlur dans application/modules/"
    exit 1
fi

echo "✅ Module PhotoBlur trouvé"

# Vérifier les permissions
echo "🔍 Vérification des permissions..."

# Vérifier les permissions d'écriture
if [ ! -w "application/modules" ]; then
    echo "⚠️  Attention : Permissions d'écriture insuffisantes sur application/modules"
    echo "   Vous devrez peut-être ajuster les permissions manuellement"
fi

# Vérifier la structure des fichiers
echo "🔍 Vérification de la structure du module..."

files=(
    "application/modules/PhotoBlur/Bootstrap.php"
    "application/modules/PhotoBlur/Plugin/Core.php"
    "application/modules/PhotoBlur/View/Helper/ItemBackgroundPhoto.php"
    "application/modules/PhotoBlur/externals/scripts/photoblur.js"
    "application/modules/PhotoBlur/externals/styles/photoblur.css"
    "application/modules/PhotoBlur/settings/manifest.php"
    "application/modules/PhotoBlur/settings/install.php"
)

missing_files=0
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file (manquant)"
        missing_files=$((missing_files + 1))
    fi
done

if [ $missing_files -gt 0 ]; then
    echo ""
    echo "❌ $missing_files fichier(s) manquant(s). Installation incomplète."
    exit 1
fi

# Vérifier les fichiers de traduction
echo ""
echo "🔍 Vérification des traductions..."

if [ -f "application/languages/fr/photoblur.csv" ]; then
    echo "  ✅ Traduction française"
else
    echo "  ⚠️  Traduction française manquante"
fi

if [ -f "application/languages/en/photoblur.csv" ]; then
    echo "  ✅ Traduction anglaise"
else
    echo "  ⚠️  Traduction anglaise manquante"
fi

# Test de syntaxe PHP (si PHP est disponible)
echo ""
echo "🔍 Vérification de la syntaxe PHP..."

if command -v php > /dev/null 2>&1; then
    php_files=(
        "application/modules/PhotoBlur/Bootstrap.php"
        "application/modules/PhotoBlur/Plugin/Core.php"
        "application/modules/PhotoBlur/View/Helper/ItemBackgroundPhoto.php"
        "application/modules/PhotoBlur/settings/manifest.php"
        "application/modules/PhotoBlur/settings/install.php"
    )

    syntax_errors=0
    for file in "${php_files[@]}"; do
        if php -l "$file" > /dev/null 2>&1; then
            echo "  ✅ $file"
        else
            echo "  ❌ $file (erreur de syntaxe)"
            syntax_errors=$((syntax_errors + 1))
        fi
    done

    if [ $syntax_errors -gt 0 ]; then
        echo ""
        echo "❌ $syntax_errors erreur(s) de syntaxe PHP détectée(s)."
        echo "   Veuillez corriger ces erreurs avant d'installer le module."
        exit 1
    fi
else
    echo "  ⚠️  PHP non trouvé - vérification de syntaxe ignorée"
    echo "     Assurez-vous que PHP est installé sur votre serveur de production"
fi

# Instructions finales
echo ""
echo "✅ Vérifications terminées avec succès !"
echo ""
echo "=== ÉTAPES SUIVANTES ==="
echo ""
echo "1. 🌐 Connectez-vous à votre panneau d'administration SocialEngine"
echo "2. 📦 Allez dans 'Manage' → 'Packages'"
echo "3. 🔍 Trouvez 'PhotoBlur' dans la liste des modules"
echo "4. ⚡ Cliquez sur 'Install' puis 'Enable'"
echo ""
echo "=== FONCTIONNALITÉS ACTIVÉES ==="
echo ""
echo "✨ Floutage automatique des photos pour les visiteurs non connectés"
echo "🛡️  Protection contre la sauvegarde d'images"
echo "📱 Protection mobile contre les appuis longs"
echo "💬 Message d'incitation à la connexion"
echo "🌍 Support multilingue (français/anglais)"
echo ""
echo "=== TEST DU MODULE ==="
echo ""
echo "Pour tester le module après installation :"
echo "  php application/modules/PhotoBlur/test_module.php"
echo ""
echo "=== SUPPORT ==="
echo ""
echo "📖 Documentation complète : application/modules/PhotoBlur/README.md"
echo "⚙️  Personnalisation des styles : application/modules/PhotoBlur/externals/styles/photoblur.css"
echo "🔧 Configuration avancée : application/modules/PhotoBlur/externals/scripts/photoblur.js"
echo ""
echo "🎉 Installation préparée avec succès ! Rendez-vous dans votre panneau d'administration."