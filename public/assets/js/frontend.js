/* Tamil News Portal — Frontend JS */
'use strict';

/* ── MODAL ── */
function openModal()  { document.getElementById('loginModal')?.classList.add('open'); }
function closeModal() { document.getElementById('loginModal')?.classList.remove('open'); }
function handleOverlayClick(e) { if (e.target === e.currentTarget) closeModal(); }

/* ── USER DROPDOWN ── */
/* ── Dropdown, modal, drawer — wired directly (script is at end of body) ── */
(function () {

  /* Profile dropdown — stopPropagation prevents immediate close */
  var avatarBtn = document.querySelector('[data-action="toggle-dropdown"]');
  var dropdown  = document.getElementById('userDropdown');
  if (avatarBtn && dropdown) {
    avatarBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      dropdown.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!avatarBtn.contains(e.target)) {
        dropdown.classList.remove('open');
      }
    });
  }

  /* Generic action dispatcher — single delegated handler */
  document.addEventListener('click', function (e) {
    var el  = e.target.closest('[data-action]');
    if (!el) return;
    var act = el.getAttribute('data-action');

    switch (act) {
      case 'open-modal':
        openModal(); break;
      case 'close-modal':
        closeModal(); break;
      case 'modal-overlay':
        if (e.target === el) closeModal(); break;
      case 'open-drawer':
        openDrawer(); break;
      case 'close-drawer':
        closeDrawer(); break;
      case 'drawer-open-modal':
        closeDrawer(); setTimeout(openModal, 200); break;
      case 'open-rate-sheet':
        openRateSheet(); break;
      case 'close-rate-sheet':
        closeRateSheet(); break;
      case 'close-rate':
        if (typeof closeRate === 'function') closeRate(); break;
      case 'close-lightbox':
        if (typeof closeAdLightbox === 'function') closeAdLightbox(); break;
      case 'close-nav-drawer':
        if (typeof closeNavDrawer === 'function') closeNavDrawer(); break;
      case 'close-vertical-ad':
        var va = el.closest('.mob-vertical-ad');
        if (va) va.style.display = 'none'; break;
      case 'google-popup':
        var pu = el.dataset.url;
        if (!pu) break;
        var pw = window.open(pu, 'googleLogin',
          'width=480,height=600,top=' + Math.round((screen.height-600)/2) +
          ',left=' + Math.round((screen.width-480)/2) + ',scrollbars=yes');
        var pt = setInterval(function () {
          if (!pw || pw.closed) {
            clearInterval(pt);
            window.location.reload();
          }
        }, 600);
        break;
    }
  });

  /* Stop propagation on elements that need it */
  document.querySelectorAll('[data-stop-propagation]').forEach(function (el) {
    el.addEventListener('click', function (e) { e.stopPropagation(); });
  });

}());

/* ── TOAST ── */
function showToast(msg, duration = 3000) {
  const t = document.createElement('div');
  t.textContent = msg;
  t.style.cssText = `
    position:fixed; bottom:80px; left:50%; transform:translateX(-50%);
    background:var(--charcoal); color:white;
    font-family:'Noto Sans Tamil',sans-serif; font-size:13px;
    padding:10px 20px; border-radius:6px; z-index:9999;
    box-shadow:0 4px 16px rgba(0,0,0,.2); white-space:nowrap;
    animation:fadeUp .3s ease both;`;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), duration);
}

/* ── COPY LINK ── */
function copyLink() {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(window.location.href)
      .then(() => showToast('✓ Link copied!'));
  } else {
    const ta = document.createElement('textarea');
    ta.value = window.location.href;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    ta.remove();
    showToast('✓ Link copied!');
  }
}

/* ── LAZY LOAD TICKER ── */
(function () {
  const ticker = document.getElementById('tickerInner');
  if (ticker) {
    ticker.addEventListener('mouseover', () => ticker.style.animationPlayState = 'paused');
    ticker.addEventListener('mouseout',  () => ticker.style.animationPlayState = 'running');
  }
})();

/* ── SCROLL: STICKY SIDEBAR ── */
(function() {
  const sticky = document.querySelector('.sidebar-sticky');
  if (!sticky) return;
  const offset = 80;
  function update() {
    const scrollY = window.scrollY;
    sticky.style.top = offset + 'px';
  }
  window.addEventListener('scroll', update, { passive: true });
})();

/* ── IMAGE LAZY LOAD FALLBACK ── */
document.querySelectorAll('img[loading="lazy"]').forEach(img => {
  img.addEventListener('error', function() {
    this.src = 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&q=60';
    this.onerror = null;
  });
});

