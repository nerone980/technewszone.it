<?php
// =================================================================
// FILE: feed.php — Feed RSS del sito (le ultime notizie aggregate)
// Rende il sito a sua volta "seguibile" e citabile.
// =================================================================
require_once __DIR__ . '/config.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$base = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'iltuosito.it');
$cache_file = __DIR__ . '/rss_cache.json';

$data = [];
if (file_exists($cache_file)) {
    $data = json_decode(file_get_contents($cache_file), true) ?: [];
}

// Raccogli tutte le notizie (esclude categorie speciali con "_")
$all = [];
foreach ($data as $cat => $arts) {
    if (strpos($cat, '_') === 0) continue;
    foreach ($arts as $a) {
        $a['_cat'] = $cat;
        $all[] = $a;
    }
}
usort($all, fn($x, $y) => ($y['timestamp'] ?? 0) - ($x['timestamp'] ?? 0));
$all = array_slice($all, 0, 40);

$lastBuild = !empty($all) ? date(DATE_RSS, $all[0]['timestamp']) : date(DATE_RSS);

function rss_clean($s) {
    return htmlspecialchars(strip_tags((string)$s), ENT_QUOTES | ENT_XML1, 'UTF-8');
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>TechNewsZone — Tech, Crypto e Finanza</title>
    <link><?php echo $base; ?>/</link>
    <atom:link href="<?php echo $base; ?>/feed.php" rel="self" type="application/rss+xml"/>
    <description>Le ultime notizie di tecnologia, crypto, finanza e attualità, aggregate in tempo reale.</description>
    <language>it-IT</language>
    <lastBuildDate><?php echo $lastBuild; ?></lastBuildDate>
<?php foreach ($all as $a):
    $title = rss_clean($a['title'] ?? '');
    $link  = htmlspecialchars($a['link'] ?? $base, ENT_QUOTES | ENT_XML1);
    $desc  = rss_clean(mb_substr($a['summary'] ?? '', 0, 300));
    $date  = date(DATE_RSS, $a['timestamp'] ?? time());
    $cat   = rss_clean($a['_cat'] ?? '');
?>
    <item>
        <title><?php echo $title; ?></title>
        <link><?php echo $link; ?></link>
        <guid isPermaLink="true"><?php echo $link; ?></guid>
        <category><?php echo $cat; ?></category>
        <pubDate><?php echo $date; ?></pubDate>
        <description><?php echo $desc; ?></description>
    </item>
<?php endforeach; ?>
</channel>
</rss>
