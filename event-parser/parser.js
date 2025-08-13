// Module de parsing pour les événements
class EventParser {
    constructor() {
        this.parsers = {
            'default': this.parseDefault,
            'libertinage': this.parseLibertinage,
            'club': this.parseClub,
            'soiree': this.parseSoiree
        };
    }

    // Méthode principale de parsing
    async parsePage(url, options = {}) {
        try {
            console.log(`Parsing de l'URL: ${url}`);
            
            // Détection automatique du type de site
            const siteType = this.detectSiteType(url);
            console.log(`Type de site détecté: ${siteType}`);
            
            // Récupération du contenu HTML
            const html = await this.fetchPageContent(url);
            if (!html) {
                throw new Error('Impossible de récupérer le contenu de la page');
            }
            
            // Parsing selon le type de site
            const parser = this.parsers[siteType] || this.parsers['default'];
            const events = await parser.call(this, html, url, options);
            
            return events;
        } catch (error) {
            console.error('Erreur lors du parsing:', error);
            throw error;
        }
    }

    // Détection automatique du type de site
    detectSiteType(url) {
        const domain = new URL(url).hostname.toLowerCase();
        
        if (domain.includes('libertin') || domain.includes('swinger')) {
            return 'libertinage';
        } else if (domain.includes('club') || domain.includes('boite')) {
            return 'club';
        } else if (domain.includes('soiree') || domain.includes('event')) {
            return 'soiree';
        }
        
        return 'default';
    }