/* ── READING PROGRESS BAR (article pages) ── */
(function() {
  const artBody = document.querySelector('.art-body');
  if (!artBody) return;
  const bar = document.createElement('div');
  bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:var(--red);z-index:9999;transition:width .1s;';
  document.body.appendChild(bar);
  window.addEventListener('scroll', () => {
    const total = document.body.scrollHeight - window.innerHeight;
    const pct   = Math.min(100, (window.scrollY / total) * 100);
    bar.style.width = pct + '%';
  }, { passive: true });
})();


/* ── WINDOW RESIZE: close modals ── */
window.addEventListener('resize', () => {
  if (window.innerWidth > 768) {
    closeModal();
    document.getElementById('userDropdown')?.classList.remove('open');
  }
}, { passive: true });

/* ── TOUCH SWIPE: close modal on swipe down ── */
(function() {
  let startY = 0;
  const modal = document.getElementById('loginModal');
  if (!modal) return;
  modal.addEventListener('touchstart', e => { startY = e.touches[0].clientY; }, { passive: true });
  modal.addEventListener('touchend',   e => {
    if (e.changedTouches[0].clientY - startY > 80) closeModal();
  }, { passive: true });
})();


/* Short URL copy button */
function copyShortLink(btn) {
  var url = btn.dataset.url;
  if (!url) return;
  navigator.clipboard.writeText(url).then(function () {
    var span = btn.querySelector('span');
    if (span) { var orig = span.textContent; span.textContent = 'Copied!'; setTimeout(function(){ span.textContent = orig; }, 2000); }
  }).catch(function () {
    window.prompt('Copy this link:', url);
  });
}

/* ── Category infinite scroll ── */
(function () {
  var sentinel = document.getElementById('catScrollSentinel');
  var spinner  = document.getElementById('catScrollSpinner');
  var endMsg   = document.getElementById('catScrollEnd');
  var grid     = document.querySelector('.g4');
  if (!sentinel || !grid) return;

  var page     = 1;
  var loading  = false;
  var done     = false;
  var baseUrl  = document.querySelector('meta[name="base-url"]')?.content || '';

  // Build URL from current path + query params
  function buildUrl(p) {
    var u = new URL(window.location.href);
    u.searchParams.set('page', p);
    u.searchParams.set('ajax', '1');
    return u.toString();
  }

  function loadMore() {
    if (loading || done) return;
    loading = true;
    spinner.style.display = 'flex';

    fetch(buildUrl(page + 1))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        page++;
        data.articles.forEach(function (a) {
          var hasImg = a.image_url || a.thumb_url;
          var div    = document.createElement('a');
          div.href   = baseUrl + '/article/' + a.slug;
          div.className = 'nc' + (hasImg ? '' : ' nc-no-img');

          var imgHtml = hasImg
            ? '<img src="' + (a.thumb_url || a.image_url) + '" alt="' + escHtml(a.title) + '" loading="lazy">'
            : '';
          var excHtml = (!hasImg && a.excerpt)
            ? '<div class="nc-no-img-excerpt">' + escHtml(a.excerpt.substring(0, 150)) + '</div>'
            : '';

          div.innerHTML =
            imgHtml +
            '<div class="nc-body">' +
              '<span class="ctag">' + escHtml(a.category_tamil || a.category_name || '') + '</span>' +
              '<div class="nc-title' + (hasImg ? '' : ' nc-title-lg') + '">' + escHtml(a.title) + '</div>' +
              excHtml +
              '<div class="hero4-meta notranslate">' + (a.published_at ? a.published_at.substring(0, 10) : '') + '</div>' +
            '</div>';
          grid.appendChild(div);
        });

        loading = false;
        spinner.style.display = 'none';

        if (!data.has_more) {
          done = true;
          endMsg.style.display = 'block';
          observer.disconnect();
        }
      })
      .catch(function () {
        loading = false;
        spinner.style.display = 'none';
      });
  }

  function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  var observer = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting) loadMore();
  }, { rootMargin: '300px' });

  observer.observe(sentinel);
})();




/* ── Rate sheet ── */
(function () {
  var sheet   = document.getElementById('mobRateSheet');
  var overlay = document.getElementById('mobRateOverlay');
  if (!sheet) return;
  window.openRateSheet = function () {
    sheet.classList.add('open');
    if (overlay) overlay.classList.add('open');
    fetchWeather();
  };
  window.closeRateSheet = function () {
    sheet.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
  };
}());

