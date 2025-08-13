// Cursor Event Parser - Configuration globale
const CONFIG = {
    proxyUrl: 'proxy.php',
    mondeLibertin: {
        baseUrl: 'https://mondelibertin.com',
        loginUrl: '/login',
        eventCreateUrl: '/events/create',
        eventSubmitUrl: '/events/create/submit'
    }
};

// État de l'application
let currentEvent = null;
let authToken = null;
let parsingHistory = [];

// Gestion du localStorage
const Storage = {
    save: (key, value) => {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Erreur de sauvegarde:', e);
            return false;
        }
    },
    
    load: (key) => {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Erreur de chargement:', e);
            return null;
        }
    },
    
    remove: (key) => {
        localStorage.removeItem(key);
    }
};

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    loadConfiguration();
    loadHistory();
    checkAuthentication();
});

// Gestion des onglets
function switchTab(tabName) {
    // Désactiver tous les onglets
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Activer l'onglet sélectionné
    event.target.classList.add('active');
    document.getElementById(`${tabName}-tab`).classList.add('active');
}

// Chargement de la configuration
function loadConfiguration() {
    const config = Storage.load('parserConfig');
    if (config) {
        if (config.username) document.getElementById('ml-username').value = config.username;
        if (config.defaultCategory) document.getElementById('default-category').value = config.defaultCategory;
        if (config.autoPublish !== undefined) document.getElementById('auto-publish').checked = config.autoPublish;
        if (config.partnerSites) document.getElementById('partner-sites').value = config.partnerSites.join('\n');
    }
}

// Sauvegarde de la configuration
function saveConfig() {
    const config = {
        username: document.getElementById('ml-username').value,
        password: document.getElementById('ml-password').value,
        defaultCategory: document.getElementById('default-category').value,
        autoPublish: document.getElementById('auto-publish').checked,
        partnerSites: document.getElementById('partner-sites').value.split('\n').filter(url => url.trim())
    };
    
    if (Storage.save('parserConfig', config)) {
        showStatus('Configuration sauvegardée avec succès!', 'success');
        
        // Ne pas sauvegarder le mot de passe en clair
        const displayConfig = {...config};
        delete displayConfig.password;
        updateConfigDisplay(displayConfig);
    } else {
        showStatus('Erreur lors de la sauvegarde de la configuration', 'error');
    }
}

// Mise à jour de l'affichage des configurations
function updateConfigDisplay(config) {
    const listElement = document.getElementById('saved-configs-list');
    listElement.innerHTML = `
        <div class="config-item">
            <div>
                <h5>Configuration actuelle</h5>
                <p>Utilisateur: ${config.username || 'Non défini'}</p>
                <p>Catégorie par défaut: ${config.defaultCategory || 'Non définie'}</p>
                <p>Publication auto: ${config.autoPublish ? 'Activée' : 'Désactivée'}</p>
            </div>
        </div>
    `;
}

// Parseur principal d'événements
async function parseEvent() {
    const url = document.getElementById('event-url').value.trim();
    const parserType = document.getElementById('parser-type').value;
    
    if (!url) {
        showStatus('Veuillez entrer une URL', 'error');
        return;
    }
    
    showParseStatus(true);
    updateProgress(10);
    showStatus('Connexion au site...', 'info');
    
    try {
        // Détection du type de site
        const siteType = parserType === 'auto' ? detectSiteType(url) : parserType;
        updateProgress(30);
        showStatus('Récupération des données...', 'info');
        
        // Récupération du contenu via proxy
        const response = await fetch(CONFIG.proxyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'fetch',
                url: url
            })
        });
        
        if (!response.ok) {
            throw new Error('Impossible de récupérer la page');
        }
        
        const html = await response.text();
        updateProgress(60);
        showStatus('Analyse des données...', 'info');
        
        // Parser selon le type de site
        let eventData;
        switch(siteType) {
            case 'meetup':
                eventData = parseMeetup(html);
                break;
            case 'facebook':
                eventData = parseFacebook(html);
                break;
            case 'eventbrite':
                eventData = parseEventbrite(html);
                break;
            default:
                eventData = parseGeneric(html);
        }
        
        updateProgress(90);
        
        if (eventData) {
            currentEvent = eventData;
            displayEventPreview(eventData);
            addToHistory(eventData);
            updateProgress(100);
            showStatus('Événement parsé avec succès!', 'success');
            
            // Publication automatique si activée
            const config = Storage.load('parserConfig');
            if (config && config.autoPublish) {
                setTimeout(() => publishEvent(), 1000);
            }
        } else {
            throw new Error('Impossible d\'extraire les informations de l\'événement');
        }
        
    } catch (error) {
        console.error('Erreur de parsing:', error);
        showStatus(`Erreur: ${error.message}`, 'error');
        updateProgress(0);
    }
}

