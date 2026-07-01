/* business-ad-form.js */
'use strict';

var AdForm = (function () {

  var pendingFiles  = [];
  var existingCount = 0;
  var MAX_IMAGES    = 5;

  /* ── Package change: auto-sets slot_id, amount, dates, QR ── */
  function onPackageChange() {
    var sel = document.getElementById('packageSel');
    if (!sel || !sel.value) return;
    var opt = sel.options[sel.selectedIndex];

    /* Slot ID — set hidden field */
    var slotId   = opt.dataset.slotId   || '0';
    var slotType = opt.dataset.slotType || '';
    var amount   = opt.dataset.amount   || '0';
    var days     = parseInt(opt.dataset.days || '30', 10);
    var qr       = opt.dataset.qr       || '';

    var slotInput = document.getElementById('slotIdInput');
    if (slotInput) slotInput.value = slotId;

    /* Ad type display */
    var labels = { square:'Square Ad', horizontal:'Horizontal Banner', vertical:'Vertical Ad' };
    var typeEl = document.getElementById('adTypeDisplay');
    if (typeEl) typeEl.value = labels[slotType] || slotType || '—';

    /* Amount — always update when package changes (reset manual flag) */
    var amtEl = document.getElementById('paymentAmount');
    if (amtEl) { amtEl.value = amount; delete amtEl.dataset.edited; }

    /* End date */
    var fromEl  = document.getElementById('validFrom');
    var untilEl = document.getElementById('validUntil');
    if (fromEl && fromEl.value && untilEl && !untilEl.dataset.edited) {
      var d = new Date(fromEl.value);
      d.setDate(d.getDate() + days);
      untilEl.value = d.toISOString().slice(0, 10);
    }

    /* Info text */
    var info = document.getElementById('pkgInfo');
    if (info) info.textContent = days + ' days · ₹' + parseFloat(amount).toLocaleString('en-IN');

    /* QR */
    var qrBox = document.getElementById('qrBox');
    var qrImg = document.getElementById('qrImg');
    if (qrBox && qrImg) {
      if (qr) {
        qrImg.src = (window.ASSET_URL_BASE || '') + qr;
        qrBox.classList.remove('d-none');
      } else {
        qrBox.classList.add('d-none');
      }
    }
  }

  /* ── Display type toggle ── */
  function onDisplayTypeChange() {
    var sel = document.getElementById('displayTypeSel');
    var row = document.getElementById('categoryRow');
    if (sel && row) row.style.display = sel.value === 'category' ? '' : 'none';
  }

  /* ── Image count UI ── */
  function updateImageUI() {
    var total     = existingCount + pendingFiles.length;
    var remaining = MAX_IMAGES - total;
    var labelEl   = document.getElementById('imgCountLabel');
    var secEl     = document.getElementById('uploadSection');
    var maxEl     = document.getElementById('maxReachedMsg');
    if (labelEl) labelEl.textContent = total + ' / ' + MAX_IMAGES;
    if (secEl)   secEl.classList.toggle('d-none', remaining <= 0);
    if (maxEl)   maxEl.classList.toggle('d-none', remaining > 0);
    document.getElementById('maxMoreImages').value = remaining;
  }

  function addFiles(files) {
    var remaining = MAX_IMAGES - existingCount - pendingFiles.length;
    if (remaining <= 0) return;
    pendingFiles = pendingFiles.concat(Array.from(files).slice(0, remaining));
    var dt = new DataTransfer();
    pendingFiles.forEach(function (f) { dt.items.add(f); });
    var inp = document.getElementById('imgUpload');
    if (inp) inp.files = dt.files;
    renderPreviews();
    updateImageUI();
  }

  function renderPreviews() {
    var box = document.getElementById('imgPreviews');
    if (!box) return;
    box.innerHTML = '';
    pendingFiles.forEach(function (file, idx) {
      var wrap = document.createElement('div');
      wrap.className = 'ad-img-thumb';
      var img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      var btn = document.createElement('button');
      btn.type = 'button'; btn.className = 'ad-img-del'; btn.textContent = '✕';
      btn.onclick = function () {
        pendingFiles.splice(idx, 1);
        var dt = new DataTransfer();
        pendingFiles.forEach(function (f) { dt.items.add(f); });
        var inp = document.getElementById('imgUpload');
        if (inp) inp.files = dt.files;
        renderPreviews(); updateImageUI();
      };
      wrap.appendChild(img); wrap.appendChild(btn); box.appendChild(wrap);
    });
  }

  function previewImages(input) { addFiles(Array.from(input.files)); }

  /* ── Delete existing image ── */
  function deleteImage(imageId, adId) {
    if (!confirm('Remove this image?')) return;
    fetch((window.DELETE_IMAGE_URL || '') + imageId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'ad_id=' + adId + '&_token=' + encodeURIComponent(window.CSRF_TOKEN || ''),
    }).then(function (r) { return r.json(); }).then(function (d) {
      if (d.success) {
        var el = document.getElementById('img-' + imageId);
        if (el) el.remove();
        existingCount = Math.max(0, existingCount - 1);
        updateImageUI();
      } else { alert(d.error || 'Could not delete image.'); }
    }).catch(function () { alert('Network error.'); });
  }

  /* ── Init ── */
  document.addEventListener('DOMContentLoaded', function () {
    existingCount = parseInt(document.getElementById('existingImageCount')?.value || '0', 10);

    /* Package change */
    var pkgSel = document.getElementById('packageSel');
    if (pkgSel) {
      pkgSel.addEventListener('change', onPackageChange);
      if (pkgSel.value) onPackageChange(); /* pre-fill on edit load */
    }

    /* Display type change */
    var dtSel = document.getElementById('displayTypeSel');
    if (dtSel) dtSel.addEventListener('change', onDisplayTypeChange);

    /* Prevent editing readonly fields from being flagged as manually edited */
    var amtEl = document.getElementById('paymentAmount');
    if (amtEl) amtEl.addEventListener('input', function () { amtEl.dataset.edited = '1'; });

    var untilEl = document.getElementById('validUntil');
    var fromEl  = document.getElementById('validFrom');
    if (fromEl) fromEl.addEventListener('change', function () {
      if (untilEl) untilEl.dataset.edited = '';
      onPackageChange();
    });

    /* Dropzone */
    var zone = document.getElementById('adDropzone');
    var inp  = document.getElementById('imgUpload');
    if (zone && inp) {
      zone.addEventListener('dragover',  function (e) { e.preventDefault(); zone.classList.add('dragover'); });
      zone.addEventListener('dragleave', function ()  { zone.classList.remove('dragover'); });
      zone.addEventListener('drop', function (e) {
        e.preventDefault(); zone.classList.remove('dragover');
        addFiles(Array.from(e.dataTransfer.files || []));
      });
    }

    updateImageUI();
  });

  return { deleteImage: deleteImage, previewImages: previewImages };
}());
