<?php
// =================================================================
// FILE: subscribe.php — Riceve e salva le iscrizioni push dei browser
// =================================================================
require_once __DIR__ . '/push_config.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['action'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Richiesta non valida']);
    exit;
}

// Carica le iscrizioni esistenti
$subs = [];
if (file_exists(SUBSCRIPTIONS_FILE)) {
    $subs = json_decode(file_get_contents(SUBSCRIPTIONS_FILE), true) ?: [];
}

$action = $input['action'];
$sub    = $input['subscription'] ?? null;

if (!$sub || empty($sub['endpoint'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Subscription mancante']);
    exit;
}

// Usa l'endpoint come chiave univoca (evita duplicati)
$key = hash('sha256', $sub['endpoint']);

if ($action === 'subscribe') {
    $subs[$key] = $sub;
} elseif ($action === 'unsubscribe') {
    unset($subs[$key]);
}

if (file_put_contents(SUBSCRIPTIONS_FILE, json_encode($subs, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Impossibile salvare (controlla i permessi del file)']);
    exit;
}

echo json_encode(['ok' => true, 'count' => count($subs)]);
