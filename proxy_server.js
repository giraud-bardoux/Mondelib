const http = require('http');
const https = require('https');
const url = require('url');
const fs = require('fs');
const path = require('path');

// Liste des domaines autorisés
const allowedDomains = [
  'wemagnifique.fr',
  'partner-site.com'
];

// Créer le serveur HTTP
const server = http.createServer((req, res) => {
  // Headers CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  // Gérer les requêtes OPTIONS (preflight)
  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  const parsedUrl = url.parse(req.url, true);
  const pathname = parsedUrl.pathname;

  // Route pour le proxy
  if (pathname === '/proxy_fetch.php' || pathname === '/proxy') {
    const targetUrl = parsedUrl.query.url;
    
    if (!targetUrl) {
      res.writeHead(400, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: 'URL manquante' }));
      return;
    }

    // Vérifier que le domaine est autorisé
    const targetHost = url.parse(targetUrl).hostname;
    const isAllowed = allowedDomains.some(domain => targetHost.includes(domain));
    
    if (!isAllowed) {
      res.writeHead(403, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: 'Domaine non autorisé' }));
      return;
    }

    // Faire la requête vers le site cible
    const protocol = targetUrl.startsWith('https') ? https : http;
    
    const options = {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'fr-FR,fr;q=0.9,en;q=0.8'
      }
    };

    protocol.get(targetUrl, options, (proxyRes) => {
      let data = '';
      
      proxyRes.on('data', (chunk) => {
        data += chunk;
      });
      
      proxyRes.on('end', () => {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          success: true,
          html: data,
          url: targetUrl
        }));
      });
    }).on('error', (err) => {
      res.writeHead(500, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ error: 'Erreur de récupération: ' + err.message }));
    });
    
  } 
  // Servir les fichiers statiques (HTML, JS, CSS)
  else {
    let filePath = '.' + pathname;
    if (filePath === './') {
      filePath = './mondelibertin_event_publisher.html';
    }

    const extname = String(path.extname(filePath)).toLowerCase();
    const mimeTypes = {
      '.html': 'text/html',
      '.js': 'text/javascript',
      '.css': 'text/css',
      '.json': 'application/json',
      '.png': 'image/png',
      '.jpg': 'image/jpg',
      '.gif': 'image/gif',
      '.svg': 'image/svg+xml'
    };

    const contentType = mimeTypes[extname] || 'application/octet-stream';

    fs.readFile(filePath, (error, content) => {
      if (error) {
        if (error.code === 'ENOENT') {
          res.writeHead(404);
          res.end('404 - Fichier non trouvé');
        } else {
          res.writeHead(500);
          res.end('Erreur serveur: ' + error.code);
        }
      } else {
        res.writeHead(200, { 'Content-Type': contentType });
        res.end(content, 'utf-8');
      }
    });
  }
});

const PORT = process.env.PORT || 8080;

server.listen(PORT, () => {
  console.log(`✅ Serveur proxy démarré sur http://localhost:${PORT}`);
  console.log(`📄 Page d'import: http://localhost:${PORT}/mondelibertin_event_publisher.html`);
  console.log(`🔗 Proxy endpoint: http://localhost:${PORT}/proxy`);
});