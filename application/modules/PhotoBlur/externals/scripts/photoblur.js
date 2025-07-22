/**
 * PhotoBlur Module for SocialEngine 7.4 - JavaScript Protection
 * 
 * Script pour protéger les images et gérer les interactions
 */

(function() {
    'use strict';
    
    // Configuration globale du module
    var config = window.PHOTOBLUR_CONFIG || {
        userLoggedIn: false,
        isHomepage: false,
        loginMessage: 'Connectez-vous pour voir les photos nettes',
        protectionMessage: 'Connectez-vous pour accéder aux photos'
    };
    
    /**
     * Initialisation du module de protection
     */
    function initPhotoBlur() {
        // Ne pas appliquer les protections si l'utilisateur est connecté
        if (config.userLoggedIn) {
            console.log('PhotoBlur: Utilisateur connecté - Protection désactivée');
            return;
        }
        
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', applyProtections);
        } else {
            applyProtections();
        }
        
        // Observer les nouveaux éléments ajoutés dynamiquement
        observeNewImages();
        
        console.log('PhotoBlur: Protection activée pour visiteur non connecté');
    }
    
    /**
     * Appliquer toutes les protections
     */
    function applyProtections() {
        // Protéger toutes les images existantes
        protectExistingImages();
        
        // Empêcher les raccourcis clavier
        preventKeyboardShortcuts();
        
        // Empêcher le clic droit
        preventRightClick();
        
        // Protection contre les outils de développement
        detectDevTools();
        
        // Protection mobile spécifique
        if (isMobileDevice()) {
            applyMobileProtections();
        }
        
        // Protection contre l'impression
        preventPrinting();
    }
    
    /**
     * Protéger les images existantes
     */
    function protectExistingImages() {
        // Sélecteurs pour tous les types d'images à protéger
        var selectors = [
            '.thumbs_photo',
            '.profile_photo img',
            '.user_sidebar_photo img',
            '.bg_item_photo_album_photo',
            '.bg_item_photo',
            '.photoblur-protected'
        ];
        
        selectors.forEach(function(selector) {
            var elements = document.querySelectorAll(selector);
            elements.forEach(function(element) {
                protectElement(element);
            });
        });
    }
    
    /**
     * Protéger un élément spécifique
     */
    function protectElement(element) {
        if (!element) return;
        
        // Ajouter les classes de protection
        element.classList.add('photoblur-protected');
        
        // Empêcher le glisser-déposer
        element.addEventListener('dragstart', function(e) {
            e.preventDefault();
            showProtectionMessage(config.protectionMessage);
            return false;
        });
        
        // Empêcher la sélection
        element.addEventListener('selectstart', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Empêcher les événements contextuels
        element.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showProtectionMessage(config.protectionMessage);
            return false;
        });
        
        // Événements tactiles sur mobile
        if (isMobileDevice()) {
            addMobileProtectionToElement(element);
        }
    }
    
    /**
     * Protection mobile pour un élément
     */
    function addMobileProtectionToElement(element) {
        var touchTimer;
        
        element.addEventListener('touchstart', function(e) {
            touchTimer = setTimeout(function() {
                e.preventDefault();
                showProtectionMessage(config.protectionMessage);
            }, 500); // Appui long de 500ms
        });
        
        element.addEventListener('touchend', function() {
            if (touchTimer) {
                clearTimeout(touchTimer);
            }
        });
        
        element.addEventListener('touchmove', function() {
            if (touchTimer) {
                clearTimeout(touchTimer);
            }
        });
    }
    
    /**
     * Empêcher les raccourcis clavier
     */
    function preventKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Empêcher Ctrl+S (Sauvegarder)
            if (e.ctrlKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                showProtectionMessage('Connectez-vous pour sauvegarder les images');
                return false;
            }
            
            // Empêcher Ctrl+A (Sélectionner tout) sur les zones protégées
            if (e.ctrlKey && e.key.toLowerCase() === 'a') {
                var target = e.target;
                if (target.closest('.photoblur-protected') || target.classList.contains('photoblur-protected')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Empêcher Ctrl+C (Copier)
            if (e.ctrlKey && e.key.toLowerCase() === 'c') {
                var target = e.target;
                if (target.closest('.photoblur-protected') || target.classList.contains('photoblur-protected')) {
                    e.preventDefault();
                    showProtectionMessage('Connectez-vous pour copier les images');
                    return false;
                }
            }
            
            // Empêcher F12 (Outils de développement)
            if (e.key === 'F12') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Ctrl+Shift+I (Outils de développement)
            if (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'i') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Ctrl+U (Voir le source)
            if (e.ctrlKey && e.key.toLowerCase() === 'u') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Print Screen
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                showProtectionMessage(config.loginMessage);
                return false;
            }
        });
    }
    
    /**
     * Empêcher le clic droit
     */
    function preventRightClick() {
        document.addEventListener('contextmenu', function(e) {
            var target = e.target;
            
            // Vérifier si c'est un élément protégé
            if (target.closest('.photoblur-protected') || 
                target.classList.contains('photoblur-protected') ||
                target.closest('.thumbs_photo') ||
                target.closest('.profile_photo') ||
                target.classList.contains('bg_item_photo_album_photo')) {
                
                e.preventDefault();
                showProtectionMessage(config.protectionMessage);
                return false;
            }
        });
    }
    
    /**
     * Détection des outils de développement
     */
    function detectDevTools() {
        var devtools = { open: false };
        var threshold = 160;
        
        function checkDevTools() {
            var heightDiff = window.outerHeight - window.innerHeight;
            var widthDiff = window.outerWidth - window.innerWidth;
            
            if (heightDiff > threshold || widthDiff > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    document.body.classList.add('photoblur-devtools-detected');
                    showProtectionMessage('Outils de développement détectés - Protection renforcée');
                }
            } else {
                if (devtools.open) {
                    devtools.open = false;
                    document.body.classList.remove('photoblur-devtools-detected');
                }
            }
        }
        
        // Vérifier toutes les secondes
        setInterval(checkDevTools, 1000);
    }
    
    /**
     * Protections spécifiques au mobile
     */
    function applyMobileProtections() {
        // Empêcher le zoom sur les images protégées
        document.addEventListener('gesturestart', function(e) {
            var target = e.target;
            if (target.closest('.photoblur-protected') || target.classList.contains('photoblur-protected')) {
                e.preventDefault();
            }
        });
        
        // Empêcher le zoom via les événements touch
        var lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            var now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                var target = event.target;
                if (target.closest('.photoblur-protected') || target.classList.contains('photoblur-protected')) {
                    event.preventDefault();
                }
            }
            lastTouchEnd = now;
        }, false);
    }
    
    /**
     * Empêcher l'impression
     */
    function preventPrinting() {
        window.addEventListener('beforeprint', function() {
            var protectedElements = document.querySelectorAll('.photoblur-protected');
            protectedElements.forEach(function(element) {
                element.style.display = 'none';
            });
        });
        
        window.addEventListener('afterprint', function() {
            var protectedElements = document.querySelectorAll('.photoblur-protected');
            protectedElements.forEach(function(element) {
                element.style.display = '';
            });
        });
    }
    
    /**
     * Observer les nouvelles images ajoutées dynamiquement
     */
    function observeNewImages() {
        if (!window.MutationObserver) {
            return; // Pas de support pour MutationObserver
        }
        
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Vérifier si le nœud lui-même est protégeable
                        if (isProtectableElement(node)) {
                            protectElement(node);
                        }
                        
                        // Chercher des éléments protégeables dans les enfants
                        var protectableElements = node.querySelectorAll && node.querySelectorAll(
                            '.thumbs_photo, .profile_photo img, .bg_item_photo_album_photo, .photoblur-protected'
                        );
                        
                        if (protectableElements) {
                            Array.prototype.forEach.call(protectableElements, protectElement);
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
    
    /**
     * Vérifier si un élément doit être protégé
     */
    function isProtectableElement(element) {
        return element.classList.contains('thumbs_photo') ||
               element.classList.contains('bg_item_photo_album_photo') ||
               element.classList.contains('photoblur-protected') ||
               (element.tagName === 'IMG' && element.closest('.profile_photo'));
    }
    
    /**
     * Afficher un message de protection
     */
    function showProtectionMessage(message) {
        // Éviter les doublons
        if (document.querySelector('.photoblur-protection-message')) {
            return;
        }
        
        var messageDiv = document.createElement('div');
        messageDiv.className = 'photoblur-protection-message';
        messageDiv.textContent = message;
        messageDiv.style.cssText = [
            'position: fixed',
            'top: 50%',
            'left: 50%',
            'transform: translate(-50%, -50%)',
            'background: rgba(0, 0, 0, 0.9)',
            'color: white',
            'padding: 15px 25px',
            'border-radius: 8px',
            'font-size: 16px',
            'z-index: 10000',
            'box-shadow: 0 4px 20px rgba(0,0,0,0.3)',
            'animation: fadeInMessage 0.3s ease',
            'text-align: center',
            'max-width: 300px'
        ].join(';');
        
        document.body.appendChild(messageDiv);
        
        // Supprimer après 3 secondes
        setTimeout(function() {
            if (messageDiv.parentNode) {
                messageDiv.style.animation = 'fadeOutMessage 0.3s ease forwards';
                setTimeout(function() {
                    if (messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 300);
            }
        }, 3000);
    }
    
    /**
     * Détecter si c'est un appareil mobile
     */
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               ('ontouchstart' in window) ||
               (navigator.maxTouchPoints && navigator.maxTouchPoints > 1);
    }
    
    /**
     * Ajouter les animations CSS si nécessaires
     */
    function addAnimations() {
        if (document.getElementById('photoblur-animations')) {
            return; // Déjà ajoutées
        }
        
        var style = document.createElement('style');
        style.id = 'photoblur-animations';
        style.textContent = [
            '@keyframes fadeInMessage {',
            '  from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }',
            '  to { opacity: 1; transform: translate(-50%, -50%) scale(1); }',
            '}',
            '@keyframes fadeOutMessage {',
            '  from { opacity: 1; transform: translate(-50%, -50%) scale(1); }',
            '  to { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }',
            '}'
        ].join('\n');
        
        document.head.appendChild(style);
    }
    
    // Initialisation
    function init() {
        addAnimations();
        initPhotoBlur();
    }
    
    // Lancer l'initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();