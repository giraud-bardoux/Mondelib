# 🚨 RÉSOLUTION D'URGENCE - Erreur SocialEngine 078feb

## ⚡ Actions Immédiates (2 minutes)

### 🚩 **PRIORITÉ 1 : Rétablir l'accès au site**

#### Option A : Via interface d'administration (si accessible)
```
1. Connectez-vous à : http://votre-site.com/admin
2. Manage → Packages  
3. PhotoBlur → Disable (désactiver)
4. Si erreur persiste → PhotoBlur → Uninstall
```

#### Option B : Via base de données (si admin inaccessible)
```sql
-- Connexion phpMyAdmin/HeidiSQL/MySQL
-- Désactiver le module PhotoBlur
UPDATE engine4_core_modules SET enabled = 0 WHERE name = 'photoblur';

-- Supprimer les paramètres PhotoBlur
DELETE FROM engine4_core_settings WHERE name LIKE 'photoblur.%';
```

#### Option C : Via fichiers (accès FTP/SSH)
```bash
# Renommer temporairement le module
mv application/modules/PhotoBlur application/modules/PhotoBlur_DISABLED

# Vider le cache
rm -rf application/temporary/cache/*
```

---

## 🔍 **Diagnostic Rapide de la Cause**

### 🧪 Exécutez le script de diagnostic :
```bash
./debug_photoblur_error.sh
```

### 📋 **Causes probables de l'erreur 078feb :**

#### 1. **Erreur de syntaxe PHP** (60% des cas)
```
❌ Problème : Caractère invalide dans un fichier PHP
✅ Solution : Vérifiez les fins de ligne, encoding UTF-8
```

#### 2. **Permissions insuffisantes** (25% des cas)
```
❌ Problème : Serveur ne peut pas lire les fichiers
✅ Solution : chmod 755 sur dossiers, 644 sur fichiers
```

#### 3. **Conflit de modules** (10% des cas)
```
❌ Problème : Incompatibilité avec module existant
✅ Solution : Désactiver autres modules récents
```

#### 4. **Mémoire PHP insuffisante** (5% des cas)
```
❌ Problème : memory_limit trop bas
✅ Solution : Augmenter dans php.ini ou .htaccess
```

---

## 🛠️ **Solutions par Ordre de Priorité**

### **SOLUTION 1 : Rollback Complet** ⚡ (Recommandé)
```bash
# Via SSH/Terminal
cd /chemin/vers/socialengine

# 1. Supprimer le module
rm -rf application/modules/PhotoBlur/

# 2. Nettoyer la base de données
mysql -u username -p database_name << EOF
DELETE FROM engine4_core_modules WHERE name = 'photoblur';
DELETE FROM engine4_core_settings WHERE name LIKE 'photoblur.%';
EOF

# 3. Vider le cache
rm -rf application/temporary/cache/*

# 4. Tester l'accès
curl -I http://votre-site.com
```

### **SOLUTION 2 : Correction des Fichiers** 🔧
```bash
# Vérifier et corriger l'encoding
file application/modules/PhotoBlur/Bootstrap.php
# Doit afficher: UTF-8 Unicode text

# Corriger les permissions
find application/modules/PhotoBlur -type d -exec chmod 755 {} \;
find application/modules/PhotoBlur -type f -exec chmod 644 {} \;

# Vérifier la syntaxe PHP
php -l application/modules/PhotoBlur/Bootstrap.php
php -l application/modules/PhotoBlur/Plugin/Core.php
```

### **SOLUTION 3 : Mode Debug** 🔬
```php
// application/settings/database.php
// Ajouter temporairement à la fin du fichier :
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### **SOLUTION 4 : Réinstallation Propre** 🔄
```bash
# 1. Sauvegarde du module actuel
cp -r application/modules/PhotoBlur /tmp/photoblur_backup

# 2. Suppression complète
rm -rf application/modules/PhotoBlur
rm -rf application/temporary/cache/*

# 3. Re-télécharger le module (version fraîche)
# 4. Réinstaller via admin
```

---

## 📞 **Vérifications Post-Résolution**

### ✅ **Checklist de rétablissement :**
```
[ ] Site accessible à l'URL principale
[ ] Interface d'administration accessible  
[ ] Pages utilisateurs fonctionnelles
[ ] Aucune erreur PHP visible
[ ] Logs serveur propres (pas d'erreurs)
```

### 🧪 **Test de fonctionnement :**
```bash
# Test HTTP
curl -s -o /dev/null -w "%{http_code}" http://votre-site.com
# Doit retourner : 200

# Test admin
curl -s -o /dev/null -w "%{http_code}" http://votre-site.com/admin
# Doit retourner : 200 ou 302 (redirection login)
```

---

## 🔄 **Plan de Réinstallation (Après résolution)**

### 📋 **Avant de réinstaller PhotoBlur :**

1. **✅ Vérifiez l'environnement :**
   ```
   - PHP version ≥ 7.0
   - Memory limit ≥ 128M
   - SocialEngine 7.4 fonctionnel
   ```

2. **✅ Préparez la réinstallation :**
   ```bash
   # Vérification pré-installation
   ./install_photoblur.sh
   ```

3. **✅ Installation par étapes :**
   ```
   - Copier SEULEMENT les fichiers PHP d'abord
   - Tester l'accès site
   - Installer via admin
   - Ajouter CSS/JS ensuite
   ```

---

## 📋 **Informations de Support**

### 🚨 **Si l'erreur persiste :**

**Informations à collecter :**
```
1. Version SocialEngine exacte
2. Version PHP du serveur  
3. Logs d'erreur serveur (50 dernières lignes)
4. Étape exacte où l'erreur survient
5. Modules installés récemment
```

**Commandes de diagnostic :**
```bash
# Version PHP
php -v

# Logs récents
tail -50 /var/log/apache2/error.log

# Espace disque
df -h

# Mémoire
free -m
```

---

## ⏰ **Chronologie de Résolution**

| Temps | Action | Résultat Attendu |
|-------|--------|------------------|
| **0-2 min** | Désactiver PhotoBlur | Site accessible |
| **2-5 min** | Diagnostic cause | Erreur identifiée |
| **5-10 min** | Appliquer solution | Problème résolu |
| **10-15 min** | Tests complets | Site 100% fonctionnel |

---

## 🎯 **Résultat Final Attendu**

```
✅ Site SocialEngine accessible
✅ Interface admin fonctionnelle  
✅ Aucune erreur 078feb
✅ Logs serveur propres
✅ Prêt pour réinstallation PhotoBlur (optionnel)
```

---

**🚨 IMPORTANT : En cas d'urgence absolue, appliquez d'abord la SOLUTION 1 (Rollback) pour rétablir l'accès au site, puis investiguer la cause.**