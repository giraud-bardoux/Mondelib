// CORS Proxy JavaScript
// Cette solution propose plusieurs alternatives pour contourner les restrictions CORS

/* =====================
 * Alternative 1: Utiliser un service de proxy public
 * ===================== */
async function fetchWithPublicProxy(url) {
    const proxyUrls = [
        'https://api.allorigins.win/raw?url=',
        'https://cors-anywhere.herokuapp.com/',
        'https://api.codetabs.com/v1/proxy?quest='
    ];
    
    for (const proxyUrl of proxyUrls) {
        try {
            const response = await fetch(proxyUrl + encodeURIComponent(url));
            if (response.ok) {
                return await response.text();
            }
        } catch (error) {
            console.warn(`Proxy ${proxyUrl} failed:`, error);
        }
    }
    
    throw new Error('Tous les proxies publics ont échoué');
}

/* =====================
 * Alternative 2: Solution avec extension navigateur
 * ===================== */
function showCorsInstructions() {
    return `
    Pour résoudre l'erreur "Failed to fetch", vous avez plusieurs options :

    1. **Extension CORS (Recommandé)**:
       - Installez l'extension "CORS Unblock" ou "CORS Everywhere" 
       - Activez l'extension temporairement
       - Relancez l'importation

    2. **Flags Chrome/Edge**:
       - Fermez complètement votre navigateur
       - Redémarrez avec: --disable-web-security --disable-features=VizDisplayCompositor --user-data-dir=/tmp/chrome-dev
       
    3. **Serveur local**:
       - Utilisez le fichier proxy.php avec un serveur web local (Apache/Nginx)
       
    4. **Proxy public** (peut être instable):
       - L'importateur essaiera automatiquement plusieurs services proxy
    `;
}

/* =====================
 * Alternative 3: Fetch avec retry et fallbacks
 * ===================== */
async function fetchWithFallbacks(url) {
    const methods = [
        // Méthode 1: Fetch direct (fonctionne si CORS est désactivé)
        () => fetch(url).then(r => r.text()),
        
        // Méthode 2: Proxy public AllOrigins
        () => fetch(`https://api.allorigins.win/raw?url=${encodeURIComponent(url)}`).then(r => r.text()),
        
        // Méthode 3: Autre proxy public
        () => fetch(`https://api.codetabs.com/v1/proxy?quest=${encodeURIComponent(url)}`).then(r => r.text()),
        
        // Méthode 4: Proxy CORS Anywhere (nécessite activation)
        () => fetch(`https://cors-anywhere.herokuapp.com/${url}`).then(r => r.text())
    ];
    
    for (let i = 0; i < methods.length; i++) {
        try {
            console.log(`Tentative ${i + 1}/${methods.length}...`);
            const html = await methods[i]();
            console.log(`Succès avec la méthode ${i + 1}`);
            return html;
        } catch (error) {
            console.warn(`Méthode ${i + 1} échouée:`, error.message);
            if (i === methods.length - 1) {
                throw new Error(`Toutes les méthodes ont échoué. ${showCorsInstructions()}`);
            }
        }
    }
}

// Export pour utilisation dans le fichier principal
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { fetchWithFallbacks, showCorsInstructions };
}