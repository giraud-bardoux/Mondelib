/**
 * Member Search Memory for SocialEngine
 * Sauvegarde et restaure les paramètres de recherche de membres
 */

(function() {
    'use strict';
    
    // Configuration
    const COOKIE_NAME = 'member_search_params';
    const COOKIE_EXPIRY_DAYS = 30;
    
    // Fonction pour obtenir un cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    // Fonction pour définir un cookie
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
    }
    
    // Fonction pour supprimer un cookie
    function deleteCookie(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/`;
    }
    
    // Fonction pour sauvegarder les paramètres de recherche
    function saveSearchParams() {
        const form = document.querySelector('.layout_user_browse_search form');
        if (!form) return;
        
        const formData = new FormData(form);
        const searchParams = {};
        
        // Parcourir tous les champs du formulaire
        for (let [key, value] of formData.entries()) {
            if (value && value !== '' && value !== '0') {
                searchParams[key] = value;
            }
        }
        
        // Sauvegarder aussi les champs select
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            if (select.value && select.value !== '' && select.value !== '0') {
                searchParams[select.name] = select.value;
            }
        });
        
        // Sauvegarder les checkboxes
        const checkboxes = form.querySelectorAll('input[type="checkbox"]:checked');
        checkboxes.forEach(checkbox => {
            searchParams[checkbox.name] = checkbox.value;
        });
        
        // Sauvegarder les radios
        const radios = form.querySelectorAll('input[type="radio"]:checked');
        radios.forEach(radio => {
            searchParams[radio.name] = radio.value;
        });
        
        // Sauvegarder dans le cookie
        if (Object.keys(searchParams).length > 0) {
            setCookie(COOKIE_NAME, encodeURIComponent(JSON.stringify(searchParams)), COOKIE_EXPIRY_DAYS);
        }
    }
    
    // Fonction pour restaurer les paramètres de recherche
    function restoreSearchParams() {
        const savedParams = getCookie(COOKIE_NAME);
        if (!savedParams) return;
        
        try {
            const searchParams = JSON.parse(decodeURIComponent(savedParams));
            const form = document.querySelector('.layout_user_browse_search form');
            if (!form) return;
            
            // Restaurer les valeurs
            Object.keys(searchParams).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field) {
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        const specificField = form.querySelector(`[name="${key}"][value="${searchParams[key]}"]`);
                        if (specificField) {
                            specificField.checked = true;
                        }
                    } else {
                        field.value = searchParams[key];
                        
                        // Déclencher l'événement change pour mettre à jour les dépendances
                        const event = new Event('change', { bubbles: true });
                        field.dispatchEvent(event);
                    }
                }
            });
            
            // Afficher le bouton de réinitialisation
            showResetButton();
            
        } catch (e) {
            console.error('Erreur lors de la restauration des paramètres de recherche:', e);
        }
    }
    
    // Fonction pour réinitialiser la recherche
    function resetSearch() {
        deleteCookie(COOKIE_NAME);
        const form = document.querySelector('.layout_user_browse_search form');
        if (form) {
            form.reset();
            
            // Déclencher la recherche avec les paramètres par défaut
            if (typeof searchMembers === 'function') {
                searchMembers();
            }
        }
        hideResetButton();
    }
    
    // Fonction pour afficher le bouton de réinitialisation
    function showResetButton() {
        let resetButton = document.getElementById('member-search-reset');
        if (!resetButton) {
            const form = document.querySelector('.layout_user_browse_search form');
            if (!form) return;
            
            resetButton = document.createElement('button');
            resetButton.id = 'member-search-reset';
            resetButton.type = 'button';
            resetButton.className = 'button member-search-reset-btn';
            resetButton.innerHTML = '<i class="fa fa-times"></i> Réinitialiser la recherche';
            resetButton.onclick = resetSearch;
            
            // Ajouter le bouton après le bouton de recherche
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton && submitButton.parentNode) {
                submitButton.parentNode.insertBefore(resetButton, submitButton.nextSibling);
            }
        }
        resetButton.style.display = 'inline-block';
    }
    
    // Fonction pour cacher le bouton de réinitialisation
    function hideResetButton() {
        const resetButton = document.getElementById('member-search-reset');
        if (resetButton) {
            resetButton.style.display = 'none';
        }
    }
    
    // Initialisation
    function init() {
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }
        
        // Vérifier si nous sommes sur la page de recherche de membres
        if (!window.location.pathname.includes('/members') && !window.location.pathname.includes('/user')) {
            return;
        }
        
        // Restaurer les paramètres sauvegardés
        setTimeout(restoreSearchParams, 100);
        
        // Intercepter la soumission du formulaire
        const form = document.querySelector('.layout_user_browse_search form');
        if (form) {
            // Sauvegarder lors de la soumission
            form.addEventListener('submit', function(e) {
                saveSearchParams();
            });
            
            // Sauvegarder aussi lors des changements de champs
            const fields = form.querySelectorAll('input, select, textarea');
            fields.forEach(field => {
                field.addEventListener('change', function() {
                    // Attendre un peu pour que tous les changements soient appliqués
                    setTimeout(saveSearchParams, 500);
                });
            });
        }
        
        // Si nous avons l'ancienne fonction searchMembers, la wrapper
        if (typeof window.searchMembers === 'function') {
            const originalSearchMembers = window.searchMembers;
            window.searchMembers = function() {
                saveSearchParams();
                return originalSearchMembers.apply(this, arguments);
            };
        }
    }
    
    // Lancer l'initialisation
    init();
    
    // Réinitialiser si la page est rechargée via AJAX
    if (typeof en4 !== 'undefined' && en4.core && en4.core.runonce) {
        en4.core.runonce.add(function() {
            setTimeout(init, 100);
        });
    }
})();