/* ── Weather fetch — self-contained, no IIFE dependency ── */
function fetchWeather() {
  var deskEl    = document.getElementById('desktopWeatherVal');
  var deskCity  = document.getElementById('desktopWeatherCity');
  var badgeTemp = document.getElementById('mobWeatherBadgeTemp');
  var badge     = document.getElementById('mobWeatherBadge');
  var base      = (document.querySelector('meta[name="base-url"]') || {}).content || '';

  function setTemp(t) {
    var txt = t + '°C';
    if (deskEl)    deskEl.textContent    = txt;
    if (badgeTemp) badgeTemp.textContent = txt;
    if (badge)     badge.classList.add('loaded');
  }

  function setCity(name) {
    if (!name) return;
    if (deskCity) deskCity.textContent = name;
    var mobCity = document.getElementById('mobWeatherBadgeCity');
    if (mobCity) mobCity.textContent = name;
  }

  function fetchTemp(lat, lon) {
    fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current_weather=true')
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d && d.current_weather) setTemp(Math.round(d.current_weather.temperature)); })
      .catch(function () {});
  }

  // City from GPS + Nominatim (OpenStreetMap) — exact, free, no API key
  // Never use IP geolocation — inaccurate (shows ISP city, not actual location)
  function fetchCityByCoords(lat, lon) {
    fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lon +
          '&format=json&zoom=10&addressdetails=1', {
      headers: { 'Accept-Language': 'en' }
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var a = d.address || {};
        // city → town → village → county (district)
        var name = a.city || a.town || a.village || a.county || a.state_district || '';
        setCity(name);
      })
      .catch(function () {});
  }

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (p) {
        var lat = p.coords.latitude.toFixed(6);
        var lon = p.coords.longitude.toFixed(6);
        fetchTemp(lat, lon);
        fetchCityByCoords(lat, lon);
      },
      function () { /* GPS denied — no city shown */ },
      { timeout: 8000, maximumAge: 300000 }
    );
  }
}


/* ═══════════════════════════════════════════════════
   DISTRICT DETECTION SYSTEM — Phase 2 & 3
   ═══════════════════════════════════════════════════ */
(function () {
  var COOKIE_KEY   = 'tn_district_id';
  var COOKIE_NAME  = 'tn_district_name';
  var API_ENDPOINT = (document.querySelector('meta[name="base-url"]') || {}).content + '/api/district/detect';
  var COOKIE_DAYS  = 30;

  function getCookie(name) {
    var v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
    return v ? v.pop() : null;
  }

  function setCookie(name, value, days) {
    var d = new Date(); d.setTime(d.getTime() + days * 864e5);
    document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
  }

  function updateDistrictUI(id, name) {
    var el = document.getElementById('districtSelectorLabel');
    if (el && name) el.textContent = '📍 ' + name;
  }

  function saveDistrict(districtId, districtName) {
    setCookie(COOKIE_KEY,  districtId,   COOKIE_DAYS);
    setCookie(COOKIE_NAME, districtName, COOKIE_DAYS);
    updateDistrictUI(districtId, districtName);
  }

  function setDesktopCity(name) {
    if (!name) return;
    var dc = document.getElementById('desktopWeatherCity');
    if (dc) dc.textContent = name;
  }

  function detectByCoords(lat, lng) {
    if (lat < 8.0 || lat > 13.6 || lng < 76.2 || lng > 80.4) return;
    fetch(API_ENDPOINT + '?lat=' + lat.toFixed(4) + '&lng=' + lng.toFixed(4))
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.district_id) {
          var city = d.city_name || d.district_name;
          saveDistrict(d.district_id, d.district_name);
          setCookie('tn_city_name', city, COOKIE_DAYS);  // 30 days, same as district
          setDesktopCity(city);
        }
      })
      .catch(function () {});
  }

  // Always show city from cookie immediately on every page load
  setDesktopCity(getCookie('tn_city_name') || getCookie(COOKIE_NAME));

  if (!getCookie(COOKIE_KEY)) {
    // First visit — detect district + city
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function (pos) { detectByCoords(pos.coords.latitude, pos.coords.longitude); },
        function () {},
        { timeout: 5000, maximumAge: 86400000 }
      );
    }
  } else if (!getCookie('tn_city_name')) {
    // District cookie exists but city expired — re-detect city only
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function (pos) { detectByCoords(pos.coords.latitude, pos.coords.longitude); },
        function () {},
        { timeout: 5000, maximumAge: 86400000 }
      );
    }
  } else {
    updateDistrictUI(getCookie(COOKIE_KEY), getCookie(COOKIE_NAME));
  }

  // Manual district selector
  window.setDistrict = function (id, name) {
    saveDistrict(id, name);
    // Reload page to show district-targeted ads
    window.location.reload();
  };
}());

/* ── Auto-fetch weather on page load (desktop rates bar) ── */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () { fetchWeather(); });
} else {
  fetchWeather();
}

