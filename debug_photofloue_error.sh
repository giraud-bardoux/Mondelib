#!/bin/bash

echo "🔍 DIAGNOSTIC ERREUR PHOTOFLOUE - SocialEngine 7.4"
echo "=================================================="
echo "Erreurs détectées: 0242bf, fedbff"
echo "Date: $(date)"
echo ""

echo "📋 1. VÉRIFICATION DE LA STRUCTURE DU MODULE"
echo "--------------------------------------------"

# Vérifier si le module existe
if [ -d "application/modules/PhotoFloue" ]; then
    echo "✅ Module PhotoFloue trouvé"
    
    # Vérifier les fichiers essentiels
    essential_files=(
        "application/modules/PhotoFloue/settings/manifest.php"
        "application/modules/PhotoFloue/settings/install.php"
        "application/modules/PhotoFloue/Bootstrap.php"
        "application/modules/PhotoFloue/Plugin/Core.php"
    )
    
    for file in "${essential_files[@]}"; do
        if [ -f "$file" ]; then
            echo "✅ $file"
        else
            echo "❌ $file MANQUANT"
        fi
    done
else
    echo "❌ Module PhotoFloue non trouvé"
    exit 1
fi

echo ""
echo "📋 2. VÉRIFICATION DES PERMISSIONS"
echo "---------------------------------"

# Vérifier les permissions
check_permissions() {
    local path=$1
    if [ -d "$path" ] || [ -f "$path" ]; then
        local perms=$(stat -c %a "$path" 2>/dev/null || stat -f %A "$path" 2>/dev/null)
        echo "📁 $path: $perms"
        
        if [ -d "$path" ]; then
            if [ ! -w "$path" ]; then
                echo "⚠️  $path n'est pas accessible en écriture"
            fi
        fi
    fi
}

check_permissions "application/modules/PhotoFloue"
check_permissions "application/modules/PhotoFloue/settings"
check_permissions "application/languages"
check_permissions "temporary"

echo ""
echo "📋 3. VÉRIFICATION DU MANIFEST"
echo "-----------------------------"

if [ -f "application/modules/PhotoFloue/settings/manifest.php" ]; then
    echo "🔍 Vérification de la syntaxe PHP du manifest..."
    
    # Vérifier la syntaxe PHP si disponible
    if command -v php >/dev/null 2>&1; then
        php -l application/modules/PhotoFloue/settings/manifest.php
        if [ $? -eq 0 ]; then
            echo "✅ Syntaxe PHP du manifest correcte"
        else
            echo "❌ Erreur de syntaxe dans le manifest"
        fi
    else
        echo "⚠️  PHP CLI non disponible pour vérifier la syntaxe"
    fi
    
    # Vérifier le contenu du manifest
    echo ""
    echo "📄 Contenu clé du manifest:"
    grep -E "(name|version|title)" application/modules/PhotoFloue/settings/manifest.php | head -5
fi

echo ""
echo "📋 4. VÉRIFICATION DE LA BASE DE DONNÉES"
echo "---------------------------------------"

if [ -f "application/settings/database.php" ]; then
    echo "✅ Fichier de configuration database.php trouvé"
else
    echo "❌ Fichier database.php manquant"
fi

echo ""
echo "📋 5. ANALYSE DES LOGS D'ERREUR"
echo "------------------------------"

# Chercher les logs récents
log_dirs=(
    "temporary/log"
    "temporary/logs"
    "var/log"
    "logs"
)

for log_dir in "${log_dirs[@]}"; do
    if [ -d "$log_dir" ]; then
        echo "📁 Logs trouvés dans: $log_dir"
        
        # Chercher les erreurs récentes liées à PhotoFloue
        find "$log_dir" -name "*.log" -mtime -1 -exec grep -l -i "photofloue\|0242bf\|fedbff" {} \; 2>/dev/null | head -3
    fi
done

echo ""
echo "📋 6. VÉRIFICATION DES CONFLITS DE MODULES"
echo "-----------------------------------------"

# Chercher d'anciens fichiers PhotoBlur
if [ -d "application/modules/PhotoBlur" ]; then
    echo "⚠️  CONFLIT: Ancien module PhotoBlur encore présent!"
    echo "   Supprimez-le avec: rm -rf application/modules/PhotoBlur"
fi

# Vérifier les anciens fichiers de traduction
if [ -f "application/languages/fr/photoblur.csv" ]; then
    echo "⚠️  Ancien fichier photoblur.csv trouvé"
fi

echo ""
echo "📋 7. RECOMMANDATIONS DE RÉSOLUTION"
echo "====================================="

echo ""
echo "🔧 SOLUTION 1: Nettoyage complet"
echo "rm -rf application/modules/PhotoBlur"
echo "rm -f application/languages/*/photoblur.csv"

echo ""
echo "🔧 SOLUTION 2: Permissions"
echo "chmod -R 755 application/modules/PhotoFloue"
echo "chmod -R 777 temporary"

echo ""
echo "🔧 SOLUTION 3: Vider le cache"
echo "rm -rf temporary/cache/*"
echo "rm -rf temporary/compile/*"

echo ""
echo "🔧 SOLUTION 4: Réinstallation propre"
echo "1. Désactiver le module PhotoFloue"
echo "2. Supprimer les paramètres DB:"
echo "   DELETE FROM engine4_core_settings WHERE name LIKE 'photofloue.%';"
echo "3. Réactiver le module"

echo ""
echo "⚠️  Si l'erreur persiste, essayez l'installation manuelle des paramètres."

echo ""
echo "📞 Support disponible via les logs SocialEngine"
echo "Fin du diagnostic - $(date)"