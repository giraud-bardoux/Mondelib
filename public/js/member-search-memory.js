/**
 * Système de mémorisation des recherches de membres pour SocialEngine 7.4
 * Utilise les cookies pour sauvegarder et restaurer les critères de recherche
 * 
 * @author Assistant
 * @version 1.0
 */

class MemberSearchMemory {
    constructor() {
        this.cookieName = 'se_member_search_memory';
        this.historyCookieName = 'se_member_search_history';
        this.maxHistoryItems = 10;
        this.cookieExpireDays = 30;
        
        this.init();
    }
    
    init() {
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }
    
    setup() {
        // Vérifier si nous sommes sur une page de recherche de membres
        const searchForm = document.querySelector('.field_search_criteria');
        if (!searchForm) {
            return;
        }
        
        console.log('Member Search Memory: Initialisation du système de mémorisation');
        
        // Restaurer les derniers critères de recherche
        this.restoreLastSearch();
        
        // Ajouter l'interface utilisateur
        this.addSearchHistoryUI();
        
        // Écouter les soumissions du formulaire
        this.setupFormListeners();
        
        // Écouter les changements dans les champs
        this.setupFieldListeners();
    }
    
    /**
     * Sauvegarde les critères de recherche dans un cookie
     */
    saveSearchCriteria(criteria) {
        const searchData = {
            criteria: criteria,
            timestamp: new Date().getTime(),
            url: window.location.pathname
        };
        
        this.setCookie(this.cookieName, JSON.stringify(searchData), this.cookieExpireDays);
        this.addToHistory(searchData);
        
        console.log('Member Search Memory: Critères sauvegardés', criteria);
    }
    
    /**
     * Restaure les derniers critères de recherche
     */
    restoreLastSearch() {
        const savedData = this.getCookie(this.cookieName);
        if (!savedData) {
            return;
        }
        
        try {
            const searchData = JSON.parse(savedData);
            // Ne restaurer que si c'est sur la même page et récent (moins de 24h)
            const dayAgo = new Date().getTime() - (24 * 60 * 60 * 1000);
            
            if (searchData.url === window.location.pathname && searchData.timestamp > dayAgo) {
                this.fillForm(searchData.criteria);
                console.log('Member Search Memory: Dernière recherche restaurée', searchData.criteria);
            }
        } catch (e) {
            console.error('Member Search Memory: Erreur lors de la restauration', e);
        }
    }
    
