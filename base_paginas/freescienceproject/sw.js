// Minimal service worker (safe default)
// No caching — this file exists only to allow registration during development
self.addEventListener('install', event => {
  // Activate immediately
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
});

// Optional: basic fetch pass-through (don't cache)
// Removed no-op fetch handler to avoid navigation overhead warnings.
