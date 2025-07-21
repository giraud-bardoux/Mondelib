/**
 * PhotoBlur Module - JavaScript Protection
 * 
 * Script pour protéger les images contre la sauvegarde et les captures d'écran
 */

(function() {
    'use strict';
    
    // Variables globales
    let isUserLoggedIn = window.PHOTOBLUR_USER_LOGGED_IN || false;
    let loginMessage = window.PHOTOBLUR_LOGIN_MESSAGE || 'Connectez-vous pour ne plus voir flou';
    
    /**
     * Initialisation du module de protection
     */
    function initPhotoBlur() {
        if (isUserLoggedIn) {
            return; // Ne pas appliquer les protections si l'utilisateur est connecté
        }
        
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', applyProtections);
        } else {
            applyProtections();
        }
        
        // Observer les nouveaux éléments ajoutés dynamiquement
        observeNewImages();
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
        preventDevTools();
        
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
        const images = document.querySelectorAll('img');
        images.forEach(function(img) {
            protectImage(img);
        });
        
        // Protéger aussi les images d'arrière-plan
        const elementsWithBgImages = document.querySelectorAll('[style*="background-image"]');
        elementsWithBgImages.forEach(function(el) {
            protectBackgroundImage(el);
        });
    }
    
    /**
     * Protéger une image spécifique
     */
    function protectImage(img) {
        // Ajouter les classes de protection
        img.classList.add('photoblur-protected');
        
        // Si c'est une photo de membre ou d'album, ajouter le flou
        if (isUserPhoto(img) || isAlbumPhoto(img)) {
            img.classList.add('photoblur-blurred');
            wrapImageWithContainer(img);
        }
        
        // Empêcher le glisser-déposer
        img.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Empêcher la sélection
        img.addEventListener('selectstart', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Empêcher les événements contextuels
        img.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showLoginMessage(e);
            return false;
        });
        
        // Empêcher les événements tactiles sur mobile
        img.addEventListener('touchstart', function(e) {
            if (e.touches.length > 1) { // Empêcher le pinch zoom
                e.preventDefault();
            }
        });
        
        img.addEventListener('touchmove', function(e) {
            if (e.touches.length > 1) {
                e.preventDefault();
            }
        });
    }
    
    /**
     * Vérifier si c'est une photo d'utilisateur
     */
    function isUserPhoto(img) {
        const src = img.src || '';
        const parent = img.closest('.profile_photo, .user_sidebar_photo, .profile-photo, .avatar');
        return parent !== null || src.includes('user') || src.includes('profile');
    }
    
    /**
     * Vérifier si c'est une photo d'album
     */
    function isAlbumPhoto(img) {
        const parent = img.closest('.album_photo, .photo_gallery, .gallery-item, .photo-item');
        const src = img.src || '';
        return parent !== null || src.includes('album') || src.includes('gallery');
    }
    
    /**
     * Envelopper l'image dans un conteneur avec tooltip
     */
    function wrapImageWithContainer(img) {
        if (img.closest('.photoblur-container')) {
            return; // Déjà enveloppée
        }
        
        const container = document.createElement('div');
        container.className = 'photoblur-container';
        container.title = loginMessage;
        
        img.parentNode.insertBefore(container, img);
        container.appendChild(img);
        
        // Ajouter un gestionnaire de clic pour le message
        container.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginMessage(e);
            return false;
        });
    }
    
    /**
     * Protéger les images d'arrière-plan
     */
    function protectBackgroundImage(element) {
        element.classList.add('photoblur-protected');
        
        // Ajouter un overlay pour empêcher l'interaction
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1; background: transparent;';
        overlay.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showLoginMessage(e);
            return false;
        });
        
        if (element.style.position === '' || element.style.position === 'static') {
            element.style.position = 'relative';
        }
        element.appendChild(overlay);
    }
    
    /**
     * Empêcher les raccourcis clavier
     */
    function preventKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Empêcher Ctrl+S (Sauvegarder)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                showLoginMessage(e);
                return false;
            }
            
            // Empêcher Ctrl+A (Sélectionner tout)
            if (e.ctrlKey && e.key === 'a') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Ctrl+C et Ctrl+V
            if (e.ctrlKey && (e.key === 'c' || e.key === 'v')) {
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
            
            // Empêcher Ctrl+U (Voir le source)
            if (e.ctrlKey && e.key === 'u') {
                e.preventDefault();
                return false;
            }
            
            // Empêcher Print Screen
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                showLoginMessage(e);
                return false;
            }
        });
    }
    
    /**
     * Empêcher le clic droit
     */
    function preventRightClick() {
        document.addEventListener('contextmenu', function(e) {
            const target = e.target;
            if (target.tagName === 'IMG' || target.closest('.photoblur-protected')) {
                e.preventDefault();
                showLoginMessage(e);
                return false;
            }
        });
    }
    
    /**
     * Protection contre les outils de développement
     */
    function preventDevTools() {
        // Détection basique de l'ouverture des outils de développement
        let devtools = {open: false, orientation: null};
        const threshold = 160;
        
        setInterval(function() {
            if (window.outerHeight - window.innerHeight > threshold || 
                window.outerWidth - window.innerWidth > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    // Flouter davantage ou masquer les images
                    document.querySelectorAll('.photoblur-protected').forEach(function(img) {
                        img.style.filter = 'blur(20px)';
                    });
                }
            } else {
                if (devtools.open) {
                    devtools.open = false;
                    // Restaurer le flou normal
                    document.querySelectorAll('.photoblur-protected').forEach(function(img) {
                        img.style.filter = 'blur(10px)';
                    });
                }
            }
        }, 500);
    }
    
    /**
     * Protections spécifiques au mobile
     */
    function applyMobileProtections() {
        // Empêcher la sauvegarde d'image sur mobile
        document.addEventListener('touchstart', function(e) {
            if (e.target.tagName === 'IMG' && e.target.classList.contains('photoblur-protected')) {
                let touchDuration = 0;
                const startTime = Date.now();
                
                const touchEndHandler = function() {
                    touchDuration = Date.now() - startTime;
                    if (touchDuration > 500) { // Appui long détecté
                        e.preventDefault();
                        showLoginMessage(e);
                    }
                    document.removeEventListener('touchend', touchEndHandler);
                };
                
                document.addEventListener('touchend', touchEndHandler);
            }
        });
        
        // Empêcher le zoom sur les images protégées
        document.addEventListener('gesturestart', function(e) {
            if (e.target.classList.contains('photoblur-protected')) {
                e.preventDefault();
            }
        });
    }
    
    /**
     * Empêcher l'impression
     */
    function preventPrinting() {
        window.addEventListener('beforeprint', function(e) {
            document.querySelectorAll('.photoblur-protected').forEach(function(img) {
                img.style.display = 'none';
            });
        });
        
        window.addEventListener('afterprint', function(e) {
            document.querySelectorAll('.photoblur-protected').forEach(function(img) {
                img.style.display = '';
            });
        });
    }
    
    /**
     * Observer les nouvelles images ajoutées dynamiquement
     */
    function observeNewImages() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.tagName === 'IMG') {
                            protectImage(node);
                        } else {
                            const images = node.querySelectorAll('img');
                            images.forEach(protectImage);
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
     * Afficher le message de connexion
     */
    function showLoginMessage(event) {
        // Créer un tooltip temporaire
        const tooltip = document.createElement('div');
        tooltip.textContent = loginMessage;
        tooltip.style.cssText = `
            position: fixed;
            top: ${event.clientY}px;
            left: ${event.clientX}px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            z-index: 10000;
            pointer-events: none;
            transform: translate(-50%, -100%);
        `;
        
        document.body.appendChild(tooltip);
        
        // Supprimer le tooltip après 2 secondes
        setTimeout(function() {
            if (tooltip.parentNode) {
                tooltip.parentNode.removeChild(tooltip);
            }
        }, 2000);
    }
    
    /**
     * Détecter si c'est un appareil mobile
     */
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    
    // Initialiser le module quand le script est chargé
    initPhotoBlur();
    
})();