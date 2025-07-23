/**
 * PhotoFloue Module v1.3.0 - JavaScript
 * Protection et floutage simplifiés pour SocialEngine 7.4
 */

(function() {
    'use strict';

    // Configuration par défaut
    var config = window.PHOTOFLOUE_CONFIG || {
        enabled: true,
        blurIntensity: 10,
        protectionEnabled: true,
        mobileProtection: true,
        loginMessage: 'Connectez-vous pour voir les photos nettes',
        version: '1.3.0'
    };

    // État de l'utilisateur
    var isUserLoggedIn = window.PHOTOFLOUE_USER_LOGGED_IN || false;

    // Variables globales
    var protectionActive = false;
    var isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    var isDevToolsOpen = false;

    /**
     * Initialisation principale
     */
    function init() {
        if (!config.enabled || isUserLoggedIn) {
            console.log('PhotoFloue v1.3.0: Utilisateur connecté ou module désactivé');
            return;
        }

        console.log('PhotoFloue v1.3.0: Initialisation pour visiteur non connecté');
        
        // Attendre que le DOM soit prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startProtection);
        } else {
            startProtection();
        }
    }

    /**
     * Démarrage de la protection
     */
    function startProtection() {
        if (!config.protectionEnabled) {
            return;
        }

        // Protection de base
        setupBasicProtection();
        
        // Protection mobile si activée
        if (config.mobileProtection && isMobile) {
            setupMobileProtection();
        }
        
        // Détection des outils de développement
        setupDevToolsDetection();
        
        // Observer les nouveaux éléments
        setupMutationObserver();
        
        protectionActive = true;
        console.log('PhotoFloue v1.3.0: Protection activée');
    }

    /**
     * Protection de base (clic droit, clavier)
     */
    function setupBasicProtection() {
        // Empêcher le clic droit
        document.addEventListener('contextmenu', function(e) {
            if (isPhotoElement(e.target)) {
                e.preventDefault();
                showMessage('Connectez-vous pour accéder aux photos');
                return false;
            }
        }, true);

        // Empêcher les raccourcis clavier
        document.addEventListener('keydown', function(e) {
            // Ctrl+S (Sauvegarder)
            if (e.ctrlKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                showMessage('Connectez-vous pour sauvegarder');
                return false;
            }
            
            // Ctrl+C (Copier)
            if (e.ctrlKey && e.key.toLowerCase() === 'c') {
                if (isPhotoInSelection()) {
                    e.preventDefault();
                    showMessage('Connectez-vous pour copier');
                    return false;
                }
            }
            
            // F12 (Outils de développement)
            if (e.key === 'F12') {
                e.preventDefault();
                showMessage('Connectez-vous pour accéder aux outils');
                return false;
            }
            
            // PrintScreen
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                showMessage('Connectez-vous pour voir les photos nettes');
                return false;
            }
        }, true);

        // Empêcher la sélection de texte
        document.addEventListener('selectstart', function(e) {
            if (isPhotoElement(e.target)) {
                e.preventDefault();
                return false;
            }
        }, true);

        // Empêcher le drag & drop
        document.addEventListener('dragstart', function(e) {
            if (isPhotoElement(e.target)) {
                e.preventDefault();
                return false;
            }
        }, true);
    }

    /**
     * Protection mobile spécifique
     */
    function setupMobileProtection() {
        var longPressTimer;
        var longPressDelay = 500; // 500ms pour détecter un appui long

        // Détecter l'appui long (équivalent du clic droit sur mobile)
        document.addEventListener('touchstart', function(e) {
            if (isPhotoElement(e.target)) {
                longPressTimer = setTimeout(function() {
                    e.preventDefault();
                    showMessage('Connectez-vous pour accéder aux photos');
                    vibrate();
                }, longPressDelay);
            }
        }, true);

        document.addEventListener('touchend', function(e) {
            if (longPressTimer) {
                clearTimeout(longPressTimer);
            }
        }, true);

        document.addEventListener('touchmove', function(e) {
            if (longPressTimer) {
                clearTimeout(longPressTimer);
            }
        }, true);

        // Empêcher le zoom sur les photos
        document.addEventListener('gesturestart', function(e) {
            if (isPhotoElement(e.target)) {
                e.preventDefault();
                return false;
            }
        }, true);
    }

    /**
     * Détection des outils de développement
     */
    function setupDevToolsDetection() {
        // Méthode simple et non invasive
        var devtools = {
            open: false,
            orientation: null
        };

        var threshold = 160;

        setInterval(function() {
            if (window.outerHeight - window.innerHeight > threshold || 
                window.outerWidth - window.innerWidth > threshold) {
                if (!devtools.open) {
                    devtools.open = true;
                    isDevToolsOpen = true;
                    document.body.classList.add('photofloue-devtools-detected');
                    console.log('PhotoFloue: Outils de développement détectés');
                }
            } else {
                if (devtools.open) {
                    devtools.open = false;
                    isDevToolsOpen = false;
                    document.body.classList.remove('photofloue-devtools-detected');
                }
            }
        }, 500);
    }

    /**
     * Observer les nouveaux éléments ajoutés au DOM
     */
    function setupMutationObserver() {
        if (typeof MutationObserver === 'undefined') {
            return; // Fallback pour les navigateurs anciens
        }

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            applyProtectionToNewElements(node);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Appliquer la protection aux nouveaux éléments
     */
    function applyProtectionToNewElements(element) {
        // Chercher les photos dans le nouvel élément
        var photos = element.querySelectorAll(
            '.bg_item_photo_album_photo, .profile_photo img, .thumbs_photo, ' +
            '.bg_item_photo, .avatar img, .user_sidebar_photo img'
        );

        photos.forEach(function(photo) {
            if (!photo.hasAttribute('data-photofloue-protected')) {
                photo.setAttribute('data-photofloue-protected', 'true');
                applyBasicProtection(photo);
            }
        });
    }

    /**
     * Appliquer la protection de base à un élément
     */
    function applyBasicProtection(element) {
        // Empêcher la sélection
        element.style.webkitUserSelect = 'none';
        element.style.mozUserSelect = 'none';
        element.style.msUserSelect = 'none';
        element.style.userSelect = 'none';
        
        // Empêcher le drag
        element.setAttribute('draggable', 'false');
        element.style.webkitUserDrag = 'none';
        element.style.mozUserDrag = 'none';
        
        // Protection tactile
        if (isMobile) {
            element.style.webkitTouchCallout = 'none';
            element.style.webkitTapHighlightColor = 'transparent';
            element.style.touchAction = 'manipulation';
        }
    }

    /**
     * Vérifier si un élément est une photo protégée
     */
    function isPhotoElement(element) {
        if (!element || !element.closest) {
            return false;
        }

        return element.closest(
            '.bg_item_photo_album_photo, .profile_photo, .thumbs_photo, ' +
            '.bg_item_photo, .avatar, .user_sidebar_photo, .photo_thumb'
        ) !== null;
    }

    /**
     * Vérifier si la sélection contient des photos
     */
    function isPhotoInSelection() {
        var selection = window.getSelection();
        if (!selection.rangeCount) {
            return false;
        }

        var range = selection.getRangeAt(0);
        var container = range.commonAncestorContainer;
        
        if (container.nodeType === Node.TEXT_NODE) {
            container = container.parentNode;
        }

        return isPhotoElement(container);
    }

    /**
     * Afficher un message à l'utilisateur
     */
    function showMessage(text) {
        // Méthode simple et non invasive
        if (typeof console !== 'undefined') {
            console.log('PhotoFloue: ' + text);
        }

        // Créer une notification discrète
        var notification = document.createElement('div');
        notification.textContent = text;
        notification.style.cssText = 
            'position: fixed; top: 20px; right: 20px; ' +
            'background: rgba(0,0,0,0.9); color: white; ' +
            'padding: 12px 16px; border-radius: 6px; ' +
            'font-size: 14px; z-index: 10000; ' +
            'opacity: 0; transition: opacity 0.3s ease; ' +
            'pointer-events: none; max-width: 300px;';

        document.body.appendChild(notification);

        // Animation d'apparition
        setTimeout(function() {
            notification.style.opacity = '1';
        }, 10);

        // Suppression automatique
        setTimeout(function() {
            if (notification.parentNode) {
                notification.style.opacity = '0';
                setTimeout(function() {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, 3000);
    }

    /**
     * Vibration pour mobile (si supportée)
     */
    function vibrate() {
        if (navigator.vibrate) {
            navigator.vibrate([100, 50, 100]);
        }
    }

    /**
     * Fonction utilitaire pour déboguer
     */
    function getDebugInfo() {
        return {
            version: '1.3.0',
            config: config,
            isUserLoggedIn: isUserLoggedIn,
            protectionActive: protectionActive,
            isMobile: isMobile,
            isDevToolsOpen: isDevToolsOpen,
            userAgent: navigator.userAgent
        };
    }

    // Exposer la fonction de debug globalement (développement uniquement)
    if (typeof window !== 'undefined') {
        window.photoFloueDebug = getDebugInfo;
    }

    // Initialisation
    init();

})();