/* ── Mega menu — body-level floating div, z-index 9999, nothing clips it ── */
(function () {
  function init() {
    var btn  = document.getElementById('navMoreBtn');
    var data = window._navOverflow;
    var base = window._navBase || '';
    if (!btn || !data || !data.length) return;

    /* Build dropdown once, attach to body */
    var menu = document.createElement('div');
    menu.id  = 'tnMegaMenu';
    menu.style.cssText = [
      'position:fixed',
      'display:none',
      'z-index:9999',
      'background:#fff',
      'border-top:3px solid #C0001A',
      'border-radius:0 0 8px 8px',
      'box-shadow:0 12px 40px rgba(0,0,0,.22)',
      'padding:14px 18px',
      'min-width:280px',
      'font-family:Noto Sans Tamil,sans-serif'
    ].join(';');

    /* Build columns */
    var cols = [];
    var chunk = 4;
    for (var i = 0; i < data.length; i += chunk) cols.push(data.slice(i, i + chunk));

    /* Special articles */
    var cur = window._navCurrent || '';
    var special = {
      slug: 'special-articles',
      name: '✦ சிறப்புக் கட்டுரைகள்',
      active: cur.indexOf('special-articles') !== -1
    };
    if (cols.length) cols[cols.length - 1].push(special);
    else cols.push([special]);

    var inner = document.createElement('div');
    inner.style.cssText = 'display:flex;gap:0;';

    cols.forEach(function (col) {
      var colDiv = document.createElement('div');
      colDiv.style.cssText = 'display:flex;flex-direction:column;min-width:140px;padding:0 14px;border-right:1px solid #F0EFE9;';
      col.forEach(function (item) {
        var a = document.createElement('a');
        a.href = base + '/public/tamil-news/' + item.slug;
        a.textContent = item.name;
        a.style.cssText = [
          'display:block',
          'padding:8px 4px',
          'font-size:13px',
          'font-weight:600',
          'text-decoration:none',
          'color:' + (item.active ? '#C0001A' : '#1A1A1A'),
          'border-bottom:1px solid #F5F5F0',
          'white-space:nowrap',
          'transition:color .12s'
        ].join(';');
        a.addEventListener('mouseover', function () { a.style.color = '#C0001A'; });
        a.addEventListener('mouseout',  function () { a.style.color = item.active ? '#C0001A' : '#1A1A1A'; });
        colDiv.appendChild(a);
      });
      colDiv.lastChild.style.borderBottom = 'none';
      colDiv.lastChild.style.paddingRight = '0';
      inner.appendChild(colDiv);
    });
    if (inner.lastChild) inner.lastChild.style.borderRight = 'none';

    menu.appendChild(inner);
    document.body.appendChild(menu);

    /* Position below button */
    function position() {
      var rect = btn.getBoundingClientRect();
      menu.style.top  = (rect.bottom) + 'px';
      menu.style.left = rect.left + 'px';
    }

    /* Toggle */
    var open = false;
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      open = !open;
      menu.style.display = open ? 'block' : 'none';
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      var arrow = btn.querySelector('.nav-more-arrow');
      if (arrow) arrow.style.transform = open ? 'rotate(180deg)' : '';
      if (open) position();
    });

    /* Close on outside click */
    document.addEventListener('click', function () {
      open = false;
      menu.style.display = 'none';
      btn.setAttribute('aria-expanded', 'false');
      var arrow = btn.querySelector('.nav-more-arrow');
      if (arrow) arrow.style.transform = '';
    });

    /* Reposition on scroll/resize */
    window.addEventListener('scroll', function () {
      if (open) position();
    }, { passive: true });
    window.addEventListener('resize', function () {
      open = false;
      menu.style.display = 'none';
    }, { passive: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());

/* ── Desktop right nav drawer ── */
(function () {
  var btn     = document.getElementById('navMenuDrawerBtn');
  var drawer  = document.getElementById('navDrawer');
  var overlay = document.getElementById('navDrawerOverlay');
  var catsEl  = document.getElementById('navDrawerCats');
  if (!btn || !drawer) return;

  /* Populate ALL categories in drawer */
  var base    = window._navBase || '';
  var current = window._navCurrent || '';
  var allCats = window._navAll || [];
  if (allCats.length && catsEl) {
    var html = '<div class="nav-drawer-section">அனைத்து பிரிவுகளும்</div>';
    allCats.forEach(function (cat) {
      var active = current.indexOf(cat.slug) !== -1 ? ' active' : '';
      html += '<a href="' + base + '/public/tamil-news/' + cat.slug + '" class="nav-drawer-link' + active + '">' + cat.name + '</a>';
    });
    catsEl.innerHTML = html;
  }

  window.closeNavDrawer = function () {
    drawer.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
  };

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    var isOpen = drawer.classList.contains('open');
    drawer.classList.toggle('open');
    if (overlay) overlay.classList.toggle('open');
    document.body.style.overflow = !isOpen ? 'hidden' : '';
  });
}());
