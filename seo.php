<?php
// =================================================================
// FILE: seo.php — Helper SEO condiviso (meta, canonical, OG, JSON-LD)
// Incluso da view_desktop.php e view_mobile.php.
// Presuppone che $grouped_news_data sia già stato caricato.
// =================================================================

// --- Config base del sito (personalizza questi valori) ---
$SEO_SITE_NAME   = 'TechNewsZone';
$SEO_BASE_URL    = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'iltuosito.it'); // dominio automatico
$SEO_DEFAULT_DESC = 'Le ultime notizie di tecnologia, crypto, finanza, gaming e attualità in tempo reale. Aggregatore news aggiornato di continuo.';
$SEO_DEFAULT_IMG = $SEO_BASE_URL . '/android-chrome-512x512.png';
$SEO_LOCALE      = 'it_IT';

// --- Risolvi la categoria richiesta via URL (?cat=slug o /categoria) ---
// Confronta lo slug richiesto con le categorie disponibili.
function seo_slugify($text) {
    $text = mb_strtolower($text, 'UTF-8');
    // rimuove emoji e simboli, tiene lettere/numeri, spazi -> trattino
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    $text = preg_replace('/\s+/', '-', trim($text));
    return trim($text, '-');
}

$SEO_active_cat   = null;   // nome categoria attiva (se URL la specifica)
$SEO_active_slug  = null;
$requested = $_GET['cat'] ?? '';
if ($requested !== '' && !empty($grouped_news_data)) {
    foreach (array_keys($grouped_news_data) as $catName) {
        if (seo_slugify($catName) === seo_slugify($requested)) {
            $SEO_active_cat  = $catName;
            $SEO_active_slug = seo_slugify($catName);
            break;
        }
    }
}

// --- Costruisci title / description / canonical in base al contesto ---
if ($SEO_active_cat) {
    $clean = trim(preg_replace('/[^\p{L}\p{N}\s&]/u', '', $SEO_active_cat)); // toglie emoji dal testo visibile
    $SEO_title = "$clean — Ultime notizie | $SEO_SITE_NAME";
    $SEO_desc  = "Tutte le ultime notizie di " . mb_strtolower($clean) . " aggiornate in tempo reale su $SEO_SITE_NAME.";
    $SEO_canonical = $SEO_BASE_URL . '/?cat=' . rawurlencode($SEO_active_slug);
} else {
    $SEO_title = "$SEO_SITE_NAME | Notizie Tech, Crypto e Finanza in tempo reale";
    $SEO_desc  = $SEO_DEFAULT_DESC;
    $SEO_canonical = $SEO_BASE_URL . '/';
}

// --- Immagine OG: la prima immagine disponibile nel contesto attivo ---
$SEO_image = $SEO_DEFAULT_IMG;
$scan = $SEO_active_cat ? [$SEO_active_cat => $grouped_news_data[$SEO_active_cat]] : $grouped_news_data;
foreach ($scan as $arts) {
    foreach ($arts as $a) {
        if (!empty($a['image_url'])) { $SEO_image = $a['image_url']; break 2; }
    }
}

// --- Funzione che stampa tutti i tag <head> SEO ---
function seo_render_head() {
    global $SEO_SITE_NAME, $SEO_title, $SEO_desc, $SEO_canonical, $SEO_image, $SEO_LOCALE, $SEO_BASE_URL;
    $t = htmlspecialchars($SEO_title, ENT_QUOTES);
    $d = htmlspecialchars($SEO_desc, ENT_QUOTES);
    $c = htmlspecialchars($SEO_canonical, ENT_QUOTES);
    $img = htmlspecialchars($SEO_image, ENT_QUOTES);
    $site = htmlspecialchars($SEO_SITE_NAME, ENT_QUOTES);
    echo <<<HTML
    <title>$t</title>
    <meta name="description" content="$d">
    <link rel="canonical" href="$c">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="$site">
    <meta property="og:title" content="$t">
    <meta property="og:description" content="$d">
    <meta property="og:url" content="$c">
    <meta property="og:image" content="$img">
    <meta property="og:locale" content="$SEO_LOCALE">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="$t">
    <meta name="twitter:description" content="$d">
    <meta name="twitter:image" content="$img">

HTML;
}

// --- Funzione che stampa il JSON-LD (dati strutturati) ---
function seo_render_jsonld() {
    global $SEO_SITE_NAME, $SEO_BASE_URL, $SEO_active_cat, $grouped_news_data;

    // 1) WebSite + SearchAction
    $website = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => $SEO_SITE_NAME,
        'url'      => $SEO_BASE_URL . '/',
    ];

    // 2) ItemList delle notizie visibili (aiuta Google a capire la lista)
    $listCat = $SEO_active_cat ?: array_key_first($grouped_news_data);
    $items = [];
    if ($listCat && !empty($grouped_news_data[$listCat])) {
        $pos = 1;
        foreach (array_slice($grouped_news_data[$listCat], 0, 10) as $a) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'url'      => $a['link'] ?? $SEO_BASE_URL,
                'name'     => $a['title'] ?? '',
            ];
        }
    }
    $itemList = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'itemListElement' => $items,
    ];

    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    echo '<script type="application/ld+json">' . json_encode($website, $flags) . '</script>' . "\n";
    if ($items) {
        echo '<script type="application/ld+json">' . json_encode($itemList, $flags) . '</script>' . "\n";
    }

    // 3) Breadcrumb (Home > Categoria) quando si è dentro una categoria
    global $SEO_active_slug;
    if ($SEO_active_cat) {
        $clean = trim(preg_replace('/[^\p{L}\p{N}\s&]/u', '', $SEO_active_cat));
        $crumb = [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $SEO_BASE_URL . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $clean, 'item' => $SEO_BASE_URL . '/?cat=' . rawurlencode($SEO_active_slug)],
            ],
        ];
        echo '<script type="application/ld+json">' . json_encode($crumb, $flags) . '</script>' . "\n";
    }
}
