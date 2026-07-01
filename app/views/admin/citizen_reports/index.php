<?php use App\Core\{Helper, Auth}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">குடிமக்கள் நிருபர்</h2>
    <p class="tn-page-sub"><?= number_format($total) ?> reports · <span class="text-warning fw-600"><?= $pending ?> pending</span></p>
  </div>
  <a href="<?= $r ?>/citizen-reporter" target="_blank" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-box-arrow-up-right me-1"></i>Public Form
  </a>
</div>

<?php if ($pending > 0): ?>
<div class="alert alert-warning py-2 mb-3 small">
  <i class="bi bi-exclamation-triangle me-1"></i>
  <?= $pending ?> report<?= $pending > 1 ? 's' : '' ?> waiting for review.
</div>
<?php endif; ?>

<div class="tn-card">
  <div class="tn-card-body p-0">
    <?php if (empty($reports)): ?>
    <div class="text-center text-muted py-5">No citizen reports yet.</div>
    <?php else: ?>
    <table class="table table-sm table-hover mb-0">
      <thead>
        <tr>
          <th>Reporter</th>
          <th>Title</th>
          <th>District</th>
          <th>Image</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reports as $rep):
          $sc = match($rep['status']) {
            'published' => 'success',
            'approved'  => 'info',
            'rejected'  => 'secondary',
            default     => 'warning'
          };
        ?>
        <tr>
          <td>
            <div class="fw-600 small"><?= Helper::e($rep['name']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= Helper::e($rep['phone']) ?></div>
          </td>
          <td class="small"><?= Helper::e(mb_substr($rep['title'], 0, 55)) ?></td>
          <td class="small text-muted"><?= Helper::e($rep['district_name'] ?? '—') ?></td>
          <td>
            <?php if (!empty($rep['image_path'])): ?>
            <span class="badge bg-info small">📷 Yes</span>
            <?php else: ?>
            <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <td><span class="badge bg-<?= $sc ?>"><?= ucfirst($rep['status']) ?></span></td>
          <td class="small text-muted"><?= substr($rep['created_at'], 0, 10) ?></td>
          <td>
            <a href="<?= $r ?>/portal/citizen-reports/<?= $rep['id'] ?>"
               class="btn btn-xs btn-outline-primary">Review</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php $queryExtra = ''; include VIEW_PATH . '/partials/pagination.php'; ?>
