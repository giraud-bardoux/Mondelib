# 🎯 **Floutage sur Toutes les Pages SAUF l'Accueil**

## 📋 **Règles de fonctionnement :**

✅ **Page d'accueil** : Photos **toujours nettes** (même pour visiteurs)  
✅ **Toutes autres pages** : Photos **floutées** pour visiteurs non connectés  
✅ **Utilisateurs connectés** : Photos **toujours nettes** partout  

---

## ⚡ **Installation (5 minutes)**

### **🎨 ÉTAPE 1 : Remplacer le CSS**

**Dans votre CSS principal** (Admin → Layout → Themes → Edit CSS), **remplacez** le code précédent par :

```css
/* === FLOUTAGE ALBUMS POUR VISITEURS NON CONNECTÉS (SAUF PAGE D'ACCUEIL) === */

/* Détection automatique du statut utilisateur - SAUF page d'accueil */
body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .bg_item_photo_album_photo,
body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .profile_photo img,
body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .user_sidebar_photo img {
    filter: blur(10px) !important;
    -webkit-filter: blur(10px) !important;
    transition: filter 0.3s ease !important;
}

/* Message au survol des albums - SAUF page d'accueil */
body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .thumbs_photo {
    position: relative;
}

body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .thumbs_photo::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 5;
    background: transparent;
    cursor: pointer;
}

body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .thumbs_photo:hover::after {
    content: 'Connectez-vous pour voir les photos nettes';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    font-size: 14px;
    white-space: nowrap;
    z-index: 10;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}

/* Protection contre la sélection - SAUF page d'accueil */
body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .thumbs_photo,
body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .bg_item_photo_album_photo {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    user-select: none !important;
    -webkit-user-drag: none !important;
    user-drag: none !important;
}

/* Mobile responsive - SAUF page d'accueil */
@media (max-width: 768px) {
    body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .thumbs_photo:hover::after {
        content: 'Connectez-vous pour voir';
        font-size: 12px;
        padding: 8px 12px;
    }
    
    /* Protection tactile mobile - SAUF page d'accueil */
    body:not(.logged-in):not(.logged_in):not(.homepage):not(.home-page) .thumbs_photo {
        -webkit-touch-callout: none !important;
        -webkit-tap-highlight-color: transparent !important;
    }
}
```

### **🔧 ÉTAPE 2 : Remplacer le JavaScript**

**Dans votre template principal**, **remplacez** le script précédent par :

