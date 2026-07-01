<?php
use App\Core\{Helper, Auth, CSRF};

$role      = Auth::role();
$isAdmin   = $role === 'admin';
$adsBase   = $isAdmin ? '/admin/business-ads' : '/portal/ads';
$canManage = in_array($role, ['admin','chief_editor']);

$statusColors = [
    'pending'  => ['bg'=>'#FEF3C7','color'=>'#92400E','label'=>'Pending Review'],
    'approved' => ['bg'=>'#DBEAFE','color'=>'#1E40AF','label'=>'Approved'],
    'active'   => ['bg'=>'#D1FAE5','color'=>'#065F46','label'=>'Active · Live'],
    'rejected' => ['bg'=>'#FEE2E2','color'=>'#991B1B','label'=>'Rejected'],
    'expired'  => ['bg'=>'#F3F4F6','color'=>'#374151','label'=>'Expired'],
];
$sc = $statusColors[$ad['status'] ?? 'pending'] ?? $statusColors['pending'];

// Load owner user
$ownerUser = null;
if (!empty($ad['owner_user_id'])) {
    try {
        $stmt = \App\Core\Database::getInstance()
            ->prepare("SELECT id,name,email FROM tn_users WHERE id=? LIMIT 1");
        $stmt->execute([(int)$ad['owner_user_id']]);
        $ownerUser = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    } catch (\Exception $e) {}
}

// Current package
$packages = (new \App\Models\AdPackageModel())->active();
$pkgMap   = [];
foreach ($packages as $p) $pkgMap[$p['id']] = $p;
$curPkg   = !empty($ad['package_id']) ? ($pkgMap[$ad['package_id']] ?? null) : null;
?>

<!-- TOPBAR -->
<div class="af-topbar">
  <a href="<?= $r . $adsBase ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <div class="af-topbar-title">
    <?= Helper::e($ad['business_name']) ?>
    <span class="ad-status-pill" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>">
      <?= $sc['label'] ?>
    </span>
  </div>
  <?php if ($canManage || Auth::id() == ($ad['submitted_by'] ?? 0)): ?>
  <a href="<?= $r . $adsBase ?>/edit/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-pencil"></i> Edit
  </a>
  <?php endif; ?>
</div>

<!-- ═══ CARD 1: AD PREVIEW ═══ -->
<div class="tn-card mb-3">

  <!-- Images -->
  <?php if (!empty($ad['images'])): ?>
  <div class="ad-img-strip p-3" id="existingImages">
    <?php foreach ($ad['images'] as $img): ?>
    <div class="ad-img-thumb" id="img-<?= $img['id'] ?>">
      <img src="<?= rtrim(ASSET_URL,'/') . '/public' . Helper::e($img['filepath']) ?>" alt="" loading="lazy">
      <?php if ($canManage): ?>
      <button type="button" class="ad-img-del"
              onclick="delImg(<?= $img['id'] ?>, <?= $ad['id'] ?>)">✕</button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Details grid -->
  <div class="tn-card-body">
    <div class="ad-info-grid">
      <div class="ad-info-row"><span>Package</span>
        <strong><?= $curPkg ? Helper::e($curPkg['name']) : '—' ?></strong></div>
      <div class="ad-info-row"><span>Ad Type</span>
        <strong><?= Helper::e($ad['slot_name'] ?? $ad['slot_type'] ?? '—') ?></strong></div>
      <div class="ad-info-row"><span>District</span>
        <strong><?= Helper::e($ad['district_name'] ?? 'All Districts') ?></strong></div>
      <div class="ad-info-row"><span>Valid</span>
        <strong><?= ($ad['valid_from'] ?? '—') . ' → ' . ($ad['valid_until'] ?? '—') ?></strong></div>
      <div class="ad-info-row"><span>Phone</span>
        <strong><?= Helper::e($ad['contact_phone'] ?? '—') ?></strong></div>
      <div class="ad-info-row"><span>Email</span>
        <strong><?= Helper::e($ad['contact_email'] ?? '—') ?></strong></div>
      <div class="ad-info-row"><span>Submitted by</span>
        <strong><?= Helper::e($ad['submitted_by_name'] ?? '—') ?></strong></div>
      <div class="ad-info-row"><span>Impressions / Clicks</span>
        <strong><?= number_format($ad['impression_count'] ?? 0) ?> / <?= number_format($ad['click_count'] ?? 0) ?></strong></div>
      <?php if (!empty($ad['notes'])): ?>
      <div class="ad-info-row"><span>Notes</span>
        <strong><?= Helper::e($ad['notes']) ?></strong></div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ═══ CARD 2: ACTIVATE (admin/chief, pending/approved only) ═══ -->
