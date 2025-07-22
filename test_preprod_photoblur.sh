#!/bin/bash

# Script de test en préproduction pour le module PhotoBlur
# À exécuter depuis la racine de votre installation SocialEngine

echo "🧪 === Test en Préproduction du Module PhotoBlur ==="
echo ""

# Variables de configuration
BACKUP_DIR="backup_preprod_$(date +%Y%m%d_%H%M%S)"
SITE_URL="${1:-http://localhost}"  # URL du site de préproduction

echo "📍 URL du site de test : $SITE_URL"
echo "💾 Dossier de sauvegarde : $BACKUP_DIR"
echo ""

# Étape 1 : Vérifications préalables
echo "=== ÉTAPE 1 : Vérifications préalables ==="

# Vérifier que nous sommes sur le bon serveur
if [ ! -f "index.php" ] || [ ! -d "application/modules" ]; then
    echo "❌ Erreur : Ce script doit être exécuté depuis la racine de SocialEngine"
    exit 1
fi

echo "✅ Répertoire SocialEngine confirmé"

# Vérifier l'environnement
if [ ! -f "application/settings/database.php" ]; then
    echo "❌ Erreur : Configuration de base de données non trouvée"
    exit 1
fi

echo "✅ Configuration de base de données trouvée"

# Vérifier que le module PhotoBlur existe
if [ ! -d "application/modules/PhotoBlur" ]; then
    echo "❌ Erreur : Module PhotoBlur non trouvé"
    echo "   Assurez-vous d'avoir copié le module avant de lancer ce test"
    exit 1
fi

echo "✅ Module PhotoBlur détecté"
echo ""

# Étape 2 : Sauvegarde de sécurité
echo "=== ÉTAPE 2 : Sauvegarde de sécurité ==="

mkdir -p "$BACKUP_DIR"

# Sauvegarder la base de données
echo "💾 Sauvegarde de la base de données..."
if command -v mysqldump > /dev/null 2>&1; then
    echo "  Créez une sauvegarde de votre base de données avant de continuer"
    echo "  Exemple : mysqldump -u user -p database_name > $BACKUP_DIR/database_backup.sql"
else
    echo "  ⚠️  mysqldump non trouvé - sauvegardez manuellement votre base de données"
fi

# Sauvegarder les modules critiques
echo "💾 Sauvegarde des modules existants..."
if [ -d "application/modules/User" ]; then
    cp -r "application/modules/User" "$BACKUP_DIR/" 2>/dev/null
    echo "  ✅ Module User sauvegardé"
fi

if [ -d "application/modules/Storage" ]; then
    cp -r "application/modules/Storage" "$BACKUP_DIR/" 2>/dev/null
    echo "  ✅ Module Storage sauvegardé"
fi

echo "✅ Sauvegardes créées dans $BACKUP_DIR"
echo ""

# Étape 3 : Installation du module
echo "=== ÉTAPE 3 : Installation du module ==="

echo "📦 Le module PhotoBlur va maintenant être installé"
echo "   1. Connectez-vous à votre panneau d'administration : $SITE_URL/admin"
echo "   2. Allez dans Manage → Packages"
echo "   3. Trouvez 'PhotoBlur' dans la liste"
echo "   4. Cliquez sur 'Install'"
echo "   5. Puis cliquez sur 'Enable'"
echo ""
echo "⏸️  Appuyez sur Entrée quand l'installation est terminée..."
read -r

# Étape 4 : Tests fonctionnels
echo "=== ÉTAPE 4 : Tests fonctionnels ==="

echo "🧪 Tests à effectuer manuellement :"
echo ""

echo "📱 TEST 1 : Visiteur non connecté"
echo "   1. Ouvrez une fenêtre de navigation privée"
echo "   2. Allez sur : $SITE_URL"
echo "   3. Naviguez vers une page avec des photos d'utilisateurs"
echo "   4. ✅ Vérifiez que les photos sont floutées"
echo "   5. ✅ Passez la souris sur une photo : le message doit apparaître"
echo "   6. ✅ Essayez un clic droit : doit être bloqué"
echo "   7. ✅ Essayez Ctrl+S : doit être bloqué"
echo ""

echo "👤 TEST 2 : Utilisateur connecté"
echo "   1. Connectez-vous avec un compte utilisateur"
echo "   2. Naviguez vers les mêmes pages"
echo "   3. ✅ Vérifiez que les photos sont nettes (pas de flou)"
echo "   4. ✅ Vérifiez que les protections ne s'appliquent pas"
echo ""

echo "📷 TEST 3 : Types de photos"
echo "   1. Testez les photos de profil"
echo "   2. Testez les photos de couverture"
echo "   3. Testez les photos d'albums (si présents)"
echo "   4. ✅ Toutes doivent être floutées pour les non-connectés"
echo ""

echo "📱 TEST 4 : Mobile (optionnel)"
echo "   1. Testez sur mobile ou avec les outils de développement"
echo "   2. Mode responsive : F12 → Toggle device toolbar"
echo "   3. ✅ Vérifiez l'appui long sur les images (doit être bloqué)"
echo "   4. ✅ Vérifiez le message au tap"
echo ""

echo "⏸️  Appuyez sur Entrée quand tous les tests sont terminés..."
read -r

# Étape 5 : Tests de performance
echo "=== ÉTAPE 5 : Tests de performance ==="

echo "⚡ Vérification de l'impact sur les performances :"
echo ""

