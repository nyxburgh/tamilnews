<?php use App\Core\Helper; ?>
<div class="portal-page-header">
  <h2 class="portal-page-title">My Ads</h2>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= count($ads) ?></div>
      <div class="portal-stat-label">My Ads</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= number_format($totalViews) ?></div>
      <div class="portal-stat-label">Total Impressions</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= number_format($totalClicks) ?></div>
      <div class="portal-stat-label">Total Clicks</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= $totalViews > 0 ? number_format($totalClicks/$totalViews*100,1).'%' : '—' ?></div>
      <div class="portal-stat-label">CTR</div>
    </div>
  </div>
</div>

<!-- Ads list -->
<?php if (empty($ads)): ?>
<div class="portal-card text-center py-5">
  <div class="portal-empty-icon">📢</div>
  <p class="text-muted mt-3">No ads assigned to your account yet.<br>Contact us to get started.</p>
</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($ads as $a):
    $sc = ['pending'=>'#F59E0B','approved'=>'#3B82F6','active'=>'#10B981','expired'=>'#6B7280'];
    $statusColor = $sc[$a['status']] ?? '#9CA3AF';
  ?>
  <div class="col-md-6">
    <div class="portal-card">
      <div class="portal-card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <strong><?= Helper::e($a['business_name']) ?></strong>
          <span class="ad-status-badge" style="--sc:<?= $statusColor ?>">
            <?= strtoupper($a['status']) ?>
          </span>
        </div>
        <?php if (!empty($a['package_name'])): ?>
        <div class="mb-2">
          <span class="badge bg-primary small"><?= Helper::e($a['package_name']) ?></span>
        </div>
        <?php endif; ?>
        <div class="row g-1 small text-muted mb-3">
          <div class="col-6">📢 <?= Helper::e(ucfirst($a['slot_type'] ?? '—')) ?></div>
          <div class="col-6">👁 <?= number_format($a['impression_count'] ?? 0) ?> views</div>
          <?php if ($a['valid_until']): ?>
          <div class="col-6">📅 Until: <?= $a['valid_until'] ?></div>
          <?php endif; ?>
          <div class="col-6">🖱 <?= number_format($a['click_count'] ?? 0) ?> clicks</div>
        </div>
        <a href="<?= $r ?>/portal/my-ads/<?= $a['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
          Manage →
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
