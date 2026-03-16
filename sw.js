const CACHE_NAME = 'albustan-cache-v1';
const urlsToCache = [
  '/albustansuites/index.php',
  '/albustansuites/assets/css/style.css',
  '/albustansuites/assets/css/rtl.css',
  '/albustansuites/images/logo.png',
  '/albustansuites/manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response; // Return cached response
        }
        return fetch(event.request).catch(() => {
          // If offline and request fails, we could return a fallback page here
        });
      })
  );
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
