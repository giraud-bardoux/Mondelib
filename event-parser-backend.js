/**
 * Event Parser Backend - Serveur Node.js pour gérer l'authentification et la soumission
 * Version de production pour mondelibertin.com
 */

const express = require('express');
const cors = require('cors');
const axios = require('axios');
const cheerio = require('cheerio');
const FormData = require('form-data');
const cookieParser = require('cookie-parser');
const session = require('express-session');
const app = express();

// Configuration
const config = {
    port: process.env.PORT || 3001,
    mondeLibertyBaseUrl: 'https://mondelibertin.com',
    createEventUrl: 'https://mondelibertin.com/events/create',
    loginUrl: 'https://mondelibertin.com/login',
    sessionSecret: process.env.SESSION_SECRET || 'votre-secret-ici',
    corsOrigins: process.env.CORS_ORIGINS ? process.env.CORS_ORIGINS.split(',') : ['*']
};

// Middleware
app.use(cors({
    origin: config.corsOrigins,
    credentials: true
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser());
app.use(session({
    secret: config.sessionSecret,
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false, maxAge: 24 * 60 * 60 * 1000 } // 24h
}));

// Store des sessions utilisateur
const userSessions = new Map();

/**
 * Classe pour gérer les interactions avec MondeLibertie
 */
class MondeLibertyClient {
    constructor() {
        this.axios = axios.create({
            baseURL: config.mondeLibertyBaseUrl,
            timeout: 30000,
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            }
        });
        this.cookies = {};
        this.csrfToken = null;
    }

    /**
     * Connexion avec identifiants
     */
    async login(username, password) {
        try {
            console.log(`[Login] Tentative de connexion pour: ${username}`);
            
            // 1. Récupérer la page de login pour obtenir le token CSRF
            const loginPageResponse = await this.axios.get('/login');
            const $ = cheerio.load(loginPageResponse.data);
            
            // Extraire le token CSRF (adapté au système SocialEngine)
            const csrfToken = $('input[name="_token"]').val() || 
                            $('meta[name="csrf-token"]').attr('content') ||
                            $('input[name="csrf_token"]').val();
            
            if (!csrfToken) {
                throw new Error('Token CSRF non trouvé');
            }

            console.log(`[Login] Token CSRF récupéré: ${csrfToken.substring(0, 10)}...`);

            // Récupérer les cookies de session
            const setCookieHeaders = loginPageResponse.headers['set-cookie'];
            if (setCookieHeaders) {
                setCookieHeaders.forEach(cookie => {
                    const [nameValue] = cookie.split(';');
                    const [name, value] = nameValue.split('=');
                    this.cookies[name] = value;
                });
            }

            // 2. Soumettre les identifiants
            const formData = new FormData();
            formData.append('email', username);
            formData.append('password', password);
            formData.append('_token', csrfToken);
            formData.append('remember', '1');

            const loginResponse = await this.axios.post('/login', formData, {
                headers: {
                    ...formData.getHeaders(),
                    'Cookie': this.getCookieString(),
                    'Referer': `${config.mondeLibertyBaseUrl}/login`
                },
                maxRedirects: 0,
                validateStatus: (status) => status < 400
            });

            // Vérifier la connexion réussie
            if (loginResponse.status === 302 || loginResponse.status === 200) {
                // Mettre à jour les cookies
                const newSetCookieHeaders = loginResponse.headers['set-cookie'];
                if (newSetCookieHeaders) {
                    newSetCookieHeaders.forEach(cookie => {
                        const [nameValue] = cookie.split(';');
                        const [name, value] = nameValue.split('=');
                        this.cookies[name] = value;
                    });
                }

                console.log(`[Login] Connexion réussie pour: ${username}`);
                return { success: true, message: 'Connexion réussie' };
            } else {
                throw new Error('Échec de la connexion');
            }

        } catch (error) {
            console.error(`[Login] Erreur:`, error.message);
            return { success: false, message: error.message };
        }
    }

    /**
     * Récupérer le formulaire de création d'événement
     */
    async getCreateEventForm() {
        try {
            console.log('[CreateForm] Récupération du formulaire de création');
            
            const response = await this.axios.get('/events/create', {
                headers: {
                    'Cookie': this.getCookieString()
                }
            });

            if (response.status !== 200) {
                throw new Error('Accès refusé au formulaire de création');
            }

            const $ = cheerio.load(response.data);
            
            // Extraire les informations du formulaire
            const formAction = $('form').attr('action') || '/events/create';
            const csrfToken = $('input[name="_token"]').val() || 
                            $('meta[name="csrf-token"]').attr('content');

            // Extraire la structure des champs
            const fields = {};
            $('input, textarea, select').each((i, el) => {
                const $el = $(el);
                const name = $el.attr('name');
                const type = $el.attr('type') || $el.prop('tagName').toLowerCase();
                const required = $el.attr('required') !== undefined;
                
                if (name && !name.startsWith('_')) {
                    fields[name] = { type, required };
                }
            });

            console.log('[CreateForm] Formulaire récupéré avec succès');
            
            return {
                success: true,
                formAction,
                csrfToken,
                fields
            };

        } catch (error) {
            console.error('[CreateForm] Erreur:', error.message);
            return { success: false, message: error.message };
        }
    }

    /**
     * Soumettre un événement
     */
    async submitEvent(eventData) {
        try {
            console.log('[SubmitEvent] Soumission de l\'événement');
            
            // 1. Récupérer les informations du formulaire
            const formInfo = await this.getCreateEventForm();
            if (!formInfo.success) {
                throw new Error('Impossible de récupérer le formulaire');
            }

            // 2. Préparer les données
            const formData = new FormData();
            
            // Ajouter le token CSRF
            if (formInfo.csrfToken) {
                formData.append('_token', formInfo.csrfToken);
            }

            // Mapper les données d'événement aux champs du formulaire
            const fieldMapping = {
                'title': ['title', 'name', 'event_title', 'event_name'],
                'description': ['description', 'content', 'body', 'event_description'],
                'date': ['date', 'start_date', 'event_date', 'when'],
                'location': ['location', 'venue', 'address', 'where'],
                'price': ['price', 'cost', 'fee', 'ticket_price']
            };

            for (const [eventField, value] of Object.entries(eventData)) {
                if (value) {
                    const possibleFields = fieldMapping[eventField] || [eventField];
                    
                    for (const fieldName of possibleFields) {
                        if (formInfo.fields[fieldName]) {
                            formData.append(fieldName, value);
                            console.log(`[SubmitEvent] ${fieldName}: ${value}`);
                            break;
                        }
                    }
                }
            }

            // 3. Soumettre le formulaire
            const response = await this.axios.post(formInfo.formAction, formData, {
                headers: {
                    ...formData.getHeaders(),
                    'Cookie': this.getCookieString(),
                    'Referer': `${config.mondeLibertyBaseUrl}/events/create`
                },
                maxRedirects: 0,
                validateStatus: (status) => status < 400
            });

            if (response.status === 302 || response.status === 201) {
                console.log('[SubmitEvent] Événement créé avec succès');
                return { success: true, message: 'Événement créé avec succès' };
            } else {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

        } catch (error) {
            console.error('[SubmitEvent] Erreur:', error.message);
            return { success: false, message: error.message };
        }
    }

    /**
     * Construire la chaîne de cookies
     */
    getCookieString() {
        return Object.entries(this.cookies)
            .map(([name, value]) => `${name}=${value}`)
            .join('; ');
    }

    /**
     * Vérifier si la session est active
     */
    async checkSession() {
        try {
            const response = await this.axios.get('/events/create', {
                headers: {
                    'Cookie': this.getCookieString()
                }
            });
            
            return response.status === 200;
        } catch (error) {
            return false;
        }
    }
}

/**
 * Parser HTML pour extraire les données d'événement
 */
async function parseEventFromUrl(url, customSelectors = null) {
    try {
        console.log(`[Parser] Parsing de: ${url}`);
        
        const response = await axios.get(url, {
            timeout: 10000,
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
        });

        const $ = cheerio.load(response.data);
        const eventData = {};

        if (customSelectors) {
            // Utilisation des sélecteurs personnalisés
            for (const [field, selector] of Object.entries(customSelectors)) {
                const element = $(selector).first();
                if (element.length) {
                    eventData[field] = element.text().trim();
                }
            }
        } else {
            // Détection automatique
            const autoSelectors = {
                title: ['h1', '.event-title', '.title', '.event-name', 'title'],
                description: ['.description', '.event-description', '.content', '.summary'],
                date: ['.date', '.event-date', 'time[datetime]', '.when'],
                location: ['.location', '.venue', '.address', '.where'],
                price: ['.price', '.cost', '.fee', '.ticket-price']
            };

            for (const [field, selectors] of Object.entries(autoSelectors)) {
                for (const selector of selectors) {
                    const element = $(selector).first();
                    if (element.length && element.text().trim()) {
                        eventData[field] = element.text().trim();
                        break;
                    }
                }
            }
        }

        console.log('[Parser] Parsing terminé:', eventData);
        return { success: true, data: eventData };

    } catch (error) {
        console.error('[Parser] Erreur:', error.message);
        return { success: false, message: error.message };
    }
}

// Routes API

/**
 * Route de test
 */
app.get('/api/status', (req, res) => {
    res.json({ 
        status: 'OK', 
        timestamp: new Date().toISOString(),
        version: '1.0.0'
    });
});

/**
 * Connexion utilisateur
 */
app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    
    if (!username || !password) {
        return res.status(400).json({ error: 'Identifiants manquants' });
    }

    const client = new MondeLibertyClient();
    const result = await client.login(username, password);
    
    if (result.success) {
        // Stocker la session client
        const sessionId = req.sessionID;
        userSessions.set(sessionId, client);
        
        res.json({ success: true, message: 'Connexion réussie' });
    } else {
        res.status(401).json({ error: result.message });
    }
});

