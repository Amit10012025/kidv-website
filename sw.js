// KIDV Tech Service Worker v1.0
const CACHE = 'kidvtech-v1';
const ASSETS = [
  '/',
  '/index.html',
  '/company.html',
  '/product.html',
  '/contact.html',
  '/website-list.html',
  '/erp-list.html',
  '/ai-automation-list.html',
  '/hr-payroll-list.html',
  '/mobile-app-list.html',
  '/cloud-hosting-list.html',
  '/privacy-policy.html',
  '/terms-of-service.html',
  '/404.html',
  '/logo.png',
  '/android-icon-36x36.png',
];

// Install — cache all assets
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE).then(cache => cache.addAll(ASSETS)).catch(() => {})
  );
  self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch — network first, cache fallback
self.addEventListener('fetch', e => {
  if (e.request.method !== 'GET') return;
  e.respondWith(
    fetch(e.request)
      .then(res => {
        const clone = res.clone();
        caches.open(CACHE).then(cache => cache.put(e.request, clone));
        return res;
      })
      .catch(() => caches.match(e.request).then(r => r || caches.match('/404.html')))
  );
});