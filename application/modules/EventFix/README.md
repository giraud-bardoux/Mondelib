# EventFix Plugin pour SocialEngine 7.4

Plugin qui étend la plage d'années sélectionnable dans le champ "Date de début" du module Event pour permettre la sélection de dates jusqu'à 5 ans en arrière.

## Fonctionnalités

- ✅ Étend automatiquement la plage d'années des formulaires Event (Event_Form_Create et Event_Form_Edit)
- ✅ Ajoute dynamiquement 5 années supplémentaires en arrière (configurable)
- ✅ Compatible avec SocialEngine 7.x self-hosted
- ✅ Ne modifie PAS le code du module Event existant
- ✅ Compatible avec les mises à jour officielles de SocialEngine
- ✅ Fonctionne avec les formulaires chargés dynamiquement (AJAX)
- ✅ Détection intelligente des formulaires Event

## Installation

### Étape 1 : Copier les fichiers

Copiez le dossier `EventFix` dans le répertoire des modules :

```bash
cp -r EventFix/ /chemin/vers/votre/socialengine/application/modules/
```

### Étape 2 : Définir les permissions

Assurez-vous que les permissions sont correctes :

```bash
chmod -R 755 application/modules/EventFix/
chown -R www-data:www-data application/modules/EventFix/
```

### Étape 3 : Installation via l'interface d'administration

1. Connectez-vous à l'administration de votre site SocialEngine
2. Allez dans **Admin Panel** > **Plugins** (ou **Modules**)
3. Trouvez "Event Date Range Fix" dans la liste des modules disponibles
4. Cliquez sur **Install** 
5. Activez le module en cliquant sur **Enable**

### Étape 4 : Vérification

1. Allez sur une page de création ou d'édition d'événement
2. Vérifiez que le sélecteur d'année dans le champ "Date de début" permet maintenant de sélectionner des années 5 ans en arrière
3. Ouvrez la console du navigateur (F12) pour voir les messages de confirmation du plugin

## Fonctionnement technique

### Approche non-intrusive

Le plugin utilise une approche JavaScript côté client pour modifier dynamiquement les formulaires Event :

1. **Injection de script** : Le plugin injecte un script JavaScript via l'événement `onRenderLayoutDefault`
2. **Détection intelligente** : Le script recherche automatiquement les éléments de formulaire liés aux dates d'événements
3. **Modification dynamique** : Les options d'année sont étendues pour inclure 5 années supplémentaires en arrière
4. **Compatibilité AJAX** : Utilise `MutationObserver` pour détecter les formulaires chargés dynamiquement

### Sélecteurs supportés

Le plugin recherche automatiquement ces types d'éléments :

- `select[name="starttime[year]"]`
- `select[name="starttime-year"]`
- `select[id*="starttime"][id*="year"]`
- `select[class*="starttime"][class*="year"]`
- `.form-element select[name*="starttime"]`
- `#starttime-element select`
- `form[class*="event"] select[name*="year"]`
- `form[id*="event"] select[name*="year"]`

## Configuration

### Modifier la plage d'années

Pour changer le nombre d'années en arrière (par défaut 5), modifiez cette ligne dans `Plugin/Core.php` :

```javascript
var minYear = currentYear - 5; // Changez 5 par le nombre souhaité
```

## Compatibilité

- ✅ SocialEngine 7.0.x
- ✅ SocialEngine 7.4.x
- ✅ Tous les thèmes SocialEngine
- ✅ Formulaires chargés en AJAX
- ✅ Formulaires Event de tous types

## Dépannage

### Le plugin ne fonctionne pas

1. Vérifiez que le module Event est installé et activé
2. Vérifiez que le plugin EventFix est activé dans l'administration
3. Ouvrez la console du navigateur (F12) pour voir s'il y a des erreurs JavaScript
4. Videz le cache de SocialEngine

### Messages de la console

Le plugin affiche des messages dans la console du navigateur pour confirmer son fonctionnement :

```
EventFix: Étendu la plage d'années pour select[name="starttime[year]"] de 2019 à 2030
```

### Désactiver temporairement

Pour désactiver temporairement le plugin sans le désinstaller :

1. Allez dans **Admin Panel** > **Plugins**
2. Trouvez "Event Date Range Fix"
3. Cliquez sur **Disable**

## Support

Ce plugin a été testé avec SocialEngine 7.4 et devrait fonctionner avec toutes les versions 7.x.

### Logs et débogage

Le plugin ajoute automatiquement des logs dans la console du navigateur. Pour activer le débogage avancé, ajoutez cette ligne au début du script JavaScript :

```javascript
console.log('EventFix: Plugin chargé et initialisé');
```

## Licence

Ce plugin est fourni "tel quel" sans garantie. Utilisez-le à vos propres risques.

## Versions

- **1.0.0** : Version initiale avec support pour 5 ans en arrière