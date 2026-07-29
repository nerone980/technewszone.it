<?php
// FILE: view_mobile.php — Restyling "Terminal" mobile, allineato al desktop
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

// --- Estrai categorie speciali (prefisso "_") per lo slider attualità ---
$ticker_news = [];
foreach ($grouped_news_data as $cat => $articles) {
    if (strpos($cat, '_') === 0) {
        foreach ($articles as $art) $ticker_news[] = $art;
        unset($grouped_news_data[$cat]);
    }
}
usort($ticker_news, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
$ticker_news = array_slice($ticker_news, 0, 12);

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
require_once __DIR__ . '/seo.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="dns-prefetch" href="https://api.coingecko.com">
<?php seo_render_head(); ?>
<link rel="alternate" type="application/rss+xml" title="TechNewsZone — Feed RSS" href="feed.php">
<link rel="icon" type="image/x-icon" href="/img/favicon.ico">
<link rel="apple-touch-icon" href="/img/apple-touch-icon.png">
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#0a0c0f">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
    --bg:#0a0c0f; --panel:#0f1318; --panel-2:#12171d; --line:#1c2128;
    --ink:#e6edf3; --ink-dim:#8b949e; --ink-faint:#5a6473;
    --amber:#e8b04b; --amber-soft:#3a2f17; --cyan:#56b6c2;
    --up:#3fb950; --down:#f85149; --r:12px;
}
body[data-theme="light"]{
    --bg:#f4f2ec; --panel:#ffffff; --panel-2:#faf8f3; --line:#e2ddd2;
    --ink:#1a1a17; --ink-dim:#5f5e58; --ink-faint:#8a877d;
    --amber:#b07a12; --amber-soft:#f5e6c8; --cyan:#0f7d8c;
    --up:#1a8f3c; --down:#c73434;
}
body[data-theme="light"] .hd{background:rgba(244,242,236,.92)}
body[data-theme="light"] .mcard img{filter:none}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);
    font-family:'Space Grotesk',system-ui,sans-serif;
    padding-top:96px;padding-bottom:74px;-webkit-font-smoothing:antialiased;overflow-x:hidden}