```html
<script type="text/javascript">
(function() {
    'use strict';
    
    // Détecter si nous sommes sur la page d'accueil
    function isHomePage() {
        var url = window.location.pathname;
        var href = window.location.href;
        
        // Méthodes de détection de la page d'accueil
        if (
            url === '/' ||                                    // Racine du site
            url === '/index' ||                               // Index
            url === '/home' ||                                // Page home
            url === '/index.php' ||                           // Index PHP
            href.match(/\/index\.php\?$/i) ||                 // Index avec paramètres vides
            document.querySelector('body.homepage') ||        // Classe CSS homepage
            document.querySelector('body.home-page') ||       // Classe CSS home-page
            document.querySelector('.homepage-content') ||    // Contenu homepage
            document.title.toLowerCase().includes('accueil')  // Titre contient "accueil"
        ) {
            return true;
        }
        
        return false;
    }
    
    // Détecter si l'utilisateur est connecté
    function detectUserStatus() {
        // Méthode 1: Vérifier les cookies de session
        var isLoggedIn = document.cookie.includes('en4_session') || 
                        document.cookie.includes('PHPSESSID');
        
        // Méthode 2: Vérifier la présence du menu utilisateur
        if (document.querySelector('.user_menu') || 
            document.querySelector('[href*="logout"]') ||
            document.querySelector('.member_options')) {
            isLoggedIn = true;
        }
        
        // Méthode 3: Vérifier l'URL actuelle (si contient /login alors pas connecté)
        if (window.location.href.includes('/login')) {
            isLoggedIn = false;
        }
        
        // Vérifier si on est sur la page d'accueil
        var isHome = isHomePage();
        
        // Appliquer les classes au body
        if (isLoggedIn) {
            document.body.classList.add('logged-in');
            document.body.classList.remove('not-logged-in');
        } else {
            document.body.classList.add('not-logged-in');
            document.body.classList.remove('logged-in');
        }
        
        if (isHome) {
            document.body.classList.add('homepage', 'home-page');
        } else {
            document.body.classList.remove('homepage', 'home-page');
        }
        
        return isLoggedIn;
    }
    
    // Vérifier si on doit appliquer le floutage
    function shouldApplyBlur() {
        return !detectUserStatus() && !isHomePage();
    }
    
    // Protection contre le clic droit (visiteurs seulement, pas sur l'accueil)
    function addProtection() {
        if (!shouldApplyBlur()) return; // Pas de protection pour les connectés ou sur l'accueil
        
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('.thumbs_photo') || 
                e.target.classList.contains('bg_item_photo_album_photo')) {
                e.preventDefault();
                showMessage('Connectez-vous pour accéder aux photos');
                return false;
            }
        });
        
        // Protection raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === 's' || e.key === 'c')) {
                e.preventDefault();
                showMessage('Connectez-vous pour sauvegarder les photos');
                return false;
            }
            
            // Empêcher F12 et Ctrl+Shift+I
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Afficher message de protection
    function showMessage(text) {
        if (document.querySelector('.protection-msg')) return;
        
        var msg = document.createElement('div');
        msg.className = 'protection-msg';
        msg.textContent = text;
        msg.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.9);color:white;padding:15px 20px;border-radius:5px;z-index:10000;font-size:14px;box-shadow:0 4px 20px rgba(0,0,0,0.3);';
        document.body.appendChild(msg);
        
        setTimeout(function() { 
            if (msg.parentNode) {
                msg.style.opacity = '0';
                setTimeout(function() { msg.remove(); }, 300);
            }
        }, 2500);
    }
    
    // Initialisation
    function init() {
        var userLoggedIn = detectUserStatus();
        var homePage = isHomePage();
        var blurActive = shouldApplyBlur();
        
        addProtection();
        
        // Log pour debug
        console.log('Photo Blur System:', {
            'User logged in': userLoggedIn,
            'Home page': homePage,
            'Blur active': blurActive
        });
    }
    
    // Lancer quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
</script>
```

---

## ✅ **Tests de Validation**

### **🏠 Test Page d'Accueil :**
1. **Navigation privée** → Page d'accueil (`http://preprod.mondelibertin.com/`)
2. **Vérifiez** : Photos **NETTES** (pas de flou même non connecté)

### **📷 Test Pages Albums :**
1. **Navigation privée** → Page album (`/albums/view/2/test`)
2. **Vérifiez** : Photos **FLOUTÉES** + message au survol

### **👤 Test Utilisateur Connecté :**
1. **Connectez-vous** 
2. **Toutes pages** : Photos **NETTES** partout

---

## 🔧 **Personnalisation**

### **Modifier la détection de page d'accueil :**
Si votre page d'accueil a une URL spécifique, modifiez cette ligne dans le JavaScript :

```javascript
// Ajouter votre URL spécifique
if (
    url === '/' ||
    url === '/votre-page-accueil' ||  // Ajoutez ici
    // ... reste du code
)
```

### **Exclure d'autres pages :**
```javascript
// Exemple pour exclure aussi la page "à propos"
if (url === '/about' || url === '/contact') {
    return true; // Traiter comme page d'accueil
}
```

---

## 📊 **Matrice de Fonctionnement**

| Situation | Page d'Accueil | Autres Pages |
|-----------|----------------|--------------|
| **Visiteur non connecté** | Photos NETTES ✅ | Photos FLOUTÉES 🌫️ |
| **Utilisateur connecté** | Photos NETTES ✅ | Photos NETTES ✅ |

---

## 🔍 **Debug**

Pour vérifier le fonctionnement, ouvrez la console (F12) et regardez le log :
```
Photo Blur System: {
  User logged in: false,
  Home page: true,
  Blur active: false
}
```

- **Home page: true** = Sur la page d'accueil (pas de flou)
- **Home page: false** = Sur autre page (flou si non connecté)
- **Blur active: true** = Floutage appliqué

---

**🎉 Parfait ! Maintenant les photos sont nettes sur la page d'accueil pour inciter à la découverte, et floutées partout ailleurs pour encourager l'inscription !**