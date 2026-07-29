const CACHE_NAME = 'techzone-v12-theme';
const urlsToCache = [
  'index.php',
  'manifest.json'
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache).catch(() => {}))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(names =>
      Promise.all(names.map(n => n !== CACHE_NAME ? caches.delete(n) : null))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // Mai cachare l'aggiornamento o le API live: sempre dalla rete
  if (url.pathname.includes('update_cache.php') ||
      url.pathname.includes('subscribe.php') ||
      url.pathname.includes('newsletter_subscribe.php') ||
      url.pathname.includes('send_push.php') ||
      url.pathname.includes('sitemap.php') ||
      url.pathname.includes('feed.php') ||
      url.pathname.includes('robots.txt') ||
      url.hostname.includes('coingecko') ||
      url.hostname.includes('alternative.me') ||
      url.hostname.includes('tradingview')) {
    event.respondWith(fetch(event.request));
    return;
  }

  // Network-first per le pagine, fallback alla cache se offline
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});

// === PUSH NOTIFICATIONS ===
self.addEventListener('push', event => {
  let data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) {}

  const title = data.title || 'TechNewsZone';
  const options = {
    body: data.body || 'Nuova notizia disponibile',
    icon: 'android-chrome-192x192.png',
    badge: 'favicon-32x32.png',
    image: data.image || undefined,
    tag: data.url || 'technewszone',     // raggruppa notifiche dello stesso articolo
    renotify: true,
    data: { url: data.url || '/index.php' }
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  const target = (event.notification.data && event.notification.data.url) || '/index.php';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
      // Se c'è già una finestra aperta sul sito, focalizzala
      for (const c of list) {
        if ('focus' in c) { c.navigate(target); return c.focus(); }
      }
      return clients.openWindow(target);
    })
  );
});
