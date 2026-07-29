<?php
// FILE: config.php — Configurazione unica del sistema
// Allineato: tutto il sistema usa rss_cache.json (formato JSON), non più .dat

// --- IMPOSTAZIONI DEL SISTEMA ---
define('CACHE_FILE', __DIR__ . '/rss_cache.json'); // un solo file cache per tutto
define('CACHE_LIFETIME', 1800);                    // 30 minuti
define('MAX_FAIL_COUNT', 5);

// Quanti articoli scaricare per ogni fonte durante l'aggiornamento
define('ITEMS_PER_FEED', 20);

// Variabili globali
$grouped_news_data = [];
$cache_status = 'Non ancora caricata.';
$fetch_errors  = [];

// --- LISTA FEED AGGREGATORE ---
// Formato semplice: 'Categoria' => [array di url]
// (allineato a ciò che si aspetta update_cache.php)
$feeds = [
    'Tecnologia' => [
        'https://www.hwupgrade.it/rss_news.xml',
        'https://www.tomshw.it/feed/',
        'https://www.wired.it/feed/',
        'https://www.hdblog.it/feed/',
        'https://android.hdblog.it/feed/',
        'https://www.dday.it/rss/articoli.xml',
        'https://www.ilsoftware.it/feed/',
        'https://www.smartworld.it/feed',
    ],
    'Offerte' => [
        'https://www.smartworld.it/tag/offerte/feed',
        'https://www.hdblog.it/feed/rss/sezione/offerte/',
        'https://www.tuttoandroid.net/offerte/feed/',
        'https://www.dday.it/rss/offerte.xml',
        'https://www.tomshw.it/feed/offerte/',
    ],
    'Crypto' => [
        'https://it.cointelegraph.com/rss',
        'https://cryptonomist.ch/feed/',
        'https://it.investing.com/rss/news_25.rss',
        'https://www.coindesk.com/arc/outboundfeeds/rss/',
        'https://www.criptovaluta.it/feed',
    ],
    'Finanza' => [
        'https://www.ansa.it/sito/notizie/economia/economia_rss.xml',
        'https://quifinanza.it/feed/',
        'https://www.ilsole24ore.com/rss/finanza-e-mercati.xml',
        'https://it.investing.com/rss/news_1.rss',
        'https://www.wallstreetitalia.com/feed/',
        'https://www.finanzaonline.com/feed',
    ],
    'Gaming' => [
        'https://multiplayer.it/feed/',
        'https://www.ign.com/it/feed.xml',
        'https://www.everyeye.it/feed/feed_news_rss.asp',
        'https://www.eurogamer.it/feed',
        'https://www.spaziogames.it/feed/',
    ],
    'Apple' => [
        'https://www.iphoneitalia.com/feed',
        'https://www.macitynet.it/feed/',
        'https://www.melamorsicata.it/feed/',
        'https://www.ispazio.net/feed',
    ],
    'Android' => [
        'https://www.tuttoandroid.net/feed/',
        'https://www.androidworld.it/feed/',
        'https://www.androidiani.com/feed',
        'https://www.hdblog.it/feed/rss/sezione/android/',
    ],
    'Auto & Motori' => [
        'https://www.hdmotori.it/feed/',
        'https://www.alvolante.it/rss.xml',
        'https://www.formulapassion.it/automoto/feed',
        'https://it.motor1.com/rss/news/',
        'https://www.autoblog.it/feed',
    ],
    'Scienza & Spazio' => [
        'https://www.ansa.it/sito/notizie/scienza/scienza_rss.xml',
        'https://www.lescienze.it/rss/all/rss2.0.xml',
        'https://astrospace.it/feed/',
        'https://www.focus.it/rss/scienza.rss',
        'https://www.esa.int/rssfeed/TopNews',
        'https://www.space.com/feeds/all',
    ],
    'Cybersecurity' => [
        'https://www.punto-informatico.it/feed/',
        'https://www.redhotcyber.com/feed/',
        'https://www.cybersecurity360.it/feed/',
        'https://www.matricedigitale.it/feed/',
    ],
    'AI & Innovazione' => [
        'https://www.agendadigitale.eu/feed/',
        'https://www.artificialintelligence-news.com/feed/',
    ],
    'Mondo Tech' => [
        'https://www.theverge.com/rss/index.xml',
        'https://www.engadget.com/rss.xml',
        'https://techcrunch.com/feed/',
        'https://feeds.arstechnica.com/arstechnica/index',
        'https://www.wired.com/feed/rss',
    ],

    // Categoria speciale: alimenta SOLO lo slider attualità in cima.
    // Il prefisso "_" la nasconde dai canali nelle viste.
    '_Attualità' => [
        'https://news.google.com/rss?hl=it&gl=IT&ceid=IT:it',
        'https://www.repubblica.it/rss/homepage/rss2.0.xml',
        'https://www.lastampa.it/rss/copertina.xml',
        'https://www.ilsole24ore.com/rss/italia.xml',
        'https://www.fanpage.it/feed/',
    ],
];
