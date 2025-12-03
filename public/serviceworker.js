self.addEventListener('install', function (e) {
  e.waitUntil(
    caches.open('fplGalaxyV3').then(async function (cache) {
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
        '/assets/img/google144.webp',


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
