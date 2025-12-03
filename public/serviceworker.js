self.addEventListener('install', function (e) {
  e.waitUntil(
    caches.open('fplGalaxyV2').then(async function (cache) {
      const files = [
        '/',
        '/login',
        '/register',
        '/leagues',
        'assets/css/main.css',
        '/assets/js/main.js',
        '/assets/img/logo.webp',
        '/banner.webp',

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

// Serve from cache first, then network; if offline, show offline.html
self.addEventListener('fetch', function (e) {
  e.respondWith(
    fetch(e.request)
      .catch(() => {
        return caches.match(e.request).then((response) => {
          if (response) {
            return response;
          }

        });
      })
  );
});
