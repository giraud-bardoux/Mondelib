# PhotoBlur Module for SocialEngine 7.4

## 📖 Description

Le module **PhotoBlur** est conçu pour SocialEngine 7.4 afin de flouter automatiquement les photos des utilisateurs et des albums pour les visiteurs non connectés. L'objectif est d'encourager l'inscription en montrant un aperçu flouté du contenu premium.

## ✨ Fonctionnalités

### 🎯 Floutage Intelligent
- **Photos utilisateurs** : Floute automatiquement les photos de profil et avatars
- **Photos d'albums** : Applique le flou aux galeries et collections de photos
- **Détection automatique** : Identifie et traite tous les éléments photo pertinents

### 🔒 Protection Avancée
- **Anti-capture d'écran** : Prévention des captures via raccourcis clavier
- **Protection mobile** : Gestion spécialisée pour les appareils tactiles
- **Anti-clic droit** : Désactivation du menu contextuel sur les photos
- **Détection dev tools** : Renforcement automatique si outils de développement détectés

### 🎨 Interface Utilisateur
- **Tooltips informatifs** : Messages explicatifs au survol des photos
- **Messages dynamiques** : Incitation personnalisable à la connexion
- **Animations fluides** : Transitions CSS élégantes
- **Responsive design** : Adaptation automatique mobile/desktop

## 🚀 Installation

### Prérequis
- SocialEngine 7.4 ou supérieur
- Modules `user` et `album` activés
- PHP 7.0+ recommandé

### Étapes d'installation

1. **Télécharger le module**
   ```bash
   # Copier les fichiers du module dans le répertoire SocialEngine
   cp -r PhotoBlur/ /path/to/socialengine/application/modules/
   ```

2. **Installer via l'admin**
   - Connectez-vous en tant qu'administrateur
   - Allez dans `Admin Panel > Plugins > Browse Plugins`
   - Trouvez "PhotoBlur Module" et cliquez "Install"
   - Activez le module après installation

3. **Vérification**
   - Déconnectez-vous et visitez une page avec des photos
   - Les photos doivent apparaître floutées
   - Reconnectez-vous : les photos doivent être nettes

## ⚙️ Configuration

### Paramètres disponibles

| Paramètre | Description | Valeur par défaut |
|-----------|-------------|-------------------|
| `photoblur.enabled` | Activer/désactiver le module | `1` (activé) |
| `photoblur.blur_intensity` | Intensité du flou (1-20px) | `10` |
| `photoblur.apply_to_users` | Flouter les photos utilisateurs | `1` (oui) |
| `photoblur.apply_to_albums` | Flouter les photos d'albums | `1` (oui) |
| `photoblur.mobile_protection` | Protection mobile renforcée | `1` (activé) |
| `photoblur.login_message` | Message d'incitation | "Connectez-vous pour voir les photos nettes" |

### Modification via base de données

```sql
-- Changer l'intensité du flou à 15px
UPDATE engine4_core_settings 
SET value = '15' 
WHERE name = 'photoblur.blur_intensity';

-- Désactiver la protection mobile
UPDATE engine4_core_settings 
SET value = '0' 
WHERE name = 'photoblur.mobile_protection';
```

## 🔧 Architecture Technique

### Structure des fichiers

```
PhotoBlur/
├── Bootstrap.php                    # Initialisation du module
├── Plugin/
│   └── Core.php                    # Logique principale et hooks
├── View/
│   └── Helper/
│       └── ItemBackgroundPhoto.php # Surcharge helper photos
├── externals/
│   ├── scripts/
│   │   └── photoblur.js           # Protection JavaScript
│   └── styles/
│       └── photoblur.css          # Styles de floutage
├── settings/
│   ├── manifest.php               # Configuration du module
│   └── install.php                # Script d'installation
└── README.md                      # Cette documentation
```

### Hooks utilisés

