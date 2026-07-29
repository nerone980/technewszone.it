<?php
// =================================================================
// FILE: newsletter_subscribe.php — Raccolta iscrizioni newsletter
// Salva le email in newsletter_emails.csv (una per riga).
// =================================================================

header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/newsletter_emails.csv';

// Leggi input (sia JSON che form classico)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = $data['email'] ?? ($_POST['email'] ?? '');
$hp    = $data['website'] ?? ($_POST['website'] ?? ''); // honeypot anti-bot

// Honeypot: se compilato, è un bot → fingi successo e ignora
if (!empty($hp)) {
    echo json_encode(['ok' => true]);
    exit;
}

$email = trim(strtolower($email));

// Validazione
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Email non valida']);
    exit;
}

// Anti-duplicati: leggi le email già presenti
$existing = [];
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = str_getcsv($line);
        if (!empty($parts[0])) $existing[] = strtolower($parts[0]);
    }
}

if (in_array($email, $existing, true)) {
    echo json_encode(['ok' => true, 'already' => true]); // già iscritto, non è un errore
    exit;
}

// Aggiungi: email, data, ip (per eventuale verifica anti-abuso)
$row = [
    $email,
    date('Y-m-d H:i:s'),
    $_SERVER['REMOTE_ADDR'] ?? '',
];

$fp = fopen($file, 'a');
if ($fp === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Errore di salvataggio']);
    exit;
}
fputcsv($fp, $row);
fclose($fp);

echo json_encode(['ok' => true]);
