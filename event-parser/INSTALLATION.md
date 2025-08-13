# 🚀 Guide d'Installation Rapide - Parseur d'Événements

## 📋 Prérequis

- Serveur web (Apache/Nginx)
- PHP 7.4+ (pour l'intégration SocialEngine)
- Navigateur web moderne
- Accès administrateur au serveur

## ⚡ Installation Express

### Option 1: Installation automatique (recommandée)

```bash
# 1. Télécharger le projet
git clone [URL_DU_REPO] event-parser
cd event-parser

# 2. Rendre le script exécutable
chmod +x deploy.sh

# 3. Lancer l'installation automatique
./deploy.sh --auto
```

### Option 2: Installation manuelle

```bash
# 1. Copier les fichiers dans votre répertoire web
cp -r event-parser/ /var/www/html/

# 2. Définir les permissions
chmod -R 755 /var/www/html/event-parser/
chown -R www-data:www-data /var/www/html/event-parser/

# 3. Redémarrer Apache
sudo systemctl restart apache2
```

## 🌐 Configuration

### 1. Accès à l'application

Ouvrez votre navigateur et allez sur :
```
http://votre-domaine.com/event-parser/
```

### 2. Configuration initiale

1. **URL de publication** : `https://mondelibertin.com/events/create`
2. **Clé API** : (optionnel, selon votre configuration)
3. **Test** : Utilisez `test.html` pour vérifier le fonctionnement

### 3. Intégration SocialEngine

Pour l'intégration complète avec SocialEngine :

1. **Copier** `socialengine-integration.php` dans votre SocialEngine
2. **Configurer** les permissions d'API
3. **Tester** l'authentification

## 🔧 Configuration avancée

### Personnalisation des sélecteurs

Éditez `config.js` pour ajouter vos sites partenaires :

```javascript
partners: {
    sites: [
        {
            name: 'Votre Site Partenaire',
            domain: 'votresite.com',
            type: 'libertinage',
            customSelectors: {
                title: '.event-title',
                date: '.event-date',
                // ...
            }
        }
    ]
}
```

### Configuration Apache

Le fichier `.htaccess` est déjà configuré avec :
- Sécurité renforcée
- Compression GZIP
- Cache optimisé
- Protection contre les attaques

### Variables d'environnement

Créez un fichier `.env` (optionnel) :

```env
SOCIALENGINE_URL=https://mondelibertin.com
API_KEY=votre_cle_api
DEBUG_MODE=false
```

## 🧪 Tests

### Test automatique

Accédez à la page de test :
```
http://votre-domaine.com/event-parser/test.html
```

### Test manuel

1. **Parsing** : Entrez une URL d'événement
2. **Vérification** : Contrôlez les données extraites
3. **Publication** : Testez l'envoi vers SocialEngine

## 🔒 Sécurité

### Authentification

L'application nécessite que vous soyez connecté sur mondelibertin.com pour publier.

### Permissions

- **Lecture** : Tous les utilisateurs
- **Écriture** : Utilisateurs authentifiés uniquement
- **Administration** : Administrateurs SocialEngine

### Protection

- Protection XSS
- Protection CSRF
- Validation des entrées
- Sanitisation des données

## 📊 Monitoring

### Logs

Les logs sont disponibles dans :
- **Application** : Interface intégrée
- **Serveur** : `/var/log/apache2/error.log`
- **Intégration** : `logs/integration.log`

### Métriques

- Nombre d'événements parsés
- Taux de succès de publication
- Temps de réponse
- Erreurs rencontrées

## 🚨 Dépannage

### Problèmes courants

#### 1. Erreur CORS
```
Solution : Vérifiez que les proxies CORS sont accessibles
```

#### 2. Parsing échoue
```
Solution : Vérifiez la structure HTML de la page source
```

#### 3. Publication échoue
```
Solution : Vérifiez l'authentification SocialEngine
```

#### 4. Permissions refusées
```bash
# Solution
sudo chown -R www-data:www-data /var/www/html/event-parser/
sudo chmod -R 755 /var/www/html/event-parser/
```

### Commandes utiles

```bash
# Vérifier les permissions
ls -la /var/www/html/event-parser/

# Vérifier les logs Apache
tail -f /var/log/apache2/error.log

# Tester l'application
curl -I http://localhost/event-parser/

# Redémarrer Apache
sudo systemctl restart apache2
```

## 📞 Support

### Documentation

- **README.md** : Documentation complète
- **INSTALLATION.md** : Ce guide
- **test.html** : Tests intégrés

### Contact

Pour toute question :
1. Consultez les logs de l'application
2. Vérifiez cette documentation
3. Contactez l'équipe technique

## 🔄 Mises à jour

### Mise à jour automatique

```bash
# Sauvegarder la configuration
cp config.js config.js.backup

# Mettre à jour les fichiers
git pull origin main

# Restaurer la configuration
cp config.js.backup config.js
```

### Mise à jour manuelle

1. **Sauvegarder** vos configurations
2. **Remplacer** les fichiers
3. **Tester** le fonctionnement
4. **Restaurer** les configurations personnalisées

## ✅ Checklist d'installation

- [ ] Fichiers copiés dans le répertoire web
- [ ] Permissions configurées
- [ ] Apache redémarré
- [ ] Application accessible via navigateur
- [ ] Tests passés avec succès
- [ ] Configuration SocialEngine effectuée
- [ ] Logs fonctionnels
- [ ] Sécurité activée

## 🎯 Prochaines étapes

1. **Configurer** vos sites partenaires
2. **Tester** avec des URLs réelles
3. **Former** les utilisateurs
4. **Monitorer** les performances
5. **Optimiser** selon les besoins

---

**Version** : 1.0.0  
**Dernière mise à jour** : 2024  
**Compatibilité** : SocialEngine 7.4+