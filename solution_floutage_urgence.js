/**
 * SOLUTION D'URGENCE - Protection JavaScript simple
 * 
 * À ajouter dans le footer de votre site ou dans un fichier JS chargé globalement
 */

(function() {
    'use strict';
    
    // Vérifier si l'utilisateur est connecté
    function isUserLoggedIn() {
        // Méthode 1: Vérifier les classes CSS du body
        if (document.body.classList.contains('logged-in') || 
            document.body.classList.contains('logged_in')) {
            return true;
        }
        
        // Méthode 2: Vérifier la présence de cookies de session
        var cookies = document.cookie;
        if (cookies.includes('en4_session') || cookies.includes('PHPSESSID')) {
            // Vérifier si le cookie n'est pas vide
            var sessionMatch = cookies.match(/en4_session=([^;]+)/);
            if (sessionMatch && sessionMatch[1] !== '' && sessionMatch[1] !== 'deleted') {
                return true;
            }
        }
        
        // Méthode 3: Vérifier la présence d'éléments user
        var userMenu = document.querySelector('.user_menu, .account_menu, .profile_menu');
        if (userMenu) {
            return true;
        }
        
        return false;
    }
    
    // Initialiser les protections
    function initProtection() {
        // Ne rien faire si l'utilisateur est connecté
        if (isUserLoggedIn()) {
            console.log('Utilisateur connecté - Protection désactivée');
            return;
        }
        
        console.log('Visiteur non connecté - Protection des photos activée');
        
        // Ajouter classe CSS pour la détection
        document.body.classList.add('photo-protection-active');
        document.body.classList.remove('logged-in', 'logged_in');
        
        // Empêcher le clic droit sur les photos
        preventRightClick();
        
        // Empêcher les raccourcis clavier
        preventKeyboardShortcuts();
        
        // Protection mobile
        if (isMobileDevice()) {
            applyMobileProtection();
        }
        
        // Observer les nouvelles images
        observeNewImages();
    }
    
    // Empêcher le clic droit
    function preventRightClick() {
        document.addEventListener('contextmenu', function(e) {
            var target = e.target;
            
            // Vérifier si c'est une photo protégée
            if (target.closest('.bg_item_photo_album_photo') ||
                target.closest('.profile_photo') ||
                target.closest('.thumbs_photo') ||
                target.classList.contains('bg_item_photo_album_photo')) {
                
                e.preventDefault();
                showMessage('Connectez-vous pour accéder aux photos');
                return false;
            }
        });
    }
    
    // Empêcher les raccourcis clavier
    function preventKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Empêcher Ctrl+S (Sauvegarder)
            if (e.ctrlKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                showMessage('Connectez-vous pour sauvegarder');
                return false;
            }
            
            // Empêcher Ctrl+C (Copier) sur les images
            if (e.ctrlKey && e.key.toLowerCase() === 'c') {
                var activeElement = document.activeElement;
                if (activeElement && activeElement.closest('.bg_item_photo_album_photo, .profile_photo')) {
                    e.preventDefault();
                    showMessage('Connectez-vous pour copier');
                    return false;
                }
            }
            
            // Empêcher Print Screen
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                showMessage('Connectez-vous pour voir les photos nettes');
                return false;
            }
            
            // Empêcher F12 (Outils de développement)
            if (e.key === 'F12') {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Protection mobile
    function applyMobileProtection() {
        var touchTimer;
        
        document.addEventListener('touchstart', function(e) {
            var target = e.target;
            
            if (target.closest('.bg_item_photo_album_photo, .profile_photo')) {
                touchTimer = setTimeout(function() {
                    showMessage('Connectez-vous pour accéder aux photos');
                }, 500); // Appui long détecté
            }
        });
        
        document.addEventListener('touchend', function() {
            if (touchTimer) {
                clearTimeout(touchTimer);
            }
        });
        
        document.addEventListener('touchmove', function() {
            if (touchTimer) {
                clearTimeout(touchTimer);
            }
        });
    }
    
    // Observer les nouvelles images
    function observeNewImages() {
        if (!window.MutationObserver) {
            return;
        }
        
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Vérifier les nouvelles photos
                        var photos = node.querySelectorAll && node.querySelectorAll(
                            '.bg_item_photo_album_photo, .profile_photo img'
                        );
                        
                        if (photos && photos.length > 0) {
                            console.log('Nouvelles photos détectées - Protection appliquée');
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Afficher un message
    function showMessage(text) {
        // Éviter les doublons
        var existingMessage = document.querySelector('.photo-protection-message');
        if (existingMessage) {
            return;
        }
        
        var message = document.createElement('div');
        message.className = 'photo-protection-message';
        message.textContent = text;
        message.style.cssText = [
            'position: fixed',
            'top: 50%',
            'left: 50%',
            'transform: translate(-50%, -50%)',
            'background: rgba(0, 0, 0, 0.9)',
            'color: white',
            'padding: 15px 25px',
            'border-radius: 8px',
            'font-size: 14px',
            'z-index: 10000',
            'box-shadow: 0 4px 20px rgba(0,0,0,0.3)'
        ].join(';');
        
        document.body.appendChild(message);
        
        // Supprimer après 3 secondes
        setTimeout(function() {
            if (message.parentNode) {
                message.remove();
            }
        }, 3000);
    }
    
    // Détecter les appareils mobiles
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               ('ontouchstart' in window);
    }
    
    // Initialisation
    function init() {
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProtection);
        } else {
            initProtection();
        }
    }
    
    // Lancer l'initialisation
    init();
    
})();