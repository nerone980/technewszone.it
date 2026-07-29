<?php
// =================================================================
// FILE: send_push.php — Invia notifiche push per le BREAKING NEWS
// =================================================================
// Da lanciare via CRON, idealmente subito dopo update_cache.php.
// Esempio crontab (ogni 20 minuti):
//   */20 * * * * /usr/bin/php /percorso/del/sito/send_push.php
//
// Invia una notifica SOLO per notizie:
//   - più recenti di PUSH_MAX_AGE_MINUTES
//   - mai notificate prima (controllo anti-doppione)
// =================================================================

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/push_config.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$cache_file = __DIR__ . '/rss_cache.json';
$is_cli = (php_sapi_name() === 'cli');
$log = fn($m) => print($is_cli ? "$m\n" : "$m<br>");

// 1. Iscrizioni presenti?
if (!file_exists(SUBSCRIPTIONS_FILE)) { $log('Nessuna iscrizione. Stop.'); exit; }
$subs = json_decode(file_get_contents(SUBSCRIPTIONS_FILE), true) ?: [];
if (empty($subs)) { $log('Nessuna iscrizione. Stop.'); exit; }

// 2. Notizie disponibili?
if (!file_exists($cache_file)) { $log('Cache notizie assente. Stop.'); exit; }
$news = json_decode(file_get_contents($cache_file), true) ?: [];

// 3. Stato anti-doppione: link già notificati
$already = [];
if (file_exists(PUSH_STATE_FILE)) {
    $already = json_decode(file_get_contents(PUSH_STATE_FILE), true) ?: [];
}

// 4. Trova le breaking news nuove
$now = time();
$max_age = PUSH_MAX_AGE_MINUTES * 60;
$candidates = [];
foreach ($news as $category => $articles) {
    if (stripos($category, 'home') !== false) continue; // salta la Home (duplicati)
    foreach ($articles as $art) {
        $link = $art['link'] ?? '';
        if (!$link) continue;
        $id = hash('sha256', $link);
        if (isset($already[$id])) continue;                 // già notificata
        if (($now - ($art['timestamp'] ?? 0)) > $max_age) continue; // troppo vecchia
        $candidates[$id] = [
            'title'    => $art['title'] ?? 'Nuova notizia',
            'link'     => $link,
            'source'   => $art['source'] ?? '',
            'category' => $category,
            'image'    => $art['image_url'] ?? '',
            'ts'       => $art['timestamp'] ?? $now,
        ];
    }
}

if (empty($candidates)) { $log('Nessuna breaking news nuova.'); exit; }

// Ordina dalla più recente e limita per evitare spam
uasort($candidates, fn($a, $b) => $b['ts'] - $a['ts']);
$candidates = array_slice($candidates, 0, PUSH_MAX_PER_RUN, true);

// 5. Prepara WebPush
$auth = ['VAPID' => [
    'subject'    => VAPID_SUBJECT,
    'publicKey'  => VAPID_PUBLIC_KEY,
    'privateKey' => VAPID_PRIVATE_KEY,
]];
$webPush = new WebPush($auth, ['TTL' => 3600, 'urgency' => 'high']);

// 6. Accoda una notifica per ogni candidato a ogni iscritto
$sent_ids = [];
foreach ($candidates as $id => $c) {
    $payload = json_encode([
        'title' => '⚡ ' . $c['category'],
        'body'  => $c['title'],
        'url'   => $c['link'],
        'image' => $c['image'],
        'source'=> $c['source'],
    ]);
    foreach ($subs as $s) {
        try {
            $webPush->queueNotification(Subscription::create($s), $payload);
        } catch (\Throwable $e) { /* iscrizione malformata: ignora */ }
    }
    $sent_ids[$id] = ['title' => $c['title'], 'sent_at' => $now];
}

// 7. Invia e gestisci le iscrizioni scadute
$expired = [];
foreach ($webPush->flush() as $report) {
    if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
        $expired[] = hash('sha256', $report->getEndpoint());
    }
}

// 8. Rimuovi iscrizioni scadute
if ($expired) {
    foreach ($subs as $k => $s) {
        if (in_array(hash('sha256', $s['endpoint']), $expired, true)) unset($subs[$k]);
    }
    file_put_contents(SUBSCRIPTIONS_FILE, json_encode($subs, JSON_PRETTY_PRINT));
}

// 9. Aggiorna lo stato anti-doppione (mantieni solo le ultime 500 voci)
$already = array_merge($already, $sent_ids);
if (count($already) > 500) $already = array_slice($already, -500, null, true);
file_put_contents(PUSH_STATE_FILE, json_encode($already, JSON_PRETTY_PRINT));

$log('Inviate ' . count($sent_ids) . ' notifiche a ' . count($subs) . ' iscritti.');
if ($expired) $log('Rimosse ' . count($expired) . ' iscrizioni scadute.');
