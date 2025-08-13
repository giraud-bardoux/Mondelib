// Configuration du parseur d'événements
const CONFIG = {
    // Configuration générale
    app: {
        name: 'Parseur d\'Événements - Monde Libertin',
        version: '1.0.0',
        debug: false, // Mettre à true pour activer les logs détaillés
        autoSave: true, // Sauvegarde automatique des données
        maxEvents: 50 // Nombre maximum d'événements à parser
    },

    // Configuration SocialEngine
    socialEngine: {
        baseUrl: 'https://mondelibertin.com',
        createEventUrl: '/events/create',
        apiEndpoint: '/api/events',
        sessionTimeout: 30 * 60 * 1000, // 30 minutes
        retryAttempts: 3
    },

    // Configuration du parsing
    parsing: {
        timeout: 30000, // 30 secondes
        maxRetries: 3,
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        
        // Proxies CORS (par ordre de préférence)
        corsProxies: [
            'https://api.allorigins.win/get?url=',
            'https://cors-anywhere.herokuapp.com/',
            'https://thingproxy.freeboard.io/fetch/',
            'https://api.codetabs.com/v1/proxy?quest='
        ],

        // Sélecteurs par défaut pour les événements
        defaultSelectors: {
            title: ['h1', 'h2', 'h3', '.title', '.event-title', '.soiree-title'],
            description: ['.description', '.desc', '.content', '.text', 'p'],
            date: ['.date', '.event-date', '.soiree-date', '[datetime]', 'time'],
            time: ['.time', '.heure', '.event-time'],
            location: ['.location', '.lieu', '.place', '.venue'],
            address: ['.address', '.adresse', '.address-line'],
            price: ['.price', '.prix', '.tarif', '.cost'],
            organizer: ['.organizer', '.organisateur', '.host'],
            contact: ['.contact', '.email', '.phone', '.tel'],
            image: ['img', '.image', '.photo', '.picture']
        }
    },

    // Configuration des types de sites
    siteTypes: {
        libertinage: {
            name: 'Site Libertinage',
            keywords: ['libertin', 'swinger', 'coquin', 'rencontre'],
            selectors: {
                event: ['.soiree-libertine', '.event-libertin', '.swinger-party', '.rencontre-libertine'],
                age: ['.age', '[class*="age"]'],
                dressCode: ['.dress-code', '[class*="dress"]']
            },
            tags: ['libertinage', 'rencontre', 'swinger']
        },
        
        club: {
            name: 'Club/Boîte',
            keywords: ['club', 'boite', 'discotheque', 'nightclub'],
            selectors: {
                event: ['.club-event', '.programme', '.agenda', '.soiree-club'],
                music: ['.music', '[class*="music"]'],
                entryTime: ['.entry-time', '[class*="entry"]']
            },
            tags: ['club', 'boite', 'nightclub']
        },
        
        soiree: {
            name: 'Soirée/Événement',
            keywords: ['soiree', 'event', 'party', 'evenement'],
            selectors: {
                event: ['.soiree', '.party', '.event', '.evenement']
            },
            tags: ['soirée', 'événement', 'party']
        }
    },

    // Configuration de l'interface
    ui: {
        theme: 'default', // 'default', 'dark', 'light'
        language: 'fr',
        autoRefresh: true,
        refreshInterval: 5000, // 5 secondes
        
        // Messages personnalisés
        messages: {
            fr: {
                parsing: 'Parsing en cours...',
                success: 'Parsing terminé avec succès',
                error: 'Erreur lors du parsing',
                noEvents: 'Aucun événement trouvé',
                publishing: 'Publication en cours...',
                published: 'Événement(s) publié(s) avec succès',
                publishError: 'Erreur lors de la publication'
            }
        }
    },

    // Configuration de validation
    validation: {
        requiredFields: ['title', 'date', 'location'],
        minTitleLength: 3,
        maxTitleLength: 200,
        maxDescriptionLength: 1000,
        dateFormat: 'YYYY-MM-DD',
        timeFormat: 'HH:mm'
    },

    // Configuration des logs
    logging: {
        enabled: true,
        level: 'info', // 'debug', 'info', 'warning', 'error'
        maxEntries: 1000,
        autoClear: false,
        saveToFile: false
    },

    // Configuration de sécurité
    security: {
        allowedDomains: [
            'mondelibertin.com',
            '*.mondelibertin.com',
            'localhost',
            '127.0.0.1'
        ],
        blockExternalScripts: true,
        sanitizeInput: true,
        maxUrlLength: 2048
    },

    // Configuration des partenaires
    partners: {
        // Ajoutez ici vos sites partenaires
        sites: [
            {
                name: 'Site Partenaire 1',
                domain: 'partenaire1.com',
                type: 'libertinage',
                customSelectors: {
                    // Sélecteurs spécifiques à ce site
                }
            }
            // Ajoutez d'autres partenaires ici
        ]
    }
};

// Fonctions utilitaires de configuration
const ConfigUtils = {
    // Obtenir la configuration pour un type de site
    getSiteTypeConfig(type) {
        return CONFIG.siteTypes[type] || CONFIG.siteTypes.default;
    },

    // Vérifier si un domaine est autorisé
    isDomainAllowed(domain) {
        return CONFIG.security.allowedDomains.some(allowed => {
            if (allowed.startsWith('*.')) {
                return domain.endsWith(allowed.substring(2));
            }
            return domain === allowed;
        });
    },

    // Obtenir les sélecteurs pour un site partenaire
    getPartnerSelectors(domain) {
        const partner = CONFIG.partners.sites.find(site => 
            domain.includes(site.domain)
        );
        return partner ? partner.customSelectors : null;
    },

    // Valider une URL
    validateUrl(url) {
        try {
            const urlObj = new URL(url);
            return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
        } catch {
            return false;
        }
    },

    // Obtenir un message localisé
    getMessage(key, language = 'fr') {
        return CONFIG.ui.messages[language]?.[key] || key;
    },

    // Logger avec le niveau configuré
    log(message, level = 'info') {
        if (!CONFIG.logging.enabled) return;
        
        const levels = ['debug', 'info', 'warning', 'error'];
        const currentLevel = levels.indexOf(CONFIG.logging.level);
        const messageLevel = levels.indexOf(level);
        
        if (messageLevel >= currentLevel) {
            console.log(`[${level.toUpperCase()}] ${message}`);
        }
    }
};

// Export pour utilisation globale
window.CONFIG = CONFIG;
window.ConfigUtils = ConfigUtils;