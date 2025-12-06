const STATIC_CACHE = 'fplGalaxyV2';
const LEAGUE_CACHE = 'league-cache';

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(async cache => {
      const files = [
        '/',
        '/login',
        '/register',
        '/forgot-password',
        '/leagues',
        '/standings',
        '/dashboard',
        '/profile',
        '/admin',
        '/privacy-policy',
        '/terms-and-conditions',
        '/how-to-find-fpl-league-id',
        '/assets/css/main.css',
        '/assets/js/main.js',
        '/assets/img/logo.webp',
        '/assets/img/logo-light.webp',
        '/assets/img/banner.webp',
        '/assets/img/google144.png',
        '/offline.html'
      ];
      for (let file of files) {
        try {
          await cache.add(file);
        } catch (err) {
          console.warn(`Failed to cache ${file}`, err);
        }
      }
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', event => {
  const url = event.request.url;

  // Dynamic caching for league API
  if (url.includes('/leagues/')) {
    event.respondWith(
      caches.open(LEAGUE_CACHE).then(cache => {
        return fetch(event.request)
          .then(response => {
            cache.put(event.request, response.clone());
            return response;
          })
          .catch(() => cache.match(event.request));
      })
    );
    return;
  }

  // For navigation requests (pages), network first
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          return caches.open(STATIC_CACHE).then(cache => {
            cache.put(event.request, response.clone());
            return response;
          });
        })
        .catch(() => caches.match(event.request).then(resp => resp || caches.match('/offline.html')))
    );
    return;
  }

  // For other assets: cache-first
  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});
