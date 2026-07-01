/**
 * language-switcher.js
 * Provider-based, swappable translation module.
 *
 * To switch providers later (e.g. to Weglot with an API key):
 *   1. Write a new provider object matching the same interface as
 *      GTranslateProvider (init(), setLanguage(code)).
 *   2. Change ACTIVE_PROVIDER below to point at it.
 * Nothing else in this file, or the rest of the site, needs to change.
 */
(function () {
  'use strict';

  // ════════════════════════════════════════════
  // Provider interface: { init(), setLanguage(langCode) }
  // ════════════════════════════════════════════

  // ── Provider: Google Translate (free, client-side, no API key) ──
  var GTranslateProvider = {
    _ready: false,
    _pending: null,

    init: function () {
      if (document.getElementById('google_translate_element')) return;

      // Hidden container Google's widget renders into
      var el = document.createElement('div');
      el.id = 'google_translate_element';
      el.style.display = 'none';
      document.body.appendChild(el);

      // Google calls this global function once its script loads
      window.googleTranslateElementInit = function () {
        new google.translate.TranslateElement({
          pageLanguage: 'ta',
          includedLanguages: 'ta,en,hi',
          autoDisplay: false
        }, 'google_translate_element');
        GTranslateProvider._ready = true;
        if (GTranslateProvider._pending) {
          GTranslateProvider.setLanguage(GTranslateProvider._pending);
          GTranslateProvider._pending = null;
        }
      };

      var script = document.createElement('script');
      script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
      document.body.appendChild(script);
    },

    setLanguage: function (langCode) {
      if (!this._ready) { this._pending = langCode; return; }

      // Google renders a hidden <select class="goog-te-combo"> — driving
      // that select is the standard way to switch language without
      // showing Google's own banner/UI.
      var select = document.querySelector('select.goog-te-combo');
      if (!select) {
        // Not rendered yet — retry briefly
        setTimeout(function () { GTranslateProvider.setLanguage(langCode); }, 300);
        return;
      }
      select.value = langCode;
      select.dispatchEvent(new Event('change'));
    }
  };

  // ════════════════════════════════════════════
  // Active provider — change this one line to swap providers later
  // ════════════════════════════════════════════
  var ACTIVE_PROVIDER = GTranslateProvider;

  // ════════════════════════════════════════════
  // Public API used by the switcher buttons in the header/drawer
  // ════════════════════════════════════════════
  var STORAGE_KEY = 'tn_lang';

  function setLanguage(code) {
    var btn = document.querySelector('[data-lang-btn="' + code + '"]');
    if (btn && !btn.classList.contains('active')) {
      btn.classList.add('lang-loading');
      setTimeout(function () { btn.classList.remove('lang-loading'); }, 1500);
    }
    ACTIVE_PROVIDER.setLanguage(code === 'ta' ? 'ta' : code);
    try { localStorage.setItem(STORAGE_KEY, code); } catch (e) {}
    document.querySelectorAll('[data-lang-btn]').forEach(function (b) {
      b.classList.toggle('active', b.dataset.langBtn === code);
    });

    // Update <html lang> so screen readers and crawlers see the active language
    var langMap = { ta: 'ta', en: 'en', hi: 'hi' };
    document.documentElement.lang = langMap[code] || 'ta';

    // Update content-language meta
    var clMeta = document.querySelector('meta[http-equiv="content-language"]');
    if (clMeta) clMeta.setAttribute('content', code);
  }

  function restoreLanguage() {
    var saved;
    try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
    if (saved && saved !== 'ta') setLanguage(saved);
  }

  window.LanguageSwitcher = {
    init: function () {
      ACTIVE_PROVIDER.init();
      document.querySelectorAll('[data-lang-btn]').forEach(function (btn) {
        btn.addEventListener('click', function () { setLanguage(btn.dataset.langBtn); });
      });
      // Give Google's script a moment to load before restoring a saved choice
      setTimeout(restoreLanguage, 1200);
    },
    setLanguage: setLanguage
  };

  // Script loads at end of body — DOM is already ready, no need to
  // wait for DOMContentLoaded. Starting Google's script fetch immediately
  // shaves off the visible delay on the very first language switch.
  window.LanguageSwitcher.init();
})();
