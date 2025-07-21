# Module PhotoBlur pour SocialEngine 7.4

## Description

Le module PhotoBlur permet de flouter automatiquement les photos des membres et des albums pour les visiteurs non connectés à votre site SocialEngine. Il offre également une protection contre la sauvegarde d'images et les captures d'écran.

## Fonctionnalités

- ✅ **Floutage automatique** : Les photos des utilisateurs et des albums sont automatiquement floutées pour les visiteurs non connectés
- ✅ **Protection contre la sauvegarde** : Empêche le clic droit, le glisser-déposer et les raccourcis clavier de sauvegarde
- ✅ **Protection mobile** : Empêche les appuis longs et les gestes de sauvegarde sur mobile
- ✅ **Message d'incitation** : Affiche "Connectez-vous pour ne plus voir flou" au survol des images
- ✅ **Protection contre les captures d'écran** : Détection basique et protection renforcée
- ✅ **Multilingue** : Support français et anglais
- ✅ **Compatible SocialEngine 7.4** : Intégration native avec l'architecture SocialEngine

## Installation

### 1. Téléchargement du module

Placez le dossier `PhotoBlur` dans le répertoire `application/modules/` de votre installation SocialEngine.

### 2. Installation via l'interface d'administration

1. Connectez-vous à votre panneau d'administration SocialEngine
2. Allez dans **Manage** → **Packages**
3. Trouvez le module "PhotoBlur" dans la liste
4. Cliquez sur **Install** puis **Enable**

### 3. Configuration

Le module fonctionne immédiatement après l'installation avec les paramètres par défaut :
- Floutage activé pour les visiteurs non connectés
- Intensité du flou : 10px
- Protection contre les captures d'écran activée
- Message de connexion affiché

## Utilisation

### Fonctionnement automatique

Le module fonctionne automatiquement une fois installé :

1. **Visiteurs non connectés** : Voient toutes les photos d'utilisateurs et d'albums floutées
2. **Utilisateurs connectés** : Voient toutes les photos normalement, sans flou

### Photos concernées

- Photos de profil des utilisateurs
- Photos de couverture
- Photos dans les albums
- Toutes les images liées au module Storage de SocialEngine

### Protection contre la sauvegarde

Le module empêche :
- Clic droit sur les images
- Glisser-déposer des images
- Raccourcis clavier (Ctrl+S, Ctrl+C, etc.)
- Appui long sur mobile
- Impression des images
- Accès via les outils de développement (détection basique)

## Aspects techniques

### Structure des fichiers

```
application/modules/PhotoBlur/
├── Bootstrap.php                      # Initialisation du module
├── Plugin/
│   └── Core.php                      # Logique principale
├── View/Helper/
│   └── ItemBackgroundPhoto.php      # Helper de vue surchargé
├── externals/
│   ├── scripts/
│   │   └── photoblur.js             # Protection JavaScript
│   └── styles/
│       └── photoblur.css            # Styles de floutage
├── settings/
│   ├── manifest.php                 # Configuration du module
│   └── install.php                  # Script d'installation
└── README.md                        # Documentation
```

### Hooks utilisés

- `onRenderLayoutDefault` : Injection du CSS/JS
- `onItemPhotoRender` : Traitement des photos

### Classes CSS appliquées

- `.photoblur-blurred` : Effet de flou
- `.photoblur-protected` : Protection contre la sélection
- `.photoblur-container` : Conteneur avec tooltip

## Limitations

⚠️ **Important** : Aucune protection n'est 100% infaillible contre les utilisateurs déterminés avec des connaissances techniques avancées. Ce module offre une protection raisonnable pour décourager la plupart des tentatives de sauvegarde.

### Limitations techniques

1. **Outils de développement** : Les utilisateurs avancés peuvent toujours accéder aux sources via les outils de développement du navigateur
2. **Désactivation JavaScript** : Si JavaScript est désactivé, seule la protection CSS reste active
3. **Captures d'écran** : Les captures d'écran complètes de l'écran restent possibles
4. **Cache du navigateur** : Les images peuvent rester en cache temporairement

## Compatibilité

- **SocialEngine** : Version 7.4 et supérieure
- **PHP** : 7.0 et supérieure
- **Navigateurs** : Tous les navigateurs modernes (Chrome, Firefox, Safari, Edge)
- **Mobile** : iOS et Android

## Support et développement

Ce module a été développé pour répondre aux besoins spécifiques de protection des photos sur SocialEngine 7.4.

### Personnalisation

Vous pouvez modifier :
- L'intensité du flou dans `photoblur.css`
- Les messages dans les fichiers de traduction
- Les règles de protection dans `photoblur.js`
- La logique de détection dans `Plugin/Core.php`

## Licence

Licence personnalisée - Utilisation autorisée pour ce projet spécifique.