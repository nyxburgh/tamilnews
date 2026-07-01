<?php use App\Core\{Helper, CSRF, Auth}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Review Report</h2>
    <p class="tn-page-sub"><?= Helper::e($report['name']) ?> · <?= Helper::e($report['phone']) ?></p>
  </div>
  <a href="<?= $r ?>/portal/citizen-reports" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">

    <!-- Report details -->
    <div class="tn-card mb-3">
      <div class="tn-card-header">
        <span><?= Helper::e($report['title']) ?></span>
        <?php
        $sc = match($report['status']) {
            'published' => 'success',
            'approved'  => 'info',
            'rejected'  => 'secondary',
            default     => 'warning'
        };
        ?>
        <span class="badge bg-<?= $sc ?> ms-2"><?= ucfirst($report['status']) ?></span>
      </div>
      <div class="tn-card-body">
        <div class="row g-2 small text-muted mb-3">
          <div class="col-6"><i class="bi bi-person me-1"></i><?= Helper::e($report['name']) ?></div>
          <div class="col-6"><i class="bi bi-telephone me-1"></i><?= Helper::e($report['phone']) ?></div>
          <?php if ($report['email']): ?>
          <div class="col-6"><i class="bi bi-envelope me-1"></i><?= Helper::e($report['email']) ?></div>
          <?php endif; ?>
          <?php if ($report['district_name'] ?? null): ?>
          <div class="col-6"><i class="bi bi-geo me-1"></i><?= Helper::e($report['district_name']) ?></div>
          <?php endif; ?>
          <?php if ($report['location']): ?>
          <div class="col-6"><i class="bi bi-pin me-1"></i><?= Helper::e($report['location']) ?></div>
          <?php endif; ?>
          <div class="col-6"><i class="bi bi-clock me-1"></i><?= substr($report['created_at'], 0, 16) ?></div>
        </div>

        <div class="citizen-report-content mb-3">
          <?= nl2br(Helper::e($report['content'])) ?>
        </div>

        <?php if (!empty($report['image_path'])): ?>
        <div class="citizen-report-image">
          <img src="<?= rtrim(ASSET_URL, '/') . Helper::e($report['image_path']) ?>"
               alt="Citizen report image">
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($report['status'] === 'pending'): ?>

    <!-- Approve & Publish -->
    <div class="tn-card mb-3">
      <div class="tn-card-header"><span>✓ Approve &amp; Publish as Article</span></div>
      <div class="tn-card-body">
        <form method="POST" action="<?= $r ?>/portal/citizen-reports/<?= $report['id'] ?>/approve">
          <?= CSRF::field() ?>
          <div class="mb-3">
            <label class="form-label small fw-600">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select form-select-sm" required>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name_tamil'] ?: $cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-600">Article Title</label>
            <input type="text" name="title" class="form-control form-control-sm"
                   value="<?= Helper::e($report['title']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-600">Content <small class="text-muted">(edit before publishing)</small></label>
            <textarea name="content" class="form-control form-control-sm" rows="10"><?= Helper::e($report['content']) ?></textarea>
          </div>
          <button class="btn btn-success">
            <i class="bi bi-check-circle me-1"></i>Approve &amp; Publish
          </button>
        </form>
      </div>
    </div>

    <!-- Reject -->
    <div class="tn-card">
      <div class="tn-card-header"><span>✗ Reject</span></div>
      <div class="tn-card-body">
        <form method="POST" action="<?= $r ?>/portal/citizen-reports/<?= $report['id'] ?>/reject">
          <?= CSRF::field() ?>
          <div class="d-flex gap-2">
            <input type="text" name="reason" class="form-control form-control-sm"
                   placeholder="Reason for rejection (optional)">
            <button class="btn btn-outline-danger btn-sm text-nowrap">Reject</button>
          </div>
        </form>
      </div>
    </div>

    <?php elseif ($report['status'] === 'approved' || $report['status'] === 'published'): ?>
    <div class="alert alert-success small">
      <i class="bi bi-check-circle me-1"></i>
      Published as article
      <?php if ($report['article_id']): ?>
      — <a href="<?= $r ?>/admin/articles/edit/<?= $report['article_id'] ?>">Edit Article #<?= $report['article_id'] ?></a>
      <?php endif; ?>
    </div>

    <?php elseif ($report['status'] === 'rejected'): ?>
    <div class="alert alert-secondary small">
      <i class="bi bi-x-circle me-1"></i>
      Rejected<?= !empty($report['rejection_reason']) ? ': ' . Helper::e($report['rejection_reason']) : '' ?>
    </div>
    <?php endif; ?>

  </div>
</div>
