// Application principale du parseur d'événements
class EventParserApp {
    constructor() {
        this.parsedEvents = [];
        this.currentEvent = null;
        this.isParsing = false;
        this.isPublishing = false;
        
        this.initializeElements();
        this.bindEvents();
        this.updateCurrentTime();
        this.log('Application initialisée', 'info');
    }

    initializeElements() {
        // Éléments de formulaire
        this.targetUrlInput = document.getElementById('targetUrl');
        this.mondelibertinUrlInput = document.getElementById('mondelibertinUrl');
        this.apiKeyInput = document.getElementById('apiKey');
        
        // Boutons
        this.parseBtn = document.getElementById('parseBtn');
        this.clearBtn = document.getElementById('clearBtn');
        this.publishBtn = document.getElementById('publishBtn');
        this.clearLogsBtn = document.getElementById('clearLogsBtn');
        
        // Conteneurs
        this.parseStatus = document.getElementById('parseStatus');
        this.publishStatus = document.getElementById('publishStatus');
        this.parsedData = document.getElementById('parsedData');
        this.logs = document.getElementById('logs');
        this.currentTimeSpan = document.getElementById('currentTime');
        
        // Modal
        this.modal = document.getElementById('detailsModal');
        this.modalContent = document.getElementById('modalContent');
        this.closeModal = document.querySelector('.close');
    }

