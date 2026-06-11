<?php use App\Core\Helper; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📈 <?= Helper::e($user["name"]) ?></h2>
    <p class="tn-page-sub"><?= $user["role_name"] ?> &nbsp;·&nbsp; <?= $user["email"] ?></p>
  </div>
  <a href="<?= $r ?>/admin/performance" class="btn btn-outline-secondary">← Back</a>
</div>

<div class="row g-4">
  <?php foreach ($performance as $p): ?>
  <div class="col-md-4">
    <div class="tn-card">
      <div class="tn-card-header"><?= date("F Y", strtotime($p["month"])) ?></div>
      <div class="tn-card-body">
        <div class="row text-center g-2">
          <div class="col-6"><div style="font-size:24px;font-weight:700;color:#C0001A"><?= $p["articles_published"] ?></div><div style="font-size:11px;color:#9A9890">Published</div></div>
          <div class="col-6"><div style="font-size:24px;font-weight:700"><?= $p["articles_submitted"] ?></div><div style="font-size:11px;color:#9A9890">Submitted</div></div>
          <div class="col-6"><div style="font-size:20px;font-weight:700;color:#10B981"><?= number_format($p["total_views"]) ?></div><div style="font-size:11px;color:#9A9890">Views</div></div>
          <div class="col-6"><div style="font-size:20px;font-weight:700;color:#EF4444"><?= $p["articles_rejected"] ?></div><div style="font-size:11px;color:#9A9890">Rejected</div></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($performance)): ?>
  <div class="col-12"><div class="tn-card"><div class="tn-card-body text-center py-4 text-muted">No performance data available.</div></div></div>
  <?php endif; ?>
</div>
