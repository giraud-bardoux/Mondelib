#!/bin/bash

# Script de diagnostic pour erreur PhotoBlur 078feb
echo "🚨 Diagnostic d'erreur SocialEngine - Code 078feb"
echo "=================================================="
echo ""

# Variables
ERROR_CODE="078feb"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_DIR="debug_logs_$TIMESTAMP"

echo "📊 Création du dossier de diagnostic : $LOG_DIR"
mkdir -p "$LOG_DIR"

echo ""
echo "🔍 ÉTAPE 1 : Vérification des logs d'erreurs"
echo "============================================"

# Vérifier les logs PHP
if [ -f "/var/log/apache2/error.log" ]; then
    echo "📋 Extraction des erreurs PHP récentes..."
    tail -50 /var/log/apache2/error.log | grep -i "photoblur\|fatal\|error" > "$LOG_DIR/apache_errors.txt"
    echo "✅ Logs Apache sauvés dans $LOG_DIR/apache_errors.txt"
elif [ -f "/var/log/httpd/error_log" ]; then
    echo "📋 Extraction des erreurs PHP récentes..."
    tail -50 /var/log/httpd/error_log | grep -i "photoblur\|fatal\|error" > "$LOG_DIR/httpd_errors.txt"
    echo "✅ Logs HTTPD sauvés dans $LOG_DIR/httpd_errors.txt"
else
    echo "⚠️  Logs serveur non trouvés. Vérifiez via cPanel ou panneau d'hébergement"
fi

# Vérifier les logs SocialEngine
if [ -d "application/temporary/log" ]; then
    echo "📋 Vérification des logs SocialEngine..."
    find application/temporary/log -name "*.log" -mtime -1 -exec tail -20 {} \; > "$LOG_DIR/socialengine_logs.txt"
    echo "✅ Logs SocialEngine sauvés dans $LOG_DIR/socialengine_logs.txt"
fi

echo ""
echo "🔍 ÉTAPE 2 : Vérification de la structure du module"
echo "=================================================="

# Vérifier que le module existe
if [ -d "application/modules/PhotoBlur" ]; then
    echo "✅ Module PhotoBlur présent"
    
    # Vérifier les fichiers critiques
    critical_files=(
        "application/modules/PhotoBlur/Bootstrap.php"
        "application/modules/PhotoBlur/Plugin/Core.php"
        "application/modules/PhotoBlur/settings/manifest.php"
        "application/modules/PhotoBlur/settings/install.php"
    )
    
    echo "📋 Vérification des fichiers critiques..."
    for file in "${critical_files[@]}"; do
        if [ -f "$file" ]; then
            echo "  ✅ $file"
        else
            echo "  ❌ $file MANQUANT"
        fi
    done
else
    echo "❌ Module PhotoBlur non trouvé dans application/modules/"
fi

echo ""
echo "🔍 ÉTAPE 3 : Vérification des permissions"
echo "========================================"

if [ -d "application/modules/PhotoBlur" ]; then
    echo "📋 Permissions du module PhotoBlur..."
    ls -la application/modules/PhotoBlur/ > "$LOG_DIR/permissions.txt"
    
    # Vérifier les permissions critiques
    if [ -r "application/modules/PhotoBlur/Bootstrap.php" ]; then
        echo "✅ Bootstrap.php lisible"
    else
        echo "❌ Bootstrap.php non lisible"
    fi
    
    if [ -r "application/modules/PhotoBlur/settings/manifest.php" ]; then
        echo "✅ manifest.php lisible"
    else
        echo "❌ manifest.php non lisible"
    fi
fi

echo ""
echo "🔍 ÉTAPE 4 : Test de syntaxe PHP"
echo "================================"

if command -v php > /dev/null 2>&1; then
    echo "📋 Vérification de la syntaxe PHP..."
    
    php_files=(
        "application/modules/PhotoBlur/Bootstrap.php"
        "application/modules/PhotoBlur/Plugin/Core.php"
        "application/modules/PhotoBlur/settings/manifest.php"
        "application/modules/PhotoBlur/settings/install.php"
    )
    
    syntax_ok=true
    for file in "${php_files[@]}"; do
        if [ -f "$file" ]; then
            if php -l "$file" > /dev/null 2>&1; then
                echo "  ✅ $file"
            else
                echo "  ❌ $file (erreur de syntaxe)"
                php -l "$file" 2>&1 >> "$LOG_DIR/syntax_errors.txt"
                syntax_ok=false
            fi
        fi
    done
    
    if [ "$syntax_ok" = false ]; then
        echo "❌ Erreurs de syntaxe détectées - voir $LOG_DIR/syntax_errors.txt"
    fi
else
    echo "⚠️  PHP CLI non disponible pour test syntaxe"
fi

echo ""
echo "🔍 ÉTAPE 5 : Vérification de la base de données"
echo "=============================================="

echo "📋 Vérification manuelle requise :"
echo "1. Connectez-vous à votre base de données (phpMyAdmin/cPanel)"
echo "2. Vérifiez la table 'engine4_core_settings'"
echo "3. Recherchez des entrées 'photoblur.%'"
echo "4. Vérifiez la table 'engine4_core_modules' pour PhotoBlur"

echo ""
echo "🔍 ÉTAPE 6 : Solutions de récupération"
echo "===================================="

cat > "$LOG_DIR/solutions_recovery.md" << 'EOF'
# Solutions pour erreur 078feb

## Solution 1 : Rollback complet
```bash
# Désactiver le module via base de données
UPDATE engine4_core_modules SET enabled = 0 WHERE name = 'photoblur';

# Supprimer les settings
DELETE FROM engine4_core_settings WHERE name LIKE 'photoblur.%';

# Supprimer les fichiers
rm -rf application/modules/PhotoBlur/
```

## Solution 2 : Réinstallation propre
```bash
# 1. Nettoyer complètement
rm -rf application/modules/PhotoBlur/

# 2. Vider le cache
rm -rf application/temporary/cache/*

# 3. Remettre le module
# Copiez à nouveau le dossier PhotoBlur

# 4. Corriger les permissions
chmod -R 755 application/modules/PhotoBlur/
```

## Solution 3 : Debug mode
```php
// Dans application/settings/database.php
// Temporairement activer le debug
$config = array(
    'params' => array(
        'profiler' => array(
            'enabled' => true,
        )
    )
);
```

## Solution 4 : Vérification des dépendances
Assurez-vous que ces modules sont activés :
- Core (version 7.4+)
- User 
- Storage

EOF

echo "✅ Solutions sauvées dans $LOG_DIR/solutions_recovery.md"

echo ""
echo "📋 RAPPORT DE DIAGNOSTIC"
echo "======================="
echo "Erreur: $ERROR_CODE"
echo "Timestamp: $TIMESTAMP"
echo "Logs sauvés dans: $LOG_DIR/"
echo ""
echo "🚀 ACTIONS RECOMMANDÉES (dans l'ordre) :"
echo "1. Consultez les logs dans $LOG_DIR/"
echo "2. Appliquez la Solution 1 pour rollback"
echo "3. Si nécessaire, appliquez la Solution 2 pour réinstallation"
echo "4. Contactez support si problème persiste"
echo ""
echo "📞 Pour support immédiat :"
echo "- Envoyez le contenu de $LOG_DIR/"
echo "- Indiquez votre version SocialEngine"
echo "- Précisez à quelle étape l'erreur est survenue"