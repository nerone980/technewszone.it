<?php
// =================================================================
// FILE: generate_keys.php — Genera le chiavi VAPID (UNA VOLTA SOLA)
// =================================================================
// Apri questo file nel browser UNA volta, copia le due chiavi che
// compaiono dentro push_config.php, poi CANCELLA questo file dal server.
// =================================================================

require_once __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;

header('Content-Type: text/html; charset=utf-8');
echo "<body style='background:#0a0c0f;color:#e6edf3;font-family:monospace;padding:40px;line-height:1.7'>";
echo "<h2 style='color:#e8b04b'>Generazione chiavi VAPID</h2>";

try {
    $keys = VAPID::createVapidKeys();
    echo "<p style='color:#3fb950'>Chiavi generate con successo. Copiale in push_config.php:</p>";
    echo "<div style='background:#0f1318;border:1px solid #1c2128;border-radius:10px;padding:20px;margin-top:16px'>";
    echo "<p><b style='color:#56b6c2'>VAPID_PUBLIC_KEY</b><br><textarea style='width:100%;height:60px;background:#06080a;color:#e6edf3;border:1px solid #1c2128;border-radius:6px;padding:10px'>"
        . htmlspecialchars($keys['publicKey']) . "</textarea></p>";
    echo "<p><b style='color:#56b6c2'>VAPID_PRIVATE_KEY</b><br><textarea style='width:100%;height:60px;background:#06080a;color:#e6edf3;border:1px solid #1c2128;border-radius:6px;padding:10px'>"
        . htmlspecialchars($keys['privateKey']) . "</textarea></p>";
    echo "</div>";
    echo "<p style='color:#f85149;margin-top:20px'><b>IMPORTANTE:</b> dopo aver copiato le chiavi, CANCELLA questo file (generate_keys.php) dal server per sicurezza.</p>";
} catch (\Throwable $e) {
    echo "<p style='color:#f85149'>Errore: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Verifica che l'estensione PHP <b>gmp</b> sia attiva e che Composer sia stato installato (cartella vendor presente).</p>";
}
echo "</body>";
