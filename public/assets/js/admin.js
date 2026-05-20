/* Base URL for all admin routes */
const r = document.querySelector('meta[name="base-url"]')?.content || '';

/* Tamil News Portal — Admin JS */
'use strict';

// ── CSRF HEADER FOR ALL AJAX ──────────────────────
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Attach CSRF to all fetch() requests
const _fetch = window.fetch;
window.fetch = function(url, opts = {}) {
  opts.headers = opts.headers || {};
  if (!(opts.headers instanceof Headers)) {
    opts.headers['X-CSRF-TOKEN'] = csrfToken;
  }
  return _fetch(url, opts);
};

// ── SIDEBAR TOGGLE ────────────────────────────────
const sidebar        = document.getElementById('sidebar');
const sidebarToggle  = document.getElementById('sidebarToggle');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function openSidebar() {
  sidebar?.classList.add('open');
  sidebarOverlay?.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeSidebar() {
  sidebar?.classList.remove('open');
  sidebarOverlay?.classList.remove('open');
  document.body.style.overflow = '';
}

sidebarToggle?.addEventListener('click', () => {
  sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
});

sidebarOverlay?.addEventListener('click', closeSidebar);

// Close on nav link click (mobile)
sidebar?.querySelectorAll('.tn-nav-item').forEach(link => {
  link.addEventListener('click', () => {
    if (window.innerWidth <= 768) closeSidebar();
  });
});

// ── AUTO DISMISS ALERTS ───────────────────────────
setTimeout(() => {
  document.querySelectorAll('.alert.alert-success, .alert.alert-info').forEach(el => {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
    bsAlert?.close();
  });
}, 4000);

// ── CONFIRM DELETE PROTECTION ─────────────────────
document.querySelectorAll('form[data-confirm]').forEach(form => {
  form.addEventListener('submit', function(e) {
    if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
  });
});

// ── SLUG AUTO-GENERATE ────────────────────────────
function generateSlug(text) {
  return text.toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]/g, '')
    .replace(/\-+/g, '-')
    .replace(/^-+|-+$/g, '');
}

// ── FORM DIRTY CHECK ──────────────────────────────
let formDirty = false;
document.querySelectorAll('#articleForm input, #articleForm textarea, #articleForm select').forEach(el => {
  el.addEventListener('change', () => { formDirty = true; });
});
window.addEventListener('beforeunload', e => {
  if (formDirty) {
    e.preventDefault();
    e.returnValue = '';
  }
});
document.getElementById('articleForm')?.addEventListener('submit', () => { formDirty = false; });

// ── BULK CHECKBOX ALL ─────────────────────────────
const checkAll = document.getElementById('checkAll');
if (checkAll) {
  checkAll.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
  });
  document.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', () => {
      const all    = document.querySelectorAll('.row-check').length;
      const checked = document.querySelectorAll('.row-check:checked').length;
      checkAll.indeterminate = checked > 0 && checked < all;
      checkAll.checked = checked === all;
    });
  });
}

// ── TOAST HELPER ──────────────────────────────────
window.showToast = function(msg, type = 'success') {
  const id   = 'toast-' + Date.now();
  const html = `
    <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" style="position:fixed;top:70px;right:20px;z-index:9999;min-width:260px">
      <div class="d-flex">
        <div class="toast-body fw-500">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
  document.body.insertAdjacentHTML('beforeend', html);
  const el = document.getElementById(id);
  const toast = new bootstrap.Toast(el, { delay: 3500 });
  toast.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
};

// ── INLINE BREAKING TOGGLE ────────────────────────
document.querySelectorAll('.breaking-toggle').forEach(btn => {
  btn.addEventListener('click', async function(e) {
    e.preventDefault();
    const id  = this.dataset.id;
    const res = await fetch(`/admin/articles/toggle-breaking/${id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (res.ok) {
      const data = await res.json();
      this.innerHTML = data.is_breaking
        ? '<span class="badge bg-danger">BREAKING</span>'
        : '<span class="text-muted">—</span>';
    }
  });
});

// ── CHARACTER COUNTER ─────────────────────────────
document.querySelectorAll('[maxlength]').forEach(el => {
  const max  = parseInt(el.maxLength);
  const hint = document.createElement('small');
  hint.className = 'text-muted d-block text-end mt-1';
  hint.textContent = `0 / ${max}`;
  el.parentNode.appendChild(hint);
  el.addEventListener('input', function() {
    hint.textContent = `${this.value.length} / ${max}`;
    hint.className = this.value.length > max * 0.9 ? 'text-warning d-block text-end mt-1' : 'text-muted d-block text-end mt-1';
  });
});

// ── CATEGORY SORT VIA SORTABLEJS ──────────────────
const catSortable = document.getElementById('catSortable');
if (catSortable && typeof Sortable !== 'undefined') {
  Sortable.create(catSortable, {
    handle: '.drag-handle',
    animation: 150,
    onEnd: function() {
      const ids = [...catSortable.querySelectorAll('tr[data-id]')].map(r => r.dataset.id);
      fetch('/admin/categories/sort', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids }),
      });
    }
  });
}
