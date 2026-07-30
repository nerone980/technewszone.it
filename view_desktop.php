<?php
// FILE: view_desktop.php — Restyling "Terminal" + funzioni complete
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

require_once __DIR__ . '/partners.php';
$partners = get_active_partners();
require_once __DIR__ . '/category_icons.php';

$cache_file = __DIR__ . '/rss_cache.json';
$grouped_news_data = [];
if (file_exists($cache_file)) {
    $content = file_get_contents($cache_file);
    if ($content && ($decoded = json_decode($content, true))) $grouped_news_data = $decoded;
}

// --- Estrai le categorie speciali (prefisso "_") per lo slider attualità ---
$ticker_news = [];
foreach ($grouped_news_data as $cat => $articles) {
    if (strpos($cat, '_') === 0) {
        foreach ($articles as $art) $ticker_news[] = $art;
        unset($grouped_news_data[$cat]); // non deve apparire tra i canali
    }
}
usort($ticker_news, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$ticker_news = array_slice($ticker_news, 0, 12);

// --- HOME: ultime 24h, ordinate ---
$home_news = [];
$now = time();
foreach ($grouped_news_data as $cat => $articles) {
    foreach ($articles as $art) {
        if (($now - $art['timestamp']) < 86400 && ($art['source'] ?? '') !== 'System') $home_news[] = $art;
    }
}
usort($home_news, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
if (!empty($home_news)) {
    $grouped_news_data = array_merge(['Home' => array_slice($home_news, 0, 24)], $grouped_news_data);
}
$INITIAL_LOAD_COUNT = 8;
require_once __DIR__ . '/seo.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="dns-prefetch" href="https://api.coingecko.com">
<?php seo_render_head(); ?>
<link rel="alternate" type="application/rss+xml" title="TechNewsZone — Feed RSS" href="feed.php">
<link rel="icon" type="image/x-icon" href="/img/favicon.ico">
<link rel="apple-touch-icon" href="/img/apple-touch-icon.png">
<link rel="manifest" href="manifest.json">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
    --bg:#0a0c0f; --panel:#0f1318; --panel-2:#12171d; --line:#1c2128;
    --ink:#e6edf3; --ink-dim:#8b949e; --ink-faint:#5a6473;
    --amber:#e8b04b; --amber-soft:#3a2f17; --cyan:#56b6c2;
    --up:#3fb950; --down:#f85149;
    --r:12px;
}
body[data-theme="light"]{
    --bg:#f4f2ec; --panel:#ffffff; --panel-2:#faf8f3; --line:#e2ddd2;
    --ink:#1a1a17; --ink-dim:#5f5e58; --ink-faint:#8a877d;
    --amber:#b07a12; --amber-soft:#f5e6c8; --cyan:#0f7d8c;
    --up:#1a8f3c; --down:#c73434;
}
body[data-theme="light"] ::-webkit-scrollbar-thumb{background:#cfc9bc}
body[data-theme="light"] .topbar{background:rgba(244,242,236,.85)}
body[data-theme="light"] .card-img{filter:none}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{
    margin:0; background:var(--bg); color:var(--ink);
    font-family:'Space Grotesk',system-ui,sans-serif;
    padding-top:58px; -webkit-font-smoothing:antialiased;
}
::selection{background:var(--amber);color:#000}
::-webkit-scrollbar{width:10px;height:8px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:#222a33;border-radius:10px}
.mono{font-family:'IBM Plex Mono',ui-monospace,monospace}
.wrap{max-width:1340px;margin:0 auto;padding:0 22px}

/* TOPBAR */
.topbar{
    position:fixed;top:0;left:0;right:0;height:58px;z-index:1100;
    background:rgba(10,12,15,.85);backdrop-filter:blur(12px);
    border-bottom:1px solid var(--line);
    display:flex;align-items:center;
}
.topbar .wrap{display:flex;justify-content:space-between;align-items:center;width:100%}
.brand{font-weight:700;font-size:1.15rem;letter-spacing:-.02em;text-decoration:none;color:var(--ink);display:flex;align-items:center;gap:10px}
.brand-ico{flex-shrink:0}
.brand b{color:var(--amber)}
.brand .dot{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--up);margin-left:8px;animation:pulse 2s infinite;vertical-align:middle}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.topbar-clock{font-size:.78rem;color:var(--ink-faint);letter-spacing:.04em}
.btn-refresh{
    background:transparent;border:1px solid var(--line);color:var(--ink-dim);
    width:34px;height:34px;border-radius:9px;cursor:pointer;transition:.2s;
    display:inline-flex;align-items:center;justify-content:center;margin-left:14px;
}
.btn-refresh:hover{border-color:var(--amber);color:var(--amber);transform:rotate(90deg)}
#themeBtn:hover{transform:none}

/* NEWSFLASH — slider attualità */
.newsflash{display:flex;align-items:center;gap:0;background:var(--panel-2);border:1px solid var(--line);
    border-left:3px solid var(--amber);border-radius:var(--r);overflow:hidden;height:44px;margin:22px 0 0;
    text-decoration:none;transition:border-color .2s}
.newsflash:hover{border-color:#2c3640;border-left-color:var(--amber)}
.nf-tag{flex-shrink:0;display:flex;align-items:center;gap:7px;padding:0 16px;font-family:'IBM Plex Mono',monospace;
    font-size:.66rem;font-weight:600;letter-spacing:.14em;color:var(--amber);border-right:1px solid var(--line);height:100%}
.nf-dot{width:6px;height:6px;border-radius:50%;background:var(--down);animation:pulse 1.4s infinite}
.nf-body{flex:1;min-width:0;display:flex;align-items:center;gap:12px;padding:0 18px;overflow:hidden}
.nf-title{color:var(--ink);font-size:.9rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    transition:opacity .4s}
.nf-source{flex-shrink:0;font-family:'IBM Plex Mono',monospace;font-size:.7rem;color:var(--ink-faint)}
.nf-nav{flex-shrink:0;padding:0 16px;border-left:1px solid var(--line);height:100%;display:flex;align-items:center}
.nf-count{font-family:'IBM Plex Mono',monospace;font-size:.7rem;color:var(--ink-faint)}
.newsflash .nf-title.fade{opacity:0}

/* TICKER */
.ticker{
    display:flex;align-items:stretch;background:var(--panel);
    border:1px solid var(--line);border-radius:var(--r);
    overflow:hidden;height:46px;margin:14px 0 26px;
}
.ticker-tag{
    flex-shrink:0;display:flex;align-items:center;gap:7px;padding:0 16px;
    background:var(--amber-soft);color:var(--amber);font-size:.68rem;
    font-weight:600;letter-spacing:.12em;border-right:1px solid var(--line);
}
.ticker-tag .live{width:6px;height:6px;border-radius:50%;background:var(--up);animation:pulse 1.5s infinite}
.ticker-view{flex:1;overflow:hidden;position:relative}
.ticker-row{display:flex;position:absolute;height:100%;align-items:center;white-space:nowrap;animation:slide 55s linear infinite}
.ticker:hover .ticker-row{animation-play-state:paused}
.t-item{display:flex;align-items:center;gap:9px;padding:0 26px;font-size:.86rem;border-right:1px solid var(--line)}
.t-item img{width:20px;height:20px;border-radius:50%}
.t-item .sym{font-weight:600}
.t-item .px{color:var(--ink-dim)}
.t-up{color:var(--up)}.t-down{color:var(--down)}
@keyframes slide{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* GRID */
.layout{display:grid;grid-template-columns:1fr 290px;gap:26px;align-items:start;padding-bottom:60px}
@media(max-width:980px){.layout{grid-template-columns:1fr}}

/* PARTNER / SPONSOR */
.sponsor-note{font-family:'IBM Plex Mono',monospace;font-size:.72rem;color:var(--ink-faint);
    margin:0 0 18px;padding:8px 12px;border:1px dashed var(--line);border-radius:8px;display:inline-block}
.partner-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:640px){.partner-grid{grid-template-columns:1fr}}
.partner{display:flex;flex-direction:column;background:var(--panel);border:1px solid var(--line);
    border-radius:var(--r);padding:20px;text-decoration:none;transition:border-color .2s,transform .2s;position:relative;overflow:hidden}
.partner::before{content:'';position:absolute;top:0;left:0;width:100%;height:3px;background:var(--pc,var(--amber))}
.partner:hover{border-color:#2c3640;transform:translateY(-3px)}
.partner-top{display:flex;align-items:center;gap:14px;margin-bottom:14px}
.partner-logo{width:46px;height:46px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:1.3rem;color:#0a0c0f;background:var(--pc,var(--amber));object-fit:cover}
.partner-name{font-size:1.15rem;font-weight:700;color:var(--ink);letter-spacing:-.01em}
.partner-bonus{font-family:'IBM Plex Mono',monospace;font-size:.72rem;font-weight:600;color:var(--pc,var(--amber))}
.partner-tag{color:var(--ink-dim);font-size:.88rem;line-height:1.45;margin:0 0 18px;flex:1}
.partner-cta{align-self:flex-start;display:inline-flex;align-items:center;gap:8px;font-weight:600;font-size:.85rem;
    color:#0a0c0f;background:var(--pc,var(--amber));padding:9px 18px;border-radius:8px;transition:gap .2s}
.partner:hover .partner-cta{gap:12px}
.partner-disclaimer{font-family:'IBM Plex Mono',monospace;font-size:.62rem;color:var(--ink-faint);margin-top:10px}
.movers-head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:16px}
.movers-title{font-size:.72rem;letter-spacing:.14em;color:var(--amber)}
.movers-sub{font-size:.68rem;letter-spacing:.04em;color:var(--ink-faint)}
.movers-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:980px){.movers-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.movers-grid{grid-template-columns:1fr}}
.mover{display:flex;align-items:center;gap:12px;background:var(--bg);border:1px solid var(--line);
    border-radius:10px;padding:12px 14px;text-decoration:none;transition:border-color .2s,transform .2s}
.mover:hover{border-color:#2c3640;transform:translateY(-2px)}
.mover-ico{width:34px;height:34px;border-radius:50%;flex-shrink:0;background:#06080a}
.mover-info{min-width:0;flex:1}
.mover-sym{font-weight:700;font-size:.92rem;color:var(--ink);text-transform:uppercase}
.mover-price{font-family:'IBM Plex Mono',monospace;font-size:.78rem;color:var(--ink-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mover-chg{flex-shrink:0;font-family:'IBM Plex Mono',monospace;font-size:.82rem;font-weight:600;padding:3px 8px;border-radius:6px}
.mover-chg.up{color:var(--up);background:rgba(63,185,80,.12)}
.mover-chg.down{color:var(--down);background:rgba(248,81,73,.12)}
.mover-skeleton{height:58px;border-radius:10px;background:linear-gradient(90deg,var(--bg) 25%,var(--panel-2) 50%,var(--bg) 75%);
    background-size:200% 100%;animation:shimmer 1.4s infinite}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* SEARCH */
.searchbar{position:relative;margin:0 0 22px}
.searchbar i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--ink-faint);font-size:.9rem}
.searchbar input{
    width:100%;background:var(--panel);border:1px solid var(--line);border-radius:var(--r);
    color:var(--ink);font-family:'IBM Plex Mono',monospace;font-size:.9rem;
    padding:13px 44px 13px 42px;outline:none;transition:border-color .2s;
}
.searchbar input:focus{border-color:var(--amber)}
.searchbar input::placeholder{color:var(--ink-faint)}
.search-clear{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;
    color:var(--ink-faint);cursor:pointer;font-size:1rem;display:none}
.search-clear:hover{color:var(--ink)}
.search-info{font-family:'IBM Plex Mono',monospace;font-size:.78rem;color:var(--ink-dim);margin:0 0 18px;display:none}
.search-info b{color:var(--amber)}
.no-results{text-align:center;color:var(--ink-faint);font-family:'IBM Plex Mono',monospace;padding:40px 0;display:none}

/* SECTION HEAD */
.sec-head{display:flex;align-items:baseline;gap:12px;margin:0 0 18px;padding-bottom:12px;border-bottom:1px solid var(--line)}
.sec-head h2{font-size:1.3rem;font-weight:700;margin:0;letter-spacing:-.02em}
.sec-head .cnt{font-size:.75rem;color:var(--ink-faint)}
.sec-head .bar{flex:1;height:1px}

/* NEWS GRID */
.news-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
@media(max-width:640px){.news-grid{grid-template-columns:1fr}}
.card{
    background:var(--panel);border:1px solid var(--line);border-radius:var(--r);
    overflow:hidden;display:flex;flex-direction:column;
    transition:border-color .2s, transform .2s;
}
.card:hover{border-color:#2c3640;transform:translateY(-2px)}
.card-img{height:160px;width:100%;object-fit:cover;background:#06080a;filter:saturate(.9)}
.card-body{padding:16px;display:flex;flex-direction:column;flex:1;gap:12px}
.card-title{color:var(--ink);font-weight:600;font-size:1.02rem;line-height:1.32;text-decoration:none}
.card-title:hover{color:var(--amber)}
.card-sum{color:var(--ink-dim);font-size:.82rem;line-height:1.5;margin:0}
.card-foot{margin-top:auto;display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid var(--line)}
.src{font-family:'IBM Plex Mono',monospace;font-size:.72rem;color:var(--cyan);letter-spacing:.02em}
.share-row{display:flex;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--line)}
.share-btn{width:30px;height:30px;border-radius:8px;border:1px solid var(--line);background:transparent;
    color:var(--ink-faint);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;
    text-decoration:none;font-size:.85rem;transition:.15s}
.share-btn:hover{border-color:var(--amber);color:var(--amber)}
.share-btn.s-wa:hover{border-color:#25d366;color:#25d366}
.share-btn.s-tg:hover{border-color:#2aabee;color:#2aabee}
.share-btn.s-x:hover{border-color:#e6edf3;color:#e6edf3}
.x-icon{font-size:.95rem;font-weight:700;line-height:1}
.date{font-family:'IBM Plex Mono',monospace;font-size:.72rem;color:var(--ink-faint)}
.tag-new{
    display:inline-block;font-family:'IBM Plex Mono',monospace;font-size:.6rem;font-weight:600;
    letter-spacing:.1em;color:var(--bg);background:var(--amber);
    padding:2px 7px;border-radius:4px;margin-right:8px;vertical-align:middle;
}

.load-more{
    display:block;margin:26px auto 0;background:transparent;
    border:1px solid var(--line);color:var(--ink-dim);
    font-family:'IBM Plex Mono',monospace;font-size:.78rem;letter-spacing:.08em;
    padding:11px 30px;border-radius:30px;cursor:pointer;transition:.2s;
}
.load-more:hover{border-color:var(--amber);color:var(--amber)}

/* SIDEBAR */
.side{position:sticky;top:80px;display:flex;flex-direction:column;gap:22px}
@media(max-width:980px){.side{position:static}}

/* GAUGE */
.gauge-panel{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:22px;text-align:center}
.gauge-panel .lbl{font-family:'IBM Plex Mono',monospace;font-size:.68rem;letter-spacing:.14em;color:var(--ink-faint);margin-bottom:18px}
.gauge{position:relative;width:170px;height:90px;margin:0 auto 4px;overflow:hidden}
.gauge-arc{position:absolute;width:170px;height:170px;border-radius:50%;
    background:conic-gradient(from -90deg,var(--down) 0%,var(--amber) 50%,var(--up) 100%);
    -webkit-mask:radial-gradient(circle,transparent 60%,#000 61%);
    mask:radial-gradient(circle,transparent 60%,#000 61%);}
.gauge-needle{position:absolute;bottom:0;left:50%;width:3px;height:72px;background:var(--ink);
    transform-origin:bottom center;transform:rotate(-90deg);
    transition:transform 2s cubic-bezier(.15,.6,.2,1);border-radius:3px}
.gauge-needle::after{content:'';position:absolute;bottom:-5px;left:-3.5px;width:10px;height:10px;border-radius:50%;background:var(--ink)}
#fng-val{font-family:'IBM Plex Mono',monospace;font-size:2rem;font-weight:600;line-height:1;margin-top:10px}
#fng-class{font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-dim);margin-top:6px}

/* CHANNELS NAV */
.channels{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:10px}
.channels .head{font-family:'IBM Plex Mono',monospace;font-size:.68rem;letter-spacing:.14em;color:var(--ink-faint);padding:8px 10px 12px}
.chan-btn{
    display:flex;justify-content:space-between;align-items:center;width:100%;
    background:transparent;border:none;color:var(--ink-dim);text-align:left;
    padding:10px 12px;border-radius:8px;cursor:pointer;font-size:.86rem;
    font-weight:500;font-family:inherit;transition:.15s;
}
.chan-btn:hover{background:var(--panel-2);color:var(--ink)}
.chan-btn.active{background:var(--amber-soft);color:var(--amber)}
.chan-partner{border:1px dashed var(--line)}
.chan-partner:hover{border-color:var(--amber)}
.chan-btn .n{font-family:'IBM Plex Mono',monospace;font-size:.72rem;color:var(--ink-faint)}
.chan-btn.active .n{color:var(--amber)}
.cat-ico{color:var(--amber);width:18px;text-align:center;font-size:.92em;margin-right:2px}
.chan-btn.active .cat-ico{color:var(--amber)}

.pane{display:none}.pane.active{display:block}
.site-footer{border-top:1px solid var(--line);margin-top:40px;padding:30px 0;text-align:center}
.footer-brand{font-weight:700;font-size:1.05rem;margin-bottom:12px}
.footer-brand b{color:var(--amber)}
.footer-links{display:flex;gap:22px;justify-content:center;margin-bottom:14px;flex-wrap:wrap}
.footer-links a{color:var(--ink-dim);text-decoration:none;font-size:.88rem}
.footer-links a:hover{color:var(--amber)}
.footer-note{font-size:.7rem;color:var(--ink-faint)}
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
<?php seo_render_jsonld(); ?>
</head>
<body>
<script>try{var _t=localStorage.getItem('tnz_theme');if(_t==='light')document.body.setAttribute('data-theme','light');}catch(e){}</script>

<header class="topbar">
    <div class="wrap">
        <a class="brand" href="index.php">
            <svg class="brand-ico" viewBox="0 0 60 60" width="30" height="30" aria-hidden="true">
                <g transform="translate(30,30)">
                    <circle cx="0" cy="0" r="21" fill="none" stroke="#e8b04b" stroke-width="2.4"/>
                    <ellipse cx="0" cy="0" rx="21" ry="8.2" fill="none" stroke="#e8b04b" stroke-width="1.6" opacity="0.5"/>
                    <line x1="-21" y1="0" x2="21" y2="0" stroke="#e8b04b" stroke-width="1.6" opacity="0.5"/>
                    <ellipse cx="0" cy="0" rx="32" ry="13" fill="none" stroke="#56b6c2" stroke-width="1.9" opacity="0.75" transform="rotate(-25)"/>
                    <circle cx="29" cy="-14" r="4" fill="#56b6c2"/>
                </g>
            </svg>
            <span>Tech<b>News</b>Zone</span><span class="dot"></span>
        </a>
        <div style="display:flex;align-items:center">
            <span class="topbar-clock mono" id="clock">--:--:--</span>
            <button class="btn-refresh" id="themeBtn" title="Cambia tema" onclick="toggleTheme()"><i class="fas fa-moon"></i></button>
            <button class="btn-refresh" onclick="location.reload()" title="Aggiorna"><i class="fas fa-rotate-right"></i></button>
        </div>
    </div>
</header>

<div class="wrap">

    <?php if (!empty($ticker_news)): ?>
    <a class="newsflash" id="newsflash" href="#" target="_blank" rel="noopener">
        <span class="nf-tag"><span class="nf-dot"></span>ATTUALITÀ</span>
        <span class="nf-body">
            <span class="nf-title" id="nf-title"><?php echo htmlspecialchars($ticker_news[0]['title']); ?></span>
            <span class="nf-source" id="nf-source"><?php echo htmlspecialchars($ticker_news[0]['source']); ?></span>
        </span>
        <span class="nf-nav"><span class="nf-count" id="nf-count">1/<?php echo count($ticker_news); ?></span></span>
    </a>
    <script id="nf-data" type="application/json"><?php
        echo json_encode(array_map(fn($a) => [
            't' => $a['title'], 's' => $a['source'], 'l' => $a['link']
        ], $ticker_news), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    ?></script>
    <?php endif; ?>

    <div class="ticker">
        <div class="ticker-tag"><span class="live"></span>MARKET LIVE</div>
        <div class="ticker-view"><div class="ticker-row" id="ticker">Sincronizzazione mercati…</div></div>
    </div>

    <div class="layout">
        <main>
            <div class="movers" id="movers">
                <div class="movers-head">
                    <span class="movers-title mono">TOP MOVERS · 24H</span>
                    <span class="movers-sub mono" id="movers-status">caricamento mercati…</span>
                </div>
                <div class="movers-grid" id="movers-grid">
                    <div class="mover-skeleton"></div>
                    <div class="mover-skeleton"></div>
                    <div class="mover-skeleton"></div>
                    <div class="mover-skeleton"></div>
                </div>
            </div>

            <div class="searchbar">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" id="search" placeholder="Cerca tra tutte le notizie…" autocomplete="off">
                <button class="search-clear" id="searchClear" onclick="clearSearch()"><i class="fas fa-xmark"></i></button>
            </div>
            <p class="search-info" id="searchInfo"></p>

            <section class="pane" id="searchResults" style="display:none">
                <div class="news-grid" id="searchGrid"></div>
                <div class="no-results" id="noResults">Nessuna notizia trovata.</div>
            </section>

            <?php if (!empty($partners)): ?>
            <section class="pane" id="cpartner">
                <div class="sec-head">
                    <h2><i class="fas fa-handshake cat-ico" aria-hidden="true"></i> Offerte Partner</h2>
                    <span class="bar"></span>
                    <span class="cnt mono"><?php echo count($partners); ?> offerte</span>
                </div>
                <span class="sponsor-note">Contenuti sponsorizzati · link referral</span>
                <div class="partner-grid">
                    <?php foreach ($partners as $p):
                        $pc = htmlspecialchars($p['color'] ?: '#e8b04b');
                        $initial = mb_strtoupper(mb_substr($p['name'], 0, 1));
                    ?>
                    <a class="partner" href="<?php echo htmlspecialchars($p['url']); ?>" target="_blank" rel="noopener sponsored nofollow" style="--pc:<?php echo $pc; ?>">
                        <div class="partner-top">
                            <?php if (!empty($p['logo'])): ?>
                                <img class="partner-logo" src="<?php echo htmlspecialchars($p['logo']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <?php else: ?>
                                <span class="partner-logo"><?php echo htmlspecialchars($initial); ?></span>
                            <?php endif; ?>
                            <div>
                                <div class="partner-name"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="partner-bonus"><?php echo htmlspecialchars($p['bonus']); ?></div>
                            </div>
                        </div>
                        <p class="partner-tag"><?php echo htmlspecialchars($p['tagline']); ?></p>
                        <span class="partner-cta"><?php echo htmlspecialchars($p['cta'] ?: 'Scopri'); ?> <i class="fas fa-arrow-right"></i></span>
                        <span class="partner-disclaimer">#adv · link referral</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php
            $is_first = true;
            foreach ($grouped_news_data as $cat => $articles):
                $slug = 'c'.md5($cat);
                // attiva la categoria richiesta dall'URL, altrimenti la prima
                $is_active = $SEO_active_cat ? ($cat === $SEO_active_cat) : $is_first;
            ?>
            <section class="pane <?php echo $is_active ? 'active' : ''; ?>" id="<?php echo $slug; ?>">
                <div class="sec-head">
                    <h2><?php echo category_icon_html($cat); ?> <?php echo htmlspecialchars($cat); ?></h2>
                    <span class="bar"></span>
                    <span class="cnt mono"><?php echo count($articles); ?> articoli</span>
                </div>
                <div class="news-grid">
                    <?php foreach ($articles as $i => $art):
                        $is_new = (time() - $art['timestamp']) < 7200;
                        $hide = $i >= $INITIAL_LOAD_COUNT ? 'style="display:none"' : '';
                    ?>
                    <article class="card item" <?php echo $hide; ?>>
                        <?php if (!empty($art['image_url'])): ?>
                            <img class="card-img" src="<?php echo htmlspecialchars($art['image_url']); ?>" loading="lazy" decoding="async" width="400" height="160" alt="<?php echo htmlspecialchars(mb_substr($art['title'],0,60)); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <a class="card-title" href="<?php echo htmlspecialchars($art['link']); ?>" target="_blank" rel="noopener">
                                <?php if ($is_new): ?><span class="tag-new">NEW</span><?php endif; ?>
                                <?php echo htmlspecialchars($art['title']); ?>
                            </a>
                            <?php if (!empty($art['summary'])): ?>
                            <p class="card-sum"><?php echo htmlspecialchars(mb_substr($art['summary'], 0, 120)); ?>…</p>
                            <?php endif; ?>
                            <div class="card-foot">
                                <span class="src"><?php echo htmlspecialchars($art['source']); ?></span>
                                <span class="date"><?php echo date('d/m · H:i', $art['timestamp']); ?></span>
                            </div>
                            <div class="share-row">
                                <button class="share-btn" title="Condividi" onclick="shareArticle(this)"
                                    data-url="<?php echo htmlspecialchars($art['link']); ?>"
                                    data-title="<?php echo htmlspecialchars($art['title']); ?>">
                                    <i class="fas fa-share-nodes"></i>
                                </button>
                                <a class="share-btn s-wa" title="WhatsApp" target="_blank" rel="noopener"
                                   href="https://wa.me/?text=<?php echo rawurlencode($art['title'].' '.$art['link']); ?>"><i class="fab fa-whatsapp"></i></a>
                                <a class="share-btn s-tg" title="Telegram" target="_blank" rel="noopener"
                                   href="https://t.me/share/url?url=<?php echo rawurlencode($art['link']); ?>&text=<?php echo rawurlencode($art['title']); ?>"><i class="fab fa-telegram"></i></a>
                                <a class="share-btn s-x" title="X" target="_blank" rel="noopener"
                                   href="https://twitter.com/intent/tweet?text=<?php echo rawurlencode($art['title']); ?>&url=<?php echo rawurlencode($art['link']); ?>"><span class="x-icon">𝕏</span></a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php if (count($articles) > $INITIAL_LOAD_COUNT): ?>
                <button class="load-more" onclick="loadMore('<?php echo $slug; ?>',this)">CARICA ALTRI ARTICOLI</button>
                <?php endif; ?>
            </section>
            <?php $is_first = false; endforeach; ?>
        </main>

        <aside class="side">
            <div class="gauge-panel">
                <div class="lbl">FEAR &amp; GREED INDEX</div>
                <div class="gauge">
                    <div class="gauge-arc"></div>
                    <div class="gauge-needle" id="needle"></div>
                </div>
                <div id="fng-val">--</div>
                <div id="fng-class">in attesa</div>
            </div>

            <nav class="channels">
                <div class="head">CANALI</div>
                <?php if (!empty($partners)): ?>
                <a class="chan-btn chan-partner" href="#" data-target="cpartner" onclick="showPane(this); return false;">
                    <span><i class="fas fa-handshake cat-ico" aria-hidden="true"></i> Offerte Partner</span>
                    <span class="n"><?php echo count($partners); ?></span>
                </a>
                <?php endif; ?>
                <?php
                $is_first = true;
                foreach ($grouped_news_data as $cat => $arts):
                    $slug = 'c'.md5($cat);
                    $cat_url = '?cat=' . rawurlencode(seo_slugify($cat));
                    $is_active = $SEO_active_cat ? ($cat === $SEO_active_cat) : $is_first;
                ?>
                <a class="chan-btn <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo $cat_url; ?>" data-target="<?php echo $slug; ?>" onclick="showPane(this); return false;">
                    <span><?php echo category_icon_html($cat); ?> <?php echo htmlspecialchars($cat); ?></span>
                    <span class="n"><?php echo count($arts); ?></span>
                </a>
                <?php $is_first = false; endforeach; ?>
            </nav>
        </aside>
    </div>

    <footer class="site-footer">
        <div class="footer-brand">Tech<b>News</b>Zone</div>
        <nav class="footer-links">
            <a href="chi-siamo.php">Chi siamo</a>
            <a href="privacy.php">Privacy</a>
            <a href="feed.php">Feed RSS</a>
        </nav>
        <div class="footer-note mono">Aggregatore di notizie · i diritti dei contenuti appartengono alle fonti originali</div>
    </footer>
</div>

<script>
// clock
function tick(){const d=new Date();document.getElementById('clock').textContent=d.toLocaleTimeString('it-IT');}
setInterval(tick,1000);tick();

// tema chiaro/scuro
function applyTheme(t){
    document.body.setAttribute('data-theme', t);
    const i=document.querySelector('#themeBtn i');
    if(i) i.className = t==='light' ? 'fas fa-sun' : 'fas fa-moon';
}
function toggleTheme(){
    const cur=document.body.getAttribute('data-theme')==='light'?'light':'dark';
    const next=cur==='light'?'dark':'light';
    try{localStorage.setItem('tnz_theme',next);}catch(e){}
    applyTheme(next);
}
(function(){
    let saved='dark';
    try{saved=localStorage.getItem('tnz_theme')||'dark';}catch(e){}
    applyTheme(saved);
})();

// condivisione nativa (mobile) con fallback copia link
function shareArticle(btn){
    const url=btn.dataset.url, title=btn.dataset.title;
    if(navigator.share){
        navigator.share({title:title, url:url}).catch(()=>{});
    }else{
        navigator.clipboard.writeText(url).then(()=>{
            const old=btn.innerHTML;
            btn.innerHTML='<i class="fas fa-check"></i>';
            setTimeout(()=>btn.innerHTML=old,1500);
        }).catch(()=>{});
    }
}

// newsflash attualità (rotazione in dissolvenza)
(function(){
    const data=document.getElementById('nf-data');
    if(!data)return;
    let items=[];try{items=JSON.parse(data.textContent);}catch(e){return;}
    if(!items.length)return;
    const box=document.getElementById('newsflash'),tEl=document.getElementById('nf-title'),
          sEl=document.getElementById('nf-source'),cEl=document.getElementById('nf-count');
    let i=0;
    function show(n){
        box.href=items[n].l||'#';
        sEl.textContent=items[n].s||'';
        cEl.textContent=(n+1)+'/'+items.length;
    }
    show(0);
    if(items.length<2)return;
    setInterval(()=>{
        tEl.classList.add('fade');
        setTimeout(()=>{
            i=(i+1)%items.length;
            tEl.textContent=items[i].t;
            show(i);
            tEl.classList.remove('fade');
        },400);
    },6000);
})();

// tabs
function showPane(btn){
    clearSearch();
    document.querySelectorAll('.pane:not(#searchResults)').forEach(p=>{p.classList.remove('active');p.style.display='';});
    document.querySelectorAll('.chan-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById(btn.dataset.target).classList.add('active');
    btn.classList.add('active');
    // aggiorna l'URL senza ricaricare (per condivisione e SEO lato utente)
    if(btn.getAttribute('href')){
        try{history.pushState(null,'',btn.getAttribute('href'));}catch(e){}
    }
    window.scrollTo({top:0,behavior:'smooth'});
}

// load more
function loadMore(slug,btn){
    const hidden=[...document.getElementById(slug).querySelectorAll('.item')].filter(c=>c.style.display==='none');
    hidden.slice(0,8).forEach(c=>c.style.display='flex');
    if(hidden.length<=8)btn.style.display='none';
}

// search (client-side su tutte le card già caricate)
const searchInput=document.getElementById('search');
const searchClear=document.getElementById('searchClear');
const searchInfo=document.getElementById('searchInfo');
const searchResults=document.getElementById('searchResults');
const searchGrid=document.getElementById('searchGrid');
const noResults=document.getElementById('noResults');

function getAllCards(){
    const seen=new Set();const cards=[];
    document.querySelectorAll('.pane:not(#searchResults) .card').forEach(c=>{
        const title=c.querySelector('.card-title')?.textContent.trim()||'';
        const key=title.toLowerCase();
        if(seen.has(key))return; seen.add(key);
        cards.push(c);
    });
    return cards;
}

function runSearch(q){
    q=q.trim().toLowerCase();
    if(!q){clearSearch();return;}
    searchClear.style.display='block';
    document.querySelectorAll('.pane:not(#searchResults)').forEach(p=>p.style.display='none');

    const matches=getAllCards().filter(c=>c.textContent.toLowerCase().includes(q));
    searchGrid.innerHTML='';
    matches.forEach(c=>{const cl=c.cloneNode(true);cl.style.display='flex';searchGrid.appendChild(cl);});

    searchResults.style.display='block';
    noResults.style.display=matches.length?'none':'block';
    searchInfo.style.display='block';
    searchInfo.innerHTML=`<b>${matches.length}</b> risultati per “${q}”`;
}

function clearSearch(){
    searchInput.value='';
    searchClear.style.display='none';
    searchInfo.style.display='none';
    searchResults.style.display='none';
    searchGrid.innerHTML='';
    document.querySelectorAll('.pane:not(#searchResults)').forEach(p=>p.style.display='');
}

let searchTimer;
searchInput.addEventListener('input',e=>{
    clearTimeout(searchTimer);
    searchTimer=setTimeout(()=>runSearch(e.target.value),180);
});

// market data
async function updateMarkets(){
    try{
        const r=await fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,solana,ripple,cardano,dogecoin,polkadot,binancecoin&order=market_cap_desc&sparkline=false');
        const data=await r.json();
        const row=data.map(c=>{
            const up=c.price_change_percentage_24h>=0;
            return `<div class="t-item"><img src="${c.image}" alt=""><span class="sym">${c.symbol.toUpperCase()}</span>
            <span class="px">$${c.current_price.toLocaleString()}</span>
            <span class="${up?'t-up':'t-down'}">${up?'▲':'▼'} ${Math.abs(c.price_change_percentage_24h).toFixed(1)}%</span></div>`;
        }).join('');
        document.getElementById('ticker').innerHTML=row+row;
    }catch(e){}
    try{
        const f=await(await fetch('https://api.alternative.me/fng/')).json();
        const v=parseInt(f.data[0].value);
        document.getElementById('needle').style.transform=`rotate(${v*1.8-90}deg)`;
        document.getElementById('fng-val').textContent=v;
        const cl=document.getElementById('fng-class');
        cl.textContent=f.data[0].value_classification;
        const col=v>55?'var(--up)':(v<45?'var(--down)':'var(--amber)');
        document.getElementById('fng-val').style.color=col;
    }catch(e){}
}
updateMarkets();setInterval(updateMarkets,60000);

async function updateMovers(){
    const grid=document.getElementById('movers-grid'),status=document.getElementById('movers-status');
    if(!grid)return;
    try{
        const r=await fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=50&page=1&sparkline=false&price_change_percentage=24h');
        const data=await r.json();
        // ordina per variazione assoluta e prendi i 4 più estremi (mix gainer/loser)
        const sorted=data.filter(c=>typeof c.price_change_percentage_24h==='number')
            .sort((a,b)=>Math.abs(b.price_change_percentage_24h)-Math.abs(a.price_change_percentage_24h))
            .slice(0,4);
        grid.innerHTML=sorted.map(c=>{
            const up=c.price_change_percentage_24h>=0;
            const price=c.current_price>=1?c.current_price.toLocaleString('en-US',{maximumFractionDigits:2}):c.current_price.toPrecision(3);
            return `<a class="mover" href="https://www.coingecko.com/en/coins/${c.id}" target="_blank" rel="noopener">
                <img class="mover-ico" src="${c.image}" alt="">
                <div class="mover-info"><div class="mover-sym">${c.symbol}</div><div class="mover-price">$${price}</div></div>
                <span class="mover-chg ${up?'up':'down'}">${up?'+':''}${c.price_change_percentage_24h.toFixed(1)}%</span>
            </a>`;
        }).join('');
        if(status)status.textContent='aggiornato '+new Date().toLocaleTimeString('it-IT',{hour:'2-digit',minute:'2-digit'});
    }catch(e){
        if(status)status.textContent='dati non disponibili';
    }
}
updateMovers();setInterval(updateMovers,120000);

// service worker
if('serviceWorker' in navigator){navigator.serviceWorker.register('service-worker.js').catch(()=>{});}
</script>
<script src="cookie-consent.js"></script>
</body>
</html>
