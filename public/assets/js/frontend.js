/* Tamil News Portal — Frontend JS */
'use strict';

/* ── MODAL ── */
function openModal()  { document.getElementById('loginModal')?.classList.add('open'); }
function closeModal() { document.getElementById('loginModal')?.classList.remove('open'); }
function handleOverlayClick(e) { if (e.target === e.currentTarget) closeModal(); }

/* ── USER DROPDOWN ── */
function toggleDropdown() {
  document.getElementById('userDropdown')?.classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const wrap = document.querySelector('.user-avatar-wrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('userDropdown')?.classList.remove('open');
  }
});

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
