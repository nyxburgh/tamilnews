<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Ad Slots</h2>
</div>
<div class="row g-4">
  <?php foreach ($slots as $slot): ?>
  <div class="col-lg-6">
    <div class="tn-card">
      <div class="tn-card-header">
        <div>
          <span class="fw-600"><?= Helper::e($slot['name']) ?></span>
          <div class="text-muted small"><code><?= Helper::e($slot['position']) ?></code> · <?= Helper::e($slot['desktop_size']) ?> / <?= Helper::e($slot['mobile_size']) ?></div>
        </div>
        <span class="badge <?= $slot['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $slot['is_active'] ? 'Active' : 'Disabled' ?></span>
      </div>
      <div class="tn-card-body">
        <form action="<?= $r ?>/admin/ads/edit/<?= $slot['id'] ?>" method="POST">
          <?= CSRF::field() ?>
          <div class="mb-3">
            <label class="form-label">Ad Code <small class="text-muted">(HTML / AdSense snippet)</small></label>
            <textarea name="ad_code" class="form-control font-monospace" rows="5"
                      placeholder="<!-- Paste your ad code here -->"><?= Helper::e($slot['ad_code'] ?? '') ?></textarea>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active_<?= $slot['id'] ?>"
                     <?= $slot['is_active'] ? 'checked' : '' ?>>
              <label class="form-check-label" for="active_<?= $slot['id'] ?>">Enable slot</label>
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save me-1"></i>Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
