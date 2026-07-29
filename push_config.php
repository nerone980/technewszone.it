<?php
// =================================================================
// FILE: push_config.php — Configurazione sistema notifiche push
// =================================================================
//
// Le chiavi VAPID identificano il TUO server presso i servizi push
// dei browser. Si generano UNA SOLA VOLTA con generate_keys.php.
// Dopo averle generate, incolla qui sotto i due valori.
// =================================================================

// Email/URL di contatto (richiesto dal protocollo VAPID).
// Deve iniziare con "mailto:" oppure essere un indirizzo https del tuo sito.
define('VAPID_SUBJECT', 'mailto:tuoindirizzo@esempio.it');

// >>> INCOLLA QUI LE CHIAVI GENERATE DA generate_keys.php <<<
define('VAPID_PUBLIC_KEY',  'INCOLLA_QUI_LA_CHIAVE_PUBBLICA');
define('VAPID_PRIVATE_KEY', 'INCOLLA_QUI_LA_CHIAVE_PRIVATA');

// File dove vengono salvate le iscrizioni dei browser (niente database).
define('SUBSCRIPTIONS_FILE', __DIR__ . '/push_subscriptions.json');

// File che ricorda quali notizie sono già state notificate (anti-doppione).
define('PUSH_STATE_FILE', __DIR__ . '/push_state.json');

// Quanti minuti deve avere al massimo una notizia per essere "breaking".
// Solo le notizie più recenti di questo valore generano una notifica.
define('PUSH_MAX_AGE_MINUTES', 60);

// Numero massimo di notifiche inviate per ogni giro di cron (anti-spam).
define('PUSH_MAX_PER_RUN', 3);
