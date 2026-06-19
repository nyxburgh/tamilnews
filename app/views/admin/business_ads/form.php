<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title"><?= $isEdit ? '✏️ Edit Ad' : '📢 New Business Ad' ?></h2>
    <?php if ($isEdit): ?>
    <p class="tn-page-sub"><?= Helper::e($ad['business_name'] ?? '') ?></p>
    <?php endif; ?>
  </div>
  <a href="<?= $r ?>/admin/business-ads" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<form method="POST"
      action="<?= $r ?>/admin/business-ads/<?= $isEdit ? 'update/'.$ad['id'] : 'store' ?>"
      enctype="multipart/form-data"
      id="adForm">
<?= CSRF::field() ?>

<div class="row g-4">

  <!-- LEFT COL -->
  <div class="col-md-7">

    <!-- Business Details (includes District) -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">🏢 Business Details</div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Business Name <span class="text-danger">*</span></label>
          <input type="text" name="business_name" class="form-control"
                 value="<?= Helper::e($ad['business_name'] ?? '') ?>" required
                 placeholder="e.g. Sri Murugan Textiles">
        </div>
        <div class="row g-2">
          <div class="col-sm-6">
            <label class="form-label fw-600">Phone</label>
            <input type="tel" name="contact_phone" class="form-control"
                   value="<?= Helper::e($ad['contact_phone'] ?? '') ?>"
                   placeholder="+91 98765 43210">
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-600">Email</label>
            <input type="email" name="contact_email" class="form-control"
                   value="<?= Helper::e($ad['contact_email'] ?? '') ?>"
                   placeholder="business@email.com">
          </div>
        </div>
        <div class="mb-0 mt-3">
          <label class="form-label fw-600">District</label>
          <select name="district_id" id="districtSel" class="form-select">
            <option value="">-- Select District --</option>
            <?php foreach ($districts as $d): ?>
            <option value="<?= $d['id'] ?>" <?= ($ad['district_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
              <?= Helper::e($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-0 mt-3">
          <label class="form-label fw-600">Internal Notes</label>
          <textarea name="notes" class="form-control" rows="2"
                    placeholder="Any notes about this ad..."><?= Helper::e($ad['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Display Settings (includes Package + Validity) -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">🎯 Display Settings</div>
      <div class="tn-card-body">

        <div class="mb-3">
          <label class="form-label fw-600">Ad Type <span class="text-danger">*</span></label>
          <div class="d-flex gap-3">
            <?php foreach ($slots as $slot): ?>
            <label class="slot-pick <?= ($ad['slot_id'] ?? '') == $slot['id'] ? 'active' : '' ?>"
                   style="flex:1;border:2px solid var(--card-border,#dee2e6);border-radius:8px;padding:12px;cursor:pointer;text-align:center;transition:all .15s"
                   onclick="this.parentElement.querySelectorAll('.slot-pick').forEach(x=>x.classList.remove('active'));this.classList.add('active');document.getElementById('slotIdInput').value='<?= $slot['id'] ?>'">
              <input type="radio" name="_slot_display" style="display:none"
                     <?= ($ad['slot_id'] ?? '') == $slot['id'] ? 'checked' : '' ?>>
              <?php if ($slot['type'] === 'square'): ?>
              <div style="width:50px;height:50px;background:var(--portal-gray1,#f0f0f0);border:1px solid var(--portal-gray2,#ccc);border-radius:4px;margin:0 auto 6px"></div>
              <?php else: ?>
              <div style="width:80px;height:22px;background:var(--portal-gray1,#f0f0f0);border:1px solid var(--portal-gray2,#ccc);border-radius:4px;margin:0 auto 6px"></div>
              <?php endif; ?>
              <div style="font-weight:700;font-size:13px"><?= Helper::e($slot['name']) ?></div>
              <div style="font-size:11px;color:var(--portal-muted,#6b7280)"><?= Helper::e($slot['desktop_size']) ?></div>
            </label>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="slot_id" id="slotIdInput"
                 value="<?= (int)($ad['slot_id'] ?? 1) ?>" required>
          <style>
            .slot-pick.active { border-color:#C0001A !important; background:rgba(192,0,26,.05); }
          </style>
        </div>

        <div class="mb-3">
          <label class="form-label fw-600">Display Type <span class="text-danger">*</span></label>
          <div class="d-flex gap-3 flex-wrap">
            <?php foreach (['global'=>'🌐 Global (all pages)','location'=>'📍 Location-based','category'=>'📂 Category-based'] as $val => $label): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="display_type"
                     id="dt_<?= $val ?>" value="<?= $val ?>"
                     onchange="toggleDisplayOptions(this.value)"
                     <?= ($ad['display_type'] ?? 'global') === $val ? 'checked' : '' ?>>
              <label class="form-check-label" for="dt_<?= $val ?>"><?= $label ?></label>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="form-text">
            Global → shows everywhere · Location → shows only in articles from that district · Category → shows only in that category section
          </div>
        </div>

        <div id="categoryRow" class="mb-3" style="<?= ($ad['display_type'] ?? 'global') === 'category' ? '' : 'display:none' ?>">
          <label class="form-label fw-600">Category</label>
          <select name="category_id" class="form-select">
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
            <?php if ($cat['parent_id']) continue; ?>
            <option value="<?= $cat['id'] ?>" <?= ($ad['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
              <?= Helper::e($cat['name_tamil'] ?: $cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <hr class="my-3">

        <!-- Package selection -->
        <div class="mb-3">
          <label class="form-label fw-600">Ad Package <span class="text-danger">*</span></label>
          <?php if (empty($packages)): ?>
          <div class="alert alert-warning py-2 mb-0" style="font-size:12px">
            No active packages found. Ask admin to create one in <strong>Ad Packages</strong>.
          </div>
          <?php else: ?>
          <select name="package_id" id="packageSel" class="form-select" required
                  onchange="onPackageChange()">
            <option value="">-- Select Package --</option>
            <?php foreach ($packages as $p): ?>
            <option value="<?= $p['id'] ?>"
                    data-price="<?= $p['price_inr'] ?>"
                    data-days="<?= $p['duration_days'] ?>"
                    data-qr="<?= Helper::e($p['qr_code_path'] ?? '') ?>"
                    <?= ($ad['package_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
              <?= Helper::e($p['name']) ?> — ₹<?= number_format($p['price_inr'],0) ?> / <?= $p['duration_days'] ?> days
            </option>
            <?php endforeach; ?>
          </select>
          <?php endif; ?>
        </div>

        <div class="row g-2">
          <div class="col-sm-6">
            <label class="form-label fw-600">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="valid_from" id="validFrom" class="form-control" required
                   value="<?= $ad['valid_from'] ?? date('Y-m-d') ?>"
                   onchange="recalcValidUntil()">
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-600">End Date <span class="text-danger">*</span></label>
            <input type="date" name="valid_until" id="validUntil" class="form-control" required
                   value="<?= $ad['valid_until'] ?? date('Y-m-d', strtotime('+30 days')) ?>" readonly>
          </div>
        </div>
        <div class="form-text">End date is set automatically from the selected package's duration.</div>

      </div>
    </div>

  </div>

  <!-- RIGHT COL: Payment + Images -->
  <div class="col-md-5">

    <!-- Payment -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">💰 Payment</div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Amount (₹)</label>
          <input type="number" name="payment_amount" id="paymentAmount" class="form-control" step="0.01" min="0"
                 value="<?= $ad['payment_amount'] ?? '' ?>" placeholder="0.00" readonly>
          <div class="form-text">Set automatically from the selected package.</div>
        </div>

        <!-- QR code shown when package selected -->
        <div id="qrBox" class="mb-3 text-center" style="<?= !empty($ad['package_id'] ?? null) ? '' : 'display:none' ?>">
          <div style="font-size:12px;font-weight:600;margin-bottom:6px">Scan to Pay</div>
          <img id="qrImg" src="" alt="Payment QR"
               style="width:160px;height:160px;object-fit:contain;border:1px solid var(--card-border,#dee2e6);border-radius:8px;background:#fff;padding:8px">
        </div>

        <?php if (\App\Core\Auth::can('approve_escalated') && $isEdit): ?>
        <div class="mb-3">
          <label class="form-label fw-600">Payment Status</label>
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="badge <?= $ad['payment_status'] === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' ?>">
              <?= ucfirst($ad['payment_status'] ?? 'pending') ?>
            </span>
            <?php if ($ad['payment_status'] !== 'confirmed'): ?>
            <form method="POST" action="<?= $r ?>/admin/business-ads/confirm-payment/<?= $ad['id'] ?>" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-success">✓ Confirm Payment</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="mb-0">
          <label class="form-label fw-600">Payment Note</label>
          <input type="text" name="payment_note" class="form-control"
                 value="<?= Helper::e($ad['payment_note'] ?? '') ?>"
                 placeholder="UPI / Cash / Bank transfer ref...">
        </div>
      </div>
    </div>

    <!-- Images -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">
        🖼️ Ad Images
        <span class="ms-2" style="font-size:11px;color:var(--text-muted);font-weight:400">
          Max 5 images · Optional
        </span>
      </div>
      <div class="tn-card-body">

        <!-- Existing images -->
        <?php if (!empty($ad['images'])): ?>
        <div class="mb-3">
          <div style="font-size:12px;font-weight:600;margin-bottom:8px">Current Images:</div>
          <div class="d-flex flex-wrap gap-2" id="existingImages">
            <?php foreach ($ad['images'] as $img): ?>
            <div class="ad-img-thumb" id="img-<?= $img['id'] ?>">
              <img src="<?= ASSET_URL ?><?= Helper::e($img['filepath']) ?>"
                   style="width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid var(--card-border)">
              <button type="button" class="ad-img-del"
                      onclick="deleteImage(<?= $img['id'] ?>, <?= $ad['id'] ?>)" title="Remove">✕</button>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Upload new -->
        <?php
        $existingCount = count($ad['images'] ?? []);
        $maxMore       = 5 - $existingCount;
        ?>
        <?php if ($maxMore > 0): ?>
        <div>
          <label class="form-label fw-600 small">
            Add Images
            <?php if ($existingCount > 0): ?>
            <span class="text-muted">(<?= $maxMore ?> more allowed)</span>
            <?php endif; ?>
          </label>

          <!-- Drag & drop zone -->
          <div id="adDropzone" class="ad-dropzone">
            <i class="bi bi-cloud-arrow-up" style="font-size:28px;color:#9A9890"></i>
            <div style="font-size:13px;font-weight:600;margin-top:6px">Drag &amp; drop images here</div>
            <div style="font-size:11px;color:#9A9890;margin:2px 0 10px">or click to browse · JPG, PNG, WebP — Max 2MB each<br>Square ads auto-resize to 300×150 / 600×300 / 900×450</div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('imgUpload').click()">
              <i class="bi bi-folder2-open me-1"></i> Browse Files
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="openAdMediaLibrary()">
              <i class="bi bi-images me-1"></i> Media Library
            </button>
          </div>
          <input type="file" name="images[]" accept="image/*" id="imgUpload" class="d-none"
                 multiple
                 onchange="previewImages(this, <?= $maxMore ?>)">

          <div id="imgPreviews" class="d-flex flex-wrap gap-2 mt-2"></div>

          <!-- Images picked from media library — submitted as JSON -->
          <input type="hidden" name="existing_media" id="existingMediaInput" value="">
          <div id="libraryPreviews" class="d-flex flex-wrap gap-2 mt-2"></div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning py-2 mb-0" style="font-size:12px">
          Maximum 5 images reached. Delete an existing image to add new ones.
        </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Submit -->
    <div class="d-grid gap-2">
      <button type="submit" class="btn btn-primary btn-lg">
        <i class="bi bi-<?= $isEdit ? 'save' : 'send' ?> me-2"></i>
        <?= $isEdit ? 'Update Ad' : 'Submit Ad for Approval' ?>
      </button>
      <?php if (!$isEdit): ?>
      <div style="font-size:12px;color:var(--text-muted);text-align:center">
        Ad will be reviewed by Chief Editor before going live
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>
</form>

<!-- Media Library picker modal (ad images) -->
<div id="adMediaModal" class="ad-media-modal-overlay" onclick="closeAdMediaLibrary(event)">
  <div class="ad-media-modal" onclick="event.stopPropagation()">
    <div class="ad-media-modal-header">
      <span>📁 Choose from Media Library</span>
      <button type="button" onclick="closeAdMediaLibrary()">✕</button>
    </div>
    <div id="adMediaModalBody" class="ad-media-modal-body">Loading…</div>
    <div class="ad-media-modal-footer">
      <span id="adMediaSelCount" style="font-size:12px;color:var(--text-muted)">0 selected</span>
      <button type="button" class="btn btn-primary btn-sm" onclick="confirmAdMediaSelection()">Add Selected</button>
    </div>
  </div>
</div>

<style>
.ad-img-thumb { position: relative; display: inline-block; }
.ad-img-del {
  position: absolute; top: -6px; right: -6px;
  width: 18px; height: 18px; border-radius: 50%;
  background: #EF4444; color: white; border: none;
  font-size: 10px; cursor: pointer; line-height: 1;
  display: flex; align-items: center; justify-content: center;
}

/* Dropzone */
.ad-dropzone {
  border: 2px dashed var(--card-border, #D8D6CE);
  border-radius: 10px;
  padding: 22px 16px;
  text-align: center;
  cursor: pointer;
  background: var(--portal-gray1, #FAFAF8);
  transition: border-color .15s, background .15s;
}
.ad-dropzone.dragover {
  border-color: #C0001A;
  background: rgba(192,0,26,.04);
}

/* Media library modal */
.ad-media-modal-overlay {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,.6);
  z-index: 2000;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.ad-media-modal-overlay.open { display: flex; }
.ad-media-modal {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 640px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.ad-media-modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px;
  font-weight: 700; font-size: 14px;
  border-bottom: 1px solid var(--card-border, #E5E3DC);
}
.ad-media-modal-header button {
  background: none; border: none; font-size: 16px; cursor: pointer; color: var(--text-muted, #6B6A64);
}
.ad-media-modal-body { padding: 14px 16px; overflow-y: auto; flex: 1; }
.ad-media-modal-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 16px;
  border-top: 1px solid var(--card-border, #E5E3DC);
}
.ad-media-pick-item {
  position: relative;
  display: inline-block;
  width: 80px; height: 60px;
  margin: 4px;
  border-radius: 6px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
}
.ad-media-pick-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ad-media-pick-item.selected { border-color: #C0001A; }
.ad-media-pick-item.selected::after {
  content: '✓';
  position: absolute; top: 2px; right: 2px;
  width: 18px; height: 18px;
  background: #C0001A; color: #fff;
  border-radius: 50%;
  font-size: 11px;
  display: flex; align-items: center; justify-content: center;
}
</style>

<script>
const ASSET_URL_BASE = '<?= ASSET_URL ?>';

// Drag & drop support — forwards dropped files into the hidden file input
(function() {
  const zone = document.getElementById('adDropzone');
  const input = document.getElementById('imgUpload');
  if (!zone || !input) return;
  const maxMore = <?= $maxMore ?? 0 ?>;

  zone.addEventListener('click', function(e) {
    if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) input.click();
  });
  zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
  zone.addEventListener('drop', function(e) {
    e.preventDefault();
    zone.classList.remove('dragover');
    if (!e.dataTransfer.files.length) return;
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).slice(0, maxMore).forEach(f => dt.items.add(f));
    input.files = dt.files;
    previewImages(input, maxMore);
  });
})();

// ── Media Library picker ──
let adMediaSelected = []; // [{filepath, alt}]

function openAdMediaLibrary() {
  document.getElementById('adMediaModal').classList.add('open');
  loadAdMediaPage(1);
}
function closeAdMediaLibrary(e) {
  if (e && e.target.id !== 'adMediaModal') return;
  document.getElementById('adMediaModal').classList.remove('open');
}

function loadAdMediaPage(page) {
  fetch(`<?= $r ?>/admin/media/modal?page=${page}`)
    .then(r => r.text())
    .then(html => {
      const tmp = document.createElement('div');
      tmp.innerHTML = html;
      const items = tmp.querySelectorAll('.tn-media-selectable');
      const body = document.getElementById('adMediaModalBody');
      body.innerHTML = '';
      if (!items.length) {
        body.innerHTML = '<div class="text-center text-muted py-4">No images found</div>';
        return;
      }
      items.forEach(it => {
        const img = it.querySelector('img');
        if (!img) return;
        const filepath = img.getAttribute('src');
        const div = document.createElement('div');
        div.className = 'ad-media-pick-item';
        div.dataset.filepath = filepath;
        div.innerHTML = `<img src="${filepath}" alt="">`;
        div.onclick = function() { toggleAdMediaSelect(div, filepath); };
        body.appendChild(div);
      });
    });
}

function toggleAdMediaSelect(el, filepath) {
  const idx = adMediaSelected.findIndex(m => m.filepath === filepath);
  const maxMore = <?= $maxMore ?? 0 ?>;
  if (idx > -1) {
    adMediaSelected.splice(idx, 1);
    el.classList.remove('selected');
  } else {
    if (adMediaSelected.length >= maxMore) {
      alert(`You can only add ${maxMore} more image(s).`);
      return;
    }
    adMediaSelected.push({ filepath: filepath, alt: '' });
    el.classList.add('selected');
  }
  document.getElementById('adMediaSelCount').textContent = adMediaSelected.length + ' selected';
}

function confirmAdMediaSelection() {
  document.getElementById('existingMediaInput').value = JSON.stringify(adMediaSelected);
  const box = document.getElementById('libraryPreviews');
  box.innerHTML = '';
  adMediaSelected.forEach(m => {
    const img = document.createElement('img');
    img.src = m.filepath;
    img.style.cssText = 'width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ccc';
    box.appendChild(img);
  });
  closeAdMediaLibrary();
}

// Display type toggle
function toggleDisplayOptions(val) {
  document.getElementById('categoryRow').style.display = val === 'category' ? '' : 'none';
}

// Package selection → auto-set validity, amount, QR
function onPackageChange() {
  const sel = document.getElementById('packageSel');
  const opt = sel.options[sel.selectedIndex];
  if (!opt || !opt.value) {
    document.getElementById('qrBox').style.display = 'none';
    return;
  }
  const price = opt.dataset.price || '0';
  const days  = parseInt(opt.dataset.days || '30', 10);
  const qr    = opt.dataset.qr || '';

  document.getElementById('paymentAmount').value = price;
  recalcValidUntil(days);

  const qrBox = document.getElementById('qrBox');
  const qrImg = document.getElementById('qrImg');
  if (qr) {
    qrImg.src = ASSET_URL_BASE + qr;
    qrBox.style.display = '';
  } else {
    qrBox.style.display = 'none';
  }
}

// Recalculate end date from start date + package duration
function recalcValidUntil(days) {
  if (days === undefined) {
    const sel = document.getElementById('packageSel');
    const opt = sel ? sel.options[sel.selectedIndex] : null;
    days = opt && opt.value ? parseInt(opt.dataset.days || '30', 10) : 30;
  }
  const startVal = document.getElementById('validFrom').value;
  if (!startVal) return;
  const start = new Date(startVal);
  start.setDate(start.getDate() + days);
  const yyyy = start.getFullYear();
  const mm   = String(start.getMonth() + 1).padStart(2, '0');
  const dd   = String(start.getDate()).padStart(2, '0');
  document.getElementById('validUntil').value = `${yyyy}-${mm}-${dd}`;
}

// On load: if editing with a package already selected, show its QR
document.addEventListener('DOMContentLoaded', function() {
  const sel = document.getElementById('packageSel');
  if (sel && sel.value) onPackageChange();
});

// Image preview
function previewImages(input, max) {
  const box   = document.getElementById('imgPreviews');
  const files = Array.from(input.files).slice(0, max);
  box.innerHTML = '';
  files.forEach(file => {
    const img = document.createElement('img');
    img.style.cssText = 'width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ccc';
    img.src = URL.createObjectURL(file);
    box.appendChild(img);
  });
  if (input.files.length > max) {
    alert(`Maximum ${max} images allowed. Only first ${max} will be uploaded.`);
  }
}

// Delete existing image
function deleteImage(imageId, adId) {
  if (!confirm('Remove this image?')) return;
  const csrf = document.querySelector('input[name="_token"]')?.value
             || document.querySelector('input[name="csrf_token"]')?.value || '';
  fetch(`<?= $r ?>/admin/business-ads/delete-image/${imageId}`, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `ad_id=${adId}&_token=${csrf}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('img-'+imageId)?.remove();
    }
  });
}
</script>
