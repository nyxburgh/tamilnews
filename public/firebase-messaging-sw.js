/* firebase-messaging-sw.js
 * Service Worker for Firebase Cloud Messaging background push.
 * Replace PLACEHOLDER values with your Firebase config after project creation.
 */

importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey:            self.FCM_API_KEY            || 'REPLACE_API_KEY',
  authDomain:        self.FCM_AUTH_DOMAIN        || 'REPLACE_AUTH_DOMAIN',
  projectId:         self.FCM_PROJECT_ID         || 'REPLACE_PROJECT_ID',
  storageBucket:     self.FCM_STORAGE_BUCKET     || 'REPLACE_STORAGE_BUCKET',
  messagingSenderId: self.FCM_SENDER_ID          || 'REPLACE_SENDER_ID',
  appId:             self.FCM_APP_ID             || 'REPLACE_APP_ID',
});

const messaging = firebase.messaging();

/* Background message handler */
messaging.onBackgroundMessage(function (payload) {
  const n    = payload.notification || {};
  const data = payload.data         || {};

  const notifOptions = {
    body:    n.body  || data.body  || '',
    icon:    n.icon  || '/public/assets/img/logo-192.png',
    image:   n.image || data.image || undefined,
    badge:   '/public/assets/img/logo-96.png',
    data:    { click_url: data.click_url || n.click_action || '/' },
    actions: [{ action: 'open', title: 'Read More' }],
  };

  return self.registration.showNotification(n.title || data.title || 'Tamil News', notifOptions);
});

/* Click handler */
self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  const url = event.notification.data?.click_url || '/';
  event.waitUntil(clients.openWindow(url));
});