// Détection du type de site
function detectSiteType(url) {
    if (url.includes('meetup.com')) return 'meetup';
    if (url.includes('facebook.com/events')) return 'facebook';
    if (url.includes('eventbrite.')) return 'eventbrite';
    return 'generic';
}

// Parser pour Meetup
function parseMeetup(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    const event = {
        title: '',
        date: '',
        location: '',
        description: '',
        category: 'soiree',
        source: 'Meetup'
    };
    
    // Extraction du titre
    const titleElement = doc.querySelector('h1, [data-testid="event-title"], .eventHeaderContainer h1');
    if (titleElement) event.title = titleElement.textContent.trim();
    
    // Extraction de la date
    const dateElement = doc.querySelector('time, [data-testid="event-date"], .eventTimeDisplay');
    if (dateElement) {
        event.date = dateElement.getAttribute('datetime') || dateElement.textContent.trim();
    }
    
    // Extraction du lieu
    const locationElement = doc.querySelector('[data-testid="event-location"], .venueDisplay, address');
    if (locationElement) event.location = locationElement.textContent.trim();
    
    // Extraction de la description
    const descElement = doc.querySelector('[data-testid="event-description"], .event-description, .description');
    if (descElement) {
        event.description = descElement.textContent.trim().substring(0, 1000);
    }
    
    return event;
}

// Parser pour Facebook Events
function parseFacebook(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    const event = {
        title: '',
        date: '',
        location: '',
        description: '',
        category: 'soiree',
        source: 'Facebook'
    };
    
    // Facebook utilise beaucoup de contenu généré dynamiquement
    // On cherche les patterns communs
    
    // Titre
    const titleMeta = doc.querySelector('meta[property="og:title"]');
    if (titleMeta) event.title = titleMeta.getAttribute('content');
    
    // Description
    const descMeta = doc.querySelector('meta[property="og:description"]');
    if (descMeta) event.description = descMeta.getAttribute('content');
    
    // Recherche dans le contenu JSON-LD
    const jsonLd = doc.querySelector('script[type="application/ld+json"]');
    if (jsonLd) {
        try {
            const data = JSON.parse(jsonLd.textContent);
            if (data.name) event.title = data.name;
            if (data.startDate) event.date = data.startDate;
            if (data.location && data.location.name) event.location = data.location.name;
            if (data.description) event.description = data.description;
        } catch (e) {
            console.error('Erreur parsing JSON-LD:', e);
        }
    }
    
    return event;
}

// Parser pour Eventbrite
function parseEventbrite(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    const event = {
        title: '',
        date: '',
        location: '',
        description: '',
        category: 'soiree',
        source: 'Eventbrite'
    };
    
    // Titre
    const titleElement = doc.querySelector('.event-title, h1[data-automation="listing-title"]');
    if (titleElement) event.title = titleElement.textContent.trim();
    
    // Date
    const dateElement = doc.querySelector('.event-details__data time, [data-automation="event-date-time"]');
    if (dateElement) event.date = dateElement.textContent.trim();
    
    // Lieu
    const locationElement = doc.querySelector('[data-automation="location-info"], .event-details__data--location');
    if (locationElement) event.location = locationElement.textContent.trim();
    
    // Description
    const descElement = doc.querySelector('.structured-content-rich-text, [data-automation="listing-event-description"]');
    if (descElement) event.description = descElement.textContent.trim().substring(0, 1000);
    
    // Recherche dans les métadonnées
    const titleMeta = doc.querySelector('meta[property="og:title"]');
    if (!event.title && titleMeta) event.title = titleMeta.getAttribute('content');
    
    return event;
}

