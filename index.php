<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Lade .env-Datei
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// OpenAI API-Schlüssel
$openaiApiKey = $_ENV['OPENAI_API_KEY'];

// Erstelle Guzzle Client
$client = new Client([
    'base_uri' => 'https://api.openai.com/v1/',
    'headers' => [
        'Authorization' => 'Bearer ' . $openaiApiKey,
        'Content-Type' => 'application/json',
    ],
]);

// CORS-Header setzen
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Ersetze * in Produktion mit deiner Chrome-Extension-Origin
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// API-Endpunkt für POST /api/chat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api/chat') {
    try {
        // JSON-Eingabe lesen
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['messages'])) {
            throw new Exception('Ungültige Eingabe: messages erforderlich');
        }

        // Standardmodell setzen, falls nicht angegeben
        $model = isset($input['model']) ? $input['model'] : 'gpt-3.5-turbo';

        // Sende Anfrage an OpenAI
        $response = $client->post('chat/completions', [
            'json' => [
                'model' => $model,
                'messages' => $input['messages'],
            ],
        ]);

        // Antwort verarbeiten
        $data = json_decode($response->getBody(), true);
        echo json_encode(['response' => $data['choices'][0]['message']['content']]);
    } catch (RequestException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpunkt nicht gefunden']);
}