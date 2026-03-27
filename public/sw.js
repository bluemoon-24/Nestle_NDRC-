const CACHE_NAME = 'ndrc-v1';
const ASSETS_TO_CACHE = [
  './index.php',
  './assets/css/main.css',
  './includes/header.php',
  './includes/footer.php'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      return cachedResponse || fetch(event.request);
    })
  );
});
