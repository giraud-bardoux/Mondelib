/**
 * Script de protection et floutage pour SocialEngine
 * À ajouter dans votre template principal ou en fichier externe
 */

(function() {
    'use strict';
    
    // Détecter si l'utilisateur est connecté
    function isUserLoggedIn() {
        // Méthode 1: Vérifier la classe CSS sur body
        if (document.body.classList.contains('logged-in') || 
            document.body.classList.contains('logged_in')) {
            return true;
        }
        
        // Méthode 2: Vérifier la présence d'éléments de navigation membre
        if (document.querySelector('.user_menu, .member_menu, .logout')) {
            return true;
        }
        
        // Méthode 3: Vérifier les cookies de session
        if (document.cookie.includes('en4_session') || 
            document.cookie.includes('engine4_logged_in')) {
            return true;
        }
        
        return false;
    }
    
    // Ajouter la classe appropriée au body
    function setUserStatus() {
        if (isUserLoggedIn()) {
            document.body.classList.add('logged-in');
            document.body.classList.remove('not-logged-in');
        } else {
            document.body.classList.add('not-logged-in');
            document.body.classList.remove('logged-in');
        }
    }
    
    // Protection contre les raccourcis clavier
    function preventKeyboardShortcuts() {
        if (isUserLoggedIn()) return; // Pas de protection pour les connectés
        
        document.addEventListener('keydown', function(e) {
            // Empêcher Ctrl+S (Sauvegarder)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                showProtectionMessage('Connectez-vous pour sauvegarder les images');
                return false;
            }
            
            // Empêcher Ctrl+A (Sélectionner tout)
            if (e.ctrlKey && e.key === 'a') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Ctrl+C (Copier)
            if (e.ctrlKey && e.key === 'c') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher F12 (Outils de développement)
            if (e.key === 'F12') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Ctrl+Shift+I (Outils de développement)
            if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Print Screen
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                showProtectionMessage('Connectez-vous pour ne plus voir flou');
                return false;
            }
        });
    }
    
    // Protection contre le clic droit
    function preventRightClick() {
        if (isUserLoggedIn()) return;
        
        document.addEventListener('contextmenu', function(e) {
            const target = e.target;
            
            // Vérifier si c'est une image d'album ou de profil
            if (target.closest('.thumbs_photo') || 
                target.closest('.bg_item_photo') ||
                target.closest('.profile_photo') ||
                target.classList.contains('bg_item_photo_album_photo')) {
                
                e.preventDefault();
                showProtectionMessage('Connectez-vous pour accéder aux images');
                return false;
            }
        });
    }
    
    // Afficher un message de protection
    function showProtectionMessage(message) {
        // Éviter les doublons
        if (document.querySelector('.protection-message')) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'protection-message';
        messageDiv.textContent = message;
        messageDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            font-size: 16px;
            z-index: 10000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: fadeInMessage 0.3s ease;
        `;
        
        document.body.appendChild(messageDiv);
        
        // Supprimer après 3 secondes
        setTimeout(function() {
            if (messageDiv.parentNode) {
                messageDiv.style.animation = 'fadeOutMessage 0.3s ease forwards';
                setTimeout(() => messageDiv.remove(), 300);
            }
        }, 3000);
    }
    
    // Détection des outils de développement
    function detectDevTools() {
        if (isUserLoggedIn()) return;
        
        let devtools = { open: false };
        const threshold = 160;
        
        function checkDevTools() {
            if (window.outerHeight - window.innerHeight > threshold || 
                window.outerWidth - window.innerWidth > threshold) {
                
                if (!devtools.open) {
                    devtools.open = true;
                    document.body.classList.add('dev-tools-detected');
                    showProtectionMessage('Outils de développement détectés - Protection renforcée');
                }
            } else {
                if (devtools.open) {
                    devtools.open = false;
                    document.body.classList.remove('dev-tools-detected');
                }
            }
        }
        
        setInterval(checkDevTools, 1000);
    }
    
    // Protection mobile
    function mobileProtection() {
        if (isUserLoggedIn()) return;
        
        // Détecter les appuis longs sur les images
        let touchTimer;
        
        document.addEventListener('touchstart', function(e) {
            const target = e.target;
            
            if (target.closest('.thumbs_photo') || 
                target.closest('.bg_item_photo')) {
                
                touchTimer = setTimeout(function() {
                    e.preventDefault();
                    showProtectionMessage('Connectez-vous pour accéder aux images');
                }, 500);
            }
        });
        
        document.addEventListener('touchend', function() {
            if (touchTimer) {
                clearTimeout(touchTimer);
            }
        });
        
        // Empêcher le zoom sur les images
        document.addEventListener('gesturestart', function(e) {
            if (e.target.closest('.thumbs_photo') || 
                e.target.closest('.bg_item_photo')) {
                e.preventDefault();
            }
        });
    }
    
    // Ajouter les animations CSS
    function addAnimations() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInMessage {
                from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            }
            
            @keyframes fadeOutMessage {
                from { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                to { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Initialisation
    function init() {
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }
        
        addAnimations();
        setUserStatus();
        
        // Appliquer les protections seulement si l'utilisateur n'est pas connecté
        if (!isUserLoggedIn()) {
            preventKeyboardShortcuts();
            preventRightClick();
            detectDevTools();
            mobileProtection();
            
            console.log('Protection des images activée pour visiteur non connecté');
        } else {
            console.log('Utilisateur connecté - Protection désactivée');
        }
    }
    
    // Lancer l'initialisation
    init();
    
})();