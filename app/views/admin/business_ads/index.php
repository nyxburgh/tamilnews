<?php use App\Core\{Helper, Auth, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📢 Business Ads</h2>
    <p class="tn-page-sub">
      <?= number_format($total) ?> ad<?= $total != 1 ? 's' : '' ?>
      <?= $status ? '· filtered by <strong>' . htmlspecialchars($status) . '</strong>' : '' ?>
    </p>
  </div>
  <a href="<?= $r ?>/admin/business-ads/create" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i> New Ad
  </a>
</div>

<!-- STATUS TABS -->
<style>
.bad-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px; }
.bad-tab  {
  padding:5px 16px; border-radius:20px; font-size:12px; font-weight:600;
  text-decoration:none;
  color: var(--portal-muted, #6B6A64);
  background: var(--portal-white, rgba(255,255,255,.06));
  border: 1px solid var(--portal-gray2, rgba(255,255,255,.12));
  transition: all .15s;
}
.bad-tab:hover {
  color: var(--portal-text, #fff);
  border-color: var(--portal-red, #C0001A);
}
.bad-tab.active {
  background: #C0001A;
  color: #fff !important;
  border-color: #C0001A;
}
/* dark theme override */
[data-bs-theme="dark"] .bad-tab {
  color: rgba(255,255,255,.65);
  background: rgba(255,255,255,.06);
  border-color: rgba(255,255,255,.12);
}
[data-bs-theme="dark"] .bad-tab:hover { color:#fff; }
</style>
<div class="bad-tabs">
  <?php
  $tabs = [
    ''         => ['All',      '🗂️'],
    'pending'  => ['Pending',  '⏳'],
    'approved' => ['Approved', '✅'],
    'active'   => ['Active',   '🟢'],
    'rejected' => ['Rejected', '❌'],
    'expired'  => ['Expired',  '⌛'],
  ];
  foreach ($tabs as $val => [$label, $icon]):
  ?>
  <a href="?status=<?= $val ?>"
     class="bad-tab <?= $status === $val ? 'active' : '' ?>">
    <?= $icon ?> <?= $label ?>
    <?php if ($val === 'pending'):
      try { $pc = (new \App\Models\BusinessAdModel())->pendingCount(); if($pc > 0): ?>
        <span style="background:rgba(255,255,255,.2);border-radius:10px;padding:0 6px;margin-left:3px;font-size:10px"><?= $pc ?></span>
      <?php endif; } catch(\Exception $e) {} ?>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="tn-card">
  <div class="tn-card-body p-0">
    <?php if (empty($ads)): ?>
    <div class="text-center py-5 text-muted">
      <div style="font-size:40px">📢</div>
      <p class="mt-2">No ads found.</p>
    </div>
    <?php else: ?>
    <table class="tn-table">
      <thead><tr>
        <th>Business</th>
        <th>Slot</th>
        <th>Location / Category</th>
        <th>Display</th>
        <th>Validity</th>
        <th>Payment</th>
        <th>Status</th>
        <th>By</th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($ads as $ad): ?>
      <tr>
        <td>
          <div style="font-weight:600"><?= Helper::e($ad['business_name']) ?></div>
          <?php if ($ad['image_count']): ?>
          <div style="font-size:11px;color:var(--text-muted)">📷 <?= $ad['image_count'] ?> image<?= $ad['image_count']>1?'s':'' ?></div>
          <?php endif; ?>
        </td>
        <td>
          <span class="tn-cat-badge"><?= Helper::e($ad['slot_name']) ?></span>
        </td>
        <td style="font-size:12px">
          <?php if ($ad['district_name']): ?>
          <div><i class="bi bi-geo-alt"></i> <?= Helper::e($ad['district_name']) ?><?= $ad['city_name'] ? ' › '.$ad['city_name'] : '' ?></div>
          <?php endif; ?>
          <?php if ($ad['category_name']): ?>
          <div><i class="bi bi-grid"></i> <?= Helper::e($ad['category_name']) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <?php $dtColors = ['global'=>'bg-primary','location'=>'bg-success','category'=>'bg-info']; ?>
          <span class="badge <?= $dtColors[$ad['display_type']] ?? 'bg-secondary' ?> text-white" style="font-size:10px">
            <?= ucfirst($ad['display_type']) ?>
          </span>
        </td>
        <td style="font-size:11px">
          <div><?= date('d M Y', strtotime($ad['valid_from'])) ?></div>
          <div style="color:var(--text-muted)">→ <?= date('d M Y', strtotime($ad['valid_until'])) ?></div>
        </td>
        <td>
          <?php
          $pColors = ['confirmed'=>'text-success','pending'=>'text-warning','rejected'=>'text-danger'];
          $pLabels = ['confirmed'=>'✓ Paid','pending'=>'⏳ Pending','rejected'=>'✗ Rejected'];
          ?>
          <span style="font-size:12px;font-weight:600" class="<?= $pColors[$ad['payment_status']] ?? '' ?>">
            <?= $pLabels[$ad['payment_status']] ?? '' ?>
          </span>
          <?php if ($ad['payment_amount']): ?>
          <div style="font-size:11px;color:var(--text-muted)">₹<?= number_format($ad['payment_amount'],2) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <?php
          $sColors = ['pending'=>'#F59E0B','approved'=>'#3B82F6','active'=>'#10B981','rejected'=>'#EF4444','expired'=>'#6B7280','paused'=>'#8B5CF6'];
          $sColor  = $sColors[$ad['status']] ?? '#9CA3AF';
          ?>
          <span style="background:<?= $sColor ?>;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;white-space:nowrap">
            <?= strtoupper($ad['status']) ?>
          </span>
        </td>
        <td style="font-size:11px;color:var(--text-muted)"><?= Helper::e($ad['submitted_by_name'] ?? '—') ?></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= $r ?>/admin/business-ads/show/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
              <i class="bi bi-eye"></i>
            </a>
            <a href="<?= $r ?>/admin/business-ads/edit/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
              <i class="bi bi-pencil"></i>
            </a>
            <?php if ($canApprove && $ad['status'] === 'pending'): ?>
            <form method="POST" action="<?= $r ?>/admin/business-ads/approve/<?= $ad['id'] ?>" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-success" title="Approve">✓</button>
            </form>
            <?php endif; ?>
            <form method="POST" action="<?= $r ?>/admin/business-ads/delete/<?= $ad['id'] ?>"
                  class="d-inline"
                  onsubmit="return confirm('Delete this ad and all its images permanently?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php
$queryExtra = $status ? '&status='.$status : '';
include VIEW_PATH . '/partials/pagination.php';
?>
