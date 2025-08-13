<?php
/**
 * Proxy PHP pour Cursor Event Parser
 * Gère les requêtes cross-origin et l'authentification sur MondeLiberin.com
 */

// Configuration
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Méthode non autorisée', 405);
}

// Récupération des données
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
    sendError('Données invalides');
}

// Initialisation de la session cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

// Gestion du cookie jar pour maintenir la session
$cookieFile = sys_get_temp_dir() . '/parser_cookies_' . session_id() . '.txt';
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

// Traitement selon l'action
switch ($input['action']) {
    case 'fetch':
        handleFetch($ch, $input);
        break;
        
    case 'login':
        handleLogin($ch, $input);
        break;
        
    case 'publish':
        handlePublish($ch, $input);
        break;
        
    default:
        sendError('Action non reconnue');
}

/**
 * Récupération du contenu d'une page
 */
function handleFetch($ch, $input) {
    if (!isset($input['url'])) {
        sendError('URL manquante');
    }
    
    $url = filter_var($input['url'], FILTER_VALIDATE_URL);
    if (!$url) {
        sendError('URL invalide');
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    
    // Headers pour simuler un navigateur
    $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: fr-FR,fr;q=0.9,en;q=0.8',
        'Cache-Control: no-cache',
        'Pragma: no-cache'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($response === false) {
        sendError('Erreur de récupération: ' . curl_error($ch));
    }
    
    if ($httpCode !== 200) {
        sendError('Erreur HTTP: ' . $httpCode);
    }
    
    // Retourner le HTML directement
    header('Content-Type: text/html; charset=utf-8');
    echo $response;
    exit();
}

/**
 * Authentification sur MondeLiberin
 */
function handleLogin($ch, $input) {
    if (!isset($input['username']) || !isset($input['password'])) {
        sendError('Identifiants manquants');
    }
    
    $loginUrl = $input['url'] ?? 'https://mondelibertin.com/login';
    
    // Étape 1: Récupérer la page de login pour obtenir le token CSRF
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    $loginPage = curl_exec($ch);
    
    if ($loginPage === false) {
        sendError('Impossible de charger la page de connexion');
    }
    
    // Extraction du token CSRF pour SocialEngine
    $csrfToken = extractCSRFToken($loginPage);
    
    // Étape 2: Soumettre le formulaire de connexion
    $postData = [
        'email' => $input['username'],
        'password' => $input['password'],
        'remember' => '1',
        'submit' => 'Se connecter'
    ];
    
    if ($csrfToken) {
        $postData['token'] = $csrfToken;
        $postData['csrf_token'] = $csrfToken;
    }
    
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Referer: ' . $loginUrl,
        'Origin: https://mondelibertin.com'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Vérification de la connexion
    // SocialEngine redirige généralement après connexion réussie
    if ($httpCode === 302 || $httpCode === 301) {
        // Suivre la redirection
        $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        if ($redirectUrl) {
            curl_setopt($ch, CURLOPT_URL, $redirectUrl);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            $response = curl_exec($ch);
        }
    }
    
    // Vérifier si la connexion a réussi
    if (strpos($response, 'logout') !== false || strpos($response, 'mon-compte') !== false) {
        // Connexion réussie
        // Générer un token de session
        $sessionToken = generateSessionToken();
        
        // Sauvegarder la session
        saveSession($sessionToken, $cookieFile);
        
        sendSuccess([
            'success' => true,
            'token' => $sessionToken,
            'message' => 'Connexion réussie'
        ]);
    } else {
        sendError('Échec de la connexion. Vérifiez vos identifiants.');
    }
}

/**
 * Publication d'un événement
 */
function handlePublish($ch, $input) {
    if (!isset($input['data']) || !isset($input['token'])) {
        sendError('Données ou token manquant');
    }
    
    // Vérifier et restaurer la session
    if (!restoreSession($input['token'], $ch)) {
        sendError('Session expirée. Veuillez vous reconnecter.');
    }
    
    $createUrl = $input['url'] ?? 'https://mondelibertin.com/events/create';
    
    // Étape 1: Charger la page de création pour obtenir les tokens
    curl_setopt($ch, CURLOPT_URL, $createUrl);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    $createPage = curl_exec($ch);
    
    if ($createPage === false) {
        sendError('Impossible de charger la page de création');
    }
    
    // Vérifier l'accès à la page
    if (strpos($createPage, 'login') !== false && strpos($createPage, 'logout') === false) {
        sendError('Accès refusé. Veuillez vous reconnecter.');
    }
    
    // Extraire le token CSRF
    $csrfToken = extractCSRFToken($createPage);
    
    // Étape 2: Préparer les données pour SocialEngine
    $eventData = $input['data'];
    
    // Ajouter les tokens nécessaires
    if ($csrfToken) {
        $eventData['token'] = $csrfToken;
        $eventData['csrf_token'] = $csrfToken;
    }
    
    // Ajouter les champs spécifiques à SocialEngine
    $eventData['form_token'] = $csrfToken;
    $eventData['submit'] = 'Créer l\'événement';
    
    // Étape 3: Soumettre le formulaire
    $submitUrl = str_replace('/create', '/create/submit', $createUrl);
    
    curl_setopt($ch, CURLOPT_URL, $submitUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($eventData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Referer: ' . $createUrl,
        'Origin: https://mondelibertin.com',
        'X-Requested-With: XMLHttpRequest'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Analyser la réponse
    if ($httpCode === 200 || $httpCode === 201) {
        // Vérifier si c'est une réponse JSON
        $jsonResponse = json_decode($response, true);
        if ($jsonResponse) {
            if (isset($jsonResponse['status']) && $jsonResponse['status'] === 'success') {
                sendSuccess([
                    'success' => true,
                    'message' => 'Événement publié avec succès',
                    'event_id' => $jsonResponse['event_id'] ?? null
                ]);
            } else {
                sendError($jsonResponse['message'] ?? 'Erreur lors de la publication');
            }
        } else {
            // Réponse HTML, vérifier le succès
            if (strpos($response, 'success') !== false || strpos($response, 'événement créé') !== false) {
                sendSuccess([
                    'success' => true,
                    'message' => 'Événement publié avec succès'
                ]);
            } else {
                // Extraire les erreurs du HTML
                $errors = extractErrors($response);
                sendError($errors ?: 'Erreur lors de la publication');
            }
        }
    } elseif ($httpCode === 302 || $httpCode === 301) {
        // Redirection après succès
        sendSuccess([
            'success' => true,
            'message' => 'Événement publié avec succès'
        ]);
    } else {
        sendError('Erreur HTTP: ' . $httpCode);
    }
}

/**
 * Extraction du token CSRF depuis le HTML
 */
function extractCSRFToken($html) {
    // Patterns courants pour les tokens CSRF
    $patterns = [
        '/<input[^>]*name=["\']csrf_token["\'][^>]*value=["\']([^"\']+)["\']/',
        '/<input[^>]*name=["\']token["\'][^>]*value=["\']([^"\']+)["\']/',
        '/<meta[^>]*name=["\']csrf-token["\'][^>]*content=["\']([^"\']+)["\']/',
        '/var\s+csrf_token\s*=\s*["\']([^"\']+)["\']/',
        '/["\']csrf_token["\']\s*:\s*["\']([^"\']+)["\']/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Extraction des erreurs depuis le HTML
 */
function extractErrors($html) {
    $errors = [];
    
    // Patterns pour les messages d'erreur
    $patterns = [
        '/<div[^>]*class=["\'][^"\']*error[^"\']*["\'][^>]*>([^<]+)</',
        '/<span[^>]*class=["\'][^"\']*error[^"\']*["\'][^>]*>([^<]+)</',
        '/<p[^>]*class=["\'][^"\']*error[^"\']*["\'][^>]*>([^<]+)</'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $html, $matches)) {
            $errors = array_merge($errors, $matches[1]);
        }
    }
    
    return $errors ? implode(', ', array_unique($errors)) : null;
}

/**
 * Génération d'un token de session
 */
function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Sauvegarde de la session
 */
function saveSession($token, $cookieFile) {
    $sessionFile = sys_get_temp_dir() . '/parser_session_' . $token . '.txt';
    
    // Copier le fichier de cookies
    if (file_exists($cookieFile)) {
        copy($cookieFile, $sessionFile);
    }
    
    // Sauvegarder aussi en session PHP
    session_start();
    $_SESSION['parser_token'] = $token;
    $_SESSION['parser_cookies'] = $sessionFile;
    session_write_close();
}

/**
 * Restauration de la session
 */
function restoreSession($token, $ch) {
    $sessionFile = sys_get_temp_dir() . '/parser_session_' . $token . '.txt';
    
    if (file_exists($sessionFile)) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $sessionFile);
        return true;
    }
    
    // Vérifier aussi la session PHP
    session_start();
    if (isset($_SESSION['parser_token']) && $_SESSION['parser_token'] === $token) {
        if (isset($_SESSION['parser_cookies']) && file_exists($_SESSION['parser_cookies'])) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['parser_cookies']);
            session_write_close();
            return true;
        }
    }
    session_write_close();
    
    return false;
}

/**
 * Envoi d'une réponse de succès
 */
function sendSuccess($data) {
    http_response_code(200);
    echo json_encode($data);
    exit();
}

/**
 * Envoi d'une erreur
 */
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit();
}

// Nettoyage à la fin
curl_close($ch);

// Nettoyer les vieux fichiers de session (plus de 24h)
$tempDir = sys_get_temp_dir();
$files = glob($tempDir . '/parser_*');
foreach ($files as $file) {
    if (filemtime($file) < time() - 86400) {
        @unlink($file);
    }
}
?>