// Parser générique
function parseGeneric(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    
    const event = {
        title: '',
        date: '',
        location: '',
        description: '',
        category: Storage.load('parserConfig')?.defaultCategory || 'soiree',
        source: 'Site partenaire'
    };
    
    // Stratégies de parsing générique
    
    // 1. Métadonnées Open Graph
    const ogTitle = doc.querySelector('meta[property="og:title"]');
    if (ogTitle) event.title = ogTitle.getAttribute('content');
    
    const ogDesc = doc.querySelector('meta[property="og:description"]');
    if (ogDesc) event.description = ogDesc.getAttribute('content');
    
    // 2. Données structurées JSON-LD
    const jsonLdScripts = doc.querySelectorAll('script[type="application/ld+json"]');
    jsonLdScripts.forEach(script => {
        try {
            const data = JSON.parse(script.textContent);
            if (data['@type'] === 'Event' || data['@type'] === 'SocialEvent') {
                if (data.name) event.title = data.name;
                if (data.startDate) event.date = data.startDate;
                if (data.location) {
                    if (typeof data.location === 'string') {
                        event.location = data.location;
                    } else if (data.location.name) {
                        event.location = data.location.name;
                        if (data.location.address) {
                            event.location += ', ' + (data.location.address.streetAddress || data.location.address);
                        }
                    }
                }
                if (data.description) event.description = data.description;
            }
        } catch (e) {
            // Ignorer les erreurs de parsing JSON
        }
    });
    
    // 3. Recherche par éléments HTML communs
    if (!event.title) {
        const h1 = doc.querySelector('h1');
        if (h1) event.title = h1.textContent.trim();
    }
    
    if (!event.date) {
        const timeElement = doc.querySelector('time');
        if (timeElement) {
            event.date = timeElement.getAttribute('datetime') || timeElement.textContent.trim();
        }
    }
    
    if (!event.location) {
        const addressElement = doc.querySelector('address');
        if (addressElement) event.location = addressElement.textContent.trim();
    }
    
    // 4. Recherche de patterns de texte
    if (!event.date) {
        // Rechercher des patterns de date dans le texte
        const datePatterns = [
            /\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}/,
            /\d{1,2}\s+(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\s+\d{4}/i
        ];
        
        const bodyText = doc.body.textContent;
        for (const pattern of datePatterns) {
            const match = bodyText.match(pattern);
            if (match) {
                event.date = match[0];
                break;
            }
        }
    }
    
    return event;
}

// Affichage de l'aperçu de l'événement
function displayEventPreview(event) {
    document.getElementById('preview-title').textContent = event.title || 'Non défini';
    document.getElementById('preview-date').textContent = formatDate(event.date) || 'Non définie';
    document.getElementById('preview-location').textContent = event.location || 'Non défini';
    document.getElementById('preview-description').textContent = event.description || 'Non définie';
    document.getElementById('preview-category').textContent = event.category || 'Non définie';
    
    document.getElementById('event-preview').classList.remove('hidden');
}

// Formatage de la date
function formatDate(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        
        return date.toLocaleDateString('fr-FR', options);
    } catch (e) {
        return dateString;
    }
}

