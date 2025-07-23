# ✅ Checklist Installation Module PhotoFloue v1.0.1

## 🎯 **AVANT L'INSTALLATION**

### Prérequis système
- [ ] SocialEngine 7.4+ installé et fonctionnel
- [ ] Accès SSH ou FTP au serveur
- [ ] Accès administrateur au panel admin  
- [ ] PHP 7.0+ (idéalement 8.0+)
- [ ] Modules `user` et `album` activés

### Vérifications préliminaires
- [ ] Site en mode maintenance (recommandé)
- [ ] Sauvegarde de la base de données effectuée
- [ ] Sauvegarde des fichiers effectuée
- [ ] Test de l'espace disque disponible (>50MB libre)

## 📦 **INSTALLATION ÉTAPE PAR ÉTAPE**

### Option A: Installation automatique (RECOMMANDÉE)
- [ ] Connexion SSH au serveur
- [ ] Navigation vers le répertoire SocialEngine : `cd ~/preprodml/`
- [ ] Téléchargement du script : `wget https://github.com/giraud-bardoux/Mondelib/raw/photoblur/INSTALLATION_RAPIDE_PHOTOFLOUE.sh`
- [ ] Exécution : `chmod +x INSTALLATION_RAPIDE_PHOTOFLOUE.sh && ./INSTALLATION_RAPIDE_PHOTOFLOUE.sh`
- [ ] Vérification : Tous les ✅ affichés

### Option B: Installation manuelle
- [ ] Téléchargement : `wget https://github.com/giraud-bardoux/Mondelib/raw/photoblur/releases/module-PhotoFloue-1.01-FINAL.tar.gz`
- [ ] Décompression : `tar -xzf module-PhotoFloue-1.01-FINAL.tar.gz`
- [ ] Copie module : `cp -r module-PhotoFloue-1.01/PhotoFloue application/modules/`
- [ ] Copie traductions FR : `cp module-PhotoFloue-1.01/languages/photofloue-fr.csv application/languages/fr/photofloue.csv`
- [ ] Copie traductions EN : `cp module-PhotoFloue-1.01/languages/photofloue-en.csv application/languages/en/photofloue.csv`
- [ ] Permissions : `chmod -R 755 application/modules/PhotoFloue`
- [ ] Test : `php module-PhotoFloue-1.01/test_photofloue_simple.php`

## ⚙️ **ACTIVATION VIA ADMIN PANEL**

### Connexion et navigation
- [ ] Connexion en tant qu'administrateur
- [ ] Navigation : Admin Panel > Packages > Browse Plugins
- [ ] Recherche : "PhotoFloue" ou "PhotoFloue Module"
- [ ] Module visible dans la liste

### Installation et activation
- [ ] Clic sur "Install" à côté de "PhotoFloue Module v1.0.1"
- [ ] Attendre la fin de l'installation (pas d'erreur)
- [ ] Clic sur "Enable" si pas activé automatiquement
- [ ] Statut affiché : "Enabled" ✅

## 🧪 **TESTS DE VALIDATION**

### Test 1: Fonctionnement de base
- [ ] **Déconnexion complète** du site (ou navigateur privé)
- [ ] Visite d'une page avec photos d'albums
- [ ] **Vérification** : Photos sont floutées ✅
- [ ] **Connexion** avec un compte utilisateur
- [ ] **Vérification** : Photos sont nettes ✅

### Test 2: Protection anti-capture
- [ ] **Mode déconnecté** : Clic droit sur photo → Bloqué ✅
- [ ] **Mode déconnecté** : Ctrl+S → Alerte affichée ✅
- [ ] **Mode déconnecté** : Survol photo → Message "Connectez-vous..." ✅
- [ ] **Mode connecté** : Clic droit → Fonctionne normalement ✅

### Test 3: Mobile (si possible)
- [ ] **Smartphone/Tablette** : Photos floutées en mode déconnecté ✅
- [ ] **Appui long** sur photo → Bloqué ✅
- [ ] **Mode connecté** mobile → Photos nettes ✅

## 🔍 **VÉRIFICATIONS TECHNIQUES**

### Base de données
- [ ] Connexion MySQL/phpMyAdmin
- [ ] Requête : `SELECT * FROM engine4_core_modules WHERE name = 'photofloue';`
- [ ] **Résultat** : 1 ligne avec `enabled = 1` ✅
- [ ] Requête : `SELECT * FROM engine4_core_settings WHERE name LIKE 'photofloue.%';`
- [ ] **Résultat** : ~5 paramètres trouvés ✅

### Fichiers système
- [ ] Vérification : `ls -la application/modules/PhotoFloue/`
- [ ] **Présence** : Bootstrap.php, Plugin/, View/, externals/, settings/ ✅
- [ ] Vérification : `ls -la application/languages/*/photofloue.csv`
- [ ] **Présence** : Fichiers FR et EN ✅

## 🚨 **EN CAS DE PROBLÈME**

### Erreur d'installation
- [ ] Vérifier permissions : `chmod -R 755 application/modules/PhotoFloue`
- [ ] Vider cache : `rm -rf temporary/cache/* temporary/compile/*`
- [ ] Vérifier logs : `tail -f temporary/log/application.log`

### Photos non floutées
- [ ] Vérifier déconnexion complète (vider cookies)
- [ ] Tester en navigation privée
- [ ] Vérifier paramètres : `SELECT value FROM engine4_core_settings WHERE name = 'photofloue.enabled';`

### Rollback d'urgence
- [ ] Désactiver via DB : `UPDATE engine4_core_modules SET enabled = 0 WHERE name = 'photofloue';`
- [ ] Supprimer fichiers : `rm -rf application/modules/PhotoFloue`
- [ ] Nettoyer paramètres : `DELETE FROM engine4_core_settings WHERE name LIKE 'photofloue.%';`

## ✅ **VALIDATION FINALE**

### Checklist de succès
- [ ] Module installé et activé
- [ ] Photos floutées pour visiteurs
- [ ] Photos nettes pour membres
- [ ] Protection fonctionnelle
- [ ] Pas d'erreurs en logs
- [ ] Performance normale du site

### Tests utilisateur
- [ ] Test avec compte utilisateur normal
- [ ] Test avec compte admin
- [ ] Test sur page d'accueil
- [ ] Test sur pages d'albums
- [ ] Test sur profils utilisateurs

## 📊 **POST-INSTALLATION**

### Monitoring
- [ ] Surveiller logs les 24h suivantes
- [ ] Vérifier retours utilisateurs
- [ ] Tester performance site
- [ ] Valider sur différents navigateurs

### Documentation
- [ ] Noter la version installée : v1.0.1
- [ ] Documenter les paramètres modifiés
- [ ] Planifier tests réguliers
- [ ] Prévoir mise à jour future

---

**📅 Date installation :** _______________  
**👤 Installé par :** _______________  
**✅ Validation finale :** _______________  

**🎯 Installation réussie si TOUS les tests passent !**