# Event Parser pour MondeLibertie.com

Parser autonome d'événements qui peut être hébergé sur n'importe quel site et publier automatiquement sur mondelibertin.com/events/create.

## 🚀 Fonctionnalités

- **Interface web moderne** avec configuration intuitive
- **Parsing automatique** ou avec sélecteurs CSS personnalisés
- **Authentification** par session, identifiants ou token API
- **Soumission automatique** ou avec confirmation
- **Logs détaillés** pour le debugging
- **Totalement autonome** - n'affecte pas le SocialEngine
- **Portable** - peut tourner sur n'importe quel site

## 📋 Prérequis

- Node.js 16+ (pour le backend optionnel)
- Accès à mondelibertin.com avec compte utilisateur ou partenaire

## 🛠️ Installation

### Option 1: Interface web seule (recommandée pour la plupart des cas)

Simplement hébergez le fichier `event-parser.html` sur votre serveur web :

```bash
# Copiez event-parser.html dans votre dossier web
cp event-parser.html /var/www/html/
```

Accédez ensuite à `https://votresite.com/event-parser.html`

### Option 2: Avec backend Node.js (pour la production)

1. **Installation des dépendances :**
```bash
npm install
```

2. **Configuration des variables d'environnement :**
```bash
# Créez un fichier .env
cat > .env << EOF
PORT=3001
SESSION_SECRET=votre-secret-session-unique
CORS_ORIGINS=https://votresite.com,https://mondelibertin.com
EOF
```

3. **Démarrage du serveur :**
```bash
# Mode production
npm start

# Mode développement (avec auto-reload)
npm run dev
```

## 🔧 Configuration

### Interface Web

1. **URL à parser** : Entrez l'URL de la page d'événement du site partenaire
2. **Authentification** : Choisissez votre méthode :
   - Session existante (si déjà connecté sur mondelibertin.com)
   - Identifiants de connexion
   - Token API (si disponible)
3. **Règles de parsing** :
   - Détection automatique (recommandée)
   - Sélecteurs CSS personnalisés

### Sélecteurs CSS Personnalisés

Si la détection automatique ne fonctionne pas, configurez des sélecteurs CSS :

| Champ | Exemples de sélecteurs |
|-------|----------------------|
| Titre | `h1`, `.event-title`, `.title` |
| Description | `.description`, `.content`, `.summary` |
| Date | `.date`, `time[datetime]`, `.when` |
| Lieu | `.location`, `.venue`, `.address` |
| Prix | `.price`, `.cost`, `.fee` |

## 🚦 Utilisation

### Workflow Standard

1. **Configuration** : Remplissez l'URL et choisissez votre authentification
2. **Test de parsing** : Cliquez sur "🔍 Tester le parsing" pour vérifier
3. **Test de connexion** : Vérifiez l'accès à mondelibertin.com
4. **Processus complet** : Lancez le parsing et la soumission

### API Backend (si utilisé)

```javascript
// Test de statut
GET /api/status

// Connexion utilisateur
POST /api/login
{
  "username": "votre-email@exemple.com",
  "password": "votre-mot-de-passe"
}

// Parser une URL
POST /api/parse
{
  "url": "https://site-partenaire.com/event/123",
  "selectors": {
    "title": "h1",
    "description": ".content"
  }
}

// Processus complet
POST /api/process-event
{
  "url": "https://site-partenaire.com/event/123",
  "autoSubmit": true
}
```

## 🔒 Sécurité

- **Sessions sécurisées** avec cookies httpOnly
- **Tokens CSRF** pour toutes les soumissions
- **Validation des données** avant soumission
- **Logs d'audit** pour traçabilité

## 🌐 Déploiement

### Sur n'importe quel serveur web

```bash
# Apache/Nginx
cp event-parser.html /var/www/html/

# Avec backend
cp -r * /var/www/html/
cd /var/www/html/
npm install --production
pm2 start event-parser-backend.js
```

### Variables d'environnement de production

```bash
export PORT=3001
export SESSION_SECRET="un-secret-tres-long-et-unique"
export CORS_ORIGINS="https://monsite.com,https://mondelibertin.com"
export NODE_ENV=production
```

## 🐛 Résolution de problèmes

### Erreurs communes

1. **"Erreur CORS"** : Ajoutez votre domaine aux CORS_ORIGINS
2. **"Session expirée"** : Reconnectez-vous sur mondelibertin.com
3. **"Parsing échoué"** : Vérifiez les sélecteurs CSS
4. **"Token CSRF non trouvé"** : Structure de formulaire modifiée

### Debug

Activez les logs détaillés dans l'interface pour voir :
- Requêtes HTTP
- Sélecteurs utilisés
- Données extraites
- Erreurs détaillées

## 📈 Monitoring

Le backend expose plusieurs endpoints de monitoring :

```bash
# Statut du service
curl http://localhost:3001/api/status

# Sessions actives
curl http://localhost:3001/api/check-session
```

## 🔄 Mise à jour

Pour mettre à jour le parser :

```bash
# Arrêter le service
pm2 stop event-parser-backend

# Sauvegarder la configuration
cp .env .env.backup

# Télécharger la nouvelle version
wget https://votresite.com/event-parser.html

# Redémarrer
pm2 start event-parser-backend.js
```

## 📞 Support

En cas de problème :

1. Vérifiez les logs du navigateur (F12)
2. Consultez les logs du serveur (`pm2 logs`)
3. Testez avec un événement simple
4. Vérifiez la connectivité à mondelibertin.com

## 📝 Licence

MIT License - Vous pouvez utiliser, modifier et distribuer librement.

---

**Note importante** : Ce parser est autonome et n'affecte pas l'installation SocialEngine existante. Il peut être hébergé sur n'importe quel site ayant accès web à mondelibertin.com.

