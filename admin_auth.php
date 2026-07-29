<?php
// =================================================================
// FILE: admin_auth.php — Guard HTTP Basic Auth per script riservati
// =================================================================
// Uso: require_once __DIR__ . '/admin_auth.php'; require_admin_auth();
// in cima allo script da proteggere, prima di qualunque output.
// In CLI (es. cron) non chiede nulla: solo le richieste HTTP sono a rischio.
// =================================================================

require_once __DIR__ . '/admin_auth_config.php';

function require_admin_auth() {
    if (php_sapi_name() === 'cli') return;

    $user = $_SERVER['PHP_AUTH_USER'] ?? '';
    $pass = $_SERVER['PHP_AUTH_PW'] ?? '';

    // Fallback: su PHP in modalità CGI/FastCGI (comune sugli hosting condivisi)
    // Apache spesso non valorizza PHP_AUTH_USER/PW, ma passa comunque l'header
    // Authorization grezzo. Lo leggiamo a mano in quel caso.
    if ($user === '' && $pass === '') {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';
        if (stripos($authHeader, 'Basic ') === 0) {
            $decoded = base64_decode(substr($authHeader, 6), true);
            if ($decoded !== false && strpos($decoded, ':') !== false) {
                [$user, $pass] = explode(':', $decoded, 2);
            }
        }
    }

    $valid = hash_equals(ADMIN_USER, $user) && password_verify($pass, ADMIN_PASS_HASH);
    if (!$valid) {
        header('WWW-Authenticate: Basic realm="Area riservata TechNewsZone"');
        header('HTTP/1.1 401 Unauthorized');
        exit('Accesso non autorizzato.');
    }
}
