# 🚨 RÉSOLUTION D'URGENCE - Erreur PhotoFloue

## ⚠️ Erreurs détectées
- **Error Code: 0242bf**
- **Error Code: fedbff**
- **Problème**: Installation du module PhotoFloue

## 🔥 SOLUTION IMMÉDIATE

### 1. 🛑 ARRÊT D'URGENCE
```bash
# Désactiver immédiatement le module via la base de données
mysql -u [username] -p [database_name] -e "UPDATE engine4_core_modules SET enabled = 0 WHERE name = 'photofloue';"
```

### 2. 🧹 NETTOYAGE COMPLET
```bash
# Supprimer tous les fichiers liés au module
rm -rf application/modules/PhotoFloue
rm -f application/languages/fr/photofloue.csv
rm -f application/languages/en/photofloue.csv

# Nettoyer le cache
rm -rf temporary/cache/*
rm -rf temporary/compile/*
rm -rf temporary/log/*
```

### 3. 🗄️ NETTOYAGE BASE DE DONNÉES
```sql
-- Supprimer tous les paramètres du module
DELETE FROM engine4_core_settings WHERE name LIKE 'photofloue.%';

-- Supprimer l'entrée du module
DELETE FROM engine4_core_modules WHERE name = 'photofloue';

-- Vérifier que tout est supprimé
SELECT * FROM engine4_core_modules WHERE name LIKE '%photo%';
SELECT * FROM engine4_core_settings WHERE name LIKE '%photo%';
```

## 🔍 DIAGNOSTIC DES CAUSES

### Cause probable 1: Conflit de noms
- Ancien module PhotoBlur encore présent
- Paramètres en double dans la base

### Cause probable 2: Permissions insuffisantes
- Module installé sans permissions d'écriture
- Cache non accessible

### Cause probable 3: Erreur dans le manifest
- Syntaxe PHP incorrecte
- Dépendances manquantes

## 🛠️ SOLUTIONS ÉTAPE PAR ÉTAPE

### Option A: Installation propre
1. **Vérifier l'état du site**
   ```bash
   # Tester que le site fonctionne sans le module
   curl -I http://votre-site.com
   ```

2. **Réinstaller proprement**
   ```bash
   # Copier le module depuis la branche Git
   git checkout photoblur
   cp -r application/modules/PhotoFloue /path/to/socialengine/application/modules/
   cp application/languages/*/photofloue.csv /path/to/socialengine/application/languages/
   ```

3. **Corriger les permissions**
   ```bash
   chmod -R 755 application/modules/PhotoFloue
   chown -R www-data:www-data application/modules/PhotoFloue
   chmod -R 777 temporary
   ```

### Option B: Installation manuelle
1. **Insérer manuellement dans la base**
   ```sql
   INSERT INTO engine4_core_modules (name, title, description, version, enabled, type) 
   VALUES ('photofloue', 'PhotoFloue Module', 'Module de floutage des photos', '1.0.0', 1, 'module');
   ```

2. **Ajouter les paramètres**
   ```sql
   INSERT INTO engine4_core_settings (name, value) VALUES
   ('photofloue.enabled', '1'),
   ('photofloue.blur_intensity', '10'),
   ('photofloue.apply_to_users', '1'),
   ('photofloue.apply_to_albums', '1'),
   ('photofloue.mobile_protection', '1'),
   ('photofloue.login_message', 'Connectez-vous pour voir les photos nettes');
   ```

## 📋 CHECKLIST DE VÉRIFICATION

### ✅ Après résolution:
- [ ] Site accessible sans erreur
- [ ] Pages avec photos chargent correctement
- [ ] Pas d'erreur dans les logs
- [ ] Module désactivé/supprimé de la liste des modules
- [ ] Cache vidé et régénéré

### ✅ Avant réinstallation:
- [ ] Permissions correctes (755 pour modules, 777 pour temporary)
- [ ] Aucun conflit de nom (PhotoBlur supprimé)
- [ ] Base de données propre
- [ ] Espace disque suffisant
- [ ] PHP et modules requis disponibles

## 🔧 ALTERNATIVE: Installation simplifiée

Si le module complet pose problème, vous pouvez utiliser la **solution CSS/JS directe** :

### 1. Créer un fichier CSS simple
```css
/* application/themes/[votre-theme]/theme.css */
body:not(.logged-in) .bg_item_photo_album_photo,
body:not(.logged-in) .profile_photo img {
    filter: blur(10px) !important;
}
```

### 2. Ajouter le JavaScript de base
```javascript
/* Dans votre template ou footer */
<script>
if (!document.body.classList.contains('logged-in')) {
    document.addEventListener('contextmenu', function(e) {
        if (e.target.closest('.bg_item_photo_album_photo, .profile_photo')) {
            e.preventDefault();
        }
    });
}
</script>
```

## 📞 SUPPORT D'URGENCE

### Si rien ne fonctionne:
1. **Restaurer une sauvegarde** de la base de données d'avant l'installation
2. **Vérifier les logs** Apache/Nginx pour plus de détails
3. **Activer le mode debug** SocialEngine temporairement
4. **Contacter** le support technique avec les logs complets

## 🚀 PRÉVENTION

Pour éviter ce problème à l'avenir:
- Toujours tester en préprod d'abord
- Faire une sauvegarde avant installation de module
- Vérifier les permissions avant installation
- Nettoyer les anciens modules avant les nouveaux

---

**⏰ Temps estimé de résolution : 15-30 minutes**  
**🔒 Priorité : URGENTE - Site peut être inaccessible**