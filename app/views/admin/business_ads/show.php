<?php use App\Core\{Helper, Auth, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📢 <?= Helper::e($ad['business_name']) ?></h2>
    <?php
    $sColors = ['pending'=>'#F59E0B','approved'=>'#3B82F6','active'=>'#10B981','rejected'=>'#EF4444','expired'=>'#6B7280','paused'=>'#8B5CF6'];
    $sColor  = $sColors[$ad['status']] ?? '#9CA3AF';
    ?>
    <span style="background:<?= $sColor ?>;color:#fff;font-size:11px;font-weight:700;padding:3px 12px;border-radius:12px">
      <?= strtoupper($ad['status']) ?>
    </span>
  </div>
  <div class="d-flex gap-2">
    <?php if ($canEdit): ?>
    <a href="<?= $r ?>/admin/business-ads/edit/<?= $ad['id'] ?>" class="btn btn-outline-secondary">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <?php endif; ?>
    <a href="<?= $r ?>/admin/business-ads" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i> Back
    </a>
  </div>
</div>

<div class="row g-4">

  <!-- LEFT: Details + Images -->
  <div class="col-md-8">

    <!-- Images -->
    <?php if (!empty($ad['images'])): ?>
    <div class="tn-card mb-4">
      <div class="tn-card-header">🖼️ Ad Images</div>
      <div class="tn-card-body">
        <div class="d-flex flex-wrap gap-3">
          <?php foreach ($ad['images'] as $img): ?>
          <div style="position:relative">
            <img src="<?= ASSET_URL ?><?= Helper::e($img['filepath']) ?>"
                 style="width:160px;height:110px;object-fit:cover;border-radius:6px;border:1px solid var(--card-border)"
                 alt="<?= Helper::e($img['alt_text'] ?? '') ?>">
            <?php if ($img['link_url']): ?>
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              🔗 <?= Helper::e($img['link_url']) ?>
            </div>
            <?php endif; ?>
            <?php if ($canEdit): ?>
            <button type="button"
                    style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:#EF4444;color:#fff;border:none;font-size:11px;cursor:pointer"
                    onclick="deleteImage(<?= $img['id'] ?>, <?= $ad['id'] ?>)">✕</button>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if ($canEdit && count($ad['images']) < 5): ?>
        <div class="mt-3">
          <a href="<?= $r ?>/admin/business-ads/edit/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-plus me-1"></i> Add More Images
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="tn-card mb-4">
      <div class="tn-card-body text-center py-4">
        <div style="font-size:36px">🖼️</div>
        <p class="text-muted small">No images uploaded yet.</p>
        <?php if ($canEdit): ?>
        <a href="<?= $r ?>/admin/business-ads/edit/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary">
          Upload Images
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Display Preview -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">🎯 Display Configuration</div>
      <div class="tn-card-body">
        <div class="row g-3">
          <div class="col-sm-4">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Ad Slot</div>
            <div style="font-weight:600"><?= Helper::e($ad['slot_name']) ?></div>
            <?php if ($ad['desktop_size']): ?><div style="font-size:11px;color:var(--text-muted)"><?= Helper::e($ad['desktop_size']) ?></div><?php endif; ?>
          </div>
          <div class="col-sm-4">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Display Type</div>
            <?php
            $dtIcons  = ['global'=>'🌐','location'=>'📍','category'=>'📂'];
            $dtLabels = ['global'=>'Global — all pages','location'=>'Location-based','category'=>'Category-based'];
            ?>
            <div style="font-weight:600"><?= ($dtIcons[$ad['display_type']] ?? '').' '.($dtLabels[$ad['display_type']] ?? '') ?></div>
            <?php if ($ad['display_type'] === 'location' && $ad['district_name']): ?>
            <div style="font-size:12px;color:var(--text-muted)"><?= Helper::e($ad['district_name']) ?><?= $ad['city_name'] ? ' › '.$ad['city_name'] : '' ?></div>
            <?php endif; ?>
            <?php if ($ad['display_type'] === 'category' && $ad['category_name']): ?>
            <div style="font-size:12px;color:var(--text-muted)"><?= Helper::e($ad['category_name']) ?></div>
            <?php endif; ?>
          </div>
          <div class="col-sm-4">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Validity</div>
            <div style="font-weight:600"><?= date('d M Y', strtotime($ad['valid_from'])) ?></div>
            <div style="font-size:12px;color:var(--text-muted)">to <?= date('d M Y', strtotime($ad['valid_until'])) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="tn-card">
      <div class="tn-card-header">📊 Performance</div>
      <div class="tn-card-body">
        <div class="row g-3 text-center">
          <div class="col-4">
            <div style="font-size:28px;font-weight:700;color:var(--accent)"><?= number_format($ad['impression_count']) ?></div>
            <div style="font-size:11px;color:var(--text-muted)">Impressions</div>
          </div>
          <div class="col-4">
            <div style="font-size:28px;font-weight:700;color:#10B981"><?= number_format($ad['click_count']) ?></div>
            <div style="font-size:11px;color:var(--text-muted)">Clicks</div>
          </div>
          <div class="col-4">
            <div style="font-size:28px;font-weight:700;color:#F59E0B">
              <?= $ad['impression_count'] > 0 ? round(($ad['click_count']/$ad['impression_count'])*100,2) : 0 ?>%
            </div>
            <div style="font-size:11px;color:var(--text-muted)">CTR</div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- RIGHT: Actions + Info -->
  <div class="col-md-4">

    <!-- Approval Actions -->
    <?php if ($canApprove): ?>
    <div class="tn-card mb-4">
      <div class="tn-card-header">⚡ Actions</div>
      <div class="tn-card-body">

        <?php if ($ad['status'] === 'pending'): ?>
        <form method="POST" action="<?= $r ?>/admin/business-ads/approve/<?= $ad['id'] ?>" class="mb-2">
          <?= CSRF::field() ?>
          <button class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i> Approve Ad</button>
        </form>
        <button class="btn btn-outline-danger w-100" onclick="showRejectModal()">
          <i class="bi bi-x-circle me-1"></i> Reject
        </button>
        <?php elseif ($ad['status'] === 'approved'): ?>
        <div class="alert alert-info py-2 mb-2 small">Approved. Will activate when payment is confirmed.</div>
        <?php elseif ($ad['status'] === 'active'): ?>
        <div class="alert alert-success py-2 mb-0 small">✅ Ad is currently live on the website.</div>
        <?php endif; ?>

        <!-- Payment confirm -->
        <?php if ($ad['payment_status'] !== 'confirmed'): ?>
        <hr>
        <form method="POST" action="<?= $r ?>/admin/business-ads/confirm-payment/<?= $ad['id'] ?>">
          <?= CSRF::field() ?>
          <label class="form-label small fw-600">Payment Note</label>
          <input type="text" name="payment_note" class="form-control form-control-sm mb-2"
                 placeholder="UPI ref / receipt no...">
          <button class="btn btn-warning w-100 btn-sm">💰 Confirm Payment</button>
        </form>
        <?php else: ?>
        <div class="alert alert-success py-2 mt-2 mb-0 small">✅ Payment confirmed</div>
        <?php endif; ?>

      </div>
    </div>
    <?php endif; ?>

    <!-- Info Card -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">ℹ️ Info</div>
      <div class="tn-card-body">
        <div class="ad-info-row"><span>Submitted by</span><strong><?= Helper::e($ad['submitted_by_name'] ?? '—') ?></strong></div>
        <div class="ad-info-row"><span>Submitted</span><strong><?= Helper::timeAgo($ad['created_at']) ?></strong></div>
        <?php if ($ad['approved_by']): ?>
        <div class="ad-info-row"><span>Reviewed</span><strong><?= Helper::timeAgo($ad['approved_at']) ?></strong></div>
        <?php endif; ?>
        <?php if ($ad['payment_amount']): ?>
        <div class="ad-info-row"><span>Amount</span><strong>₹<?= number_format($ad['payment_amount'],2) ?></strong></div>
        <?php endif; ?>
        <div class="ad-info-row">
          <span>Payment</span>
          <strong class="<?= $ad['payment_status']==='confirmed' ? 'text-success' : 'text-warning' ?>">
            <?= ucfirst($ad['payment_status']) ?>
          </strong>
        </div>
        <?php if ($ad['notes']): ?>
        <div class="ad-info-row"><span>Notes</span><span><?= Helper::e($ad['notes']) ?></span></div>
        <?php endif; ?>
        <?php if ($ad['rejection_reason']): ?>
        <div class="alert alert-danger py-2 mt-2 mb-0 small">
          <strong>Rejection reason:</strong> <?= Helper::e($ad['rejection_reason']) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Contact -->
    <?php if ($ad['contact_phone'] || $ad['contact_email']): ?>
    <div class="tn-card">
      <div class="tn-card-header">📞 Contact</div>
      <div class="tn-card-body">
        <?php if ($ad['contact_phone']): ?><div class="mb-1"><i class="bi bi-telephone me-2"></i><?= Helper::e($ad['contact_phone']) ?></div><?php endif; ?>
        <?php if ($ad['contact_email']): ?><div><i class="bi bi-envelope me-2"></i><?= Helper::e($ad['contact_email']) ?></div><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST" action="<?= $r ?>/admin/business-ads/reject/<?= $ad['id'] ?>">
    <?= CSRF::field() ?>
    <div class="modal-header"><h5 class="modal-title">Reject Ad</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">Reason <small class="text-muted">(optional — will be sent to submitter)</small></label>
      <textarea name="reason" class="form-control" rows="3" placeholder="Reason for rejection..."></textarea>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button type="submit" class="btn btn-danger">Reject Ad</button>
    </div>
  </form>
</div></div></div>

<style>
.ad-info-row { display:flex; justify-content:space-between; align-items:flex-start; padding:6px 0; border-bottom:1px solid var(--card-border,rgba(255,255,255,.07)); font-size:12px; }
.ad-info-row:last-child { border-bottom:none; }
.ad-info-row span:first-child { color:var(--text-muted,#6b7280); flex-shrink:0; }
</style>

<script>
function showRejectModal() {
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
function deleteImage(imageId, adId) {
  if (!confirm('Remove this image?')) return;
  const csrf = document.querySelector('input[name="csrf_token"]')?.value || '';
  fetch(`<?= $r ?>/admin/business-ads/delete-image/${imageId}`, {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`ad_id=${adId}&csrf_token=${csrf}`
  }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
</script>
