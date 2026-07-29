<?php
// =================================================================
// FILE: classifier.php — Classificazione intelligente per parole chiave
// =================================================================
// Analizza titolo + riassunto e assegna la categoria più pertinente,
// ignorando la fonte. Se nessuna categoria supera la soglia minima,
// mantiene la categoria originale come fallback.
//
// COME AFFINARLO: aggiungi/togli parole nei dizionari qui sotto.
// Le parole nel TITOLO pesano di più di quelle nel riassunto.
// =================================================================

// Dizionari di parole chiave per categoria.
// Nota: usa termini minuscoli, senza accenti dove possibile.
$CLASSIFIER_KEYWORDS = [

    'Offerte' => [
        'offerta', 'offerte', 'sconto', 'sconti', 'scontato', 'coupon', 'codice sconto',
        'promozione', 'promo', 'risparmi', 'risparmio', 'prezzo piu basso', 'minimo storico',
        'black friday', 'cyber monday', 'prime day', 'saldi', 'deal', 'affare', 'in offerta',
        'crollo prezzo', 'super prezzo', 'bomba', 'imperdibile', 'occasione',
    ],

    'Crypto' => [
        'bitcoin', 'btc', 'ethereum', 'eth', 'crypto', 'criptovalut', 'blockchain', 'wallet',
        'defi', 'nft', 'altcoin', 'stablecoin', 'binance', 'coinbase', 'exchange', 'token',
        'solana', 'ripple', 'xrp', 'dogecoin', 'mining', 'halving', 'satoshi', 'web3',
    ],

    'Finanza' => [
        'borsa', 'borse', 'mercati', 'azioni', 'azionar', 'inflazione', 'bce', 'fed', 'tassi',
        'spread', 'pil', 'economia', 'investiment', 'wall street', 'nasdaq', 'ftse', 'etf',
        'obbligazion', 'dividend', 'trimestrale', 'utili', 'recessione', 'euro dollaro',
    ],

    'Gaming' => [
        'gioco', 'giochi', 'videogioc', 'gaming', 'playstation', 'ps5', 'xbox', 'nintendo',
        'switch', 'steam', 'console', 'gamer', 'gameplay', 'trailer', 'dlc', 'multiplayer',
        'esport', 'rpg', 'fps', 'open world', 'sony', 'ubisoft', 'rockstar',
    ],

    'Apple' => [
        'iphone', 'ipad', 'macbook', 'imac', 'apple watch', 'airpods', 'ios ', 'ipados',
        'macos', 'tim cook', 'app store', 'vision pro', 'iphone 16', 'iphone 17', 'siri',
    ],

    'Android' => [
        'android', 'samsung', 'galaxy', 'pixel', 'xiaomi', 'oneplus', 'huawei', 'oppo',
        'google play', 'play store', 'wear os', 'motorola', 'realme',
    ],

    'Auto & Motori' => [
        'auto', 'automobile', 'motori', 'elettrica', 'ibrida', 'suv', 'tesla', 'volkswagen',
        'ferrari', 'motore', 'ricarica', 'colonnina', 'patente', 'guida autonoma', 'ev ',
        'batteria auto', 'concept', 'formula 1', 'motogp', 'benzina', 'diesel',
    ],

    'Scienza & Spazio' => [
        'spazio', 'nasa', 'esa', 'spacex', 'razzo', 'satellite', 'luna', 'marte', 'pianeta',
        'galassia', 'telescopio', 'astronaut', 'orbita', 'ricerca', 'scienziat', 'scoperta',
        'fisica', 'quantist', 'dna', 'fossile', 'clima', 'meteorite',
    ],

    'Cybersecurity' => [
        'sicurezza informatica', 'cybersecurity', 'hacker', 'malware', 'ransomware', 'phishing',
        'vulnerabilit', 'data breach', 'attacco informatico', 'exploit', 'password', 'antivirus',
        'cyberattacco', 'violazione', 'spyware', 'trojan', 'ddos', 'furto dati',
    ],

    'AI & Innovazione' => [
        'intelligenza artificiale', ' ia ', ' ai ', 'chatgpt', 'openai', 'machine learning',
        'deep learning', 'rete neurale', 'chatbot', 'gemini', 'copilot', 'llm', 'gpt',
        'modello linguistico', 'generativa', 'automazione', 'algoritmo',
    ],

    'Tecnologia' => [
        'tecnologia', 'smartphone', 'notebook', 'laptop', 'processore', 'chip', 'gpu', 'cpu',
        'display', 'monitor', 'cuffie', 'wearable', 'gadget', 'recensione', 'hardware',
        'software', 'app', 'aggiornamento', 'wifi', 'usb', 'device',
    ],
];

// Peso extra per le parole trovate nel titolo (il titolo è più indicativo)
define('CLASSIFIER_TITLE_WEIGHT', 3);
define('CLASSIFIER_BODY_WEIGHT', 1);
// Punteggio minimo per riclassificare; sotto questa soglia si tiene il fallback
define('CLASSIFIER_MIN_SCORE', 3);

// Normalizza il testo per il confronto (minuscolo, spazi puliti)
function classifier_norm($text) {
    $text = mb_strtolower($text, 'UTF-8');
    // aggiunge spazi ai bordi per far matchare pattern come ' ai '
    return ' ' . preg_replace('/\s+/', ' ', $text) . ' ';
}

// Restituisce la categoria migliore per un articolo, o $fallback se incerta
function classify_article($title, $summary, $fallback = null) {
    global $CLASSIFIER_KEYWORDS;

    $t = classifier_norm($title);
    $b = classifier_norm($summary);

    $scores = [];
    foreach ($CLASSIFIER_KEYWORDS as $cat => $words) {
        $score = 0;
        foreach ($words as $w) {
            if ($w === '') continue;
            if (mb_strpos($t, $w) !== false) $score += CLASSIFIER_TITLE_WEIGHT;
            if (mb_strpos($b, $w) !== false) $score += CLASSIFIER_BODY_WEIGHT;
        }
        if ($score > 0) $scores[$cat] = $score;
    }

    if (empty($scores)) return $fallback;

    arsort($scores);
    $best = array_key_first($scores);
    $bestScore = $scores[$best];

    // Se il punteggio è troppo basso, non fidarsi: tieni il fallback
    if ($bestScore < CLASSIFIER_MIN_SCORE) return $fallback;

    return $best;
}
