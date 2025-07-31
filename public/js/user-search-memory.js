/**
 * User Search Memory - SocialEngine 7.4
 * Mémorise les recherches de membres en utilisant des cookies
 * 
 * @author Claude Assistant
 * @version 1.0
 */

(function() {
    'use strict';
    
    // Configuration
    const COOKIE_NAME = 'user_search_memory';
    const COOKIE_EXPIRY_DAYS = 30;
    
    /**
     * Gestionnaire de cookies
     */
    const CookieManager = {
        /**
         * Définit un cookie
         */
        set: function(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + encodeURIComponent(JSON.stringify(value)) + ';expires=' + expires.toUTCString() + ';path=/';
        },
        
        /**
         * Récupère un cookie
         */
        get: function(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) {
                    try {
                        return JSON.parse(decodeURIComponent(c.substring(nameEQ.length, c.length)));
                    } catch (e) {
                        return null;
                    }
                }
            }
            return null;
        },
        
        /**
         * Supprime un cookie
         */
        remove: function(name) {
            document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
        }
    };
    
    /**
     * Gestionnaire de recherche mémorisée
     */
    const SearchMemory = {
        /**
         * Sauvegarde les paramètres de recherche
         */
        saveSearchParams: function() {
            const form = document.querySelector('.field_search_criteria');
            if (!form) return;
            
            const formData = new FormData(form);
            const searchParams = {};
            
            // Récupère tous les paramètres du formulaire
            for (let [key, value] of formData.entries()) {
                if (value && value.trim() !== '') {
                    searchParams[key] = value;
                }
            }
            
            // Sauvegarde dans le cookie
            CookieManager.set(COOKIE_NAME, searchParams, COOKIE_EXPIRY_DAYS);
            
            // Ajoute un indicateur visuel
            form.classList.add('has-memory');
            
            console.log('Recherche sauvegardée:', searchParams);
        },
        
        /**
         * Restaure les paramètres de recherche
         */
        restoreSearchParams: function() {
            const savedParams = CookieManager.get(COOKIE_NAME);
            if (!savedParams) return;
            
            const form = document.querySelector('.field_search_criteria');
            if (!form) return;
            
            // Ajoute une classe pour l'animation de restauration
            form.classList.add('restoring');
            
            // Restaure les valeurs dans le formulaire
            Object.keys(savedParams).forEach(key => {
                const element = form.querySelector('[name="' + key + '"]');
                if (element) {
                    if (element.type === 'checkbox') {
                        element.checked = savedParams[key] === '1' || savedParams[key] === true;
                    } else if (element.type === 'select-one') {
                        element.value = savedParams[key];
                    } else {
                        element.value = savedParams[key];
                    }
                }
            });
            
            // Retire la classe de restauration après un délai
            setTimeout(() => {
                form.classList.remove('restoring');
                form.classList.add('has-memory');
            }, 1000);
            
            console.log('Recherche restaurée:', savedParams);
        },
        
        /**
         * Efface la recherche mémorisée
         */
        clearSearchMemory: function() {
            CookieManager.remove(COOKIE_NAME);
            
            // Retire les indicateurs visuels
            const form = document.querySelector('.field_search_criteria');
            if (form) {
                form.classList.remove('has-memory');
            }
            
            console.log('Mémoire de recherche effacée');
        },
        
        /**
         * Initialise le système de mémorisation
         */
        init: function() {
            // Restaure les paramètres au chargement de la page
            this.restoreSearchParams();
            
            // Écoute les soumissions de formulaire
            const form = document.querySelector('.field_search_criteria');
            if (form) {
                form.addEventListener('submit', () => {
                    setTimeout(() => {
                        this.saveSearchParams();
                    }, 100);
                });
            }
            
            // Écoute les changements de champs pour sauvegarder automatiquement
            document.addEventListener('change', (e) => {
                if (e.target.closest('.field_search_criteria')) {
                    setTimeout(() => {
                        this.saveSearchParams();
                    }, 500);
                }
            });
            
            // Écoute les clics sur les boutons de recherche
            document.addEventListener('click', (e) => {
                if (e.target.closest('.field_search_criteria button[type="submit"]')) {
                    setTimeout(() => {
                        this.saveSearchParams();
                    }, 100);
                }
            });
            
            // Ajoute un bouton pour effacer la mémoire (optionnel)
            this.addClearButton();
        },
        
        /**
         * Affiche une notification
         */
        showNotification: function(message, type = 'success') {
            // Supprime les notifications existantes
            const existingNotifications = document.querySelectorAll('.search-memory-notification');
            existingNotifications.forEach(notification => notification.remove());
            
            const notification = document.createElement('div');
            notification.className = `search-memory-notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Affiche la notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Cache la notification après 3 secondes
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        },
        
        /**
         * Ajoute un bouton pour effacer la mémoire de recherche
         */
        addClearButton: function() {
            const form = document.querySelector('.field_search_criteria');
            if (!form) return;
            
            // Vérifie si le bouton existe déjà
            if (document.getElementById('clear-search-memory')) return;
            
            const clearButton = document.createElement('button');
            clearButton.id = 'clear-search-memory';
            clearButton.type = 'button';
            clearButton.className = 'btn btn-alt';
            clearButton.innerHTML = '<i class="icon_clear"></i> Effacer la recherche mémorisée';
            
            clearButton.addEventListener('click', () => {
                this.clearSearchMemory();
                this.showNotification('Recherche mémorisée effacée avec succès');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            });
            
            // Trouve le bouton de recherche existant et ajoute le bouton d'effacement à côté
            const searchButton = form.querySelector('button[type="submit"]');
            if (searchButton && searchButton.parentNode) {
                searchButton.parentNode.appendChild(clearButton);
            }
        }
    };
    
    /**
     * Initialisation quand le DOM est prêt
     */
    function initSearchMemory() {
        // Attendre que SocialEngine soit chargé
        if (typeof en4 !== 'undefined' && en4.core && en4.core.runonce) {
            en4.core.runonce.add(function() {
                SearchMemory.init();
            });
        } else {
            // Fallback si SocialEngine n'est pas encore chargé
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    SearchMemory.init();
                }, 1000);
            });
        }
    }
    
    // Initialisation
    initSearchMemory();
    
    // Expose les fonctions globalement pour debug
    window.UserSearchMemory = SearchMemory;
    window.CookieManager = CookieManager;
    
})();