<?php
/**
 * Intégration SocialEngine pour le Parseur d'Événements
 * Monde Libertin - Version 1.0.0
 * 
 * Ce fichier permet l'intégration avec SocialEngine 7.4
 * pour la publication automatique d'événements
 */

// Configuration de sécurité
define('SECURE_ACCESS', true);

// Configuration de base
class SocialEngineIntegration {
    
    private $config = [
        'base_url' => 'https://mondelibertin.com',
        'api_endpoint' => '/api/events',
        'session_timeout' => 1800, // 30 minutes
        'max_retries' => 3,
        'debug' => false
    ];
    
    private $session = null;
    private $user = null;
    
    public function __construct($config = []) {
        $this->config = array_merge($this->config, $config);
        $this->initSession();
    }
    
    /**
     * Initialisation de la session
     */
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (isset($_SESSION['user_id'])) {
            $this->user = [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? '',
                'email' => $_SESSION['email'] ?? ''
            ];
        }
    }
    
    /**
     * Vérifier l'authentification
     */
    public function isAuthenticated() {
        return $this->user !== null;
    }
    
    /**
     * Publier un événement sur SocialEngine
     */
    public function publishEvent($eventData) {
        if (!$this->isAuthenticated()) {
            throw new Exception('Utilisateur non authentifié');
        }
        
        // Validation des données
        $this->validateEventData($eventData);
        
        // Préparation des données pour SocialEngine
        $seEventData = $this->prepareEventData($eventData);
        
        // Publication via l'API SocialEngine
        $result = $this->callSocialEngineAPI('POST', '/events', $seEventData);
        
        if ($result['success']) {
            $this->log('Événement publié avec succès: ' . $eventData['title'], 'info');
            return $result['data'];
        } else {
            throw new Exception('Erreur lors de la publication: ' . $result['error']);
        }
    }
    
    /**
     * Validation des données d'événement
     */
    private function validateEventData($data) {
        $required = ['title', 'date', 'location'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Champ requis manquant: $field");
            }
        }
        
        // Validation de la date
        if (!strtotime($data['date'])) {
            throw new Exception('Format de date invalide');
        }
        
        // Validation du titre
        if (strlen($data['title']) < 3 || strlen($data['title']) > 200) {
            throw new Exception('Le titre doit contenir entre 3 et 200 caractères');
        }
        
        return true;
    }
    
    /**
     * Préparation des données pour SocialEngine
     */
    private function prepareEventData($eventData) {
        $seData = [
            'title' => $this->sanitizeText($eventData['title']),
            'description' => $this->sanitizeText($eventData['description'] ?? ''),
            'starttime' => $this->formatDateTime($eventData['date'], $eventData['time'] ?? '20:00'),
            'endtime' => $this->formatDateTime($eventData['date'], $eventData['time'] ?? '23:00', '+3 hours'),
            'location' => $this->sanitizeText($eventData['location']),
            'address' => $this->sanitizeText($eventData['address'] ?? ''),
            'price' => $this->sanitizeText($eventData['price'] ?? ''),
            'capacity' => intval($eventData['capacity'] ?? 50),
            'organizer' => $this->sanitizeText($eventData['organizer'] ?? ''),
            'contact' => $this->sanitizeText($eventData['contact'] ?? ''),
            'tags' => $this->sanitizeTags($eventData['tags'] ?? []),
            'source_url' => $eventData['sourceUrl'] ?? '',
            'user_id' => $this->user['id'],
            'category_id' => $this->getCategoryId($eventData['tags'] ?? []),
            'privacy' => 'everyone', // ou 'registered', 'friends', etc.
            'search' => 1,
            'auth_view' => 'everyone',
            'auth_comment' => 'registered'
        ];
        
        // Ajout d'image si disponible
        if (!empty($eventData['image'])) {
            $seData['photo_id'] = $this->uploadImage($eventData['image']);
        }
        
        return $seData;
    }
    
    /**
     * Appel à l'API SocialEngine
     */
    private function callSocialEngineAPI($method, $endpoint, $data = null) {
        $url = $this->config['base_url'] . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->getAuthToken(),
            'User-Agent: Monde Libertin Event Parser/1.0.0'
        ];
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            if ($data) {
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            }
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('Erreur cURL: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $result,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'Erreur HTTP ' . $httpCode,
                'http_code' => $httpCode,
                'response' => $result
            ];
        }
    }
    
    /**
     * Obtenir le token d'authentification
     */
    private function getAuthToken() {
        // Ici vous devrez implémenter la logique d'authentification
        // avec votre système SocialEngine
        return $_SESSION['auth_token'] ?? null;
    }
    
    /**
     * Formatage de la date et heure
     */
    private function formatDateTime($date, $time, $modifier = '') {
        $datetime = $date . ' ' . $time;
        if ($modifier) {
            $datetime = date('Y-m-d H:i:s', strtotime($datetime . ' ' . $modifier));
        } else {
            $datetime = date('Y-m-d H:i:s', strtotime($datetime));
        }
        return $datetime;
    }
    
    /**
     * Nettoyage du texte
     */
    private function sanitizeText($text) {
        return htmlspecialchars(strip_tags(trim($text)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Nettoyage des tags
     */
    private function sanitizeTags($tags) {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }
        
        $cleanTags = [];
        foreach ($tags as $tag) {
            $cleanTag = trim($this->sanitizeText($tag));
            if (!empty($cleanTag)) {
                $cleanTags[] = $cleanTag;
            }
        }
        
        return array_slice($cleanTags, 0, 10); // Limite à 10 tags
    }
    
    /**
     * Obtenir l'ID de catégorie
     */
    private function getCategoryId($tags) {
        // Mapping des tags vers les catégories SocialEngine
        $categoryMapping = [
            'libertinage' => 1,
            'swinger' => 1,
            'rencontre' => 2,
            'club' => 3,
            'soirée' => 4,
            'événement' => 4
        ];
        
        foreach ($tags as $tag) {
            $tag = strtolower($tag);
            if (isset($categoryMapping[$tag])) {
                return $categoryMapping[$tag];
            }
        }
        
        return 4; // Catégorie par défaut
    }
    
    /**
     * Upload d'image
     */
    private function uploadImage($imageUrl) {
        // Téléchargement de l'image
        $imageData = file_get_contents($imageUrl);
        if ($imageData === false) {
            return null;
        }
        
        // Sauvegarde temporaire
        $tempFile = tempnam(sys_get_temp_dir(), 'event_image_');
        file_put_contents($tempFile, $imageData);
        
        // Upload vers SocialEngine
        $uploadData = [
            'file' => new CURLFile($tempFile),
            'type' => 'event'
        ];
        
        $result = $this->callSocialEngineAPI('POST', '/photos/upload', $uploadData);
        
        // Nettoyage
        unlink($tempFile);
        
        if ($result['success']) {
            return $result['data']['photo_id'];
        }
        
        return null;
    }
    
    /**
     * Logging
     */
    private function log($message, $level = 'info') {
        if ($this->config['debug']) {
            $logEntry = date('Y-m-d H:i:s') . " [$level] $message" . PHP_EOL;
            error_log($logEntry, 3, __DIR__ . '/logs/integration.log');
        }
    }
}

// API Endpoint pour les requêtes AJAX
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $integration = new SocialEngineIntegration();
        
        switch ($_POST['action']) {
            case 'publish_event':
                if (!$integration->isAuthenticated()) {
                    throw new Exception('Non authentifié');
                }
                
                $eventData = json_decode($_POST['event_data'], true);
                $result = $integration->publishEvent($eventData);
                
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
                break;
                
            case 'check_auth':
                echo json_encode([
                    'success' => true,
                    'authenticated' => $integration->isAuthenticated()
                ]);
                break;
                
            default:
                throw new Exception('Action non reconnue');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    exit;
}

// Fonction d'aide pour l'intégration directe
function publishEventToSocialEngine($eventData, $config = []) {
    $integration = new SocialEngineIntegration($config);
    return $integration->publishEvent($eventData);
}

// Exemple d'utilisation
if (defined('SECURE_ACCESS') && SECURE_ACCESS) {
    // Le fichier est inclus de manière sécurisée
    return;
}

// Test direct (à supprimer en production)
if (isset($_GET['test'])) {
    $testEvent = [
        'title' => 'Test Soirée Libertinage',
        'description' => 'Une soirée de test pour vérifier l\'intégration',
        'date' => '2024-12-31',
        'time' => '20:00',
        'location' => 'Club Test',
        'address' => '123 Rue Test, 75001 Paris',
        'price' => '25€',
        'capacity' => 50,
        'organizer' => 'Organisateur Test',
        'contact' => 'test@example.com',
        'tags' => ['libertinage', 'test'],
        'sourceUrl' => 'https://example.com/test'
    ];
    
    try {
        $integration = new SocialEngineIntegration(['debug' => true]);
        $result = $integration->publishEvent($testEvent);
        echo "Événement publié avec succès: " . json_encode($result);
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>