    bindEvents() {
        // Boutons principaux
        this.parseBtn.addEventListener('click', () => this.parsePage());
        this.clearBtn.addEventListener('click', () => this.clearData());
        this.publishBtn.addEventListener('click', () => this.publishEvents());
        this.clearLogsBtn.addEventListener('click', () => this.clearLogs());
        
        // Modal
        this.closeModal.addEventListener('click', () => this.closeModalWindow());
        window.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModalWindow();
            }
        });
        
        // Validation en temps réel
        this.targetUrlInput.addEventListener('input', () => this.validateForm());
        
        // Mise à jour de l'heure
        setInterval(() => this.updateCurrentTime(), 1000);
    }

    updateCurrentTime() {
        const now = new Date();
        this.currentTimeSpan.textContent = now.toLocaleTimeString('fr-FR');
    }

    validateForm() {
        const url = this.targetUrlInput.value.trim();
        const isValid = url && this.isValidUrl(url);
        
        this.parseBtn.disabled = !isValid || this.isParsing;
        this.log(`Validation du formulaire: ${isValid ? 'valide' : 'invalide'}`, 'info');
    }

    isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    async parsePage() {
        const url = this.targetUrlInput.value.trim();
        
        if (!url || !this.isValidUrl(url)) {
            this.showStatus('Veuillez entrer une URL valide', 'error', this.parseStatus);
            this.log('URL invalide fournie', 'error');
            return;
        }

        this.isParsing = true;
        this.parseBtn.disabled = true;
        this.parseBtn.innerHTML = '<div class="loading"></div> Parsing en cours...';
        
        this.log(`Début du parsing de: ${url}`, 'info');
        this.showStatus('Parsing en cours...', 'info', this.parseStatus);

        try {
            // Utilisation du parseur pour extraire les données
            const events = await this.parseEventsFromUrl(url);
            
            if (events && events.length > 0) {
                this.parsedEvents = events;
                this.displayParsedEvents();
                this.publishBtn.disabled = false;
                this.showStatus(`${events.length} événement(s) parsé(s) avec succès`, 'success', this.parseStatus);
                this.log(`Parsing terminé: ${events.length} événement(s) trouvé(s)`, 'success');
            } else {
                this.showStatus('Aucun événement trouvé sur cette page', 'error', this.parseStatus);
                this.log('Aucun événement trouvé lors du parsing', 'warning');
            }
        } catch (error) {
            this.showStatus(`Erreur lors du parsing: ${error.message}`, 'error', this.parseStatus);
            this.log(`Erreur de parsing: ${error.message}`, 'error');
        } finally {
            this.isParsing = false;
            this.parseBtn.disabled = false;
            this.parseBtn.innerHTML = '<i class="fas fa-play"></i> Parser la page';
        }
    }

    async parseEventsFromUrl(url) {
        // Simulation du parsing - dans un vrai cas, vous utiliseriez un proxy CORS
        // ou une API backend pour contourner les restrictions CORS
        this.log('Tentative de parsing via proxy CORS...', 'info');
        
        // Pour l'exemple, nous simulons des données parsées
        // En production, vous devriez implémenter un vrai parseur
        return new Promise((resolve) => {
            setTimeout(() => {
                const mockEvents = this.generateMockEvents(url);
                resolve(mockEvents);
            }, 2000);
        });
    }

    generateMockEvents(url) {
        // Génération d'événements fictifs pour la démonstration
        const events = [];
        const eventCount = Math.floor(Math.random() * 3) + 1;
        
        for (let i = 0; i < eventCount; i++) {
            events.push({
                id: `event_${Date.now()}_${i}`,
                title: `Soirée Libertine ${i + 1}`,
                description: `Une soirée exceptionnelle pour les amateurs de libertinage. Ambiance conviviale et respectueuse.`,
                date: new Date(Date.now() + (i + 1) * 24 * 60 * 60 * 1000).toISOString(),
                time: '20:00',
                location: `Club Libertine ${i + 1}`,
                address: `${i + 1} Rue de la Liberté, 75001 Paris`,
                price: `${20 + i * 10}€`,
                capacity: 50 + i * 20,
                organizer: `Organisateur ${i + 1}`,
                contact: `contact${i + 1}@example.com`,
                tags: ['libertinage', 'soirée', 'rencontre'],
                image: `https://via.placeholder.com/400x300/667eea/ffffff?text=Soirée+${i + 1}`,
                sourceUrl: url
            });
        }
        
        return events;
    }

    displayParsedEvents() {
        if (!this.parsedEvents || this.parsedEvents.length === 0) {
            this.parsedData.innerHTML = `
                <div class="no-data">
                    <i class="fas fa-info-circle"></i>
                    <p>Aucune donnée parsée pour le moment</p>
                </div>
            `;
            return;
        }

        const eventsHtml = this.parsedEvents.map((event, index) => `
            <div class="event-card" data-event-index="${index}">
                <h3>${event.title}</h3>
                <div class="event-field">
                    <strong>Date:</strong>
                    <span>${new Date(event.date).toLocaleDateString('fr-FR')} à ${event.time}</span>
                </div>
                <div class="event-field">
                    <strong>Lieu:</strong>
                    <span>${event.location}</span>
                </div>
                <div class="event-field">
                    <strong>Prix:</strong>
                    <span>${event.price}</span>
                </div>
                <div class="event-field">
                    <strong>Organisateur:</strong>
                    <span>${event.organizer}</span>
                </div>
                <div class="event-actions">
                    <button class="btn-edit" onclick="app.showEventDetails(${index})">
                        <i class="fas fa-eye"></i> Voir détails
                    </button>
                    <button class="btn-edit" onclick="app.editEvent(${index})">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                </div>
            </div>
        `).join('');

        this.parsedData.innerHTML = eventsHtml;
    }

    showEventDetails(index) {
        const event = this.parsedEvents[index];
        if (!event) return;

        const detailsHtml = `
            <div class="event-details">
                <h4>${event.title}</h4>
                <p><strong>Description:</strong> ${event.description}</p>
                <p><strong>Date:</strong> ${new Date(event.date).toLocaleDateString('fr-FR')} à ${event.time}</p>
                <p><strong>Lieu:</strong> ${event.location}</p>
                <p><strong>Adresse:</strong> ${event.address}</p>
                <p><strong>Prix:</strong> ${event.price}</p>
                <p><strong>Capacité:</strong> ${event.capacity} personnes</p>
                <p><strong>Organisateur:</strong> ${event.organizer}</p>
                <p><strong>Contact:</strong> ${event.contact}</p>
                <p><strong>Tags:</strong> ${event.tags.join(', ')}</p>
                <p><strong>Source:</strong> <a href="${event.sourceUrl}" target="_blank">${event.sourceUrl}</a></p>
                ${event.image ? `<img src="${event.image}" alt="${event.title}" style="max-width: 100%; margin-top: 15px; border-radius: 8px;">` : ''}
            </div>
        `;

        this.modalContent.innerHTML = detailsHtml;
        this.modal.style.display = 'block';
    }

    editEvent(index) {
        this.currentEvent = this.parsedEvents[index];
        this.log(`Édition de l'événement: ${this.currentEvent.title}`, 'info');
        
        // Ici vous pourriez ouvrir un formulaire d'édition
        // Pour l'exemple, nous affichons juste un message
        this.showStatus(`Mode édition activé pour: ${this.currentEvent.title}`, 'info', this.parseStatus);
    }

    async publishEvents() {
        if (!this.parsedEvents || this.parsedEvents.length === 0) {
            this.showStatus('Aucun événement à publier', 'error', this.publishStatus);
            return;
        }

        this.isPublishing = true;
        this.publishBtn.disabled = true;
        this.publishBtn.innerHTML = '<div class="loading"></div> Publication en cours...';

        this.log('Début de la publication des événements', 'info');
        this.showStatus('Publication en cours...', 'info', this.publishStatus);

        try {
            const mondelibertinUrl = this.mondelibertinUrlInput.value;
            const apiKey = this.apiKeyInput.value;
            
            // Simulation de la publication
            const results = await this.publishToMondelibertin(this.parsedEvents, mondelibertinUrl, apiKey);
            
            this.showStatus(`Publication réussie: ${results.success} événement(s) publié(s)`, 'success', this.publishStatus);
            this.log(`Publication terminée: ${results.success}/${this.parsedEvents.length} événements publiés`, 'success');
            
            // Optionnel: vider les données après publication
            // this.clearData();
            
        } catch (error) {
            this.showStatus(`Erreur lors de la publication: ${error.message}`, 'error', this.publishStatus);
            this.log(`Erreur de publication: ${error.message}`, 'error');
        } finally {
            this.isPublishing = false;
            this.publishBtn.disabled = false;
            this.publishBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier sur Monde Libertin';
        }
    }

    async publishToMondelibertin(events, targetUrl, apiKey) {
        // Simulation de la publication vers SocialEngine
        return new Promise((resolve) => {
            setTimeout(() => {
                const success = Math.floor(Math.random() * events.length) + 1;
                resolve({
                    success: success,
                    total: events.length,
                    failed: events.length - success
                });
            }, 3000);
        });
    }

    clearData() {
        this.parsedEvents = [];
        this.currentEvent = null;
        this.displayParsedEvents();
        this.publishBtn.disabled = true;
        this.parseStatus.innerHTML = '';
        this.publishStatus.innerHTML = '';
        this.log('Données effacées', 'info');
    }

    clearLogs() {
        this.logs.innerHTML = `
            <div class="log-entry info">
                <span class="timestamp">[${new Date().toLocaleTimeString('fr-FR')}]</span>
                <span class="message">Logs effacés</span>
            </div>
        `;
    }

    showStatus(message, type, container) {
        container.innerHTML = `<div class="status-message ${type}">${message}</div>`;
    }

    log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString('fr-FR');
        const logEntry = document.createElement('div');
        logEntry.className = `log-entry ${type}`;
        logEntry.innerHTML = `
            <span class="timestamp">[${timestamp}]</span>
            <span class="message">${message}</span>
        `;
        
        this.logs.appendChild(logEntry);
        this.logs.scrollTop = this.logs.scrollHeight;
    }

    closeModalWindow() {
        this.modal.style.display = 'none';
    }
}

// Initialisation de l'application
let app;
document.addEventListener('DOMContentLoaded', () => {
    app = new EventParserApp();
});

// Export pour utilisation globale
window.app = app;