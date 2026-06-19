/**
 * portal-nav.js
 * Shared staff-panel navigation behavior — used by portal.php and editor_portal.php.
 * Pure, page-independent JS. No inline <script> needed in layout views.
 */
(function () {
  'use strict';

  /* ── Desktop user dropdown menu ──
     portal.php uses #portalUserMenu, editor_portal.php uses #epUserMenu */
  function toggleMenu(id) {
    document.getElementById(id)?.classList.toggle('open');
  }
  window.togglePortalMenu = function () { toggleMenu('portalUserMenu'); };
  window.toggleEpMenu     = function () { toggleMenu('epUserMenu'); };

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.portal-user')) {
      document.getElementById('portalUserMenu')?.classList.remove('open');
    }
    if (!e.target.closest('.ep-user')) {
      document.getElementById('epUserMenu')?.classList.remove('open');
    }
  });

  /* ── Mobile bottom-sheet menu (shared pattern across staff layouts) ── */
  window.openPortalMenu = function () {
    document.getElementById('portalBottomOverlay')?.classList.add('open');
    document.getElementById('portalBottomSheet')?.classList.add('open');
  };
  window.closePortalMenu = function () {
    document.getElementById('portalBottomOverlay')?.classList.remove('open');
    document.getElementById('portalBottomSheet')?.classList.remove('open');
  };

  /* ── Editor Portal: full sidebar slide-over (desktop nav list on mobile) ── */
  document.getElementById('epSidebarToggle')?.addEventListener('click', function () {
    document.getElementById('epSidebar')?.classList.toggle('open');
    document.getElementById('epSidebarOverlay')?.classList.toggle('open');
  });
  document.getElementById('epSidebarOverlay')?.addEventListener('click', function () {
    document.getElementById('epSidebar')?.classList.remove('open');
    document.getElementById('epSidebarOverlay')?.classList.remove('open');
  });

  /* ── Mobile: tap anywhere on a table row → open its edit/show page ──
     Skips clicks on buttons, inputs, checkboxes, or nested forms. */
  function initTableRowTap() {
    if (window.innerWidth > 768) return;
    document.querySelectorAll('.tn-table tbody tr, .portal-table tbody tr').forEach(function (tr) {
      var editLink = tr.querySelector('a[href*="/edit/"]')
                   || tr.querySelector('a[href*="/show/"]')
                   || tr.querySelector('a.btn');
      if (!editLink) return;
      tr.style.cursor = 'pointer';
      tr.addEventListener('click', function (e) {
        if (e.target.closest('button, input, .form-check, form')) return;
        window.location.href = editLink.href;
      });
    });
  }
  document.addEventListener('DOMContentLoaded', initTableRowTap);
})();
