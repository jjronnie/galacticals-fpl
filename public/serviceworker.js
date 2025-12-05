self.addEventListener('install', function (e) {
  e.waitUntil(
    caches.open('fplGalaxyV2').then(async function (cache) {
      const files = [
        '/',
        '/login',
        '/register',
        '/forgot-password',
        '/leagues',
        '/standings',
        '/dashboard',
        '/profile',
        '/privacy-policy',
        '/terms-and-conditions',
        'assets/css/main.css',
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
});

// Single fetch listener for all requests
self.addEventListener('fetch', function (event) {
  const url = event.request.url;

  // League API caching (dynamic cache)
  if (url.includes('/leagues/')) {
    event.respondWith(
      caches.open('league-cache').then(cache => {
        return fetch(event.request)
          .then(response => {
            cache.put(event.request, response.clone());
            return response;
          })
          .catch(() => cache.match(event.request));
      })
    );
    return; // exit, league handled
  }

  // General cache: cache-first, fallback to offline.html for pages
  event.respondWith(
    caches.match(event.request).then(response => {
      if (response) return response;
      return fetch(event.request)
        .catch(() => {
          if (event.request.mode === 'navigate') {
            return caches.match('/offline.html');
          }
        });
    })
  );
});
