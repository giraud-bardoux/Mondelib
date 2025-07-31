# Mémorisation de Recherche des Membres - SocialEngine 7.4

## Description

Cette fonctionnalité permet de mémoriser automatiquement les paramètres de recherche des membres sur votre site SocialEngine 7.4. Les utilisateurs n'auront plus besoin de refaire leurs recherches à chaque fois qu'ils visitent la page de recherche.

## Fonctionnalités

- ✅ **Sauvegarde automatique** : Les paramètres de recherche sont sauvegardés automatiquement dans les cookies
- ✅ **Restauration automatique** : Les paramètres sont restaurés au chargement de la page
- ✅ **Durée de conservation** : Les recherches sont mémorisées pendant 30 jours
- ✅ **Bouton d'effacement** : Possibilité d'effacer la recherche mémorisée
- ✅ **Notifications visuelles** : Feedback utilisateur avec des notifications
- ✅ **Responsive design** : Compatible avec tous les appareils
- ✅ **Pas de modification du code SocialEngine** : Utilise uniquement JavaScript et cookies

## Fichiers installés

1. **`public/js/user-search-memory.js`** - Script principal de mémorisation
2. **`public/css/user-search-memory.css`** - Styles pour l'interface
3. **`application/modules/User/views/scripts/index/browse.tpl`** - Template modifié

## Comment ça fonctionne

### Sauvegarde automatique
- Quand un utilisateur modifie un champ de recherche, les paramètres sont automatiquement sauvegardés
- Quand un utilisateur soumet le formulaire de recherche, les paramètres sont sauvegardés
- Les données sont stockées dans un cookie nommé `user_search_memory`

### Restauration automatique
- Au chargement de la page de recherche, les paramètres mémorisés sont automatiquement restaurés
- L'utilisateur voit ses critères de recherche précédents pré-remplis

### Interface utilisateur
- Un bouton "Effacer la recherche mémorisée" apparaît à côté du bouton de recherche
- Une icône 💾 apparaît sur le formulaire quand une recherche est mémorisée
- Des notifications apparaissent lors des actions (sauvegarde, effacement)

## Paramètres mémorisés

Le système mémorise tous les champs du formulaire de recherche :
- Nom d'affichage
- Type de membre
- Localisation
- Coordonnées GPS (lat/lng)
- Rayon de recherche
- Membres avec photos uniquement
- Membres en ligne uniquement
- Tous les champs personnalisés

## Configuration

### Durée de conservation
Pour modifier la durée de conservation (actuellement 30 jours), modifiez la constante dans `user-search-memory.js` :

```javascript
const COOKIE_EXPIRY_DAYS = 30; // Changez cette valeur
```

### Nom du cookie
Pour modifier le nom du cookie, changez la constante :

```javascript
const COOKIE_NAME = 'user_search_memory'; // Changez cette valeur
```

## Dépannage

### Le script ne fonctionne pas
1. Vérifiez que les fichiers sont bien placés dans les bons dossiers
2. Vérifiez que les permissions des fichiers sont correctes
3. Vérifiez la console du navigateur pour les erreurs JavaScript

### Les cookies ne sont pas sauvegardés
1. Vérifiez que les cookies sont activés dans le navigateur
2. Vérifiez que le domaine est correct
3. Vérifiez la console pour les erreurs

### Le bouton d'effacement n'apparaît pas
1. Vérifiez que le CSS est bien chargé
2. Vérifiez que le formulaire de recherche existe sur la page
3. Vérifiez la console pour les erreurs

## Compatibilité

- ✅ SocialEngine 7.4
- ✅ Tous les navigateurs modernes
- ✅ Mobile et desktop
- ✅ Tous les thèmes SocialEngine

## Support

Pour toute question ou problème, vérifiez :
1. La console du navigateur (F12)
2. Les logs d'erreur du serveur
3. Que tous les fichiers sont bien installés

## Sécurité

- Les données sont stockées localement dans les cookies du navigateur
- Aucune donnée sensible n'est transmise
- Le système respecte la vie privée des utilisateurs
- Les cookies expirent automatiquement après 30 jours

## Mise à jour

Pour mettre à jour le système :
1. Remplacez les fichiers existants par les nouvelles versions
2. Videz le cache du navigateur
3. Testez la fonctionnalité

## Licence

Ce code est fourni "tel quel" sans garantie. Utilisez à vos propres risques.