# ✅ Checklist de Test en Préproduction - Module PhotoBlur

## 🚀 Préparation (5 minutes)

### Avant installation
- [ ] **Sauvegarde de la base de données créée**
- [ ] **Sauvegarde des fichiers critiques créée**
- [ ] **URL de préproduction accessible** : `http://votre-preprod.com`
- [ ] **Accès admin confirmé** : `http://votre-preprod.com/admin`
- [ ] **Module PhotoBlur copié** dans `application/modules/`

---

## 📦 Installation (5 minutes)

### Via l'interface d'administration
- [ ] **Connexion admin** réussie
- [ ] **Manage → Packages** accessible
- [ ] **Module PhotoBlur** visible dans la liste
- [ ] **Installation** cliquée et terminée sans erreur
- [ ] **Activation** cliquée et terminée sans erreur
- [ ] **Aucune erreur PHP** affichée

---

## 🧪 Tests Fonctionnels (15 minutes)

### Test 1 : Visiteur non connecté
**🎯 Objectif :** Vérifier que les photos sont floutées et protégées

#### Navigation privée
- [ ] **Fenêtre privée** ouverte
- [ ] **Page d'accueil** chargée : `http://votre-preprod.com`
- [ ] **Page avec photos** trouvée (profils, albums)

#### Vérifications visuelles
- [ ] **Photos de profil** sont floutées (effet blur visible)
- [ ] **Photos de couverture** sont floutées (si présentes)
- [ ] **Photos d'albums** sont floutées (si présentes)
- [ ] **Message au survol** apparaît : "Connectez-vous pour ne plus voir flou"

#### Tests de protection
- [ ] **Clic droit** sur photo → Bloqué + message affiché
- [ ] **Ctrl+S** (sauvegarder) → Bloqué + message affiché
- [ ] **Ctrl+C** (copier) → Bloqué
- [ ] **Ctrl+A** (sélectionner tout) → Bloqué
- [ ] **Glisser-déposer** photo → Impossible
- [ ] **F12** (outils dev) → Bloqué ou flou renforcé

### Test 2 : Utilisateur connecté
**🎯 Objectif :** Vérifier que les photos sont nettes et sans protection

#### Connexion utilisateur
- [ ] **Connexion** avec un compte utilisateur valide
- [ ] **Mêmes pages** visitées qu'en mode non-connecté

#### Vérifications
- [ ] **Photos de profil** sont nettes (pas de flou)
- [ ] **Photos de couverture** sont nettes
- [ ] **Photos d'albums** sont nettes
- [ ] **Aucune protection** n'empêche les interactions normales
- [ ] **Clic droit fonctionnel** (menu normal du navigateur)

---

## 📱 Tests Mobile (10 minutes)

### Simulation mobile (F12 → Device toolbar)
- [ ] **Mode responsive** activé dans les outils dev
- [ ] **iPhone/Android** sélectionné

#### Tests tactiles (simulation)
- [ ] **Appui simple** sur photo → Message affiché
- [ ] **Appui long** → Bloqué (pas de menu contextuel)
- [ ] **Pinch zoom** sur photo → Bloqué
- [ ] **Orientation portrait/paysage** → Flou conservé

---

## ⚡ Tests de Performance (10 minutes)

### Outils de développement (F12)
- [ ] **Onglet Network** ouvert
- [ ] **Page rechargée** avec photos

#### Fichiers du module
- [ ] **photoblur.css** se charge (status 200)
- [ ] **photoblur.js** se charge (status 200)
- [ ] **Temps total** < 2 secondes

#### Performance
- [ ] **Onglet Performance** → Enregistrement fait
- [ ] **CPU usage** raisonnable (< 50% sustained)
- [ ] **Mémoire stable** (pas de fuite visible)

---

## 🛡️ Tests de Sécurité (15 minutes)

