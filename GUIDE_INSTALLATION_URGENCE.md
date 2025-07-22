# 🚨 GUIDE D'INSTALLATION D'URGENCE - PhotoFloue

## ⏰ Temps total estimé : 10 minutes

## 🛑 ÉTAPE 1 : NETTOYAGE IMMÉDIAT (2 min)

### A. Accéder à votre serveur
```bash
# Via SSH ou panneau de contrôle de votre hébergeur
cd /chemin/vers/votre/socialengine/
```

### B. Supprimer le module défaillant
```bash
# Supprimer complètement le module PhotoFloue
rm -rf application/modules/PhotoFloue

# Supprimer les fichiers de traduction
rm -f application/languages/fr/photofloue.csv
rm -f application/languages/en/photofloue.csv

# Vider le cache
rm -rf temporary/cache/*
rm -rf temporary/compile/*
```

### C. Nettoyer la base de données
```sql
-- Connexion à MySQL
mysql -u [VOTRE_USER] -p [VOTRE_DATABASE]

-- Supprimer tous les paramètres du module
DELETE FROM engine4_core_settings WHERE name LIKE 'photofloue.%';

-- Supprimer l'entrée du module
DELETE FROM engine4_core_modules WHERE name = 'photofloue';

-- Vérifier que c'est propre
SELECT * FROM engine4_core_modules WHERE name LIKE '%photo%';
```

## 🎨 ÉTAPE 2 : SOLUTION CSS IMMÉDIATE (3 min)

### A. Accéder à l'administration SocialEngine
1. Connectez-vous en tant qu'administrateur
2. Allez dans **Layout** > **Themes** ou **Appearance** > **CSS**

### B. Ajouter le CSS de floutage
**Copier-coller ce code dans votre CSS personnalisé :**

```css
/* === FLOUTAGE PHOTOS POUR VISITEURS NON CONNECTÉS === */
body:not(.logged-in):not(.logged_in) .bg_item_photo_album_photo {
    filter: blur(10px) !important;
    -webkit-filter: blur(10px) !important;
    transition: filter 0.3s ease !important;
}

body:not(.logged-in):not(.logged_in) .bg_item_photo {
    filter: blur(10px) !important;
    -webkit-filter: blur(10px) !important;
    transition: filter 0.3s ease !important;
}

body:not(.logged-in):not(.logged_in) .profile_photo img {
    filter: blur(10px) !important;
    -webkit-filter: blur(10px) !important;
    transition: filter 0.3s ease !important;
}

body:not(.logged-in):not(.logged_in) .user_sidebar_photo img {
    filter: blur(10px) !important;
    -webkit-filter: blur(10px) !important;
    transition: filter 0.3s ease !important;
}

/* Protection anti-clic droit */
body:not(.logged-in):not(.logged_in) .bg_item_photo_album_photo,
body:not(.logged-in):not(.logged_in) .profile_photo {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    user-select: none !important;
    -webkit-touch-callout: none !important;
}

/* Message au survol */
body:not(.logged-in):not(.logged_in) .bg_item_photo_album_photo::after {
    content: 'Connectez-vous pour voir les photos nettes';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 3;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

body:not(.logged-in):not(.logged_in) .bg_item_photo_album_photo:hover::after {
    opacity: 1;
}

body:not(.logged-in):not(.logged_in) .bg_item_photo_album_photo {
    position: relative;
}
```

## ⚡ ÉTAPE 3 : PROTECTION JAVASCRIPT (3 min)

### A. Localiser votre template principal
Trouvez le fichier de template principal, généralement :
- `application/themes/[votre-theme]/layout.tpl`
- ou via **Admin > Layout > Edit Layout**

### B. Ajouter le JavaScript avant `</body>`
**Ajouter ce code juste avant la balise fermante `</body>` :**

```html
<script>
(function() {
    // Vérifier si l'utilisateur est connecté
    function isUserLoggedIn() {
        return document.body.classList.contains('logged-in') || 
               document.body.classList.contains('logged_in') ||
               document.cookie.includes('en4_session=') && 
               !document.cookie.includes('en4_session=deleted');
    }
    
    // Protection pour visiteurs non connectés
    if (!isUserLoggedIn()) {
        // Empêcher clic droit sur photos
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('.bg_item_photo_album_photo, .profile_photo')) {
                e.preventDefault();
                alert('Connectez-vous pour accéder aux photos');
                return false;
            }
        });
        
        // Empêcher raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === 's' || e.key === 'S')) {
                e.preventDefault();
                alert('Connectez-vous pour sauvegarder');
                return false;
            }
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                alert('Connectez-vous pour voir les photos nettes');
                return false;
            }
        });
    }
})();
</script>
```

## ✅ ÉTAPE 4 : VÉRIFICATION (2 min)

### A. Tester en mode déconnecté
1. **Ouvrez un navigateur privé/incognito**
2. **Visitez votre site SANS vous connecter**
3. **Allez sur une page avec des photos d'albums**
4. **Vérifiez que les photos sont floutées**

### B. Tester en mode connecté
1. **Connectez-vous avec un compte**
2. **Vérifiez que les photos sont nettes**
3. **Testez le clic droit (doit fonctionner)**

### C. Vérifier sur mobile
1. **Testez sur smartphone**
2. **Vérifiez l'appui long sur photos (doit être bloqué)**

## 🎯 RÉSULTAT ATTENDU

✅ **Photos floutées** pour visiteurs non connectés  
✅ **Photos nettes** pour utilisateurs connectés  
✅ **Protection clic droit** active  
✅ **Messages d'incitation** au survol  
✅ **Fonctionnel sur mobile** et desktop  

## 🔧 DÉPANNAGE RAPIDE

### Si le flou ne fonctionne pas :
1. **Vérifiez dans la console du navigateur** (F12) s'il y a des erreurs
2. **Assurez-vous que les classes CSS correspondent** à votre thème
3. **Testez avec ce CSS simplifié** :
```css
.bg_item_photo_album_photo { filter: blur(10px) !important; }
.profile_photo img { filter: blur(10px) !important; }
```

### Si la protection ne fonctionne pas :
1. **Vérifiez que le JavaScript est bien dans le bon template**
2. **Testez sans autres plugins/modules** qui pourraient interférer
3. **Vérifiez la console pour des erreurs JavaScript**

## 📞 SUPPORT IMMÉDIAT

Si vous rencontrez des difficultés :
1. **Consultez les logs** : `temporary/log/`
2. **Testez étape par étape** : CSS d'abord, puis JavaScript
3. **Sauvegardez** avant chaque modification

---

**🚀 TEMPS TOTAL : 10 minutes maximum**  
**🎯 RÉSULTAT : Floutage fonctionnel immédiatement**