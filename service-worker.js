const CACHE_NAME = "ardetho-erp-v19";

const FILES_TO_CACHE = [
  "./",
  "./index.html",
  "./about.html",
  "./modules.html",
  "./contact.html",
  "./login.html",
  "./dashboard.html",
  "./clients.html",
  "./client-form.html",
  "./products.html",
  "./product-form.html",
  "./sales.html",
  "./sale-form.html",
  "./financial.html",
  "./financial-form.html",
  "./reports.html",
  "./erp-modules.html",
  "./settings.html",
  "./profile.html",
  "./hr.html",
  "./hr-form.html",

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
  "./assets/images/ardetho-icon.png"
];

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
          .filter((cacheName) => cacheName !== CACHE_NAME)
          .map((cacheName) => caches.delete(cacheName))
      );
    }).then(() => {
      return self.clients.claim();
    })
  );
});

self.addEventListener("fetch", (event) => {
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      return cachedResponse || fetch(event.request);
    })
  );
});