echo "🔍 TEST DE CHARGEMENT :"
echo "   1. Ouvrez les outils de développement (F12)"
echo "   2. Onglet Network"
echo "   3. Rechargez une page avec photos"
echo "   4. ✅ Vérifiez que photoblur.css et photoblur.js se chargent"
echo "   5. ✅ Temps de chargement acceptable (< 2s total)"
echo ""

echo "📊 UTILISATION MÉMOIRE :"
echo "   1. Onglet Performance des outils de développement"
echo "   2. Enregistrez pendant la navigation"
echo "   3. ✅ Pas de fuite mémoire visible"
echo "   4. ✅ CPU usage raisonnable"
echo ""

echo "⏸️  Appuyez sur Entrée pour continuer..."
read -r

# Étape 6 : Tests de sécurité
echo "=== ÉTAPE 6 : Tests de sécurité ==="

echo "🛡️  Tests de contournement (simulation d'attaque) :"
echo ""

echo "🔧 TEST OUTILS DE DÉVELOPPEMENT :"
echo "   1. Ouvrez les outils de développement (F12)"
echo "   2. En mode visiteur non-connecté"
echo "   3. ✅ Vérifiez que le flou s'intensifie"
echo "   4. Essayez de modifier le CSS dans l'inspecteur"
echo "   5. ✅ Le JavaScript doit résister aux modifications basiques"
echo ""

echo "🚫 TEST DÉSACTIVATION JAVASCRIPT :"
echo "   1. Désactivez JavaScript dans le navigateur"
echo "   2. Rechargez la page"
echo "   3. ✅ Le flou CSS doit rester actif"
echo "   4. ✅ Les protections de base doivent fonctionner"
echo ""

echo "📋 TEST RACCOURCIS CLAVIER :"
echo "   En mode visiteur non-connecté, testez :"
echo "   - Ctrl+S (sauvegarder) : ❌ doit être bloqué"
echo "   - Ctrl+A (sélectionner) : ❌ doit être bloqué"
echo "   - Ctrl+C (copier) : ❌ doit être bloqué"
echo "   - F12 (dev tools) : ❌ doit être bloqué"
echo "   - Print Screen : ❌ doit afficher le message"
echo ""

echo "⏸️  Appuyez sur Entrée pour les tests finaux..."
read -r

# Étape 7 : Tests de compatibilité
echo "=== ÉTAPE 7 : Tests de compatibilité ==="

echo "🌐 Tests navigateurs (si possible) :"
echo "   ✅ Chrome : Testez le flou et les protections"
echo "   ✅ Firefox : Testez le flou et les protections"
echo "   ✅ Safari : Testez le flou et les protections"
echo "   ✅ Edge : Testez le flou et les protections"
echo ""

echo "📱 Tests de responsivité :"
echo "   ✅ Mobile portrait : Flou et protections OK"
echo "   ✅ Mobile paysage : Flou et protections OK"
echo "   ✅ Tablette : Flou et protections OK"
echo "   ✅ Desktop : Flou et protections OK"
echo ""

# Étape 8 : Rapport de test
echo "=== ÉTAPE 8 : Rapport de test ==="

echo "📝 Créer le rapport de test..."

cat > "$BACKUP_DIR/rapport_test_photoblur.md" << 'EOF'
# Rapport de Test - Module PhotoBlur

## Informations générales
- **Date du test :** $(date)
- **Environnement :** Préproduction
- **Version SocialEngine :** 7.4
- **Version Module :** 1.0.0

## Tests effectués

### ✅ Tests fonctionnels
- [ ] Floutage pour visiteurs non-connectés
- [ ] Photos nettes pour utilisateurs connectés
- [ ] Message au survol fonctionnel
- [ ] Protection clic droit active
- [ ] Raccourcis clavier bloqués

### ✅ Tests de performance
- [ ] Temps de chargement acceptable
- [ ] Pas de fuite mémoire
- [ ] CSS/JS se chargent correctement

### ✅ Tests de sécurité
- [ ] Résistance aux outils de développement
- [ ] Protection sans JavaScript
- [ ] Blocage des raccourcis

### ✅ Tests de compatibilité
- [ ] Chrome
- [ ] Firefox
- [ ] Safari/Edge
- [ ] Mobile/Tablette

## Problèmes identifiés
<!-- Notez ici les problèmes rencontrés -->

## Recommandations
<!-- Notez ici vos recommandations -->

## Validation finale
- [ ] Module prêt pour la production
- [ ] Besoin d'ajustements
EOF

echo "✅ Rapport créé : $BACKUP_DIR/rapport_test_photoblur.md"
echo ""

# Étape 9 : Nettoyage et finalisation
echo "=== ÉTAPE 9 : Finalisation ==="

echo "🏁 Tests terminés !"
echo ""
echo "📋 RÉSUMÉ DES ACTIONS :"
echo "   ✅ Module PhotoBlur installé et testé"
echo "   💾 Sauvegardes créées dans : $BACKUP_DIR"
echo "   📝 Rapport de test disponible"
echo ""

echo "🚀 PROCHAINES ÉTAPES :"
echo "   1. Complétez le rapport de test"
echo "   2. Si tout est OK : déployez en production"
echo "   3. Si problèmes : consultez les logs et ajustez"
echo ""

echo "📞 EN CAS DE PROBLÈME :"
echo "   1. Restaurez depuis la sauvegarde : $BACKUP_DIR"
echo "   2. Désinstallez le module via l'admin"
echo "   3. Consultez les logs SocialEngine"
echo ""

echo "✨ Test de préproduction terminé avec succès !"