<?php if ($canManage && in_array($ad['status'] ?? '', ['pending','approved'])): ?>
<div class="tn-card mb-3" style="border-left:4px solid #10B981">
  <div class="tn-card-header" style="color:#065F46">
    <i class="bi bi-check-circle me-2"></i>Activate Ad
    <small class="opacity-60 fw-400 ms-2">Confirms payment and makes ad live immediately</small>
  </div>
  <div class="tn-card-body">
    <form method="POST" action="<?= $r . ($isAdmin ? '/admin/business-ads/confirm-payment/' : '/portal/ads/activate/') . $ad['id'] ?>">
      <?= CSRF::field() ?>
      <div class="row g-2 align-items-end">
        <div class="col-sm-4">
          <label class="form-label small fw-600">Amount Received (₹)</label>
          <input type="number" name="payment_amount" class="form-control form-control-sm"
                 step="0.01" min="0"
                 value="<?= $ad['payment_amount'] ?? ($curPkg['amount'] ?? '') ?>"
                 placeholder="0.00">
        </div>
        <div class="col-sm-5">
          <label class="form-label small fw-600">Payment Reference</label>
          <input type="text" name="payment_note" class="form-control form-control-sm"
                 value="<?= Helper::e($ad['payment_note'] ?? '') ?>"
                 placeholder="UPI ref / receipt no / Cash">
        </div>
        <div class="col-sm-3">
          <button class="btn btn-success w-100 btn-sm">
            <i class="bi bi-check-lg me-1"></i>Activate Ad
          </button>
        </div>
      </div>
    </form>

    <div class="mt-2 pt-2" style="border-top:1px solid #E8E6E0">
      <button class="btn btn-sm btn-outline-danger" onclick="showRejectBox()">
        <i class="bi bi-x-circle me-1"></i>Reject this Ad
      </button>
      <div id="rejectBox" class="mt-2 d-none">
        <form method="POST" action="<?= $r . ($isAdmin ? '/admin/business-ads/reject/' : '/portal/ads/reject/') . $ad['id'] ?>">
          <?= CSRF::field() ?>
          <div class="d-flex gap-2">
            <input type="text" name="reason" class="form-control form-control-sm"
                   placeholder="Reason for rejection (optional)">
            <button class="btn btn-danger btn-sm text-nowrap">Confirm Reject</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══ CARD 3: ACTIVE STATUS INFO ═══ -->
<?php if (($ad['status'] ?? '') === 'active'): ?>
<div class="tn-card mb-3" style="border-left:4px solid #10B981">
  <div class="tn-card-body d-flex align-items-center gap-3 flex-wrap">
    <span class="badge bg-success fs-6">✅ Ad is Live</span>
    <span class="text-muted small">Payment: ₹<?= number_format((float)($ad['payment_amount'] ?? 0), 2) ?>
      <?= !empty($ad['payment_note']) ? '· ' . Helper::e($ad['payment_note']) : '' ?>
    </span>
    <?php if ($canManage): ?>
    <form method="POST" action="<?= $r . ($isAdmin ? '/admin/business-ads/toggle/' : '/portal/ads/toggle/') . $ad['id'] ?>"
          class="ms-auto">
      <?= CSRF::field() ?>
      <button class="btn btn-sm btn-outline-warning">⏸ Pause Ad</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ═══ CARD 4: OWNER LOGIN ═══ -->