/**
 * Test de connexion
 */
app.get('/api/check-session', async (req, res) => {
    const sessionId = req.sessionID;
    const client = userSessions.get(sessionId);
    
    if (!client) {
        return res.status(401).json({ error: 'Session non trouvée' });
    }

    const isActive = await client.checkSession();
    res.json({ active: isActive });
});

/**
 * Parser une URL
 */
app.post('/api/parse', async (req, res) => {
    const { url, selectors } = req.body;
    
    if (!url) {
        return res.status(400).json({ error: 'URL manquante' });
    }

    const result = await parseEventFromUrl(url, selectors);
    
    if (result.success) {
        res.json(result.data);
    } else {
        res.status(500).json({ error: result.message });
    }
});

/**
 * Soumettre un événement
 */
app.post('/api/submit-event', async (req, res) => {
    const sessionId = req.sessionID;
    const client = userSessions.get(sessionId);
    
    if (!client) {
        return res.status(401).json({ error: 'Session non trouvée' });
    }

    const eventData = req.body;
    const result = await client.submitEvent(eventData);
    
    if (result.success) {
        res.json({ success: true, message: result.message });
    } else {
        res.status(500).json({ error: result.message });
    }
});

/**
 * Processus complet: parse + submit
 */
app.post('/api/process-event', async (req, res) => {
    const { url, selectors, autoSubmit } = req.body;
    const sessionId = req.sessionID;
    const client = userSessions.get(sessionId);
    
    if (!client) {
        return res.status(401).json({ error: 'Session non trouvée' });
    }

    try {
        // 1. Parser l'URL
        const parseResult = await parseEventFromUrl(url, selectors);
        if (!parseResult.success) {
            return res.status(500).json({ error: `Erreur de parsing: ${parseResult.message}` });
        }

        const eventData = parseResult.data;

        // 2. Soumettre si autoSubmit est activé
        if (autoSubmit) {
            const submitResult = await client.submitEvent(eventData);
            if (!submitResult.success) {
                return res.status(500).json({ 
                    error: `Erreur de soumission: ${submitResult.message}`,
                    parsedData: eventData 
                });
            }

            res.json({ 
                success: true, 
                message: 'Événement parsé et créé avec succès',
                parsedData: eventData 
            });
        } else {
            res.json({ 
                success: true, 
                message: 'Événement parsé avec succès',
                parsedData: eventData,
                requiresConfirmation: true 
            });
        }

    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

/**
 * Nettoyage des sessions expirées
 */
setInterval(() => {
    console.log(`[Cleanup] Nettoyage des sessions. Sessions actives: ${userSessions.size}`);
    // Dans un vrai environnement, vous pourriez vérifier l'activité des sessions
}, 60 * 60 * 1000); // Toutes les heures

// Démarrage du serveur
app.listen(config.port, () => {
    console.log(`🚀 Event Parser Backend démarré sur le port ${config.port}`);
    console.log(`📡 API disponible sur: http://localhost:${config.port}/api`);
    console.log(`🔗 MondeLibertie URL: ${config.mondeLibertyBaseUrl}`);
});

// Gestion de l'arrêt propre
process.on('SIGINT', () => {
    console.log('\n🛑 Arrêt du serveur...');
    process.exit(0);
});

module.exports = app;