    /**
     * Remplit le formulaire avec les critères donnés
     */
    fillForm(criteria) {
        const form = document.querySelector('.field_search_criteria');
        if (!form) return;
        
        Object.keys(criteria).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field && criteria[fieldName] !== '') {
                if (field.type === 'checkbox') {
                    field.checked = criteria[fieldName] === '1' || criteria[fieldName] === true;
                } else {
                    field.value = criteria[fieldName];
                }
                
                // Déclencher l'événement change pour les champs qui en ont besoin
                const event = new Event('change', { bubbles: true });
                field.dispatchEvent(event);
            }
        });
    }
    
    /**
     * Extrait les critères de recherche du formulaire
     */
    extractSearchCriteria() {
        const form = document.querySelector('.field_search_criteria');
        if (!form) return {};
        
        const criteria = {};
        const formData = new FormData(form);
        
        // Champs texte et select
        const textFields = ['displayname', 'profile_type', 'location', 'lat', 'lng', 'miles'];
        textFields.forEach(fieldName => {
            const value = formData.get(fieldName);
            if (value && value.trim() !== '') {
                criteria[fieldName] = value.trim();
            }
        });
        
        // Champs checkbox
        const checkboxFields = ['extra[has_photo]', 'extra[is_online]'];
        checkboxFields.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field && field.checked) {
                criteria[fieldName] = '1';
            }
        });
        
        return criteria;
    }
    
    /**
     * Ajoute l'interface utilisateur pour l'historique des recherches
     */
    addSearchHistoryUI() {
        const form = document.querySelector('.field_search_criteria');
        if (!form) return;
        
        // Créer le conteneur pour l'historique
        const historyContainer = document.createElement('div');
        historyContainer.className = 'search-memory-container';
        historyContainer.innerHTML = `
            <div class="search-memory-header">
                <button type="button" class="search-memory-toggle" title="Historique des recherches">
                    📋 Recherches récentes
                </button>
                <button type="button" class="search-memory-clear" title="Effacer la dernière recherche">
                    🗑️ Effacer
                </button>
            </div>
            <div class="search-memory-history" style="display: none;">
                <div class="search-memory-list"></div>
            </div>
        `;
        
        // Ajouter les styles CSS
        this.addStyles();
        
        // Insérer avant le formulaire
        form.parentNode.insertBefore(historyContainer, form);
        
        // Événements
        const toggleBtn = historyContainer.querySelector('.search-memory-toggle');
        const clearBtn = historyContainer.querySelector('.search-memory-clear');
        const historyDiv = historyContainer.querySelector('.search-memory-history');
        
        toggleBtn.addEventListener('click', () => {
            const isVisible = historyDiv.style.display !== 'none';
            historyDiv.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) {
                this.loadSearchHistory();
            }
        });
        
        clearBtn.addEventListener('click', () => {
            this.clearLastSearch();
        });
    }
    
    /**
     * Charge et affiche l'historique des recherches
     */
    loadSearchHistory() {
        const historyData = this.getSearchHistory();
        const listContainer = document.querySelector('.search-memory-list');
        
        if (!listContainer) return;
        
        if (historyData.length === 0) {
            listContainer.innerHTML = '<div class="search-memory-item search-memory-empty">Aucune recherche récente</div>';
            return;
        }
        
        listContainer.innerHTML = historyData.map((item, index) => {
            const criteriaText = this.formatCriteriaForDisplay(item.criteria);
            const date = new Date(item.timestamp).toLocaleString('fr-FR');
            
            return `
                <div class="search-memory-item" data-index="${index}">
                    <div class="search-memory-criteria">${criteriaText}</div>
                    <div class="search-memory-date">${date}</div>
                    <button type="button" class="search-memory-apply" title="Appliquer cette recherche">
                        ↻ Appliquer
                    </button>
                </div>
            `;
        }).join('');
        
        // Ajouter les événements pour appliquer les recherches
        listContainer.querySelectorAll('.search-memory-apply').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.closest('.search-memory-item').dataset.index);
                const searchData = historyData[index];
                this.fillForm(searchData.criteria);
                
                // Déclencher la recherche si la fonction existe
                if (typeof window.searchMembers === 'function') {
                    window.searchMembers();
                }
            });
        });
    }
    
    /**
     * Formate les critères pour l'affichage
     */
    formatCriteriaForDisplay(criteria) {
        const parts = [];
        
        if (criteria.displayname) {
            parts.push(`Nom: "${criteria.displayname}"`);
        }
        
        if (criteria.location) {
            parts.push(`Lieu: "${criteria.location}"`);
        }
        
        if (criteria.miles) {
            parts.push(`Rayon: ${criteria.miles} miles`);
        }
        
        if (criteria['extra[has_photo]']) {
            parts.push('Avec photo');
        }
        
        if (criteria['extra[is_online]']) {
            parts.push('En ligne');
        }
        
        return parts.length > 0 ? parts.join(', ') : 'Recherche vide';
    }
    
    /**
     * Ajoute les critères à l'historique
     */
    addToHistory(searchData) {
        let history = this.getSearchHistory();
        
        // Éviter les doublons récents (même critères dans les 5 dernières recherches)
        const criteriaString = JSON.stringify(searchData.criteria);
        history = history.filter((item, index) => {
            return index >= 5 || JSON.stringify(item.criteria) !== criteriaString;
        });
        
        // Ajouter en tête
        history.unshift(searchData);
        
        // Limiter la taille
        if (history.length > this.maxHistoryItems) {
            history = history.slice(0, this.maxHistoryItems);
        }
        
        this.setCookie(this.historyCookieName, JSON.stringify(history), this.cookieExpireDays);
    }
    
    /**
     * Récupère l'historique des recherches
     */
    getSearchHistory() {
        const historyData = this.getCookie(this.historyCookieName);
        if (!historyData) return [];
        
        try {
            return JSON.parse(historyData);
        } catch (e) {
            console.error('Member Search Memory: Erreur lecture historique', e);
            return [];
        }
    }
    
    /**
     * Efface la dernière recherche sauvegardée
     */
    clearLastSearch() {
        this.deleteCookie(this.cookieName);
        console.log('Member Search Memory: Dernière recherche effacée');
        
        // Vider le formulaire
        const form = document.querySelector('.field_search_criteria');
        if (form) {
            form.reset();
        }
    }
    
    /**
     * Configure les écouteurs d'événements pour le formulaire
     */
    setupFormListeners() {
        const form = document.querySelector('.field_search_criteria');
        if (!form) return;
        
        // Intercepter la soumission du formulaire
        form.addEventListener('submit', (e) => {
            const criteria = this.extractSearchCriteria();
            if (Object.keys(criteria).length > 0) {
                this.saveSearchCriteria(criteria);
            }
        });
        
        // Intercepter les appels à searchMembers si elle existe
        if (typeof window.searchMembers === 'function') {
            const originalSearchMembers = window.searchMembers;
            window.searchMembers = () => {
                const criteria = this.extractSearchCriteria();
                if (Object.keys(criteria).length > 0) {
                    this.saveSearchCriteria(criteria);
                }
                return originalSearchMembers();
            };
        }
    }
    
    /**
     * Configure les écouteurs pour les changements de champs
     */
    setupFieldListeners() {
        const form = document.querySelector('.field_search_criteria');
        if (!form) return;
        
        // Sauvegarder automatiquement après un délai d'inactivité
        let saveTimeout;
        const autoSave = () => {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const criteria = this.extractSearchCriteria();
                if (Object.keys(criteria).length > 0) {
                    this.saveSearchCriteria(criteria);
                }
            }, 2000); // Sauvegarder après 2 secondes d'inactivité
        };
        
        // Écouter les changements sur tous les champs
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.addEventListener('input', autoSave);
            field.addEventListener('change', autoSave);
        });
    }
    
    /**
     * Ajoute les styles CSS pour l'interface
     */
    addStyles() {
        if (document.getElementById('search-memory-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'search-memory-styles';
        style.textContent = `
            .search-memory-container {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #f9f9f9;
            }
            
            .search-memory-header {
                padding: 10px;
                background: #f0f0f0;
                border-bottom: 1px solid #ddd;
                display: flex;
                gap: 10px;
            }
            
            .search-memory-toggle,
            .search-memory-clear {
                padding: 5px 10px;
                border: 1px solid #ccc;
                background: white;
                border-radius: 3px;
                cursor: pointer;
                font-size: 12px;
            }
            
            .search-memory-toggle:hover,
            .search-memory-clear:hover {
                background: #e9e9e9;
            }
            
            .search-memory-history {
                max-height: 300px;
                overflow-y: auto;
            }
            
            .search-memory-item {
                padding: 10px;
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .search-memory-item:last-child {
                border-bottom: none;
            }
            
            .search-memory-criteria {
                flex: 1;
                font-weight: bold;
                color: #333;
            }
            
            .search-memory-date {
                font-size: 11px;
                color: #666;
                min-width: 120px;
            }
            
            .search-memory-apply {
                padding: 3px 8px;
                border: 1px solid #007cba;
                background: #0085ba;
                color: white;
                border-radius: 3px;
                cursor: pointer;
                font-size: 11px;
            }
            
            .search-memory-apply:hover {
                background: #005a87;
            }
            
            .search-memory-empty {
                color: #666;
                font-style: italic;
                text-align: center;
            }
        `;
        
        document.head.appendChild(style);
    }
    
    /**
     * Utilitaires pour les cookies
     */
    setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires.toUTCString()};path=/`;
    }
    
    getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
        }
        return null;
    }
    
    deleteCookie(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/`;
    }
}

// Initialiser le système dès que possible
if (typeof window !== 'undefined') {
    window.memberSearchMemory = new MemberSearchMemory();
}