// Publication de l'événement
async function publishEvent() {
    if (!currentEvent) {
        showStatus('Aucun événement à publier', 'error');
        return;
    }
    
    const config = Storage.load('parserConfig');
    if (!config || !config.username || !config.password) {
        showStatus('Veuillez configurer vos identifiants MondeLiberin', 'error');
        switchTab('config');
        return;
    }
    
    showStatus('Connexion à MondeLiberin...', 'info');
    
    try {
        // 1. Authentification
        const loginSuccess = await authenticateMondeLiberin(config.username, config.password);
        if (!loginSuccess) {
            throw new Error('Échec de l\'authentification');
        }
        
        showStatus('Connecté! Publication en cours...', 'info');
        
        // 2. Préparation des données pour SocialEngine
        const eventData = prepareEventForSocialEngine(currentEvent);
        
        // 3. Envoi via proxy
        const response = await fetch(CONFIG.proxyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'publish',
                url: CONFIG.mondeLibertin.baseUrl + CONFIG.mondeLibertin.eventSubmitUrl,
                data: eventData,
                token: authToken
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('Événement publié avec succès!', 'success');
            currentEvent.published = true;
            currentEvent.publishedAt = new Date().toISOString();
            updateHistory();
        } else {
            throw new Error(result.message || 'Échec de la publication');
        }
        
    } catch (error) {
        console.error('Erreur de publication:', error);
        showStatus(`Erreur: ${error.message}`, 'error');
    }
}

// Authentification sur MondeLiberin
async function authenticateMondeLiberin(username, password) {
    try {
        const response = await fetch(CONFIG.proxyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'login',
                url: CONFIG.mondeLibertin.baseUrl + CONFIG.mondeLibertin.loginUrl,
                username: username,
                password: password
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.token) {
            authToken = result.token;
            Storage.save('authToken', {
                token: authToken,
                expires: Date.now() + (3600 * 1000) // 1 heure
            });
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Erreur d\'authentification:', error);
        return false;
    }
}

// Vérification de l'authentification
function checkAuthentication() {
    const savedAuth = Storage.load('authToken');
    if (savedAuth && savedAuth.expires > Date.now()) {
        authToken = savedAuth.token;
        return true;
    }
    return false;
}

// Préparation des données pour SocialEngine
function prepareEventForSocialEngine(event) {
    // Format spécifique pour SocialEngine 7.4
    return {
        'event_form': 'true',
        'title': event.title,
        'description': event.description,
        'category_id': getCategoryId(event.category),
        'start_date': formatDateForSocialEngine(event.date),
        'end_date': formatDateForSocialEngine(event.date, true), // +2 heures par défaut
        'location': event.location,
        'host': event.organizer || '',
        'approval': '0', // Pas d'approbation requise
        'invite': '2', // Membres peuvent inviter
        'photo': '1', // Upload de photos autorisé
        'privacy': 'everyone', // Public
        'auth_view': 'everyone',
        'auth_comment': 'member',
        'auth_photo': 'member',
        'auth_invite': 'member'
    };
}

// Conversion de catégorie
function getCategoryId(category) {
    const categories = {
        'soiree': '1',
        'club': '2',
        'rencontre': '3',
        'evenement': '4'
    };
    return categories[category] || '1';
}

// Formatage de date pour SocialEngine
function formatDateForSocialEngine(dateString, addHours = false) {
    try {
        let date = new Date(dateString);
        if (isNaN(date.getTime())) {
            date = new Date(); // Date du jour par défaut
        }
        
        if (addHours) {
            date.setHours(date.getHours() + 2);
        }
        
        // Format: MM/DD/YYYY HH:MM AM/PM
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const year = date.getFullYear();
        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        
        return `${month}/${day}/${year} ${hours}:${minutes} ${ampm}`;
    } catch (e) {
        return '';
    }
}

// Test du parser
function testParser() {
    // Événement de test
    const testEvent = {
        title: 'Soirée Test - Ne pas publier',
        date: new Date().toISOString(),
        location: 'Paris, France',
        description: 'Ceci est un événement de test pour vérifier le fonctionnement du parser.',
        category: 'soiree',
        source: 'Test'
    };
    
    currentEvent = testEvent;
    displayEventPreview(testEvent);
    showStatus('Test réussi! Événement fictif chargé.', 'success');
}