::selection{background:var(--amber);color:#000}
.mono{font-family:'IBM Plex Mono',ui-monospace,monospace}

/* HEADER */
.hd{position:fixed;top:0;left:0;right:0;z-index:2000;background:rgba(10,12,15,.92);backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
.hd-top{display:flex;justify-content:space-between;align-items:center;padding:12px 16px}
.brand{font-weight:700;font-size:1.1rem;letter-spacing:-.02em;display:flex;align-items:center;gap:8px}
.brand b{color:var(--amber)}
.brand .dot{display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--up);margin-left:6px;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.hd-btn{background:transparent;border:1px solid var(--line);color:var(--ink-dim);width:34px;height:34px;border-radius:9px;font-size:.95rem}
.push-btn.push-on{border-color:var(--amber);color:var(--amber);background:var(--amber-soft)}


/* TICKER */
.tk{height:38px;display:flex;align-items:center;overflow:hidden;border-top:1px solid var(--line);background:var(--panel)}
.tk-row{display:flex;white-space:nowrap;animation:slide 40s linear infinite}
@keyframes slide{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.tk-item{display:flex;align-items:center;gap:6px;padding:0 16px;font-size:.78rem;border-right:1px solid var(--line)}
.tk-item img{width:16px;height:16px;border-radius:50%}
.tk-item .sym{font-weight:600}
.t-up{color:var(--up)}.t-down{color:var(--down)}

/* CATEGORY CHIPS */
.chips{position:fixed;top:96px;left:0;right:0;z-index:1900;background:var(--bg);border-bottom:1px solid var(--line);
    overflow-x:auto;white-space:nowrap;padding:10px 12px;-webkit-overflow-scrolling:touch}
.chips::-webkit-scrollbar{display:none}
.chip{display:inline-block;background:var(--panel);border:1px solid var(--line);color:var(--ink-dim);
    font-size:.78rem;font-weight:500;font-family:inherit;padding:7px 14px;border-radius:20px;margin-right:7px}
.chip.active{background:var(--amber);border-color:var(--amber);color:#000;font-weight:600}
.cat-ico{color:var(--amber);font-size:.9em;margin-right:3px}
.chip.active .cat-ico{color:#000}
.chip-partner{border-style:dashed}
body{padding-top:144px}

/* GAUGE STRIP */
.fng{display:flex;align-items:center;gap:18px;background:var(--panel);border:1px solid var(--line);border-radius:var(--r);margin:14px 14px 0;padding:14px 18px}
.gauge{position:relative;width:96px;height:50px;overflow:hidden;flex-shrink:0}
.gauge-arc{position:absolute;width:96px;height:96px;border-radius:50%;
    background:conic-gradient(from -90deg,var(--down) 0%,var(--amber) 50%,var(--up) 100%);
    -webkit-mask:radial-gradient(circle,transparent 60%,#000 61%);mask:radial-gradient(circle,transparent 60%,#000 61%)}
.gauge-needle{position:absolute;bottom:0;left:50%;width:2.5px;height:40px;background:var(--ink);
    transform-origin:bottom center;transform:rotate(-90deg);transition:transform 1.6s cubic-bezier(.15,.6,.2,1);border-radius:3px}
.fng-meta .lbl{font-family:'IBM Plex Mono',monospace;font-size:.62rem;letter-spacing:.12em;color:var(--ink-faint)}
#m-fng-val{font-family:'IBM Plex Mono',monospace;font-size:1.7rem;font-weight:600;line-height:1.1}
#m-fng-class{font-size:.74rem;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-dim)}

/* CONTAINER + SECTIONS */
.feed{padding:14px}
.cat{display:none}.cat.active{display:block}
.cat-title{font-size:1.15rem;font-weight:700;margin:4px 0 14px;letter-spacing:-.02em}

.mcard{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);overflow:hidden;margin-bottom:14px}
.mcard img{width:100%;height:150px;object-fit:cover;background:#06080a;display:block;filter:saturate(.9)}
.mcard .b{padding:14px}
.mtitle{color:var(--ink);font-weight:600;font-size:1rem;line-height:1.34;text-decoration:none;display:block}
.msum{color:var(--ink-dim);font-size:.8rem;line-height:1.5;margin:9px 0 0}
.mfoot{display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:11px;border-top:1px solid var(--line)}
.mshare{margin-left:auto;width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:transparent;
    color:var(--ink-dim);display:inline-flex;align-items:center;justify-content:center;font-size:.9rem}
.mshare:active{border-color:var(--amber);color:var(--amber)}
.mfoot .date{margin-left:12px}
.src{font-family:'IBM Plex Mono',monospace;font-size:.68rem;color:var(--cyan)}
.date{font-family:'IBM Plex Mono',monospace;font-size:.68rem;color:var(--ink-faint)}
.tag-new{display:inline-block;font-family:'IBM Plex Mono',monospace;font-size:.56rem;font-weight:600;letter-spacing:.1em;
    color:var(--bg);background:var(--amber);padding:2px 6px;border-radius:4px;margin-right:7px;vertical-align:middle}

/* BOTTOM NAV */
.bnav{position:fixed;bottom:0;left:0;right:0;height:62px;z-index:2000;background:rgba(10,12,15,.95);
    backdrop-filter:blur(10px);border-top:1px solid var(--line);display:flex}
.bnav button{flex:1;background:transparent;border:none;color:var(--ink-dim);font-family:inherit;
    font-size:.7rem;font-weight:600;letter-spacing:.04em;display:flex;flex-direction:column;align-items:center;gap:5px;justify-content:center}
.bnav button i{font-size:1.05rem}
.bnav button.on{color:var(--amber)}

/* DRAWER */
.drawer{position:fixed;inset:0;z-index:3000;background:rgba(0,0,0,.6);display:none}
.drawer.open{display:block}
.drawer-panel{position:absolute;left:0;top:0;bottom:0;width:270px;background:var(--panel);border-right:1px solid var(--line);
    transform:translateX(-100%);transition:transform .25s;overflow-y:auto}
.drawer.open .drawer-panel{transform:translateX(0)}
.drawer-head{display:flex;justify-content:space-between;align-items:center;padding:18px;border-bottom:1px solid var(--line)}
.drawer-head h6{margin:0;font-family:'IBM Plex Mono',monospace;font-size:.72rem;letter-spacing:.14em;color:var(--ink-faint)}
.drawer-head button{background:none;border:none;color:var(--ink-dim);font-size:1.2rem}
.drawer-item{display:block;width:100%;text-align:left;background:none;border:none;border-bottom:1px solid var(--line);
    color:var(--ink-dim);font-family:inherit;font-size:.92rem;font-weight:500;padding:15px 18px}
.drawer-item.active{color:var(--amber);background:var(--amber-soft)}
/* PARTNER MOBILE */
.sponsor-note-m{font-family:'IBM Plex Mono',monospace;font-size:.66rem;color:var(--ink-faint);
    margin:0 0 14px;padding:6px 10px;border:1px dashed var(--line);border-radius:8px;display:inline-block}
.partner-m{display:block;background:var(--panel);border:1px solid var(--line);border-radius:var(--r);
    padding:16px;text-decoration:none;margin-bottom:14px;position:relative;overflow:hidden}
.partner-m::before{content:'';position:absolute;top:0;left:0;width:100%;height:3px;background:var(--pc,var(--amber))}
.partner-m-top{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.partner-m-logo{width:42px;height:42px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:1.2rem;color:#0a0c0f;background:var(--pc,var(--amber));object-fit:cover}
.partner-m-name{font-size:1.05rem;font-weight:700;color:var(--ink)}
.partner-m-bonus{font-family:'IBM Plex Mono',monospace;font-size:.68rem;font-weight:600;color:var(--pc,var(--amber))}
.partner-m-tag{color:var(--ink-dim);font-size:.84rem;line-height:1.45;margin:0 0 14px}
.partner-m-cta{display:inline-flex;align-items:center;gap:7px;font-weight:600;font-size:.82rem;
    color:#0a0c0f;background:var(--pc,var(--amber));padding:8px 16px;border-radius:8px}
.partner-m-disc{font-family:'IBM Plex Mono',monospace;font-size:.6rem;color:var(--ink-faint);margin-top:8px;display:block}
.movers-m-head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:12px}
.movers-m-title{font-size:.64rem;letter-spacing:.12em;color:var(--amber)}
.movers-m-sub{font-size:.6rem;color:var(--ink-faint)}
.movers-m-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mover{display:flex;align-items:center;gap:10px;background:var(--bg);border:1px solid var(--line);
    border-radius:10px;padding:10px;text-decoration:none}
.mover-ico{width:30px;height:30px;border-radius:50%;flex-shrink:0;background:#06080a}
.mover-info{min-width:0;flex:1}
.mover-sym{font-weight:700;font-size:.82rem;color:var(--ink);text-transform:uppercase}
.mover-price{font-family:'IBM Plex Mono',monospace;font-size:.68rem;color:var(--ink-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mover-chg{flex-shrink:0;font-family:'IBM Plex Mono',monospace;font-size:.72rem;font-weight:600;padding:2px 6px;border-radius:5px}
.mover-chg.up{color:var(--up);background:rgba(63,185,80,.12)}
.mover-chg.down{color:var(--down);background:rgba(248,81,73,.12)}
.mover-skeleton{height:50px;border-radius:10px;background:linear-gradient(90deg,var(--bg) 25%,var(--panel-2) 50%,var(--bg) 75%);
    background-size:200% 100%;animation:shimmer 1.4s infinite}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* SEARCH MOBILE */
.m-search{position:relative;margin:14px 14px 0}
.m-search i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--ink-faint);font-size:.85rem}
.m-search input{width:100%;background:var(--panel);border:1px solid var(--line);border-radius:var(--r);
    color:var(--ink);font-family:'IBM Plex Mono',monospace;font-size:.88rem;padding:12px 40px 12px 38px;outline:none}
.m-search input:focus{border-color:var(--amber)}
.m-search input::placeholder{color:var(--ink-faint)}
.m-search-clear{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;
    color:var(--ink-faint);font-size:1rem;display:none}
.m-search-info{font-family:'IBM Plex Mono',monospace;font-size:.74rem;color:var(--ink-dim);margin:8px 14px 0;display:none}
.m-search-info b{color:var(--amber)}
.m-noresults{text-align:center;color:var(--ink-faint);padding:30px 0;display:none}
.m-footer{border-top:1px solid var(--line);margin-top:20px;padding:24px 14px 10px;text-align:center}
.nl-box-m{background:var(--panel);border:1px solid var(--line);border-radius:var(--r);padding:18px;margin-bottom:20px;text-align:left}
.nl-title-m{font-weight:700;font-size:.95rem;color:var(--ink);margin-bottom:6px}
.nl-title-m i{color:var(--amber);margin-right:6px}
.nl-desc-m{font-size:.82rem;color:var(--ink-dim);margin:0 0 12px}
.nl-box-m input[type=email]{width:100%;box-sizing:border-box;background:var(--bg);border:1px solid var(--line);
    border-radius:8px;color:var(--ink);font-family:'IBM Plex Mono',monospace;font-size:.85rem;padding:11px 12px;
    outline:none;margin-bottom:8px}
.nl-box-m input[type=email]:focus{border-color:var(--amber)}
.nl-box-m button{width:100%;background:var(--amber);border:none;border-radius:8px;color:#0a0c0f;font-weight:700;
    font-size:.88rem;padding:11px;font-family:inherit}
.nl-hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}
.nl-msg{font-size:.82rem;margin-top:10px}
.nl-msg.ok{color:var(--up)}.nl-msg.err{color:var(--down)}
.m-footer-links{display:flex;gap:18px;justify-content:center;margin-bottom:10px}
.m-footer-links a{color:var(--ink-dim);text-decoration:none;font-size:.82rem}
.m-footer-note{font-size:.64rem;color:var(--ink-faint)}

/* NEWSFLASH MOBILE */
.nf-m{display:flex;align-items:center;gap:0;height:34px;border-top:1px solid var(--line);
    background:var(--panel-2);border-left:3px solid var(--amber);text-decoration:none;overflow:hidden}
.nf-m-tag{flex-shrink:0;display:flex;align-items:center;gap:5px;padding:0 11px;font-family:'IBM Plex Mono',monospace;
    font-size:.58rem;font-weight:600;letter-spacing:.1em;color:var(--amber);border-right:1px solid var(--line);height:100%}
.nf-m-dot{width:5px;height:5px;border-radius:50%;background:var(--down);animation:pulse 1.4s infinite}
.nf-m-title{flex:1;min-width:0;padding:0 12px;color:var(--ink);font-size:.8rem;white-space:nowrap;
    overflow:hidden;text-overflow:ellipsis;transition:opacity .4s}
.nf-m-title.fade{opacity:0}

/* offset header (header-top + newsflash + ticker) */
.chips{top:130px !important}
body{padding-top:178px}

@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
<?php seo_render_jsonld(); ?>
</head>
<body>
<script>try{var _t=localStorage.getItem('tnz_theme');if(_t==='light')document.body.setAttribute('data-theme','light');}catch(e){}</script>

<header class="hd">
    <div class="hd-top">
        <span class="brand">
            <svg viewBox="0 0 60 60" width="24" height="24" aria-hidden="true" style="flex-shrink:0">
                <g transform="translate(30,30)">
                    <circle cx="0" cy="0" r="21" fill="none" stroke="#e8b04b" stroke-width="2.6"/>
                    <ellipse cx="0" cy="0" rx="21" ry="8.2" fill="none" stroke="#e8b04b" stroke-width="1.8" opacity="0.5"/>
                    <line x1="-21" y1="0" x2="21" y2="0" stroke="#e8b04b" stroke-width="1.8" opacity="0.5"/>
                    <ellipse cx="0" cy="0" rx="32" ry="13" fill="none" stroke="#56b6c2" stroke-width="2" opacity="0.75" transform="rotate(-25)"/>
                    <circle cx="29" cy="-14" r="4" fill="#56b6c2"/>
                </g>
            </svg>
            <span>Tech<b>News</b>Zone</span><span class="dot"></span>
        </span>
        <div style="display:flex;gap:8px">
            <button class="hd-btn" id="themeBtn" title="Cambia tema" onclick="toggleTheme()"><i class="fas fa-moon"></i></button>
            <button class="hd-btn" onclick="forceUpdate()"><i class="fas fa-rotate-right"></i></button>
        </div>
    </div>
    <?php if (!empty($ticker_news)): ?>
    <a class="nf-m" id="newsflash" href="<?php echo htmlspecialchars($ticker_news[0]['link']); ?>" target="_blank" rel="noopener">
        <span class="nf-m-tag"><span class="nf-m-dot"></span>NEWS</span>
        <span class="nf-m-title" id="nf-title"><?php echo htmlspecialchars($ticker_news[0]['title']); ?></span>
    </a>
    <script id="nf-data" type="application/json"><?php
        echo json_encode(array_map(fn($a) => ['t'=>$a['title'],'l'=>$a['link']], $ticker_news),
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
    ?></script>
    <?php endif; ?>
    <div class="tk"><div class="tk-row" id="ticker">Sincronizzazione…</div></div>
</header>

<div class="chips" id="chips">
    <?php if (!empty($partners)): ?>
    <a class="chip chip-partner" href="#" data-target="mpartner" onclick="showCat(this); return false;"><i class="fas fa-handshake cat-ico" aria-hidden="true"></i> Offerte Partner</a>
    <?php endif; ?>
    <?php
    $is_first = true;
    foreach (array_keys($grouped_news_data) as $cat):
        $slug = 'm'.md5($cat);
        $cat_url = '?cat=' . rawurlencode(seo_slugify($cat));
        $is_active = $SEO_active_cat ? ($cat === $SEO_active_cat) : $is_first;
    ?>
    <a class="chip <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo $cat_url; ?>" data-target="<?php echo $slug; ?>" onclick="showCat(this); return false;"><?php echo category_icon_html($cat); ?> <?php echo htmlspecialchars($cat); ?></a>
    <?php $is_first = false; endforeach; ?>
</div>

<div class="fng">
    <div class="gauge"><div class="gauge-arc"></div><div class="gauge-needle" id="m-needle"></div></div>
    <div class="fng-meta">
        <div class="lbl">FEAR &amp; GREED</div>
        <div id="m-fng-val">--</div>
        <div id="m-fng-class">in attesa</div>
    </div>
</div>

<div class="movers-m" id="movers">
    <div class="movers-m-head">
        <span class="movers-m-title mono">TOP MOVERS · 24H</span>
        <span class="movers-m-sub mono" id="movers-status">caricamento…</span>
    </div>
    <div class="movers-m-grid" id="movers-grid">
        <div class="mover-skeleton"></div>
        <div class="mover-skeleton"></div>
    </div>
</div>

<div class="m-search">
    <i class="fas fa-magnifying-glass"></i>
    <input type="text" id="search" placeholder="Cerca notizie…" autocomplete="off">
    <button class="m-search-clear" id="searchClear" onclick="clearSearch()"><i class="fas fa-xmark"></i></button>
</div>
<p class="m-search-info mono" id="searchInfo"></p>

<div class="feed">
    <section class="cat" id="searchResults" style="display:none">
        <h2 class="cat-title" id="searchTitle">Risultati</h2>
        <div id="searchList"></div>
        <div class="m-noresults mono" id="noResults">Nessuna notizia trovata.</div>
    </section>

    <?php if (!empty($partners)): ?>
    <section class="cat" id="mpartner">
        <h2 class="cat-title"><i class="fas fa-handshake cat-ico" aria-hidden="true"></i> Offerte Partner</h2>
        <span class="sponsor-note-m">Contenuti sponsorizzati · link referral</span>
        <?php foreach ($partners as $p):
            $pc = htmlspecialchars($p['color'] ?: '#e8b04b');
            $initial = mb_strtoupper(mb_substr($p['name'], 0, 1));
        ?>
        <a class="partner-m" href="<?php echo htmlspecialchars($p['url']); ?>" target="_blank" rel="noopener sponsored nofollow" style="--pc:<?php echo $pc; ?>">
            <div class="partner-m-top">
                <?php if (!empty($p['logo'])): ?>
                    <img class="partner-m-logo" src="<?php echo htmlspecialchars($p['logo']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                <?php else: ?>
                    <span class="partner-m-logo"><?php echo htmlspecialchars($initial); ?></span>
                <?php endif; ?>
                <div>
                    <div class="partner-m-name"><?php echo htmlspecialchars($p['name']); ?></div>
                    <div class="partner-m-bonus"><?php echo htmlspecialchars($p['bonus']); ?></div>
                </div>
            </div>
            <p class="partner-m-tag"><?php echo htmlspecialchars($p['tagline']); ?></p>
            <span class="partner-m-cta"><?php echo htmlspecialchars($p['cta'] ?: 'Scopri'); ?> <i class="fas fa-arrow-right"></i></span>
            <span class="partner-m-disc">#adv · link referral</span>
        </a>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php
    $is_first = true;
    foreach ($grouped_news_data as $cat => $arts):
        $slug = 'm'.md5($cat);
        $is_active = $SEO_active_cat ? ($cat === $SEO_active_cat) : $is_first;
    ?>
    <section class="cat <?php echo $is_active ? 'active' : ''; ?>" id="<?php echo $slug; ?>">
        <h2 class="cat-title"><?php echo category_icon_html($cat); ?> <?php echo htmlspecialchars($cat); ?></h2>
        <?php foreach ($arts as $art): $is_new = (time() - $art['timestamp']) < 7200; ?>
        <article class="mcard">
            <?php if (!empty($art['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($art['image_url']); ?>" loading="lazy" decoding="async" width="400" height="150" alt="<?php echo htmlspecialchars(mb_substr($art['title'],0,60)); ?>">
            <?php endif; ?>
            <div class="b">
                <a class="mtitle" href="<?php echo htmlspecialchars($art['link']); ?>" target="_blank" rel="noopener">
                    <?php if ($is_new): ?><span class="tag-new">NEW</span><?php endif; ?>
                    <?php echo htmlspecialchars($art['title']); ?>
                </a>
                <?php if (!empty($art['summary'])): ?>
                <p class="msum"><?php echo htmlspecialchars(mb_substr($art['summary'], 0, 100)); ?>…</p>
                <?php endif; ?>
                <div class="mfoot">
                    <span class="src"><?php echo htmlspecialchars($art['source']); ?></span>
                    <span class="date"><?php echo date('d/m · H:i', $art['timestamp']); ?></span>
                    <button class="mshare" title="Condividi" onclick="shareArticle(this)"
                        data-url="<?php echo htmlspecialchars($art['link']); ?>"
                        data-title="<?php echo htmlspecialchars($art['title']); ?>"><i class="fas fa-share-nodes"></i></button>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>
    <?php $is_first = false; endforeach; ?>

    <footer class="m-footer">
        <div class="nl-box-m">
            <div class="nl-title-m"><i class="fas fa-envelope"></i> Newsletter</div>
            <p class="nl-desc-m">Le notizie migliori nella tua email.</p>
            <div id="nlForm">
                <input type="email" id="nlEmail" placeholder="latua@email.it" autocomplete="email">
                <input type="text" id="nlHp" class="nl-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                <button onclick="subscribeNewsletter()">Iscriviti</button>
            </div>
            <div class="nl-msg" id="nlMsg"></div>
        </div>
        <nav class="m-footer-links">
            <a href="chi-siamo.php">Chi siamo</a>
            <a href="privacy.php">Privacy</a>
            <a href="feed.php">RSS</a>
        </nav>
        <div class="m-footer-note mono">Aggregatore · i diritti dei contenuti appartengono alle fonti</div>
    </footer>
</div>

<nav class="bnav">
    <button class="on" onclick="goHome(this)"><i class="fas fa-bolt"></i>HOME</button>
    <button onclick="openDrawer()"><i class="fas fa-layer-group"></i>CANALI</button>
    <button onclick="forceUpdate()"><i class="fas fa-rotate-right"></i>AGGIORNA</button>
</nav>

<div class="drawer" id="drawer" onclick="if(event.target===this)closeDrawer()">
    <div class="drawer-panel">
        <div class="drawer-head"><h6>CANALI</h6><button onclick="closeDrawer()">&times;</button></div>
        <?php if (!empty($partners)): ?>
        <a class="drawer-item" href="#" data-target="mpartner" onclick="pickCat(this); return false;" style="color:var(--amber)"><i class="fas fa-handshake cat-ico" aria-hidden="true"></i> Offerte Partner</a>
        <?php endif; ?>
        <?php
        $is_first = true;
        foreach ($grouped_news_data as $cat => $arts):
            $slug = 'm'.md5($cat);
            $cat_url = '?cat=' . rawurlencode(seo_slugify($cat));
            $is_active = $SEO_active_cat ? ($cat === $SEO_active_cat) : $is_first;
        ?>
        <a class="drawer-item <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo $cat_url; ?>" data-target="<?php echo $slug; ?>" onclick="pickCat(this); return false;"><?php echo category_icon_html($cat); ?> <?php echo htmlspecialchars($cat); ?></a>
        <?php $is_first = false; endforeach; ?>
    </div>
</div>

<script>
// condivisione nativa mobile
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

// iscrizione newsletter
async function subscribeNewsletter(){
    const email=document.getElementById('nlEmail').value.trim();
    const hp=document.getElementById('nlHp').value;
    const msg=document.getElementById('nlMsg');
    if(!email || email.indexOf('@')<1){
        msg.className='nl-msg err'; msg.textContent='Inserisci un\'email valida.'; return;
    }
    msg.className='nl-msg'; msg.textContent='Invio…';
    try{
        const r=await fetch('newsletter_subscribe.php',{
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({email:email, website:hp})
        });
        const d=await r.json();
        if(d.ok){
            msg.className='nl-msg ok';
            msg.textContent=d.already?'Sei già iscritto. Grazie!':'Iscrizione confermata. Grazie!';
            document.getElementById('nlForm').style.display='none';
        }else{
            msg.className='nl-msg err'; msg.textContent=d.error||'Errore, riprova.';
        }
    }catch(e){
        msg.className='nl-msg err'; msg.textContent='Errore di connessione.';
    }
}

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
</script>
<script>
// newsflash attualità
(function(){
    const data=document.getElementById('nf-data');
    if(!data)return;
    let items=[];try{items=JSON.parse(data.textContent);}catch(e){return;}
    if(items.length<2)return;
    const box=document.getElementById('newsflash'),tEl=document.getElementById('nf-title');
    let i=0;
    setInterval(()=>{
        tEl.classList.add('fade');
        setTimeout(()=>{
            i=(i+1)%items.length;
            tEl.textContent=items[i].t;
            box.href=items[i].l||'#';
            tEl.classList.remove('fade');
        },400);
    },6000);
})();

function showCat(el){
    clearSearch();
    const t=el.dataset.target;
    document.querySelectorAll('.cat:not(#searchResults)').forEach(c=>c.classList.remove('active'));
    document.getElementById(t).classList.add('active');
    document.querySelectorAll('.chip').forEach(c=>c.classList.toggle('active',c.dataset.target===t));
    document.querySelectorAll('.drawer-item').forEach(c=>c.classList.toggle('active',c.dataset.target===t));
    if(el.getAttribute('href')){try{history.pushState(null,'',el.getAttribute('href'));}catch(e){}}
    window.scrollTo({top:0,behavior:'smooth'});
}
function pickCat(el){showCat(el);closeDrawer();}
function goHome(){const first=document.querySelector('.chip');if(first)showCat(first);}
function openDrawer(){document.getElementById('drawer').classList.add('open');}
function closeDrawer(){document.getElementById('drawer').classList.remove('open');}

// search mobile
const searchInput=document.getElementById('search');
const searchClear=document.getElementById('searchClear');
const searchInfo=document.getElementById('searchInfo');
const searchResults=document.getElementById('searchResults');
const searchList=document.getElementById('searchList');
const noResults=document.getElementById('noResults');

function getAllCards(){
    const seen=new Set();const cards=[];
    document.querySelectorAll('.cat:not(#searchResults) .mcard').forEach(c=>{
        const title=c.querySelector('.mtitle')?.textContent.trim()||'';
        const key=title.toLowerCase();
        if(seen.has(key))return;seen.add(key);
        cards.push(c);
    });
    return cards;
}

function runSearch(q){
    q=q.trim().toLowerCase();
    if(!q){clearSearch();return;}
    searchClear.style.display='block';
    document.querySelectorAll('.cat:not(#searchResults)').forEach(c=>c.classList.remove('active'));

    const matches=getAllCards().filter(c=>c.textContent.toLowerCase().includes(q));
    searchList.innerHTML='';
    matches.forEach(c=>searchList.appendChild(c.cloneNode(true)));

    searchResults.style.display='block';
    searchResults.classList.add('active');
    document.getElementById('searchTitle').textContent=`Risultati · ${matches.length}`;
    noResults.style.display=matches.length?'none':'block';
    searchInfo.style.display='block';
    searchInfo.innerHTML=`<b>${matches.length}</b> per “${q}”`;
    window.scrollTo({top:0,behavior:'smooth'});
}

function clearSearch(){
    if(searchInput)searchInput.value='';
    if(searchClear)searchClear.style.display='none';
    if(searchInfo)searchInfo.style.display='none';
    if(searchResults){searchResults.style.display='none';searchResults.classList.remove('active');}
    if(searchList)searchList.innerHTML='';
}

let searchTimer;
if(searchInput)searchInput.addEventListener('input',e=>{
    clearTimeout(searchTimer);
    searchTimer=setTimeout(()=>runSearch(e.target.value),180);
});

function forceUpdate(){
    if('serviceWorker' in navigator){
        navigator.serviceWorker.getRegistrations().then(rs=>rs.forEach(r=>r.unregister()));
    }
    location.reload(true);
}

async function updateMarkets(){
    try{
        const r=await fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&ids=bitcoin,ethereum,solana,ripple,cardano,dogecoin,binancecoin&order=market_cap_desc&sparkline=false');
        const data=await r.json();
        const row=data.map(c=>{
            const up=c.price_change_percentage_24h>=0;
            return `<span class="tk-item"><img src="${c.image}" alt=""><span class="sym">${c.symbol.toUpperCase()}</span>
            $${c.current_price.toLocaleString()} <span class="${up?'t-up':'t-down'}">${up?'▲':'▼'}${Math.abs(c.price_change_percentage_24h).toFixed(1)}%</span></span>`;
        }).join('');
        document.getElementById('ticker').innerHTML=row+row;
    }catch(e){}
    try{
        const f=await(await fetch('https://api.alternative.me/fng/')).json();
        const v=parseInt(f.data[0].value);
        document.getElementById('m-needle').style.transform=`rotate(${v*1.8-90}deg)`;
        document.getElementById('m-fng-val').textContent=v;
        document.getElementById('m-fng-class').textContent=f.data[0].value_classification;
        document.getElementById('m-fng-val').style.color=v>55?'var(--up)':(v<45?'var(--down)':'var(--amber)');
    }catch(e){}
}
updateMarkets();setInterval(updateMarkets,60000);

async function updateMovers(){
    const grid=document.getElementById('movers-grid'),status=document.getElementById('movers-status');
    if(!grid)return;
    try{
        const r=await fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=50&page=1&sparkline=false&price_change_percentage=24h');
        const data=await r.json();
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
        if(status)status.textContent=new Date().toLocaleTimeString('it-IT',{hour:'2-digit',minute:'2-digit'});
    }catch(e){
        if(status)status.textContent='non disponibile';
    }
}
updateMovers();setInterval(updateMovers,120000);

if('serviceWorker' in navigator){navigator.serviceWorker.register('service-worker.js').catch(()=>{});}
</script>
<script src="cookie-consent.js"></script>
</body>
</html>
