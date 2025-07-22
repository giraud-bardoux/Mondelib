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
    
    // Protection mobile
    function addMobileProtection() {
        if (!shouldApplyBlur()) return;
        
        var touchTimer;
        
        document.addEventListener('touchstart', function(e) {
            if (e.target.closest('.thumbs_photo')) {
                touchTimer = setTimeout(function() {
                    e.preventDefault();
                    showMessage('Connectez-vous pour accéder aux photos');
                }, 500); // Appui long de 500ms
            }
        });
        
        document.addEventListener('touchend', function() {
            if (touchTimer) {
                clearTimeout(touchTimer);
            }
        });
    }
    
    // Initialisation
    function init() {
        var userLoggedIn = detectUserStatus();
        var homePage = isHomePage();
        var blurActive = shouldApplyBlur();
        
        addProtection();
        addMobileProtection();
        
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