<?php
use App\Core\{Helper, Auth, CSRF};
$isAdmin    = Auth::role() === 'admin';
$adsBase    = $isAdmin ? '/admin/business-ads' : '/portal/ads';
$existingCount = count($ad['images'] ?? []);
$maxMore    = max(0, 5 - $existingCount);

// Correct action URL per role — must use BASE_URL/public, not ASSET_URL ($r)
if ($isEdit) {
    $action = BASE_URL . '/public' . ($isAdmin ? '/admin/business-ads/update/' : '/portal/ads/edit/') . $ad['id'];
} else {
    $action = BASE_URL . '/public' . ($isAdmin ? '/admin/business-ads/store' : '/portal/ads/create');
}
?>

<div class="af-topbar">
  <a href="<?= BASE_URL ?>/public<?= $adsBase ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <div class="af-topbar-title">
    <?= $isEdit ? 'Edit Ad' : 'New Business Ad' ?>
    <?php if ($isEdit && !empty($ad['business_name'])): ?>
    <small class="af-topbar-meta"><?= Helper::e($ad['business_name']) ?></small>
    <?php endif; ?>
  </div>
  <button type="submit" form="adForm" class="af-topbar-save" title="Save all changes">
    <i class="bi bi-check-lg"></i>
    <span class="d-none d-sm-inline"><?= $isEdit ? 'Save All' : 'Submit' ?></span>
  </button>
</div>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" id="adForm">
<?= CSRF::field() ?>
<input type="hidden" id="maxMoreImages" value="<?= $maxMore ?>">
<input type="hidden" id="existingImageCount" value="<?= $existingCount ?>">
<input type="hidden" name="slot_id" id="slotIdInput" value="<?= (int)($ad['slot_id'] ?? 0) ?>">

<script>
window.ASSET_URL_BASE = '<?= rtrim(ASSET_URL, '/') ?>/public';
window.DELETE_IMAGE_URL = '<?= BASE_URL ?>/public<?= $isAdmin ? "/admin/business-ads" : "/portal/ads" ?>/delete-image/';
window.CSRF_TOKEN = '<?= CSRF::token() ?>';
// Packages with their slot mapping — selecting package sets slot automatically
window.AD_PACKAGES_ALL = <?= json_encode(array_map(fn($p) => [
    'id'         => $p['id'],
    'name'       => ($p['name_tamil'] ? $p['name_tamil'].' — ' : '') . $p['name'],
    'slot_type'  => $p['slot_type'] ?? 'any',
    'slot_id'    => $p['slot_id'] ?? null,
    'amount'     => $p['amount'] ?? $p['price_inr'] ?? 0,
    'days'       => $p['min_days'] ?? $p['duration_days'] ?? 30,
    'qr'         => $p['qr_code_path'] ?? '',
    'rate'       => $p['rate_per_day'] ?? 0,
], $packages)) ?>;
</script>

