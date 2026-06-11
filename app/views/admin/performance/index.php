<?php use App\Core\Helper; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📈 Reporter Performance</h2>
    <p class="tn-page-sub"><?= date('F Y', strtotime($month)) ?></p>
  </div>
  <div class="d-flex gap-2">
    <form method="GET">
      <input type="month" name="month" value="<?= date('Y-m', strtotime($month)) ?>"
             class="form-control" onchange="this.form.submit()">
    </form>
    <form method="POST" action="<?= $r ?>/admin/performance/recalculate">
      <?= \App\Core\CSRF::field() ?>
      <input type="hidden" name="month" value="<?= $month ?>">
      <button class="btn btn-outline-primary">🔄 Recalculate</button>
    </form>
  </div>
</div>

<div class="tn-card">
  <div class="tn-card-body p-0">
    <?php if (empty($leaderboard)): ?>
    <div class="text-center py-5 text-muted">
      No performance data for this month. Click Recalculate to generate.
    </div>
    <?php else: ?>
    <table class="tn-table">
      <thead><tr>
        <th>#</th>
        <th>Reporter</th>
        <th>Role</th>
        <th>Submitted</th>
        <th>Published</th>
        <th>Rejected</th>
        <th>Views</th>
        <th>Publish Rate</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($leaderboard as $i => $p): ?>
      <tr>
        <td>
          <span style="width:28px;height:28px;border-radius:50%;background:<?= $i===0?'#F59E0B':($i===1?'#9CA3AF':($i===2?'#CD7C2F':'#E8E6E0')) ?>;color:<?= $i<3?'#fff':'#1A1A1A' ?>;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:12px">
            <?= $i+1 ?>
          </span>
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:8px">
            <?php if ($p['avatar']): ?>
            <img src="<?= Helper::e($p['avatar']) ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover" alt="">
            <?php else: ?>
            <div style="width:28px;height:28px;border-radius:50%;background:#C0001A;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">
              <?= strtoupper(substr($p['name'],0,1)) ?>
            </div>
            <?php endif; ?>
            <div>
              <div style="font-weight:600"><?= Helper::e($p['name']) ?></div>
            </div>
          </div>
        </td>
        <td><span class="badge bg-secondary"><?= $p['role_name'] ?></span></td>
        <td><?= $p['articles_submitted'] ?></td>
        <td style="color:#10B981;font-weight:600"><?= $p['articles_published'] ?></td>
        <td style="color:#EF4444"><?= $p['articles_rejected'] ?></td>
        <td><?= number_format($p['total_views']) ?></td>
        <td>
          <?php
          $rate = $p['articles_submitted'] > 0
            ? round(($p['articles_published'] / $p['articles_submitted']) * 100)
            : 0;
          $color = $rate >= 80 ? '#10B981' : ($rate >= 50 ? '#F59E0B' : '#EF4444');
          ?>
          <span style="color:<?= $color ?>;font-weight:700"><?= $rate ?>%</span>
        </td>
        <td>
          <a href="<?= $r ?>/admin/performance/user/<?= $p['user_id'] ?>" class="btn btn-sm btn-outline-primary">Details</a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
