<?php
/**
 * Proxy pour récupérer le contenu des pages partenaires
 * Contourne les restrictions CORS en faisant la requête côté serveur
 */

// Configuration CORS pour permettre l'accès depuis le navigateur
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Vérifier que l'URL est fournie
if (!isset($_GET['url']) || empty($_GET['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'URL manquante']);
    exit;
}

$url = $_GET['url'];

// Valider l'URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL invalide']);
    exit;
}

// Liste des domaines autorisés (sécurité)
$allowedDomains = [
    'wemagnifique.fr',
    'partner-site.com',
    // Ajouter d'autres domaines partenaires ici
];

$urlHost = parse_url($url, PHP_URL_HOST);
$isAllowed = false;

foreach ($allowedDomains as $domain) {
    if (strpos($urlHost, $domain) !== false) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    http_response_code(403);
    echo json_encode(['error' => 'Domaine non autorisé']);
    exit;
}

// Initialiser cURL
$ch = curl_init();

// Configuration cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

// Headers supplémentaires pour simuler un navigateur
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: fr-FR,fr;q=0.9,en;q=0.8',
    'Cache-Control: no-cache',
    'Pragma: no-cache'
]);

// Exécuter la requête
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Vérifier les erreurs
if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur cURL: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Erreur HTTP: ' . $httpCode]);
    exit;
}

// Retourner le HTML
echo json_encode([
    'success' => true,
    'html' => $html,
    'url' => $url
]);