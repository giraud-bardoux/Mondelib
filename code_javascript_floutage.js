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
        if (detectUserStatus()) return;
        
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
        detectUserStatus();
        addProtection();
        addMobileProtection();
        
        // Log pour debug
        console.log('Photo Blur System:', detectUserStatus() ? 'User logged in - No blur' : 'Visitor - Blur active');
    }
    
    // Lancer quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
</script>