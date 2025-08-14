# Solutions pour résoudre l'erreur "Failed to fetch"

L'erreur "Failed to fetch" lors de l'utilisation de l'importateur de soirées Mondelibertin est causée par les restrictions **CORS (Cross-Origin Resource Sharing)** des navigateurs modernes.

## 🚀 Solutions recommandées

### 1️⃣ Extension CORS (Solution la plus simple)

#### Pour Chrome/Edge :
1. Installer l'extension **"CORS Unblock"** ou **"CORS Everywhere"**
2. Activer l'extension temporairement
3. Recharger la page de l'importateur
4. Relancer l'analyse

#### Pour Firefox :
1. Installer l'extension **"CORS Everywhere"**
2. Activer l'extension
3. Recharger et relancer

### 2️⃣ Chrome avec sécurité désactivée

1. **Fermer Chrome complètement** (vérifier dans le gestionnaire de tâches)
2. Ouvrir un terminal/invite de commandes
3. Lancer Chrome avec les flags suivants :

```bash
# Windows
"C:\Program Files\Google\Chrome\Application\chrome.exe" --disable-web-security --disable-features=VizDisplayCompositor --user-data-dir=c:\temp\chrome-dev

# Mac
open -n -a /Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --args --disable-web-security --disable-features=VizDisplayCompositor --user-data-dir=/tmp/chrome-dev

# Linux
google-chrome --disable-web-security --disable-features=VizDisplayCompositor --user-data-dir=/tmp/chrome-dev
```

### 3️⃣ Import manuel

Si les solutions automatiques ne fonctionnent pas :

1. Aller sur la page de l'événement (ex: https://wemagnifique.fr/soirees-libertines-paris/tous-les-mardis/)
2. Faire **Ctrl+U** (voir le code source)
3. Copier tout le HTML
4. Dans l'importateur, cliquer sur **"Import manuel"**
5. Coller le HTML et analyser

### 4️⃣ Serveur local avec PHP

Si vous avez accès à un serveur web local :

1. Placer les fichiers dans votre serveur web (Apache/Nginx)
2. Le fichier `proxy.php` sera utilisé automatiquement
3. Lancer l'importateur depuis votre serveur local

## 🔧 Proxies publics

L'importateur essaie automatiquement plusieurs proxies publics :
- AllOrigins (`api.allorigins.win`)
- CodeTabs (`api.codetabs.com`)
- CORS Anywhere (nécessite activation)

## ⚠️ Notes de sécurité

- Les extensions CORS et les flags de sécurité désactivent certaines protections
- Ne les utilisez que temporairement et sur des sites de confiance
- Pensez à les désactiver après utilisation

## 🐛 Dépannage

### L'extension CORS ne fonctionne pas
- Vérifier qu'elle est bien activée
- Recharger la page après activation
- Essayer une autre extension

### Chrome avec sécurité désactivée ne démarre pas
- S'assurer que Chrome est complètement fermé
- Vérifier les chemins dans les commandes
- Essayer de supprimer le dossier temporaire

### Import manuel ne fonctionne pas
- Vérifier que l'URL est correcte
- S'assurer d'avoir copié tout le HTML (Ctrl+A puis Ctrl+C)
- Le parseur doit supporter le site (actuellement : wemagnifique.fr)

## 📋 Sites supportés

Actuellement, l'importateur supporte :
- ✅ **wemagnifique.fr** - Parseur spécialisé
- ✅ **partner-site.com** - Exemple de parseur

Pour ajouter d'autres sites, modifier la fonction `parsePartnerSite()` dans le code.

## 🆘 Support

Si aucune solution ne fonctionne :
1. Vérifier la console du navigateur (F12) pour plus de détails
2. Essayer avec un autre navigateur
3. Contacter l'équipe technique Mondelibertin