<?php
// =================================================================
// FILE: page_head.php — Layout condiviso per le pagine statiche
// (Chi siamo, Privacy). Header + footer coerenti col tema.
// =================================================================

function page_head($title, $desc) {
    $base = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'iltuosito.it');
    $t = htmlspecialchars($title . ' | TechNewsZone', ENT_QUOTES);
    $d = htmlspecialchars($desc, ENT_QUOTES);
    $canonical = $base . '/' . basename($_SERVER['SCRIPT_NAME'] ?? '');
    echo <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>$t</title>
<meta name="description" content="$d">
<link rel="canonical" href="$canonical">
<meta property="og:title" content="$t">
<meta property="og:description" content="$d">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0c0f;--panel:#0f1318;--line:#1c2128;--ink:#e6edf3;--ink-dim:#8b949e;--ink-faint:#5a6473;--amber:#e8b04b}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font-family:'Space Grotesk',system-ui,sans-serif;line-height:1.65}
.topbar{border-bottom:1px solid var(--line);padding:16px 22px}
.topbar a{color:var(--ink);text-decoration:none;font-weight:700;font-size:1.1rem}
.topbar a b{color:var(--amber)}
.wrap{max-width:760px;margin:0 auto;padding:40px 22px 80px}
h1{font-size:2rem;letter-spacing:-.02em;margin:0 0 24px}
h2{font-size:1.25rem;margin:32px 0 12px;color:var(--ink)}
p{color:var(--ink-dim);margin:0 0 16px}
a{color:var(--amber)}
strong{color:var(--ink)}
.back{display:inline-block;margin-top:32px;font-family:'IBM Plex Mono',monospace;font-size:.82rem;
    color:var(--ink-dim);text-decoration:none;border:1px solid var(--line);padding:9px 18px;border-radius:8px}
.back:hover{border-color:var(--amber);color:var(--amber)}
footer{border-top:1px solid var(--line);padding:24px 22px;text-align:center;color:var(--ink-faint);font-size:.8rem}
footer a{color:var(--ink-faint)}
</style>
</head>
<body>
<div class="topbar"><a href="index.php">Tech<b>News</b>Zone</a></div>
<main class="wrap">
HTML;
}

function page_foot() {
    $year = date('Y');
    echo <<<HTML
<a class="back" href="index.php">← Torna alla home</a>
</main>
<footer>
    © $year TechNewsZone ·
    <a href="chi-siamo.php">Chi siamo</a> ·
    <a href="privacy.php">Privacy</a> ·
    <a href="feed.php">RSS</a>
</footer>
<script src="cookie-consent.js"></script>
</body>
</html>
HTML;
}
