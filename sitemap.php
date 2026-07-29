<?php
// =================================================================
// FILE: sitemap.php — Sitemap XML dinamica (home + una URL per categoria)
// Google la legge da robots.txt (Sitemap: .../sitemap.php)
// =================================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/seo.php'; // per seo_slugify()

header('Content-Type: application/xml; charset=utf-8');

$base = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'iltuosito.it');

// Leggi le categorie disponibili dalla cache (riflette ciò che è online)
$cats = [];
$cache_file = __DIR__ . '/rss_cache.json';
$lastmod = date('c');
if (file_exists($cache_file)) {
    $lastmod = date('c', filemtime($cache_file));
    $data = json_decode(file_get_contents($cache_file), true) ?: [];
    foreach (array_keys($data) as $c) {
        if (strpos($c, '_') === 0) continue; // salta categorie speciali (attualità)
        $cats[] = $c;
    }
}
// fallback: se la cache è vuota, usa le categorie del config
if (empty($cats)) {
    foreach (array_keys($feeds) as $c) {
        if (strpos($c, '_') === 0) continue;
        $cats[] = $c;
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Home
echo "  <url>\n";
echo "    <loc>{$base}/</loc>\n";
echo "    <lastmod>{$lastmod}</lastmod>\n";
echo "    <changefreq>hourly</changefreq>\n";
echo "    <priority>1.0</priority>\n";
echo "  </url>\n";

// Pagine statiche
foreach (['chi-siamo.php', 'privacy.php'] as $page) {
    echo "  <url>\n";
    echo "    <loc>{$base}/{$page}</loc>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.3</priority>\n";
    echo "  </url>\n";
}

// Una URL per categoria
foreach ($cats as $c) {
    $url = $base . '/?cat=' . rawurlencode(seo_slugify($c));
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url, ENT_XML1) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>hourly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
