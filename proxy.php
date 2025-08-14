<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Gérer les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérifier que l'URL est fournie
if (!isset($_GET['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'URL parameter is required']);
    exit();
}

$url = $_GET['url'];

// Whitelist des domaines autorisés pour des raisons de sécurité
$allowedDomains = [
    'wemagnifique.fr',
    'partner-site.com'
    // Ajouter d'autres domaines partenaires ici
];

$parsedUrl = parse_url($url);
$domain = $parsedUrl['host'];

if (!in_array($domain, $allowedDomains)) {
    http_response_code(403);
    echo json_encode(['error' => 'Domain not allowed: ' . $domain]);
    exit();
}

// Configuration cURL avec User-Agent réaliste
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: fr-FR,fr;q=0.9,en;q=0.8',
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ]
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error: ' . $error]);
    exit();
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'HTTP error: ' . $httpCode]);
    exit();
}

if (!$html) {
    http_response_code(500);
    echo json_encode(['error' => 'Empty response']);
    exit();
}

// Retourner le HTML avec succès
echo json_encode([
    'success' => true,
    'html' => $html,
    'url' => $url
]);
?>