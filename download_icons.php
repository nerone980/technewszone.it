<?php
// FILE: download_icons.php

// -----------------------------------------------------------
// 1. CONFIGURAZIONE
// -----------------------------------------------------------

// Definisce la cartella di destinazione per le icone.
// DEVE ESISTERE e avere permessi di scrittura (chmod 755/777).
$target_dir = __DIR__ . '/img/';

// Elenco degli URL delle icone e i nomi dei file locali
$icon_sources = [
    // Crypto Icons (PNG)
    ['url' => 'https://assets.coingecko.com/coins/images/1/small/bitcoin.png', 'filename' => 'btc.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/279/small/ethereum.png', 'filename' => 'eth.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/825/small/bnb.png', 'filename' => 'bnb.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/4128/small/solana.png', 'filename' => 'sol.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/44/small/xrp.png', 'filename' => 'xrp.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/975/small/cardano.png', 'filename' => 'ada.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/12559/small/avalanche.png', 'filename' => 'avax.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/5/small/dogecoin.png', 'filename' => 'doge.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/12171/small/polkadot.png', 'filename' => 'dot.png'],
    ['url' => 'https://assets.coingecko.com/coins/images/109/small/tron.png', 'filename' => 'trx.png'],

    // Flag Icons (SVG - usati nel log)
    ['url' => 'https://cdn.jsdelivr.net/gh/lipis/flag-icon-css@3.1.0/flags/1x1/us.svg', 'filename' => 'usd.svg'],
    ['url' => 'https://cdn.jsdelivr.net/gh/lipis/flag-icon-css@3.1.0/flags/1x1/eu.svg', 'filename' => 'eur.svg'],
    ['url' => 'https://cdn.jsdelivr.net/gh/lipis/flag-icon-css@3.1.0/flags/1x1/gb.svg', 'filename' => 'gbp.svg'],
    ['url' => 'https://cdn.jsdelivr.net/gh/lipis/flag-icon-css@3.1.0/flags/1x1/jp.svg', 'filename' => 'jpy.svg'],
    ['url' => 'https://cdn.jsdelivr.net/gh/lipis/flag-icon-css@3.1.0/flags/1x1/ch.svg', 'filename' => 'chf.svg'],
];


// -----------------------------------------------------------
// 2. LOGICA DI DOWNLOAD
// -----------------------------------------------------------

echo "<h1>🚀 Script di Download Icone Avviato</h1>";
echo "<p>Cartella di destinazione: <code>{$target_dir}</code></p><hr>";

// Verifica l'esistenza e la scrivibilità della directory
if (!is_dir($target_dir)) {
    echo "<p style='color:red; font-weight:bold;'>❌ ERRORE CRITICO: La cartella 'img/' non esiste!</p>";
    exit();
}
if (!is_writable($target_dir)) {
    echo "<p style='color:red; font-weight:bold;'>❌ ERRORE CRITICO: La cartella 'img/' non ha permessi di scrittura (CHMOD). Aggiorna i permessi (es. 777).</p>";
    exit();
}

$success_count = 0;
$error_count = 0;

foreach ($icon_sources as $source) {
    $remote_url = $source['url'];
    $local_path = $target_dir . $source['filename'];

    echo "<p>→ Download <code>{$source['filename']}</code> da {$remote_url}... ";

    // Tenta di scaricare il contenuto del file remoto
    $file_content = @file_get_contents($remote_url);

    if ($file_content === FALSE) {
        // Fallimento nel recuperare l'URL remoto (potrebbe essere bloccato da allow_url_fopen o cURL)
        echo "<span style='color:red;'>❌ ERRORE: Impossibile scaricare l'URL. Controlla che <code>allow_url_fopen</code> sia abilitato o che l'hosting non blocchi le richieste esterne.</span></p>";
        $error_count++;
    } else {
        // Tenta di salvare il file in locale
        $bytes_written = @file_put_contents($local_path, $file_content);

        if ($bytes_written === FALSE) {
            // Fallimento nel salvare il file (problemi di permessi)
            echo "<span style='color:red;'>❌ ERRORE: Impossibile salvare il file in locale. Controlla i permessi CHMOD della cartella 'img/'.</span></p>";
            $error_count++;
        } else {
            // Successo
            echo "<span style='color:green; font-weight:bold;'>✅ SUCCESSO!</span> Salvato {$bytes_written} bytes.</p>";
            $success_count++;
        }
    }
}

echo "<hr><h2>🏁 Script Completato</h2>";
echo "<p>Totale successi: <span style='color:green;'>{$success_count}</span></p>";
echo "<p>Totale errori: <span style='color:red;'>{$error_count}</span></p>";
?>