<?php if ($canManage): ?>
<div class="tn-card mb-3">
  <div class="tn-card-header">
    <i class="bi bi-person-badge me-2"></i>Ad Owner Login
    <small class="opacity-60 fw-400 ms-2">
      <?= $ownerUser ? 'Owner can log in and write sponsored news' : 'Optional — create if owner needs portal access' ?>
    </small>
  </div>
  <div class="tn-card-body">
    <?php if ($ownerUser): ?>
    <div class="ad-info-grid mb-3">
      <div class="ad-info-row"><span>Name</span><strong><?= Helper::e($ownerUser['name']) ?></strong></div>
      <div class="ad-info-row"><span>Email</span><strong><?= Helper::e($ownerUser['email']) ?></strong></div>
      <div class="ad-info-row"><span>Login URL</span><strong>/login</strong></div>
    </div>
    <form method="POST" action="<?= $r . ($isAdmin ? '/admin/business-ads/' : '/portal/ads/') . $ad['id'] . ($isAdmin ? '/reset-owner-pass' : '/reset-owner') ?>">
      <?= CSRF::field() ?>
      <input type="hidden" name="user_id" value="<?= $ownerUser['id'] ?>">
      <div class="d-flex gap-2 align-items-center">
        <input type="text" name="new_password" class="form-control form-control-sm"
               placeholder="New password (min 8 chars)" minlength="8" required style="max-width:260px">
        <button class="btn btn-sm btn-warning">Reset Password</button>
      </div>
    </form>
    <?php else: ?>
    <form method="POST" action="<?= $r . ($isAdmin ? '/admin/business-ads/' : '/portal/ads/') . $ad['id'] . '/owner-login' ?>">
      <?= CSRF::field() ?>
      <div class="row g-2 align-items-end">
        <div class="col-sm-4">
          <label class="form-label small fw-600">Owner Name</label>
          <input type="text" name="name" class="form-control form-control-sm"
                 value="<?= Helper::e($ad['business_name']) ?>" required>
        </div>
        <div class="col-sm-4">
          <label class="form-label small fw-600">Email</label>
          <input type="email" name="email" class="form-control form-control-sm"
                 placeholder="owner@email.com" required>
        </div>
        <div class="col-sm-3">
          <label class="form-label small fw-600">Password</label>
          <input type="text" name="password" class="form-control form-control-sm"
                 placeholder="min 8 chars" minlength="8" required>
        </div>
        <div class="col-sm-1">
          <button class="btn btn-primary btn-sm w-100">Create</button>
        </div>
      </div>
      <div class="form-text mt-1">Owner logs in at <strong>/login</strong> with role: <em>Ad Owner</em></div>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ═══ CARD 5: PUSH NOTIFICATION (admin/chief, active only) ═══ -->
<?php if ($canManage && ($ad['status'] ?? '') === 'active'): ?>
<div class="tn-card mb-3">
  <div class="tn-card-header"><i class="bi bi-send me-2"></i>Push Notification</div>
  <div class="tn-card-body">
    <form method="POST" action="<?= $r ?>/admin/push/send-ad/<?= $ad['id'] ?>">
      <?= CSRF::field() ?>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <select name="push_district" class="form-select form-select-sm" style="max-width:220px">
          <option value="">🌐 All subscribers</option>
          <?php if (!empty($ad['district_id'])): ?>
          <option value="<?= $ad['district_id'] ?>" selected>
            📍 <?= Helper::e($ad['district_name'] ?? 'Ad District') ?> only
          </option>
          <?php endif; ?>
        </select>
        <button class="btn btn-outline-danger btn-sm">
          <i class="bi bi-send-fill me-1"></i>Send Push
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
window.CSRF_TOKEN       = '<?= CSRF::token() ?>';
window.DELETE_IMAGE_URL = '<?= $r ?>/admin/business-ads/delete-image/';

function delImg(imgId, adId) {
  if (!confirm('Remove this image?')) return;
  fetch(window.DELETE_IMAGE_URL + imgId, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_token=' + encodeURIComponent(window.CSRF_TOKEN) + '&ad_id=' + adId,
  }).then(r => r.json()).then(d => {
    if (d.success) document.getElementById('img-' + imgId)?.remove();
    else alert(d.error || 'Cannot delete.');
  }).catch(() => alert('Network error.'));
}

function showRejectBox() {
  document.getElementById('rejectBox')?.classList.toggle('d-none');
}
</script>
