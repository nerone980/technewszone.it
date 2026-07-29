<?php
// FILE: functions.php — allineato al formato JSON (stesso file di update_cache.php)
// Nota: il sistema principale usa update_cache.php per generare rss_cache.json.
// Queste funzioni restano disponibili per un refresh "on demand" coerente col JSON.

$fetch_errors = [];
$cache_status = 'Pronto';

function extract_image_from_item($item) {
    if (isset($item->enclosure['url'])) {
        $type = (string)($item->enclosure['type'] ?? '');
        if (strpos($type, 'image') !== false || $type === '') return (string)$item->enclosure['url'];
    }
    $media = $item->children('media', true);
    if (isset($media->content->attributes()->url)) return (string)$media->content->attributes()->url;
    if (isset($media->thumbnail->attributes()->url)) return (string)$media->thumbnail->attributes()->url;

    $html = (string)($item->description ?? $item->summary ?? '');
    if (preg_match('/<img[^>]+src=[\'"]([^\'">]+)[\'"]/i', $html, $m)) return $m[1];
    return null;
}

function fetch_content_with_curl($url) {
    global $fetch_errors;
    if (!function_exists('curl_init')) {
        $fetch_errors[] = ['url' => $url, 'message' => 'cURL non disponibile'];
        return false;
    }
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; TechZoneBot/1.0)',
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($code == 200 && $data) return $data;
    $fetch_errors[] = ['url' => $url, 'message' => $err ?: "HTTP $code"];
    return false;
}

// $grouped_feeds atteso nel formato: 'Categoria' => [url, url, ...]
function fetch_and_process_feeds($grouped_feeds) {
    $grouped = [];
    foreach ($grouped_feeds as $category => $urls) {
        $grouped[$category] = [];
        foreach ($urls as $url) {
            $xml = fetch_content_with_curl($url);
            if (!$xml) continue;
            $rss = @simplexml_load_string($xml);
            if (!$rss) continue;

            $host  = parse_url($url, PHP_URL_HOST);
            $items = isset($rss->channel->item) ? $rss->channel->item
                   : (isset($rss->entry) ? $rss->entry : []);

            foreach ($items as $item) {
                $pub = (string)($item->pubDate ?? $item->published ?? '');
                if (!$pub) continue;
                $link = (string)($item->link ?? '#');
                if ($link === '#' && isset($item->link['href'])) $link = (string)$item->link['href'];

                $grouped[$category][] = [
                    'title'     => (string)($item->title ?? 'Senza titolo'),
                    'link'      => $link,
                    'timestamp' => strtotime($pub),
                    'summary'   => trim(strip_tags((string)($item->description ?? $item->summary ?? ''))),
                    'source'    => $host,
                    'image_url' => extract_image_from_item($item),
                ];
            }
        }
        usort($grouped[$category], fn($a, $b) => $b['timestamp'] - $a['timestamp']);
    }
    return array_filter($grouped);
}

// Legge la cache JSON; se scaduta e i feed sono passati, prova ad aggiornarla
function get_cached_news($feeds = null, $force_refresh = false) {
    global $cache_status;

    $exists = file_exists(CACHE_FILE);
    $valid  = $exists && (time() - filemtime(CACHE_FILE) < CACHE_LIFETIME);

    if ($valid && !$force_refresh) {
        $cache_status = 'Cache valida';
        return json_decode(file_get_contents(CACHE_FILE), true) ?: [];
    }

    if ($feeds) {
        $news = fetch_and_process_feeds($feeds);
        if (!empty($news)) {
            @file_put_contents(CACHE_FILE, json_encode($news, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $cache_status = 'Aggiornata';
            return $news;
        }
    }

    if ($exists) {
        $cache_status = 'Cache servita come fallback';
        return json_decode(file_get_contents(CACHE_FILE), true) ?: [];
    }

    $cache_status = 'Nessun dato disponibile';
    return [];
}
