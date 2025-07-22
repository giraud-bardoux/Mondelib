# 🎯 Solution de Floutage pour Albums SocialEngine

## 📋 Installation Simple (5 minutes)

Basée sur votre structure HTML exacte, voici la solution la plus simple et efficace :

---

## 🎨 **ÉTAPE 1 : Ajouter le CSS (3 minutes)**

### **Méthode A : Via l'administration (Recommandé)**
1. **Connexion admin** : `http://preprod.mondelibertin.com/admin`
2. **Layout → Themes → Votre thème → Edit CSS**
3. **Ajouter ce code** à la fin du fichier CSS :

```css
/* === FLOUTAGE ALBUMS POUR VISITEURS NON CONNECTÉS === */

/* Détection automatique du statut utilisateur */
body:not(.logged-in):not(.logged_in) .bg_item_photo_album_photo {
    filter: blur(10px) !important;
    -webkit-filter: blur(10px) !important;
    transition: filter 0.3s ease !important;
}

/* Floutage des autres images de profil */
body:not(.logged-in):not(.logged_in) .profile_photo img,
body:not(.logged-in):not(.logged_in) .user_sidebar_photo img {
    filter: blur(10px) !important;
    -webkit-filter: blur(10px) !important;
    transition: filter 0.3s ease !important;
}

/* Message au survol des albums */
body:not(.logged-in):not(.logged_in) .thumbs_photo {
    position: relative;
}

body:not(.logged-in):not(.logged_in) .thumbs_photo::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 5;
    background: transparent;
    cursor: pointer;
}

body:not(.logged-in):not(.logged_in) .thumbs_photo:hover::after {
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

/* Protection contre la sélection */
body:not(.logged-in):not(.logged_in) .thumbs_photo,
body:not(.logged-in):not(.logged_in) .bg_item_photo_album_photo {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    user-select: none !important;
    -webkit-user-drag: none !important;
    user-drag: none !important;
}

/* Mobile responsive */
@media (max-width: 768px) {
    body:not(.logged-in):not(.logged_in) .thumbs_photo:hover::after {
        content: 'Connectez-vous pour voir';
        font-size: 12px;
        padding: 8px 12px;
    }
}
```

### **Méthode B : Via FTP**
1. **Localisez votre fichier CSS principal** (généralement dans `/public/themes/votre-theme/`)
2. **Ajoutez le code CSS** ci-dessus à la fin du fichier

---

## 🔧 **ÉTAPE 2 : Ajouter le JavaScript (2 minutes)**

### **Dans votre template principal** (`header.tpl` ou `layout.tpl`) :

```html
<script type="text/javascript">
(function() {
    'use strict';
    
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
        
        // Appliquer la classe au body
        if (isLoggedIn) {
            document.body.classList.add('logged-in');
            document.body.classList.remove('not-logged-in');
        } else {
            document.body.classList.add('not-logged-in');
            document.body.classList.remove('logged-in');
        }
        
        return isLoggedIn;
    }
    
    // Protection contre le clic droit (visiteurs seulement)
    function addProtection() {
        if (detectUserStatus()) return; // Pas de protection pour les connectés
        
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
        });
    }
    
    // Afficher message de protection
    function showMessage(text) {
        if (document.querySelector('.protection-msg')) return;
        
        var msg = document.createElement('div');
        msg.className = 'protection-msg';
        msg.textContent = text;
        msg.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.9);color:white;padding:15px 20px;border-radius:5px;z-index:10000;font-size:14px;';
        document.body.appendChild(msg);
        
        setTimeout(function() { 
            if (msg.parentNode) msg.remove(); 
        }, 2500);
    }
    
    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        detectUserStatus();
        addProtection();
    });
    
})();
</script>
```

---

## ✅ **ÉTAPE 3 : Test Immédiat**

### **Test visiteur non connecté :**
1. **Ouvrez une fenêtre privée**
2. **Allez sur** : `http://preprod.mondelibertin.com/albums/view/2/test`
3. **Vérifiez** :
   - ✅ Photos d'albums floutées
   - ✅ Message au survol : "Connectez-vous pour voir les photos nettes"
   - ✅ Clic droit bloqué

### **Test utilisateur connecté :**
1. **Connectez-vous** normalement
2. **Même page** : Les photos doivent être **nettes** (pas de flou)

---

## 🔧 **Personnalisation (Optionnel)**

### **Modifier l'intensité du flou :**
```css
/* Plus flou (15px au lieu de 10px) */
filter: blur(15px) !important;

/* Moins flou (5px) */
filter: blur(5px) !important;
```

### **Changer le message :**
```css
/* Dans le CSS */
content: 'Votre message personnalisé';
```

### **Désactiver sur certaines pages :**
```javascript
// Ajouter dans le JavaScript
if (window.location.href.includes('/admin') || 
    window.location.href.includes('/special-page')) {
    return; // Pas de flou sur ces pages
}
```

---

## 🚨 **Résolution de Problèmes**

### **❌ Les photos ne sont pas floutées :**
```
1. Vérifiez que le CSS est bien ajouté
2. Videz le cache navigateur (Ctrl+F5)
3. Vérifiez la console (F12) pour erreurs
4. Testez en navigation privée
```

### **❌ Les photos restent floutées pour les connectés :**
```
1. Vérifiez que la classe 'logged-in' est ajoutée au <body>
2. Inspectez l'élément <body> (F12)
3. Ajustez la détection dans le JavaScript
```

### **❌ JavaScript ne fonctionne pas :**
```
1. Vérifiez la console pour erreurs (F12)
2. Placez le script avant </body>
3. Testez avec un script plus simple d'abord
```

---

## 📊 **Résultat Attendu**

```
✅ Visiteurs non connectés : Photos d'albums floutées
✅ Utilisateurs connectés : Photos nettes
✅ Message incitatif au survol
✅ Protection clic droit et raccourcis
✅ Compatible mobile
✅ Aucun impact sur les performances
```

---

## 🎯 **Validation Finale**

### **Checklist de fonctionnement :**
- [ ] **Navigation privée** → Albums flous
- [ ] **Survol photo** → Message affiché
- [ ] **Clic droit** → Bloqué + message
- [ ] **Utilisateur connecté** → Photos nettes
- [ ] **Mobile** → Fonctionne correctement

**🎉 Une fois ces 5 points validés, votre système de floutage fonctionne parfaitement !**

---

**⚡ Temps total d'installation : 5 minutes maximum**  
**🎯 Efficacité : Ciblage précis de vos éléments HTML**  
**🛡️ Sécurité : Protection contre copie basique mais efficace**