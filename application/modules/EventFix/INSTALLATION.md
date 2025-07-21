# Guide d'installation EventFix pour SocialEngine 7.4

## Vue d'ensemble

Le plugin EventFix étend automatiquement la plage d'années sélectionnable dans les formulaires de création/édition d'événements pour permettre la sélection de dates jusqu'à 5 ans en arrière.

## Prérequis

- SocialEngine 7.0+ (testé avec 7.4)
- Module Event installé et activé
- Accès administrateur au site
- Accès FTP/SSH au serveur

## Structure des fichiers

Voici la structure complète du module EventFix à placer dans `application/modules/EventFix/` :

```
EventFix/
├── Bootstrap.php                    # Initialisation du module
├── Plugin/
│   ├── Core.php                    # Plugin principal avec logique JavaScript
│   └── index.html                  # Sécurité
├── settings/
│   ├── manifest.php                # Configuration du module
│   ├── install.php                 # Script d'installation
│   └── index.html                  # Sécurité
├── index.html                      # Sécurité
├── README.md                       # Documentation
└── INSTALLATION.md                 # Ce fichier
```

## Installation étape par étape

### 1. Sauvegarde

**IMPORTANT** : Faites toujours une sauvegarde complète avant d'installer un nouveau plugin.

```bash
# Sauvegarde de la base de données
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Sauvegarde des fichiers
tar -czf socialengine_backup_$(date +%Y%m%d).tar.gz /chemin/vers/socialengine/
```

### 2. Upload des fichiers

#### Option A : Via FTP/SFTP

1. Uploadez le dossier `EventFix` complet dans `/application/modules/`
2. Assurez-vous que la structure est : `/application/modules/EventFix/...`

#### Option B : Via SSH

```bash
# Depuis le répertoire racine de SocialEngine
cd application/modules/
# Uploadez ou copiez le dossier EventFix ici
```

### 3. Permissions

Définissez les permissions appropriées :

```bash
# Pour Apache/Nginx avec www-data
chown -R www-data:www-data application/modules/EventFix/
chmod -R 755 application/modules/EventFix/

# Ou pour d'autres configurations
chown -R apache:apache application/modules/EventFix/
chmod -R 755 application/modules/EventFix/
```

### 4. Installation via l'interface d'administration

1. **Connectez-vous** à l'administration de SocialEngine
2. **Naviguez** vers `Admin Panel` > `Plugins` ou `Modules`
3. **Trouvez** "Event Date Range Fix" dans la liste des modules disponibles
4. **Cliquez** sur `Install`
5. **Attendez** la fin de l'installation
6. **Activez** le module en cliquant sur `Enable`

### 5. Vérification de l'installation

#### Test fonctionnel

1. Allez sur une page de création d'événement
2. Regardez le champ "Date de début"
3. Cliquez sur le sélecteur d'année
4. Vérifiez que vous pouvez maintenant sélectionner des années 5 ans en arrière

#### Test technique

1. Ouvrez les outils de développement du navigateur (F12)
2. Allez dans l'onglet "Console"
3. Rechargez la page de création d'événement
4. Vous devriez voir des messages comme :
   ```
   EventFix: Étendu la plage d'années pour select[name="starttime[year]"] de 2019 à 2030
   ```

## Dépannage

### Problème : Le module n'apparaît pas dans la liste

**Solution** :
1. Vérifiez les permissions des fichiers
2. Vérifiez que la structure des dossiers est correcte
3. Videz le cache de SocialEngine :
   ```bash
   rm -rf application/temporary/cache/*
   ```

### Problème : Erreur lors de l'installation

**Solution** :
1. Vérifiez les logs d'erreur PHP
2. Vérifiez que tous les fichiers sont présents
3. Vérifiez la syntaxe des fichiers PHP :
   ```bash
   php -l application/modules/EventFix/Plugin/Core.php
   php -l application/modules/EventFix/settings/manifest.php
   ```

### Problème : Le plugin ne modifie pas les formulaires

**Solutions** :
1. Vérifiez que le module Event est installé et activé
2. Vérifiez que JavaScript est activé dans le navigateur
3. Ouvrez la console du navigateur pour voir les erreurs JavaScript
4. Videz le cache du navigateur

### Problème : Conflits avec d'autres plugins

**Solution** :
1. Désactivez temporairement les autres plugins pour identifier le conflit
2. Vérifiez les logs d'erreur JavaScript dans la console du navigateur

## Configuration avancée

### Modifier le nombre d'années en arrière

Pour changer de 5 ans à un autre nombre, modifiez la ligne suivante dans `Plugin/Core.php` :

```javascript
var minYear = currentYear - 5; // Changez 5 par le nombre souhaité
```

Par exemple, pour 10 ans en arrière :
```javascript
var minYear = currentYear - 10;
```

### Ajouter des sélecteurs personnalisés

Si votre thème utilise des sélecteurs CSS différents, ajoutez-les dans le tableau `starttimeSelectors` dans `Plugin/Core.php` :

```javascript
var starttimeSelectors = [
    'select[name="starttime[year]"]',
    'select[name="starttime-year"]',
    // Ajoutez vos sélecteurs ici
    'select[name="votre-selecteur-custom"]',
];
```

## Désinstallation

### Désactiver le module

1. Allez dans `Admin Panel` > `Plugins`
2. Trouvez "Event Date Range Fix"
3. Cliquez sur `Disable`

### Désinstaller complètement

1. Dans l'admin, cliquez sur `Uninstall`
2. Supprimez les fichiers :
   ```bash
   rm -rf application/modules/EventFix/
   ```

## Support et compatibilité

### Versions supportées

- ✅ SocialEngine 7.0.x
- ✅ SocialEngine 7.1.x
- ✅ SocialEngine 7.2.x
- ✅ SocialEngine 7.3.x
- ✅ SocialEngine 7.4.x

### Navigateurs supportés

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 11+
- ✅ Edge 79+
- ✅ Internet Explorer 11

### Notes importantes

- Ce plugin NE modifie PAS le code source du module Event
- Il est compatible avec les mises à jour de SocialEngine
- Il fonctionne avec tous les thèmes SocialEngine
- Il supporte les formulaires chargés dynamiquement (AJAX)

## Contact

Pour des questions ou problèmes, vérifiez d'abord :
1. Ce guide d'installation
2. Le fichier README.md
3. Les logs d'erreur PHP et JavaScript