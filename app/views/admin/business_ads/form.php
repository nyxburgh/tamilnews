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

  <!-- LEFT COL: Business Info + Location + Display -->
  <div class="col-md-7">

    <!-- Business Details -->
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
          <label class="form-label fw-600">Internal Notes</label>
          <textarea name="notes" class="form-control" rows="2"
                    placeholder="Any notes about this ad..."><?= Helper::e($ad['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Location -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">📍 Business Location</div>
      <div class="tn-card-body">
        <div class="row g-2">
          <div class="col-sm-6">
            <label class="form-label fw-600">District</label>
            <select name="district_id" id="districtSel" class="form-select"
                    onchange="loadCities(this.value)">
              <option value="">-- Select District --</option>
              <?php foreach ($districts as $d): ?>
              <option value="<?= $d['id'] ?>" <?= ($ad['district_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                <?= Helper::e($d['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-600">City</label>
            <select name="city_id" id="citySel" class="form-select">
              <option value="">-- Select City --</option>
              <?php foreach ($cities as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($ad['city_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                <?= Helper::e($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Display Settings -->
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

        <div id="categoryRow" class="mb-0" style="<?= ($ad['display_type'] ?? 'global') === 'category' ? '' : 'display:none' ?>">
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

      </div>
    </div>

  </div>

  <!-- RIGHT COL: Validity + Payment + Images -->
  <div class="col-md-5">

    <!-- Validity -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">📅 Ad Validity</div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Start Date <span class="text-danger">*</span></label>
          <input type="date" name="valid_from" class="form-control" required
                 value="<?= $ad['valid_from'] ?? date('Y-m-d') ?>">
        </div>
        <div class="mb-0">
          <label class="form-label fw-600">End Date <span class="text-danger">*</span></label>
          <input type="date" name="valid_until" class="form-control" required
                 value="<?= $ad['valid_until'] ?? date('Y-m-d', strtotime('+30 days')) ?>">
          <div class="form-text">Ad displays between these dates after approval.</div>
        </div>
      </div>
    </div>

    <!-- Payment -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">💰 Payment</div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Amount (₹)</label>
          <input type="number" name="payment_amount" class="form-control" step="0.01" min="0"
                 value="<?= $ad['payment_amount'] ?? '' ?>" placeholder="0.00">
        </div>
        <?php if (\App\Core\Auth::can('approve_escalated') && $isEdit): ?>
        <div class="mb-3">
          <label class="form-label fw-600">Payment Status</label>
          <div class="d-flex gap-2 align-items-center">
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
          <input type="file" name="images[]" id="imgUpload" class="form-control"
                 accept="image/*" multiple
                 onchange="previewImages(this, <?= $maxMore ?>)">
          <div class="form-text">JPG, PNG, WebP — Max 2MB each</div>
          <div id="imgPreviews" class="d-flex flex-wrap gap-2 mt-2"></div>
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

<style>
.ad-img-thumb { position: relative; display: inline-block; }
.ad-img-del {
  position: absolute; top: -6px; right: -6px;
  width: 18px; height: 18px; border-radius: 50%;
  background: #EF4444; color: white; border: none;
  font-size: 10px; cursor: pointer; line-height: 1;
  display: flex; align-items: center; justify-content: center;
}
</style>

<script>
// Dynamic city loading
function loadCities(districtId) {
  const sel = document.getElementById('citySel');
  sel.innerHTML = '<option value="">Loading...</option>';
  if (!districtId) { sel.innerHTML = '<option value="">-- Select City --</option>'; return; }
  fetch(`<?= $r ?>/admin/business-ads/cities/${districtId}`)
    .then(r => r.json())
    .then(cities => {
      sel.innerHTML = '<option value="">-- Select City --</option>';
      cities.forEach(c => {
        sel.innerHTML += `<option value="${c.id}">${c.name}</option>`;
      });
    });
}

// Display type toggle
function toggleDisplayOptions(val) {
  document.getElementById('categoryRow').style.display = val === 'category' ? '' : 'none';
}

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
  const csrf = document.querySelector('input[name="csrf_token"]')?.value || '';
  fetch(`<?= $r ?>/admin/business-ads/delete-image/${imageId}`, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `ad_id=${adId}&csrf_token=${csrf}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('img-'+imageId)?.remove();
    }
  });
}


</script>
