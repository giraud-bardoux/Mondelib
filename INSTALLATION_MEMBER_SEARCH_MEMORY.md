# Installation de la mémorisation de recherche de membres pour SocialEngine 7.4

## Description
Cette fonctionnalité permet de sauvegarder automatiquement les paramètres de recherche de membres dans un cookie et de les restaurer lors de la prochaine visite.

## Fichiers créés

### Fichiers principaux
1. `/externals/scripts/member-search-memory.js` - Le script JavaScript principal
2. Modifications dans `/application/themes/harmony/harmony-custom.css` - Styles pour le bouton de réinitialisation

### Module (optionnel)
Si vous utilisez la méthode A :
- `/application/modules/Membersearchmemory/` - Module complet
- `/application/languages/en/membersearchmemory.csv` - Traductions anglaises
- `/application/languages/fr/membersearchmemory.csv` - Traductions françaises

## Installation

Il y a deux méthodes d'installation : via module (recommandé) ou manuelle.

### Méthode A : Installation via Module (Recommandé)

Cette méthode installe un module qui charge automatiquement le script sans modifier les fichiers core.

#### Étape 1 : Installation du module
1. Connectez-vous au panneau d'administration
2. Allez dans "Manage" > "Packages & Plugins"
3. Cliquez sur "Install New Packages"
4. Le module "Member Search Memory" devrait apparaître
5. Cliquez sur "Install" puis "Enable"

#### Étape 2 : Vider le cache
1. Allez dans "Manage" > "Cache"
2. Cliquez sur "Clear All Caches"

### Méthode B : Installation Manuelle

Si vous préférez ne pas installer de module, vous pouvez inclure le script manuellement.

#### Étape 1 : Inclure le script JavaScript
Vous devez ajouter une ligne pour charger le script JavaScript. Il y a deux méthodes :

#### Méthode 1 : Via le layout principal (Recommandé)
Éditez le fichier `/application/modules/Core/layouts/scripts/default.tpl`

Trouvez la ligne (environ ligne 435) :
```php
<?php echo $this->headScript()->toString()."\n" ?>
```

Ajoutez juste après :
```html
<!-- Script de mémorisation de recherche de membres -->
<script src="<?php echo $this->layout()->staticBaseUrl ?>externals/scripts/member-search-memory.js"></script>
```

#### Méthode 2 : Via le widget de recherche
Éditez le fichier `/application/modules/User/widgets/browse-search/index.tpl`

Ajoutez à la fin du fichier :
```html
<script src="<?php echo $this->layout()->staticBaseUrl ?>externals/scripts/member-search-memory.js"></script>
```

### Étape 2 : Vider le cache
1. Connectez-vous au panneau d'administration
2. Allez dans "Manage" > "Cache" 
3. Cliquez sur "Clear All Caches"

### Étape 3 : Tester
1. Allez sur la page de recherche de membres
2. Effectuez une recherche avec des critères
3. Rafraîchissez la page - les critères devraient être restaurés
4. Un bouton "Réinitialiser la recherche" devrait apparaître

## Fonctionnalités

### Sauvegarde automatique
- Les paramètres de recherche sont sauvegardés automatiquement lors de :
  - La soumission du formulaire
  - Le changement de valeur dans les champs
  - L'utilisation de la recherche AJAX

### Restauration automatique
- Les paramètres sont restaurés automatiquement au chargement de la page
- Le cookie expire après 30 jours

### Bouton de réinitialisation
- Apparaît uniquement quand des paramètres sont sauvegardés
- Permet de revenir aux paramètres par défaut
- Supprime le cookie de sauvegarde

## Personnalisation

### Modifier la durée d'expiration du cookie
Dans le fichier `member-search-memory.js`, modifiez la ligne :
```javascript
const COOKIE_EXPIRY_DAYS = 30; // Changez 30 par le nombre de jours souhaité
```

### Modifier les styles du bouton
Les styles sont dans `/application/themes/harmony/harmony-custom.css`
Vous pouvez modifier les couleurs, tailles, etc.

## Dépannage

### Le script ne fonctionne pas
1. Vérifiez que le script est bien chargé (F12 > Network)
2. Vérifiez la console pour des erreurs JavaScript
3. Assurez-vous d'avoir vidé le cache

### Les paramètres ne sont pas sauvegardés
1. Vérifiez que les cookies sont activés dans le navigateur
2. Vérifiez que le formulaire a la classe `.layout_user_browse_search`

### Le bouton de réinitialisation n'apparaît pas
1. Vérifiez que des paramètres sont bien sauvegardés dans le cookie
2. Vérifiez que Font Awesome est chargé sur votre site

## Support
Pour toute question ou problème, vérifiez :
1. La console JavaScript du navigateur (F12)
2. Que tous les fichiers sont bien présents
3. Que le cache a été vidé