    // Récupération du contenu HTML avec gestion CORS
    async fetchPageContent(url) {
        try {
            // Méthode 1: Tentative directe (peut échouer à cause de CORS)
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                }
            });
            
            if (response.ok) {
                return await response.text();
            }
        } catch (error) {
            console.warn('Tentative directe échouée, utilisation du proxy CORS:', error);
        }

        // Méthode 2: Proxy CORS
        try {
            const proxyUrl = `https://api.allorigins.win/get?url=${encodeURIComponent(url)}`;
            const response = await fetch(proxyUrl);
            const data = await response.json();
            
            if (data.contents) {
                return data.contents;
            }
        } catch (error) {
            console.warn('Proxy CORS échoué:', error);
        }

        // Méthode 3: Autres proxies CORS
        const corsProxies = [
            `https://cors-anywhere.herokuapp.com/${url}`,
            `https://thingproxy.freeboard.io/fetch/${url}`,
            `https://api.codetabs.com/v1/proxy?quest=${url}`
        ];

        for (const proxyUrl of corsProxies) {
            try {
                const response = await fetch(proxyUrl, {
                    method: 'GET',
                    headers: {
                        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    }
                });
                
                if (response.ok) {
                    return await response.text();
                }
            } catch (error) {
                console.warn(`Proxy ${proxyUrl} échoué:`, error);
            }
        }

        throw new Error('Impossible de récupérer le contenu de la page');
    }

    // Parseur par défaut - recherche générique
    async parseDefault(html, url, options) {
        const events = [];
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Recherche de patterns génériques pour les événements
        const eventSelectors = [
            '.event', '.evenement', '.soiree', '.party',
            '[class*="event"]', '[class*="soiree"]', '[class*="party"]',
            'article', '.card', '.item'
        ];
        
        for (const selector of eventSelectors) {
            const elements = doc.querySelectorAll(selector);
            
            for (const element of elements) {
                const event = this.extractEventFromElement(element, url);
                if (event && this.isValidEvent(event)) {
                    events.push(event);
                }
            }
        }
        
        return events;
    }

    // Parseur spécialisé pour les sites libertinage
    async parseLibertinage(html, url, options) {
        const events = [];
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Sélecteurs spécifiques aux sites libertinage
        const selectors = [
            '.soiree-libertine', '.event-libertin', '.swinger-party',
            '.club-event', '.rencontre-libertine', '.soiree-coquine',
            '[class*="libertin"]', '[class*="swinger"]', '[class*="coquin"]'
        ];
        
        for (const selector of selectors) {
            const elements = doc.querySelectorAll(selector);
            
            for (const element of elements) {
                const event = this.extractLibertinageEvent(element, url);
                if (event && this.isValidEvent(event)) {
                    events.push(event);
                }
            }
        }
        
        return events;
    }

    // Parseur pour les clubs
    async parseClub(html, url, options) {
        const events = [];
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Sélecteurs pour les clubs
        const selectors = [
            '.club-event', '.programme', '.agenda',
            '.soiree-club', '.event-club', '.planning'
        ];
        
        for (const selector of selectors) {
            const elements = doc.querySelectorAll(selector);
            
            for (const element of elements) {
                const event = this.extractClubEvent(element, url);
                if (event && this.isValidEvent(event)) {
                    events.push(event);
                }
            }
        }
        
        return events;
    }

    // Parseur pour les soirées générales
    async parseSoiree(html, url, options) {
        const events = [];
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Sélecteurs pour les soirées
        const selectors = [
            '.soiree', '.party', '.event', '.evenement',
            '[class*="soiree"]', '[class*="party"]', '[class*="event"]'
        ];
        
        for (const selector of selectors) {
            const elements = doc.querySelectorAll(selector);
            
            for (const element of elements) {
                const event = this.extractSoireeEvent(element, url);
                if (event && this.isValidEvent(event)) {
                    events.push(event);
                }
            }
        }
        
        return events;
    }

    // Extraction d'événement générique
    extractEventFromElement(element, sourceUrl) {
        try {
            const event = {
                id: this.generateEventId(),
                title: this.extractTitle(element),
                description: this.extractDescription(element),
                date: this.extractDate(element),
                time: this.extractTime(element),
                location: this.extractLocation(element),
                address: this.extractAddress(element),
                price: this.extractPrice(element),
                capacity: this.extractCapacity(element),
                organizer: this.extractOrganizer(element),
                contact: this.extractContact(element),
                tags: this.extractTags(element),
                image: this.extractImage(element),
                sourceUrl: sourceUrl
            };
            
            return event;
        } catch (error) {
            console.error('Erreur lors de l\'extraction d\'événement:', error);
            return null;
        }
    }

    // Extraction d'événement libertinage
    extractLibertinageEvent(element, sourceUrl) {
        const event = this.extractEventFromElement(element, sourceUrl);
        
        if (event) {
            // Ajout de tags spécifiques
            event.tags = [...(event.tags || []), 'libertinage', 'rencontre'];
            
            // Recherche d'informations spécifiques
            const ageText = this.extractText(element, '.age, [class*="age"]');
            if (ageText) {
                event.ageRequirement = ageText;
            }
            
            const dressCode = this.extractText(element, '.dress-code, [class*="dress"]');
            if (dressCode) {
                event.dressCode = dressCode;
            }
        }
        
        return event;
    }

    // Extraction d'événement club
    extractClubEvent(element, sourceUrl) {
        const event = this.extractEventFromElement(element, sourceUrl);
        
        if (event) {
            // Ajout de tags spécifiques
            event.tags = [...(event.tags || []), 'club', 'boite'];
            
            // Recherche d'informations spécifiques aux clubs
            const music = this.extractText(element, '.music, [class*="music"]');
            if (music) {
                event.music = music;
            }
            
            const entryTime = this.extractText(element, '.entry-time, [class*="entry"]');
            if (entryTime) {
                event.entryTime = entryTime;
            }
        }
        
        return event;
    }

    // Extraction d'événement soirée
    extractSoireeEvent(element, sourceUrl) {
        const event = this.extractEventFromElement(element, sourceUrl);
        
        if (event) {
            // Ajout de tags spécifiques
            event.tags = [...(event.tags || []), 'soirée', 'événement'];
        }
        
        return event;
    }

    // Méthodes d'extraction de données
    extractTitle(element) {
        const selectors = [
            'h1', 'h2', 'h3', '.title', '.event-title', '.soiree-title',
            '[class*="title"]', '[class*="nom"]', '[class*="name"]'
        ];
        
        for (const selector of selectors) {
            const titleElement = element.querySelector(selector);
            if (titleElement) {
                const title = titleElement.textContent.trim();
                if (title && title.length > 3) {
                    return title;
                }
            }
        }
        
        return 'Événement sans titre';
    }

    extractDescription(element) {
        const selectors = [
            '.description', '.desc', '.content', '.text',
            '[class*="description"]', '[class*="content"]', 'p'
        ];
        
        for (const selector of selectors) {
            const descElement = element.querySelector(selector);
            if (descElement) {
                const desc = descElement.textContent.trim();
                if (desc && desc.length > 10) {
                    return desc.substring(0, 500); // Limite à 500 caractères
                }
            }
        }
        
        return 'Aucune description disponible';
    }

    extractDate(element) {
        const selectors = [
            '.date', '.event-date', '.soiree-date',
            '[class*="date"]', '[datetime]', 'time'
        ];
        
        for (const selector of selectors) {
            const dateElement = element.querySelector(selector);
            if (dateElement) {
                const date = this.parseDate(dateElement.textContent.trim() || dateElement.getAttribute('datetime'));
                if (date) {
                    return date.toISOString();
                }
            }
        }
        
        // Date par défaut (dans 7 jours)
        return new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString();
    }

    extractTime(element) {
        const selectors = [
            '.time', '.heure', '.event-time',
            '[class*="time"]', '[class*="heure"]'
        ];
        
        for (const selector of selectors) {
            const timeElement = element.querySelector(selector);
            if (timeElement) {
                const time = timeElement.textContent.trim();
                const timeMatch = time.match(/(\d{1,2})[h:]\d{2}/);
                if (timeMatch) {
                    return timeMatch[0];
                }
            }
        }
        
        return '20:00';
    }

    extractLocation(element) {
        const selectors = [
            '.location', '.lieu', '.place', '.venue',
            '[class*="location"]', '[class*="lieu"]', '[class*="place"]'
        ];
        
        for (const selector of selectors) {
            const locationElement = element.querySelector(selector);
            if (locationElement) {
                const location = locationElement.textContent.trim();
                if (location && location.length > 2) {
                    return location;
                }
            }
        }
        
        return 'Lieu à préciser';
    }

    extractAddress(element) {
        const selectors = [
            '.address', '.adresse', '.address-line',
            '[class*="address"]', '[class*="adresse"]'
        ];
        
        for (const selector of selectors) {
            const addressElement = element.querySelector(selector);
            if (addressElement) {
                const address = addressElement.textContent.trim();
                if (address && address.length > 5) {
                    return address;
                }
            }
        }
        
        return '';
    }

    extractPrice(element) {
        const selectors = [
            '.price', '.prix', '.tarif', '.cost',
            '[class*="price"]', '[class*="prix"]', '[class*="tarif"]'
        ];
        
        for (const selector of selectors) {
            const priceElement = element.querySelector(selector);
            if (priceElement) {
                const price = priceElement.textContent.trim();
                const priceMatch = price.match(/(\d+)\s*€?/);
                if (priceMatch) {
                    return `${priceMatch[1]}€`;
                }
            }
        }
        
        return 'Prix à préciser';
    }

    extractCapacity(element) {
        const selectors = [
            '.capacity', '.capacite', '.places',
            '[class*="capacity"]', '[class*="capacite"]'
        ];
        
        for (const selector of selectors) {
            const capacityElement = element.querySelector(selector);
            if (capacityElement) {
                const capacity = capacityElement.textContent.trim();
                const capacityMatch = capacity.match(/(\d+)/);
                if (capacityMatch) {
                    return parseInt(capacityMatch[1]);
                }
            }
        }
        
        return 50;
    }

    extractOrganizer(element) {
        const selectors = [
            '.organizer', '.organisateur', '.host',
            '[class*="organizer"]', '[class*="organisateur"]'
        ];
        
        for (const selector of selectors) {
            const organizerElement = element.querySelector(selector);
            if (organizerElement) {
                const organizer = organizerElement.textContent.trim();
                if (organizer && organizer.length > 2) {
                    return organizer;
                }
            }
        }
        
        return 'Organisateur à préciser';
    }

    extractContact(element) {
        const selectors = [
            '.contact', '.email', '.phone', '.tel',
            '[class*="contact"]', 'a[href^="mailto:"]', 'a[href^="tel:"]'
        ];
        
        for (const selector of selectors) {
            const contactElement = element.querySelector(selector);
            if (contactElement) {
                if (selector.includes('mailto:')) {
                    return contactElement.href.replace('mailto:', '');
                } else if (selector.includes('tel:')) {
                    return contactElement.href.replace('tel:', '');
                } else {
                    const contact = contactElement.textContent.trim();
                    if (contact && contact.length > 5) {
                        return contact;
                    }
                }
            }
        }
        
        return '';
    }

    extractTags(element) {
        const tags = [];
        const selectors = [
            '.tags', '.categories', '.labels',
            '[class*="tag"]', '[class*="category"]'
        ];
        
        for (const selector of selectors) {
            const tagElements = element.querySelectorAll(selector);
            for (const tagElement of tagElements) {
                const tag = tagElement.textContent.trim();
                if (tag && tag.length > 2) {
                    tags.push(tag);
                }
            }
        }
        
        return tags;
    }

    extractImage(element) {
        const selectors = [
            'img', '.image', '.photo', '.picture',
            '[class*="image"]', '[class*="photo"]'
        ];
        
        for (const selector of selectors) {
            const imgElement = element.querySelector(selector);
            if (imgElement && imgElement.src) {
                return imgElement.src;
            }
        }
        
        return '';
    }

    // Méthodes utilitaires
    extractText(element, selector) {
        const foundElement = element.querySelector(selector);
        return foundElement ? foundElement.textContent.trim() : null;
    }

    parseDate(dateString) {
        if (!dateString) return null;
        
        // Tentative de parsing de différentes formats de date
        const dateFormats = [
            /(\d{1,2})\/(\d{1,2})\/(\d{4})/, // DD/MM/YYYY
            /(\d{4})-(\d{1,2})-(\d{1,2})/, // YYYY-MM-DD
            /(\d{1,2})-(\d{1,2})-(\d{4})/, // DD-MM-YYYY
            /(\d{1,2})\s+(\w+)\s+(\d{4})/ // DD Month YYYY
        ];
        
        for (const format of dateFormats) {
            const match = dateString.match(format);
            if (match) {
                try {
                    return new Date(dateString);
                } catch (e) {
                    continue;
                }
            }
        }
        
        return null;
    }

    generateEventId() {
        return `event_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    isValidEvent(event) {
        return event && 
               event.title && 
               event.title.length > 3 && 
               event.date && 
               event.location;
    }
}

// Export pour utilisation globale
window.EventParser = EventParser;