- `onRenderLayoutDefault` : Injection des variables JavaScript
- `onUserPhotoUpload` : Traitement des nouvelles photos
- `onItemCreateAfter` : Gestion des nouveaux éléments

### Sélecteurs CSS ciblés

```css
/* Photos d'albums */
.thumbs_photo
.bg_item_photo_album_photo
.bg_item_photo

/* Photos utilisateurs */
.profile_photo img
.user_sidebar_photo img
.avatar img

/* Protection générale */
.photoblur-protected
.photoblur-blurred
```

## 🛡️ Sécurité et Limitations

### Protections implémentées

✅ **Raccourcis clavier** (Ctrl+S, Ctrl+C, F12, etc.)  
✅ **Clic droit** sur les photos protégées  
✅ **Appui long mobile** (capture d'écran)  
✅ **Impression** des photos protégées  
✅ **Détection outils développement**  

### Limitations connues

⚠️ **Capture système** : Impossible de bloquer complètement les captures d'écran système  
⚠️ **JavaScript désactivé** : Le flou CSS reste mais les protections JS sont inactives  
⚠️ **Navigateurs anciens** : Compatibilité limitée avec les très vieux navigateurs  

## 🐛 Dépannage

### Photos non floutées

1. **Vérifier le statut du module**
   ```sql
   SELECT * FROM engine4_core_modules WHERE name = 'photoblur';
   ```

2. **Vérifier les paramètres**
   ```sql
   SELECT * FROM engine4_core_settings WHERE name LIKE 'photoblur.%';
   ```

3. **Vider le cache**
   - Admin Panel > System > Cache
   - Clear All Cache

### JavaScript non chargé

1. **Vérifier les fichiers**
   ```bash
   ls -la application/modules/PhotoBlur/externals/scripts/
   ls -la application/modules/PhotoBlur/externals/styles/
   ```

2. **Permissions des fichiers**
   ```bash
   chmod 644 application/modules/PhotoBlur/externals/scripts/photoblur.js
   chmod 644 application/modules/PhotoBlur/externals/styles/photoblur.css
   ```

### Erreurs d'installation

- **Dépendances manquantes** : Vérifier que les modules `user` et `album` sont installés
- **Permissions** : Vérifier les droits d'écriture sur le répertoire `application/modules/`
- **Version PHP** : S'assurer d'utiliser PHP 7.0 ou supérieur

## 📈 Performance

### Impact minimal
- **CSS** : ~10KB compressé
- **JavaScript** : ~15KB compressé  
- **Requêtes DB** : Aucune requête supplémentaire en production
- **Cache** : Compatible avec tous les systèmes de cache SocialEngine

### Optimisations
- Chargement conditionnel (seulement pour visiteurs non connectés)
- CSS et JS minifiés en production
- Détection intelligente des éléments à protéger

## 🔄 Versions et Historique

### Version 1.0.0 (Actuelle)
- ✅ Floutage des photos utilisateurs et albums
- ✅ Protection anti-capture avancée
- ✅ Interface responsive mobile/desktop
- ✅ Système de traduction FR/EN
- ✅ Installation automatisée

### Roadmap futures versions
- 🔮 **v1.1** : Interface d'administration graphique
- 🔮 **v1.2** : Floutage conditionnel par niveau d'utilisateur  
- 🔮 **v1.3** : Analytics des tentatives de contournement
- 🔮 **v1.4** : Mode "aperçu" avec zones non floutées

## 📞 Support

### Documentation SocialEngine
- [SocialEngine Development Guide](https://community.socialengine.com/blogs)
- [Module Development Best Practices](https://community.socialengine.com)

### Debugging
Pour activer le mode debug, ajouter dans `application/settings/development.php` :
```php
define('PHOTOBLUR_DEBUG', true);
```

### Logs
Les logs du module sont visibles dans :
- Navigateur : Console développeur (F12)
- Serveur : Logs PHP selon configuration

---

**Développé pour SocialEngine 7.4** | **Version 1.0.0** | **© 2024**