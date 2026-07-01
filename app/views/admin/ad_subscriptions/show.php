<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Subscription</h2>
  <a href="<?= $r ?>/admin/business-ads/show/<?= $sub['ad_id'] ?>" class="btn btn-outline-secondary btn-sm">← Back to Ad</a>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-8">
    <div class="tn-card">
      <div class="tn-card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="mb-1"><?= Helper::e($sub['business_name']) ?></h5>
            <span class="badge bg-primary"><?= Helper::e($sub['package_name']) ?></span>
            <?php
            $statusColor = $sub['status'] === 'active' ? 'success' :
                          ($sub['status'] === 'expired' ? 'secondary' :
                          ($sub['status'] === 'suspended' ? 'danger' : 'warning'));
            ?>
            <span class="ms-2 badge bg-<?= $statusColor ?>"><?= ucfirst($sub['status']) ?></span>
          </div>
          <div class="text-end">
            <div class="text-muted small">Amount Paid</div>
            <div class="fw-700 fs-5">₹<?= number_format($sub['amount_paid'], 2) ?></div>
          </div>
        </div>
        <div class="row g-2 small">
          <div class="col-6"><span class="text-muted">Valid From:</span> <?= $sub['valid_from'] ?></div>
          <div class="col-6"><span class="text-muted">Valid Until:</span> <strong><?= $sub['valid_until'] ?></strong></div>
          <div class="col-6"><span class="text-muted">Images:</span>
            <?php if ($sub['allow_images']): ?>
              Yes (max <?= $sub['max_images'] ?>, change every <?= $sub['image_change_days'] ?> days)
            <?php else: ?>No<?php endif; ?>
          </div>
          <div class="col-6"><span class="text-muted">News quota:</span>
            <?php if ($sub['allow_news']):
              $quota = $sub['news_quota'] ? $sub['news_quota'] : $sub['selected_days'];
              echo $sub['news_used'] . '/' . $quota . ' used';
            else: ?>Not included<?php endif; ?>
          </div>
          <?php if ($sub['notes']): ?>
          <div class="col-12"><span class="text-muted">Notes:</span> <?= Helper::e($sub['notes']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="tn-card">
      <div class="tn-card-header"><span class="fw-600">Actions</span></div>
      <div class="tn-card-body d-grid gap-2">

        <?php if ($sub['status'] === 'pending'): ?>
        <form method="POST" action="<?= $r ?>/admin/business-ads/subscription/<?= $sub['id'] ?>/activate">
          <?= CSRF::field() ?>
          <button class="btn btn-success w-100">✓ Activate</button>
        </form>
        <?php endif; ?>

        <?php if ($sub['status'] === 'active'): ?>
        <form method="POST" action="<?= $r ?>/admin/business-ads/subscription/<?= $sub['id'] ?>/suspend">
          <?= CSRF::field() ?>
          <button class="btn btn-warning w-100" onclick="return confirm('Suspend?')">⏸ Suspend</button>
        </form>
        <?php endif; ?>

        <form method="POST" action="<?= $r ?>/admin/business-ads/subscription/<?= $sub['id'] ?>/extend" class="d-flex gap-2">
          <?= CSRF::field() ?>
          <input type="number" name="extend_days" class="form-control form-control-sm" placeholder="Days" min="1" max="365" required>
          <button class="btn btn-outline-primary btn-sm text-nowrap">+ Extend</button>
        </form>

        <?php if (!$sub['owner_user_id']): ?>
        <a href="<?= $r ?>/admin/business-ads/subscription/<?= $sub['id'] ?>/create-login"
           class="btn btn-outline-secondary">👤 Create Owner Login</a>
        <?php else: ?>
        <div class="alert alert-success p-2 mb-0 small">
          👤 Owner: <strong><?= Helper::e($sub['owner_name']) ?></strong><br>
          <?= Helper::e($sub['owner_email']) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($news)): ?>
<div class="tn-card">
  <div class="tn-card-header"><span class="fw-600">Sponsored News (<?= count($news) ?>)</span></div>
  <div class="tn-card-body p-0">
    <table class="table table-sm mb-0">
      <thead><tr><th>Title</th><th>Status</th><th>Scheduled</th><th>Published</th><th>Approved By</th></tr></thead>
      <tbody>
        <?php foreach ($news as $n):
          $nc = $n['status'] === 'published' ? 'success' :
               ($n['status'] === 'approved' ? 'info' :
               ($n['status'] === 'pending_approval' ? 'warning' :
               ($n['status'] === 'rejected' ? 'danger' : 'secondary')));
        ?>
        <tr>
          <td><?= Helper::e(mb_substr($n['title'], 0, 50)) ?></td>
          <td><span class="badge bg-<?= $nc ?>"><?= ucfirst(str_replace('_', ' ', $n['status'])) ?></span></td>
          <td><?= $n['scheduled_date'] ?? '—' ?></td>
          <td><?= $n['published_at'] ?? '—' ?></td>
          <td><?= Helper::e($n['approved_by_name'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