<div class="af-grid">

  <!-- ════ MAIN ════ -->
  <div class="af-col-main">

    <!-- Business Details -->
    <div class="af-card">
      <div class="af-card-head"><i class="bi bi-building me-2"></i>Business Details</div>
      <div class="af-card-body">
        <div class="mb-3">
          <label class="af-label">Business Name <span class="af-req">*</span></label>
          <input type="text" name="business_name" class="af-input af-input-title"
                 value="<?= Helper::e($ad['business_name'] ?? '') ?>"
                 placeholder="e.g. Sri Murugan Textiles" required>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="af-label">Phone <span class="af-req">*</span></label>
            <input type="tel" name="contact_phone" class="af-input" required
                   value="<?= Helper::e($ad['contact_phone'] ?? '') ?>" placeholder="+91 98765 43210">
          </div>
          <div class="col-6">
            <label class="af-label">Email <span class="af-req">*</span></label>
            <input type="email" name="contact_email" class="af-input" required
                   value="<?= Helper::e($ad['contact_email'] ?? '') ?>" placeholder="business@email.com">
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="af-label">District <small class="af-hint">optional — for targeted ads</small></label>
            <select name="district_id" class="af-select">
              <option value="">— All Districts (Sitewide) —</option>
              <?php foreach ($districts as $d): ?>
              <option value="<?= $d['id'] ?>" <?= ($ad['district_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                <?= Helper::e($d['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="af-label">Display Type</label>
            <select name="display_type" class="af-select" id="displayTypeSel">
              <?php foreach (['global'=>'🌐 Global','location'=>'📍 Location','category'=>'📂 Category'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($ad['display_type'] ?? 'global') === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mb-3" id="categoryRow" style="display:<?= ($ad['display_type'] ?? 'global') === 'category' ? '' : 'none' ?>">
          <label class="af-label">Category</label>
          <select name="category_id" class="af-select">
            <option value="">— Select Category —</option>
            <?php foreach ($categories as $cat): if ($cat['parent_id']) continue; ?>
            <option value="<?= $cat['id'] ?>" <?= ($ad['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
              <?= Helper::e($cat['name_tamil'] ?: $cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="af-label">Internal Notes</label>
          <textarea name="notes" class="af-textarea" rows="2"
                    placeholder="Staff notes..."><?= Helper::e($ad['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Package → automatically sets Ad Type/Slot -->
    <div class="af-card">
      <div class="af-card-head"><i class="bi bi-box-seam me-2"></i>Ad Package <small class="opacity-50 fw-400 ms-2">Package defines ad size &amp; duration</small></div>
      <div class="af-card-body">
        <div class="mb-3">
          <label class="af-label">Select Package <span class="af-req">*</span></label>
          <select name="package_id" id="packageSel" class="af-select" required>
            <option value="">— Select a Package —</option>
            <?php foreach ($packages as $p):
              $slotLabel = match($p['slot_type'] ?? 'any') {
                  'square'     => 'Square',
                  'horizontal' => 'Horizontal Banner',
                  'vertical'   => 'Vertical',
                  default      => 'Any'
              };
              $price = ($p['slot_type'] ?? '') === 'vertical'
                  ? '₹'.($p['rate_per_day'] ?? 0).'/day'
                  : '₹'.number_format((float)($p['amount'] ?? $p['price_inr'] ?? 0));
            ?>
            <option value="<?= $p['id'] ?>"
                    data-slot-id="<?= (int)($p['slot_id'] ?? 0) ?>"
                    data-slot-type="<?= htmlspecialchars($p['slot_type'] ?? 'any') ?>"
                    data-amount="<?= (float)($p['amount'] ?? $p['price_inr'] ?? 0) ?>"
                    data-days="<?= (int)($p['min_days'] ?? $p['duration_days'] ?? 30) ?>"
                    data-qr="<?= htmlspecialchars($p['qr_code_path'] ?? '') ?>"
                    <?= ($ad['package_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
              <?= Helper::e(($p['name_tamil'] ? $p['name_tamil'].' — ' : '') . $p['name']) ?>
              · <?= $slotLabel ?> · <?= $price ?> · <?= (int)($p['min_days'] ?? 30) ?> days
            </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text" id="pkgInfo"></div>
        </div>

        <!-- Auto-filled from package -->
        <div class="row g-2">
          <div class="col-6">
            <label class="af-label">Ad Type <small class="af-hint">auto from package</small></label>
            <input type="text" id="adTypeDisplay" class="af-input" readonly disabled placeholder="Select package first"
                   style="background:var(--gray-1,#F5F5F0);cursor:not-allowed;opacity:.75"
                   value="<?php
                     $slotTypes = ['square'=>'Square Ad','horizontal'=>'Horizontal Banner','vertical'=>'Vertical Ad'];
                     $curPkg = null;
                     foreach ($packages as $p) { if ($p['id'] == ($ad['package_id'] ?? '')) { $curPkg = $p; break; } }
                     echo $curPkg ? ($slotTypes[$curPkg['slot_type'] ?? ''] ?? ucfirst($curPkg['slot_type'] ?? '')) : '';
                   ?>">
          </div>
          <div class="col-6">
            <label class="af-label">Dates <small class="af-hint">end auto from package</small></label>
            <div class="d-flex gap-2">
              <input type="date" name="valid_from" id="validFrom" class="af-input" required
                     value="<?= $ad['valid_from'] ?? date('Y-m-d') ?>">
              <input type="date" name="valid_until" id="validUntil" class="af-input"
                     style="background:var(--gray-1,#F5F5F0);cursor:not-allowed;opacity:.75"
                     value="<?= $ad['valid_until'] ?? '' ?>" readonly>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Images -->
    <div class="af-card">
      <div class="af-card-head">
        <span><i class="bi bi-images me-2"></i>Ad Images</span>
        <small class="opacity-50 fw-400">Max 5 · <span id="imgCountLabel"><?= $existingCount ?> uploaded</span></small>
      </div>
      <div class="af-card-body">
        <div class="ad-img-strip mb-3" id="existingImages">
          <?php foreach ($ad['images'] ?? [] as $img): ?>
          <div class="ad-img-thumb" id="img-<?= $img['id'] ?>">
            <img src="<?= rtrim(ASSET_URL,'/') . '/public' . Helper::e($img['filepath']) ?>" alt="">
            <button type="button" class="ad-img-del"
                    onclick="AdForm.deleteImage(<?= $img['id'] ?>, <?= $ad['id'] ?? 0 ?>)">✕</button>
          </div>
          <?php endforeach; ?>
        </div>
        <div id="uploadSection" class="<?= $maxMore <= 0 ? 'd-none' : '' ?>">
          <div id="adDropzone" class="af-dropzone">
            <div class="af-dropzone-icon">🖼️</div>
            <div class="af-dropzone-label">Drag &amp; drop or click</div>
            <div class="af-dropzone-hint">JPG · PNG · WebP · Max 2MB each</div>
            <div class="af-dropzone-btns">
              <button type="button" class="af-dropzone-btn"
                      onclick="document.getElementById('imgUpload').click()">Browse</button>
            </div>
          </div>
          <input type="file" name="images[]" id="imgUpload" accept="image/*"
                 class="d-none" multiple onchange="AdForm.previewImages(this)">
          <div id="imgPreviews" class="ad-img-strip mt-2"></div>
        </div>
        <div id="maxReachedMsg" class="text-muted small <?= $maxMore > 0 ? 'd-none' : '' ?>">
          Maximum 5 images reached. Delete one to add new.
        </div>
      </div>
    </div>

  </div><!-- /main -->

  <!-- ════ SIDEBAR ════ -->
  <div class="af-col-side">

    <!-- Payment -->
    <div class="af-card">
      <div class="af-card-head"><i class="bi bi-cash-coin me-2"></i>Payment</div>
      <div class="af-card-body">
        <div class="mb-3">
          <label class="af-label">Amount (₹) <small class="af-hint">auto from package<?= $isAdmin ? ' · editable' : '' ?></small></label>
          <input type="number" name="payment_amount" id="paymentAmount" class="af-input"
                 step="0.01" min="0"
                 <?= (!$isAdmin && $isEdit && ($ad['payment_status'] ?? '') === 'confirmed') ? 'readonly style="background:var(--gray-1,#F5F5F0);cursor:not-allowed;opacity:.75"' : '' ?>
                 value="<?= $ad['payment_amount'] ?? '' ?>" placeholder="0.00">
        </div>

        <!-- QR Code (auto shown from package) -->
        <div id="qrBox" class="mb-3 text-center d-none">
          <div class="af-label mb-2">Scan to Pay</div>
          <div class="ad-qr-wrap"><img id="qrImg" src="" alt="QR" class="ad-qr-img"></div>
        </div>

        <div class="mb-3">
          <label class="af-label">Payment Note</label>
          <input type="text" name="payment_note" class="af-input"
                 value="<?= Helper::e($ad['payment_note'] ?? '') ?>"
                 placeholder="UPI ref / Cash / Bank transfer...">
        </div>

        <?php if ($isAdmin && $isEdit && ($ad['payment_status'] ?? '') !== 'confirmed'): ?>
        <div class="mb-3 p-3 rounded" style="background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.2)">
          <label class="d-flex align-items-center gap-2" style="cursor:pointer">
            <input type="checkbox" name="activate_now" value="1" class="form-check-input">
            <span class="small fw-600">✅ Confirm payment &amp; activate on save</span>
          </label>
        </div>
        <?php endif; ?>

        <?php if ($isEdit && ($ad['payment_status'] ?? '') === 'confirmed'): ?>
        <div class="mb-3"><span class="badge bg-success">✅ Payment Confirmed · Ad Live</span></div>
        <?php endif; ?>

        <button type="submit" class="af-submit">
          <i class="bi bi-<?= $isEdit ? 'save' : 'send' ?>"></i>
          <?= $isEdit ? 'Update Ad' : 'Submit Ad' ?>
        </button>
        <div class="form-text text-center mt-1" style="font-size:10px">
          Saves all fields including dates, images &amp; payment
        </div>
      </div>
    </div>

  </div><!-- /sidebar -->

</div>
</form>
<script src="<?= ASSET_URL ?>/public/assets/js/business-ad-form.js"></script>
