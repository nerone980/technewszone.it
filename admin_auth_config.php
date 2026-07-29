<?php
// =================================================================
// FILE: admin_auth_config.php — Credenziali per gli script di manutenzione
// =================================================================
// Protegge generate_keys.php, download_icons.php e update_cache.php
// (quando aperti da browser) con un login HTTP Basic Auth.
//
// COME CAMBIARE LA PASSWORD:
// 1. Scegli una nuova password.
// 2. Genera il suo hash eseguendo da terminale (o in un file php temporaneo):
//      php -r "echo password_hash('LA_TUA_NUOVA_PASSWORD', PASSWORD_BCRYPT);"
// 3. Incolla il risultato in ADMIN_PASS_HASH qui sotto.
// =================================================================

define('ADMIN_USER', 'tnzadmin');
define('ADMIN_PASS_HASH', '$2y$12$Dcb0SrNq8YVn0DKX/fl1RuGPcL26T7DGVbf/YZFjOgcKla1BQuIZq');
