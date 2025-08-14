# Importateur de Soirées → Mondelibertin

## 📋 Description

Outil d'importation automatique des informations de soirées depuis des sites partenaires vers mondelibertin.com.

## 🚀 Solution au problème "Failed to fetch"

Le problème "Failed to fetch" était causé par les restrictions CORS (Cross-Origin Resource Sharing) qui empêchent les requêtes directes depuis le navigateur vers des domaines externes.

### Solutions implémentées :

1. **Proxy PHP** (`proxy_fetch.php`) - Pour environnements avec PHP
2. **Proxy Node.js** (`proxy_server.js`) - Alternative pour environnements Node.js

## 📁 Fichiers

- `mondelibertin_event_publisher.html` - Interface d'importation avec parser pour wemagnifique.fr
- `proxy_fetch.php` - Proxy PHP pour contourner CORS
- `proxy_server.js` - Serveur proxy Node.js (alternative)

## 🔧 Installation et utilisation

### Option 1 : Avec PHP

```bash
# Démarrer un serveur PHP local
php -S localhost:8080

# Accéder à l'interface
http://localhost:8080/mondelibertin_event_publisher.html
```

### Option 2 : Avec Node.js

```bash
# Démarrer le serveur proxy Node.js
node proxy_server.js

# Accéder à l'interface
http://localhost:8080/mondelibertin_event_publisher.html
```

## ✨ Fonctionnalités

### Parser pour wemagnifique.fr
- ✅ Extraction automatique du titre de la soirée
- ✅ Récupération de la description complète
- ✅ Détection des dates (incluant les événements récurrents comme "tous les mardis")
- ✅ Extraction du lieu/adresse
- ✅ Récupération de l'image de couverture
- ✅ Catégorisation automatique

### Sites supportés
- `wemagnifique.fr` - Parser complet implémenté
- Possibilité d'ajouter facilement d'autres sites partenaires

## 🔒 Sécurité

- Liste blanche des domaines autorisés
- Validation des URLs
- Headers CORS configurés correctement
- Protection contre les injections

## 📝 Utilisation

1. **Ouvrir l'interface** dans votre navigateur
2. **Coller l'URL** de la soirée à importer (ex: https://wemagnifique.fr/soirees-libertines-paris/tous-les-mardis/)
3. **Cliquer sur "Analyser"** pour extraire les informations
4. **Vérifier les données** extraites dans l'aperçu
5. **Cliquer sur "Publier"** pour envoyer vers Mondelibertin (nécessite d'être connecté)

## 🛠️ Personnalisation

### Ajouter un nouveau site partenaire

1. Ajouter le domaine dans la liste autorisée du proxy
2. Créer une fonction de parsing dans `mondelibertin_event_publisher.html` :

```javascript
function parseNouveauSite(doc, url){
  // Votre logique d'extraction
  return {
    title: "...",
    description: "...",
    startDate: "...",
    endDate: "...",
    location: "...",
    imageUrl: "...",
    category: "..."
  };
}
```

3. Ajouter la condition dans `parsePartnerSite()` :

```javascript
if(hostname.includes('nouveau-site.com')){
  return parseNouveauSite(doc, url);
}
```

## ⚠️ Notes importantes

- Vous devez être connecté à Mondelibertin dans le même navigateur pour publier
- Le parser s'adapte automatiquement aux soirées récurrentes (calcul de la prochaine date)
- Les images sont converties en URLs absolues automatiquement

## 🐛 Résolution de problèmes

| Problème | Solution |
|----------|----------|
| "Failed to fetch" | Vérifier que le proxy est bien démarré |
| "Domaine non autorisé" | Ajouter le domaine dans la liste blanche du proxy |
| "Accès refusé" sur Mondelibertin | Se connecter d'abord sur mondelibertin.com |
| Données manquantes | Vérifier/adapter les sélecteurs CSS dans le parser |

## 📧 Support

Pour toute question ou amélioration, n'hésitez pas à modifier les parsers selon vos besoins spécifiques.