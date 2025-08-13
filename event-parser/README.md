# 🎉 Parseur d'Événements Automatique pour MondeLiberin.com

## Description

Cette application web autonome permet de parser automatiquement des événements depuis des sites partenaires et de les publier directement sur MondeLiberin.com (ou tout site utilisant SocialEngine 7.4).

### ✨ Fonctionnalités principales

- **Parsing automatique** d'événements depuis différentes sources (Meetup, Facebook Events, Eventbrite, sites personnalisés)
- **Publication automatique** sur MondeLiberin.com via l'interface SocialEngine
- **Interface utilisateur moderne** et intuitive
- **Historique** des événements parsés
- **Configuration flexible** pour différents sites partenaires
- **Système de cache** et de session pour optimiser les performances
- **100% autonome** - Peut être hébergé sur n'importe quel serveur PHP

## 📋 Prérequis

- Serveur web avec PHP 7.2 ou supérieur
- Extension cURL PHP activée
- Accès en écriture au dossier temporaire du serveur
- Un compte valide sur MondeLiberin.com avec les droits de création d'événements

## 🚀 Installation

### 1. Téléchargement des fichiers

Téléchargez les 3 fichiers suivants dans un dossier de votre serveur web :
- `index.html` - Interface utilisateur
- `parser.js` - Logique JavaScript
- `proxy.php` - Serveur proxy PHP

### 2. Configuration du serveur

Assurez-vous que votre serveur web peut exécuter des scripts PHP et que l'extension cURL est activée.

Pour Apache, créez un fichier `.htaccess` dans le dossier :
```apache
Options +FollowSymLinks
RewriteEngine On

# Autoriser l'accès aux fichiers
<Files "proxy.php">
    Order allow,deny
    Allow from all
</Files>

# Headers CORS si nécessaire
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
```

Pour Nginx, ajoutez dans votre configuration :
```nginx
location /event-parser/ {
    try_files $uri $uri/ /index.html;
    
    location ~ \.php$ {
        fastcgi_pass php-fpm:9000;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### 3. Permissions

Assurez-vous que PHP peut écrire dans le dossier temporaire :
```bash
chmod 755 proxy.php
```

## 💻 Utilisation

### Configuration initiale

1. **Accédez à la page** via votre navigateur : `https://votresite.com/event-parser/`

2. **Configurez vos identifiants** :
   - Allez dans l'onglet "⚙️ Configuration"
   - Entrez votre nom d'utilisateur et mot de passe MondeLiberin
   - Sélectionnez une catégorie par défaut
   - Sauvegardez la configuration

### Parser un événement

1. **Copiez l'URL** de l'événement depuis le site source
2. **Collez l'URL** dans le champ "URL de l'événement"
3. **Cliquez sur "Parser l'événement"**
4. **Vérifiez les informations** dans l'aperçu
5. **Modifiez si nécessaire** en cliquant sur "Modifier"
6. **Publiez** en cliquant sur "Publier sur MondeLiberin"

### Sites supportés

Le parseur supporte automatiquement :
- **Meetup.com** - Événements publics
- **Facebook Events** - Événements publics
- **Eventbrite** - Tous les événements
- **Sites personnalisés** - Via configuration

Pour ajouter un site personnalisé, le parseur recherche automatiquement :
- Les métadonnées Open Graph
- Les données structurées JSON-LD
- Les balises HTML standards (h1, time, address)

## 🔧 Configuration avancée

### Personnalisation du proxy

Modifiez le fichier `proxy.php` pour adapter les URLs :

```php
// Dans proxy.php, ligne 106
$loginUrl = $input['url'] ?? 'https://mondelibertin.com/login';

// Changez pour votre site
$loginUrl = $input['url'] ?? 'https://votresite.com/login';
```

### Ajout de nouveaux parseurs

Pour ajouter le support d'un nouveau site, modifiez `parser.js` :

