/* push-subscribe.js
 * Handles Firebase push subscription on the frontend.
 * Config values injected by PHP via window.FCM_CONFIG.
 */
(function () {
  'use strict';

  var cfg = window.FCM_CONFIG;
  if (!cfg || !cfg.apiKey || cfg.apiKey === 'REPLACE_API_KEY') return; // Not configured yet

  /* Init Firebase */
  if (!firebase.apps.length) {
    firebase.initializeApp(cfg);
  }

  var messaging = null;
  try { messaging = firebase.messaging(); } catch (e) { return; }

  /* Register service worker */
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/public/firebase-messaging-sw.js')
      .then(function (reg) {
        /* Pass config to SW */
        reg.active?.postMessage({ type: 'FCM_CONFIG', config: cfg });
        messaging.useServiceWorker(reg);
        requestPermission();
      })
      .catch(function (err) { console.warn('SW registration failed:', err); });
  }

  function requestPermission() {
    Notification.requestPermission().then(function (permission) {
      if (permission === 'granted') {
        getToken();
      }
    });
  }

  function getToken() {
    messaging.getToken({ vapidKey: cfg.vapidKey })
      .then(function (token) {
        if (token) subscribe(token);
      })
      .catch(function (err) { console.warn('FCM token error:', err); });
  }

  function subscribe(token) {
    var base     = (document.querySelector('meta[name="base-url"]') || {}).content || '';
    var district = getCookie('tn_district_id') || '';

    fetch(base + '/api/push/subscribe', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    'token=' + encodeURIComponent(token) + '&district_id=' + encodeURIComponent(district),
    }).catch(function () {});
  }

  function getCookie(name) {
    var v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
    return v ? decodeURIComponent(v.pop()) : '';
  }

  /* In-page toast for foreground messages */
  messaging.onMessage(function (payload) {
    var n   = payload.notification || {};
    var url = (payload.data || {}).click_url || '/';
    showToast(n.title || 'Tamil News', n.body || '', url, n.image);
  });

  function showToast(title, body, url, image) {
    var existing = document.getElementById('tn-push-toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.id        = 'tn-push-toast';
    toast.className = 'tn-push-toast';
    toast.innerHTML =
      (image ? '<img src="' + image + '" class="tn-push-toast-img" alt="">' : '') +
      '<div class="tn-push-toast-body">' +
        '<div class="tn-push-toast-title">' + escHtml(title) + '</div>' +
        '<div class="tn-push-toast-msg">'   + escHtml(body)  + '</div>' +
      '</div>' +
      '<button class="tn-push-toast-close" onclick="this.parentElement.remove()">✕</button>';

    toast.addEventListener('click', function (e) {
      if (!e.target.classList.contains('tn-push-toast-close')) {
        window.open(url, '_blank');
        toast.remove();
      }
    });

    document.body.appendChild(toast);
    setTimeout(function () { toast.classList.add('tn-push-toast-show'); }, 50);
    setTimeout(function () { if (toast.parentElement) toast.remove(); }, 7000);
  }

  function escHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c];
    });
  }
}());