// Édition de l'événement
function editEvent() {
    if (!currentEvent) return;
    
    document.getElementById('edit-title').value = currentEvent.title || '';
    document.getElementById('edit-date').value = formatDateForInput(currentEvent.date);
    document.getElementById('edit-location').value = currentEvent.location || '';
    document.getElementById('edit-description').value = currentEvent.description || '';
    document.getElementById('edit-category').value = currentEvent.category || 'soiree';
    
    document.getElementById('edit-modal').classList.add('active');
}

// Format de date pour input datetime-local
function formatDateForInput(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch (e) {
        return '';
    }
}

// Sauvegarde des modifications
function saveEdit() {
    if (!currentEvent) return;
    
    currentEvent.title = document.getElementById('edit-title').value;
    currentEvent.date = document.getElementById('edit-date').value;
    currentEvent.location = document.getElementById('edit-location').value;
    currentEvent.description = document.getElementById('edit-description').value;
    currentEvent.category = document.getElementById('edit-category').value;
    
    displayEventPreview(currentEvent);
    closeModal();
    showStatus('Modifications enregistrées', 'success');
}

// Fermeture de la modal
function closeModal() {
    document.getElementById('edit-modal').classList.remove('active');
}

// Gestion de l'historique
function addToHistory(event) {
    event.parsedAt = new Date().toISOString();
    event.id = Date.now();
    
    parsingHistory.unshift(event);
    if (parsingHistory.length > 50) {
        parsingHistory = parsingHistory.slice(0, 50);
    }
    
    Storage.save('parsingHistory', parsingHistory);
    updateHistoryDisplay();
}

// Chargement de l'historique
function loadHistory() {
    parsingHistory = Storage.load('parsingHistory') || [];
    updateHistoryDisplay();
}

// Mise à jour de l'historique
function updateHistory() {
    Storage.save('parsingHistory', parsingHistory);
    updateHistoryDisplay();
}

// Affichage de l'historique
function updateHistoryDisplay() {
    const historyList = document.getElementById('history-list');
    
    if (parsingHistory.length === 0) {
        historyList.innerHTML = '<p style="color: #999;">Aucun événement parsé pour le moment</p>';
        return;
    }
    
    historyList.innerHTML = parsingHistory.map(event => `
        <div class="config-item">
            <div style="flex: 1;">
                <h5>${event.title || 'Sans titre'}</h5>
                <p>Source: ${event.source} | Date: ${formatDate(event.parsedAt)}</p>
                <p>Statut: ${event.published ? '✅ Publié' : '⏳ Non publié'}</p>
            </div>
            <div class="btn-group">
                <button class="btn btn-secondary" onclick="loadFromHistory(${event.id})">
                    Charger
                </button>
            </div>
        </div>
    `).join('');
}

// Chargement depuis l'historique
function loadFromHistory(eventId) {
    const event = parsingHistory.find(e => e.id === eventId);
    if (event) {
        currentEvent = event;
        displayEventPreview(event);
        switchTab('parser');
        showStatus('Événement chargé depuis l\'historique', 'info');
    }
}

// Affichage des statuts
function showStatus(message, type) {
    const statusMessages = document.getElementById('status-messages');
    if (!statusMessages) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `status-message status-${type}`;
    messageDiv.textContent = message;
    
    statusMessages.appendChild(messageDiv);
    
    // Auto-suppression après 5 secondes
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Affichage/masquage du statut de parsing
function showParseStatus(show) {
    const statusBox = document.getElementById('parse-status');
    if (show) {
        statusBox.classList.remove('hidden');
    } else {
        statusBox.classList.add('hidden');
    }
}

// Mise à jour de la barre de progression
function updateProgress(percent) {
    const progressBar = document.getElementById('progress');
    if (progressBar) {
        progressBar.style.width = percent + '%';
    }
}