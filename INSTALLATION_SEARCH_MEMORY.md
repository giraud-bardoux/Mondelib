# Système de mémorisation des recherches de membres pour SocialEngine 7.4

## Description

Ce script JavaScript permet de mémoriser automatiquement les critères de recherche de membres sur votre site SocialEngine 7.4. Les recherches sont sauvegardées dans des cookies et peuvent être restaurées automatiquement.

## Fonctionnalités

- ✅ **Sauvegarde automatique** des critères de recherche dans des cookies
- ✅ **Restauration automatique** de la dernière recherche (si moins de 24h)
- ✅ **Historique des recherches** avec interface utilisateur
- ✅ **Aucune modification** du code source de SocialEngine
- ✅ **Compatible** avec les recherches AJAX de SocialEngine
- ✅ **Interface intuitive** avec boutons d'action

## Installation

### Étape 1 : Télécharger le script

Le fichier `member-search-memory.js` a été créé dans le dossier `public/js/` de votre installation SocialEngine.

### Étape 2 : Inclure le script dans vos pages

Vous avez plusieurs options pour inclure le script :

#### Option A : Inclusion globale (recommandée)

Ajoutez cette ligne dans le fichier `application/themes/VOTRE_THEME/layouts/layout.tpl` (ou dans le thème par défaut) :

```html
<!-- Avant la fermeture du tag </body> -->
<script type="text/javascript" src="<?php echo $this->baseUrl() ?>/public/js/member-search-memory.js"></script>
```

#### Option B : Inclusion spécifique aux pages de recherche

Si vous préférez charger le script uniquement sur les pages de recherche de membres, ajoutez le script dans le template `application/modules/User/views/scripts/index/browse.tpl` :

```html
<!-- À la fin du fichier, avant le tag </script> existant -->
<script type="text/javascript" src="<?php echo $this->baseUrl() ?>/public/js/member-search-memory.js"></script>
```

#### Option C : Inclusion via le widget

Vous pouvez aussi l'ajouter directement dans le template du widget de recherche `application/modules/User/widgets/browse-search/index.tpl` :

```html
<!-- À la fin du fichier -->
<script type="text/javascript" src="<?php echo $this->baseUrl() ?>/public/js/member-search-memory.js"></script>
```

### Étape 3 : Vérification

1. Visitez la page de recherche de membres (`/members` ou `/user`)
2. Ouvrez les outils de développement de votre navigateur (F12)
3. Recherchez le message : `Member Search Memory: Initialisation du système de mémorisation` dans la console

## Utilisation

### Fonctionnement automatique

- **Sauvegarde** : Les critères de recherche sont automatiquement sauvegardés quand vous :
  - Soumettez le formulaire de recherche
  - Utilisez la fonction de recherche AJAX
  - Modifiez des champs (après 2 secondes d'inactivité)

- **Restauration** : La dernière recherche est automatiquement restaurée quand vous :
  - Revenez sur la page de recherche (dans les 24h)
  - Rechargez la page

### Interface utilisateur

Le script ajoute une interface au-dessus du formulaire de recherche avec :

- **📋 Recherches récentes** : Bouton pour afficher l'historique des 10 dernières recherches
- **🗑️ Effacer** : Bouton pour effacer la dernière recherche sauvegardée

### Historique des recherches

- Cliquez sur "📋 Recherches récentes" pour voir l'historique
- Chaque recherche affiche :
  - Les critères utilisés (nom, lieu, options...)
  - La date et l'heure
  - Un bouton "↻ Appliquer" pour réutiliser ces critères

## Champs pris en charge

Le script mémorise tous les champs du formulaire de recherche :

- **Nom** (`displayname`)
- **Type de membre** (`profile_type`)
- **Lieu** (`location`)
- **Coordonnées** (`lat`, `lng`)
- **Rayon de recherche** (`miles`)
- **Seulement les membres avec photos** (`extra[has_photo]`)
- **Seulement les membres en ligne** (`extra[is_online]`)

## Configuration

Vous pouvez modifier les paramètres en éditant le fichier `member-search-memory.js` :

```javascript
constructor() {
    this.cookieName = 'se_member_search_memory';           // Nom du cookie principal
    this.historyCookieName = 'se_member_search_history';   // Nom du cookie d'historique
    this.maxHistoryItems = 10;                             // Nombre max d'éléments dans l'historique
    this.cookieExpireDays = 30;                           // Durée de vie des cookies (jours)
}
```

## Compatibilité

- ✅ SocialEngine 7.4
- ✅ Tous les navigateurs modernes
- ✅ Recherche AJAX de SocialEngine
- ✅ Thèmes personnalisés
- ✅ Modules tiers (tant qu'ils utilisent les classes CSS standards)

## Dépannage

### Le script ne fonctionne pas

1. Vérifiez que le fichier `member-search-memory.js` est accessible via l'URL : `http://votre-site.com/public/js/member-search-memory.js`
2. Vérifiez la console du navigateur pour des erreurs JavaScript
3. Assurez-vous que les cookies sont activés dans le navigateur

### Les critères ne sont pas restaurés

1. Vérifiez que vous êtes sur la même page (même URL)
2. Vérifiez que moins de 24h se sont écoulées depuis la dernière recherche
3. Videz le cache du navigateur et réessayez

### L'interface ne s'affiche pas

1. Vérifiez que le formulaire de recherche a la classe CSS `field_search_criteria`
2. Vérifiez qu'il n'y a pas de conflits CSS
3. Essayez de désactiver temporairement d'autres scripts/extensions

## Sécurité

- Les données sont stockées localement dans le navigateur (cookies)
- Aucune donnée n'est envoyée vers des serveurs externes
- Les cookies expirent automatiquement après 30 jours
- Compatible avec HTTPS

## Support

Pour toute question ou problème :

1. Vérifiez d'abord la section "Dépannage" ci-dessus
2. Consultez la console du navigateur pour des messages d'erreur
3. Testez sur différents navigateurs
4. Vérifiez que votre thème SocialEngine utilise les classes CSS standards