```javascript
// Ajoutez une nouvelle fonction de parsing
function parseMonSite(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    const event = {
        title: doc.querySelector('.titre-event')?.textContent,
        date: doc.querySelector('.date-event')?.textContent,
        // ... autres champs
    };
    
    return event;
}

// Ajoutez le cas dans parseEvent()
case 'monsite':
    eventData = parseMonSite(html);
    break;
```

### Adaptation pour d'autres plateformes SocialEngine

Pour utiliser avec un autre site SocialEngine, modifiez la configuration dans `parser.js` :

```javascript
const CONFIG = {
    proxyUrl: 'proxy.php',
    mondeLibertin: {
        baseUrl: 'https://votresite.com',
        loginUrl: '/login',  // ou '/user/login'
        eventCreateUrl: '/events/create',
        eventSubmitUrl: '/events/create/submit'
    }
};
```

## 🔒 Sécurité

### Recommandations importantes

1. **Ne jamais exposer publiquement** cette application sans authentification
2. **Utilisez HTTPS** pour toutes les communications
3. **Limitez l'accès** via .htaccess ou configuration serveur :

```apache
# .htaccess pour restreindre l'accès
AuthType Basic
AuthName "Accès restreint"
AuthUserFile /chemin/vers/.htpasswd
Require valid-user
```

4. **Changez régulièrement** vos mots de passe
5. **Surveillez les logs** pour détecter toute activité suspecte

### Protection contre les abus

Le proxy PHP inclut plusieurs protections :
- Validation des URLs
- Limitation du temps d'exécution (30 secondes)
- Nettoyage automatique des sessions expirées
- Filtrage des entrées utilisateur

## 📝 Structure des données

### Format d'événement

```javascript
{
    title: "Titre de l'événement",
    date: "2024-01-15T20:00:00",
    location: "Paris, France",
    description: "Description complète",
    category: "soiree",
    source: "Meetup",
    organizer: "Nom de l'organisateur"
}
```

### Catégories disponibles

- `soiree` - Soirée
- `club` - Club
- `rencontre` - Rencontre
- `evenement` - Événement spécial

## 🐛 Dépannage

### Erreur "Impossible de récupérer la page"

- Vérifiez que cURL est activé sur votre serveur
- Vérifiez les permissions du fichier proxy.php
- Testez avec une URL simple comme Google.com

### Erreur "Échec de l'authentification"

- Vérifiez vos identifiants
- Assurez-vous que votre compte a les droits nécessaires
- Vérifiez que le site cible est accessible

### Les événements ne se publient pas

- Vérifiez la structure du formulaire sur le site cible
- Consultez les logs PHP pour les erreurs
- Testez manuellement la création d'événement sur le site

## 📊 Logs et débogage

Pour activer les logs de débogage, modifiez `proxy.php` :

```php
// Ligne 9
ini_set('display_errors', 1);  // Changez 0 en 1

// Ajoutez pour logger les requêtes
error_log("Action: " . $input['action']);
error_log("URL: " . $input['url']);
```

Les logs seront disponibles dans le fichier d'erreur PHP de votre serveur.

## 🤝 Support et contribution

Pour toute question ou problème :
1. Vérifiez d'abord cette documentation
2. Consultez les logs d'erreur
3. Testez avec le bouton "Tester le parsing"

## ⚖️ Licence et responsabilité

Cette application est fournie "telle quelle" sans garantie. L'utilisateur est responsable :
- Du respect des conditions d'utilisation des sites sources
- De la véracité des informations publiées
- Du respect de la vie privée et des données personnelles
- De l'utilisation éthique de l'outil

## 🔄 Mises à jour

### Version 1.0.0 (Janvier 2024)
- Version initiale
- Support Meetup, Facebook, Eventbrite
- Interface moderne
- Système de cache et session

### Futures améliorations prévues
- Support de plus de sites
- Import par lot (CSV, JSON)
- Planification automatique
- API REST
- Notifications par email

---

**Note importante** : Cette application est conçue pour faciliter la gestion d'événements légitimes. Toute utilisation abusive ou non autorisée est strictement interdite.