<?php
// =================================================================
// SCRIPT AGGIORNAMENTO CACHE — scrive in rss_cache.json
// =================================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classifier.php';

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
set_time_limit(300);

$cache_file = CACHE_FILE;

function fetch_feed($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code == 200) ? $data : false;
}

function extract_image($item) {
    $img = '';
    if (isset($item->enclosure['url'])) $img = (string)$item->enclosure['url'];
    if (!$img) {
        $media = $item->children('media', true);
        if (isset($media->content->attributes()->url)) $img = (string)$media->content->attributes()->url;
        if (!$img && isset($media->thumbnail->attributes()->url)) $img = (string)$media->thumbnail->attributes()->url;
    }
    if (!$img) {
        preg_match('/<img.+?src=[\'"](?P<src>.+?)[\'"]/i', (string)$item->description, $m);
        if (isset($m['src'])) $img = $m['src'];
    }
    if (!$img) {
        $content = $item->children('content', true);
        if (isset($content->encoded)) {
            preg_match('/<img.+?src=[\'"](?P<src>.+?)[\'"]/i', (string)$content->encoded, $m);
            if (isset($m['src'])) $img = $m['src'];
        }
    }
    return $img;
}
?>
<!DOCTYPE html>
<html lang="it"><head><meta charset="UTF-8">
<title>Aggiornamento Cache — TechZone</title>
<style>
    body{background:#0a0c0f;color:#cfd6dd;font-family:'IBM Plex Mono',ui-monospace,monospace;padding:30px;line-height:1.6}
    h2{color:#e8b04b;font-family:sans-serif}
    .log{background:#06080a;border:1px solid #1c2128;padding:18px;border-radius:10px;font-size:13px}
    .ok{color:#3fb950}.err{color:#f85149}.warn{color:#e8b04b}.dim{color:#6e7681}
    .done{text-align:center;margin-top:24px}
    .done h1{color:#3fb950}
    a.btn{background:#e8b04b;color:#0a0c0f;padding:11px 26px;text-decoration:none;border-radius:8px;font-weight:700;font-family:sans-serif}
</style></head><body>
<h2>Aggiornamento notizie · max <?php echo ITEMS_PER_FEED; ?> per fonte</h2>
<div class="log">
<?php
// Prepara i contenitori: una categoria per ogni categoria "normale" del config
$grouped_data = [];
foreach ($feeds as $category => $urls) {
    $grouped_data[$category] = [];
}
$total = 0;
$reclassified = 0;
$seen_links = []; // dedup tra fonti diverse

foreach ($feeds as $category => $urls) {
    $is_special = (strpos($category, '_') === 0); // es. _Attualità: non riclassificare
    echo "<br><strong style='color:#fff'>$category</strong><br>";

    foreach ($urls as $url) {
        $host = parse_url($url, PHP_URL_HOST);
        echo "<span class='dim'>↓ $host … </span>";

        $xml = fetch_feed($url);
        if (!$xml) { echo "<span class='err'>fail</span><br>"; continue; }

        $rss = @simplexml_load_string($xml);
        if (!$rss) { echo "<span class='warn'>xml err</span><br>"; continue; }

        $items = isset($rss->channel->item) ? $rss->channel->item
               : (isset($rss->entry) ? $rss->entry : []);

        $count = 0;
        foreach ($items as $item) {
            if ($count >= ITEMS_PER_FEED) break;

            $title = trim((string)$item->title);
            if (strlen($title) <= 3) continue;

            $summary = trim(strip_tags((string)$item->description));
            $summary = preg_replace('/\s*\[.*?\]\s*/', '', $summary);

            $link = (string)$item->link;
            if (!$link && isset($item->link['href'])) $link = (string)$item->link['href']; // Atom
            $link = trim($link);

            // dedup: stesso link già preso da un'altra fonte
            $lkey = md5($link);
            if ($link && isset($seen_links[$lkey])) { continue; }
            if ($link) $seen_links[$lkey] = true;

            $article = [
                'title'     => $title,
                'link'      => $link,
                'summary'   => $summary,
                'source'    => $host,
                'timestamp' => strtotime((string)($item->pubDate ?? $item->published ?? 'now')),
                'image_url' => extract_image($item),
            ];

            // Classificazione: le categorie speciali restano dove sono.
            // Per le altre, analizza il contenuto e scegli la categoria migliore.
            if ($is_special) {
                $target = $category;
            } else {
                $target = classify_article($title, $summary, $category);
                // il classificatore può restituire solo categorie esistenti nel config
                if (!isset($grouped_data[$target])) $target = $category;
                if ($target !== $category) $reclassified++;
            }

            $grouped_data[$target][] = $article;
            $count++; $total++;
        }
        echo "<span class='ok'>ok ($count)</span><br>";
    }
}

// Ordina ogni categoria per data e rimuove eventuali categorie rimaste vuote
foreach ($grouped_data as $cat => &$arts) {
    usort($arts, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
}
unset($arts);
$grouped_data = array_filter($grouped_data, fn($a) => !empty($a));

echo "</div>";
echo "<p style='color:#e8b04b;text-align:center;font-family:monospace'>$reclassified articoli riclassificati in base al contenuto</p>";

if ($total > 0) {
    $json = json_encode($grouped_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($cache_file, $json) !== false) {
        echo "<div class='done'><h1>Cache aggiornata · $total notizie</h1>";
        echo "<a class='btn' href='index.php'>Vai alla home</a></div>";
    } else {
        echo "<div class='done'><h1 style='color:#f85149'>Impossibile scrivere la cache</h1>";
        echo "<p class='dim'>Controlla i permessi di scrittura su rss_cache.json (CHMOD 664/666).</p></div>";
    }
} else {
    echo "<div class='done'><h1 style='color:#f85149'>Nessuna notizia scaricata</h1></div>";
}
?>
</body></html>
