# Parseur d'Événements - Monde Libertin

Une application web autonome pour parser des pages d'événements et les publier sur le site SocialEngine de Monde Libertin.

## 🚀 Fonctionnalités

- **Parsing automatique** : Extraction intelligente des données d'événements depuis n'importe quelle page web
- **Détection de type de site** : Reconnaissance automatique des sites libertinage, clubs, soirées
- **Interface moderne** : Design responsive et intuitif
- **Gestion CORS** : Contournement des restrictions de cross-origin
- **Publication automatique** : Intégration avec SocialEngine
- **Logs en temps réel** : Suivi complet des opérations

## 📁 Structure du projet

```
event-parser/
├── index.html          # Page principale
├── styles.css          # Styles CSS
├── app.js              # Logique principale de l'application
├── parser.js           # Module de parsing
└── README.md           # Documentation
```

## 🛠️ Installation et déploiement

### Option 1: Hébergement simple (recommandé)

1. **Téléchargez** tous les fichiers dans un dossier sur votre serveur web
2. **Accédez** à `index.html` via votre navigateur
3. **C'est tout !** L'application est prête à utiliser

### Option 2: Hébergement sur un site partenaire

1. **Uploadez** les fichiers dans un sous-dossier de votre site partenaire
2. **Configurez** l'URL de publication dans l'interface
3. **Testez** avec une URL d'événement

### Option 3: Intégration dans SocialEngine

1. **Placez** les fichiers dans le dossier `public/` de votre SocialEngine
2. **Ajoutez** un lien vers `index.html` dans votre menu d'administration
3. **Configurez** les permissions d'accès

## 🎯 Utilisation

### 1. Configuration initiale

1. **Ouvrez** l'application dans votre navigateur
2. **Vérifiez** que l'URL de publication pointe vers `https://mondelibertin.com/events/create`
3. **Ajoutez** votre clé API si nécessaire

### 2. Parsing d'une page

1. **Collez** l'URL de la page d'événement à parser
2. **Cliquez** sur "Parser la page"
3. **Attendez** que l'analyse se termine
4. **Vérifiez** les résultats dans la section "Données parsées"

### 3. Publication sur Monde Libertin

1. **Vérifiez** que vous êtes connecté sur mondelibertin.com
2. **Cliquez** sur "Publier sur Monde Libertin"
3. **Suivez** les logs pour voir le statut de la publication

## 🔧 Configuration avancée

### Personnalisation des sélecteurs

Modifiez le fichier `parser.js` pour ajouter des sélecteurs spécifiques à vos sites partenaires :

```javascript
// Dans la méthode detectSiteType()
if (domain.includes('votresite.com')) {
    return 'custom';
}

// Ajoutez un nouveau parseur
'custom': this.parseCustom,
```

### Ajout de nouveaux types de sites

```javascript
async parseCustom(html, url, options) {
    // Votre logique de parsing personnalisée
    const events = [];
    // ...
    return events;
}
```

### Configuration des proxies CORS

Si vous avez des problèmes de CORS, ajoutez vos propres proxies :

```javascript
const corsProxies = [
    'https://votre-proxy.com/',
    'https://api.allorigins.win/get?url=',
    // vos proxies personnalisés
];
```

## 🔒 Sécurité et authentification

### Authentification SocialEngine

L'application nécessite que vous soyez connecté sur mondelibertin.com pour publier. Assurez-vous que :

1. **Votre session** est active sur le site principal
2. **Vous avez les permissions** pour créer des événements
3. **L'URL de publication** est correcte

### Gestion des clés API

Si votre site utilise une API, configurez la clé dans l'interface :

1. **Entrez** votre clé API dans le champ dédié
2. **La clé** sera utilisée pour toutes les requêtes de publication
3. **Ne partagez jamais** votre clé API publiquement

## 🐛 Dépannage

### Problèmes de CORS

Si le parsing échoue à cause de CORS :

1. **Vérifiez** que l'URL est accessible publiquement
2. **Essayez** une URL différente pour tester
3. **Contactez** l'administrateur du site source

### Erreurs de publication

Si la publication échoue :

1. **Vérifiez** que vous êtes connecté sur mondelibertin.com
2. **Assurez-vous** que l'URL de publication est correcte
3. **Vérifiez** les logs pour plus de détails

### Aucun événement trouvé

Si aucun événement n'est détecté :

1. **Vérifiez** que la page contient bien des informations d'événement
2. **Essayez** d'ajouter des sélecteurs personnalisés
3. **Contactez** le support pour adapter le parseur

## 📊 Logs et monitoring

### Types de logs

- **Info** : Informations générales (bleu)
- **Success** : Opérations réussies (vert)
- **Warning** : Avertissements (jaune)
- **Error** : Erreurs (rouge)

### Surveillance

Les logs sont automatiquement sauvegardés et peuvent être consultés pour :
- **Déboguer** les problèmes
- **Suivre** les performances
- **Analyser** les patterns d'utilisation

## 🔄 Mises à jour

### Mise à jour de l'application

1. **Sauvegardez** vos configurations personnalisées
2. **Remplacez** les fichiers par les nouvelles versions
3. **Testez** que tout fonctionne correctement

### Ajout de nouveaux sites partenaires

1. **Identifiez** les patterns HTML du nouveau site
2. **Ajoutez** les sélecteurs appropriés dans `parser.js`
3. **Testez** avec quelques URLs d'exemple

## 📞 Support

Pour toute question ou problème :

1. **Consultez** les logs de l'application
2. **Vérifiez** cette documentation
3. **Contactez** l'équipe technique

## 📄 Licence

Cette application est développée spécifiquement pour Monde Libertin et ses partenaires.

---

**Version** : 1.0.0  
**Dernière mise à jour** : 2024  
**Compatibilité** : SocialEngine 7.4+