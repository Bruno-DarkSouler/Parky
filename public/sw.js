const CACHE_NAME = 'parking-finder-v1';
const DYNAMIC_CACHE_NAME = 'parking-finder-dynamic-v1';

const urlsToCache = [
  '/',
  '/manifest.json',
  '/resources/img/logo1.webp'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME && cache !== DYNAMIC_CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', event => {
  // Solo interceptar peticiones GET (para no interferir con POST, formularios, logins, etc.)
  if (event.request.method !== 'GET') {
    return;
  }

  const url = new URL(event.request.url);

  // Guardar dinámicamente en caché las peticiones de mapas (OpenStreetMap) y librerías externas (unpkg, google fonts)
  if (
    url.host.includes('tile.openstreetmap.org') ||
    url.host.includes('unpkg.com') ||
    url.host.includes('fonts.googleapis.com') ||
    url.host.includes('fonts.gstatic.com')
  ) {
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse; // Devolver desde caché inmediatamente
        }
        return fetch(event.request).then(networkResponse => {
          // Guardar una copia en la caché dinámica para la próxima vez
          return caches.open(DYNAMIC_CACHE_NAME).then(cache => {
            cache.put(event.request, networkResponse.clone());
            return networkResponse;
          });
        });
      })
    );
  } else {
    // Comportamiento por defecto para los archivos locales de la app
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        return cachedResponse || fetch(event.request);
      })
    );
  }
});