### Test 1 : Outils de développement
**En mode visiteur non-connecté :**
- [ ] **F12** → Outils de développement ouverts
- [ ] **Flou intensifié** automatiquement (20px au lieu de 10px)
- [ ] **Modification CSS** tentée dans l'inspecteur
- [ ] **Résistance** aux modifications basiques

### Test 2 : JavaScript désactivé
- [ ] **JavaScript désactivé** dans les paramètres du navigateur
- [ ] **Page rechargée**
- [ ] **Flou CSS** toujours actif (filter: blur)
- [ ] **Protections de base** toujours présentes

### Test 3 : Raccourcis avancés
**En mode visiteur non-connecté :**
- [ ] **Ctrl+Shift+I** (dev tools) → Bloqué
- [ ] **Ctrl+U** (source) → Bloqué  
- [ ] **Print Screen** → Message affiché
- [ ] **Ctrl+P** (imprimer) → Images masquées dans l'aperçu

---

## 🌐 Tests de Compatibilité (15 minutes)

### Navigateurs (si disponibles)
- [ ] **Chrome** : Flou + protections OK
- [ ] **Firefox** : Flou + protections OK
- [ ] **Safari** : Flou + protections OK
- [ ] **Edge** : Flou + protections OK

### Résolutions d'écran
- [ ] **1920x1080** (Desktop) : OK
- [ ] **1366x768** (Laptop) : OK
- [ ] **768x1024** (Tablette) : OK
- [ ] **375x667** (Mobile) : OK

---

## 🔍 Tests d'Intégration (10 minutes)

### Compatibilité SocialEngine
- [ ] **Navigation générale** du site fonctionne
- [ ] **Autres modules** fonctionnent normalement
- [ ] **Widgets utilisateur** s'affichent correctement
- [ ] **Recherche** fonctionne
- [ ] **Aucune erreur PHP** dans les logs

### Base de données
- [ ] **Settings PhotoBlur** présents dans `engine4_core_settings`
- [ ] **Valeurs par défaut** correctes
- [ ] **Aucune corruption** de données existantes

---

## 📊 Validation Finale (5 minutes)

### Critères de succès ✅
- [ ] **Floutage automatique** fonctionne pour visiteurs non-connectés
- [ ] **Photos nettes** pour utilisateurs connectés
- [ ] **Protection contre sauvegarde** active
- [ ] **Message d'incitation** affiché
- [ ] **Performance acceptable** (< 2s chargement)
- [ ] **Aucune erreur** système
- [ ] **Compatible** avec les navigateurs principaux
- [ ] **Responsive** sur mobile/tablette

### Actions selon résultats
- [ ] **✅ TOUS TESTS OK** → Prêt pour production
- [ ] **⚠️ PROBLÈMES MINEURS** → Ajustements nécessaires
- [ ] **❌ PROBLÈMES MAJEURS** → Investigation requise

---

## 🚨 Plan de Rollback

### En cas de problème critique
1. **Désactiver le module** via Admin → Packages → PhotoBlur → Disable
2. **Désinstaller** si nécessaire : Admin → Packages → PhotoBlur → Uninstall
3. **Restaurer la base** depuis la sauvegarde
4. **Restaurer les fichiers** depuis le backup
5. **Vérifier** que le site fonctionne normalement

### Contacts d'urgence
- **Admin serveur** : `[VOTRE_CONTACT]`
- **Développeur** : `[VOTRE_CONTACT]`
- **Backup location** : `backup_preprod_[DATE]/`

---

## 📝 Rapport Final

**Date du test :** _______________  
**Testeur :** _______________  
**Durée totale :** _______________ minutes  

**Résultat global :**
- [ ] ✅ Validation complète - Prêt pour production
- [ ] ⚠️ Validation avec réserves - Ajustements requis
- [ ] ❌ Échec - Investigation approfondie nécessaire

**Notes supplémentaires :**
```
[Espace pour vos observations]
```

---

**🎉 Félicitations ! Si tous les tests sont validés, votre module PhotoBlur est prêt pour la production !**