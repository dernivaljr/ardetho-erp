const CACHE_PREFIX = "ardetho-erp-";
const CACHE_NAME = `${CACHE_PREFIX}v23`;

const FILES_TO_CACHE = [
  "./manifest.json",

  "./assets/css/variables.css",
  "./assets/css/global.css",
  "./assets/css/layout.css",
  "./assets/css/components.css",
  "./assets/css/public.css",
  "./assets/css/auth.css",
  "./assets/css/dashboard.css",
  "./assets/css/pages.css",
  "./assets/css/responsive.css",

  "./assets/js/data.js",
  "./assets/js/storage.js",
  "./assets/js/auth.js",
  "./assets/js/layout.js",
  "./assets/js/utils.js",
  "./assets/js/dashboard.js",
  "./assets/js/clients.js",
  "./assets/js/client-form.js",
  "./assets/js/products.js",
  "./assets/js/product-form.js",
  "./assets/js/sales.js",
  "./assets/js/sale-form.js",
  "./assets/js/financial.js",
  "./assets/js/financial-form.js",
  "./assets/js/reports.js",
  "./assets/js/modules.js",
  "./assets/js/settings.js",
  "./assets/js/profile.js",
  "./assets/js/hr.js",
  "./assets/js/hr-form.js",
  "./assets/js/pwa.js",

  "./assets/images/ardetho-logo.png",
  "./assets/images/ardetho-icon.png",
  "./assets/images/mecanica-xyz-logo.png",
  "./assets/images/mecanica-xyz-icon.png"
];

function isSameOrigin(request) {
  return new URL(request.url).origin === self.location.origin;
}

function isPhpRequest(request) {
  return new URL(request.url).pathname.endsWith(".php");
}

function isCacheableStaticAsset(request) {
  if (request.method !== "GET" || !isSameOrigin(request)) {
    return false;
  }

  const pathname = new URL(request.url).pathname;

  return (
    pathname.endsWith("/manifest.json") ||
    pathname.includes("/assets/css/") ||
    pathname.includes("/assets/js/") ||
    pathname.includes("/assets/images/")
  );
}

self.addEventListener("install", (event) => {
  self.skipWaiting();

  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      const freshRequests = FILES_TO_CACHE.map((file) => {
        return new Request(file, { cache: "reload" });
      });

      return cache.addAll(freshRequests);
    })
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => cacheName.startsWith(CACHE_PREFIX) && cacheName !== CACHE_NAME)
          .map((cacheName) => caches.delete(cacheName))
      );
    }).then(() => {
      return self.clients.claim();
    })
  );
});

self.addEventListener("fetch", (event) => {
  const { request } = event;

  if (request.mode === "navigate" || isPhpRequest(request)) {
    event.respondWith(fetch(request));
    return;
  }

  if (!isCacheableStaticAsset(request)) {
    return;
  }

  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(request).then((networkResponse) => {
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== "basic") {
          return networkResponse;
        }

        const responseToCache = networkResponse.clone();

        caches.open(CACHE_NAME).then((cache) => {
          cache.put(request, responseToCache);
        });

        return networkResponse;
      });
    })
  );
});
