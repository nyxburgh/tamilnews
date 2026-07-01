<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div class="d-flex align-items-center gap-3">
    <?php if ($contributor['avatar']): ?>
    <img src="<?= Helper::e($contributor['avatar']) ?>" class="rounded-circle" style="width:52px;height:52px;object-fit:cover" alt="">
    <?php else: ?>
    <div class="tn-user-avatar" style="width:52px;height:52px;font-size:20px"><?= strtoupper(substr($contributor['name'],0,1)) ?></div>
    <?php endif; ?>
    <div>
      <h2 class="tn-page-title mb-0"><?= Helper::e($contributor['name']) ?></h2>
      <div class="text-muted small"><?= Helper::e($contributor['email']) ?>
        <?php if ($contributor['google_id']): ?>
        <span class="badge bg-success ms-2"><i class="bi bi-google"></i> Google Linked</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2">
    <?php if ($contributor['is_active']): ?>
    <form action="<?= $r ?>/admin/contributors/reject/<?= $contributor['id'] ?>" method="POST">
      <?= CSRF::field() ?>
      <button class="btn btn-outline-warning btn-sm">Deactivate</button>
    </form>
    <?php else: ?>
    <form action="<?= $r ?>/admin/contributors/approve/<?= $contributor['id'] ?>" method="POST">
      <?= CSRF::field() ?>
      <button class="btn btn-success btn-sm">Approve</button>
    </form>
    <?php endif; ?>
    <a href="<?= $r ?>/admin/contributors" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
</div>

<?php if ($contributor['bio']): ?>
<p class="text-muted mb-4"><?= Helper::e($contributor['bio']) ?></p>
<?php endif; ?>

<div class="row g-4 mb-4">
  <!-- ASSIGNED CATEGORIES -->
  <div class="col-lg-7">
    <div class="tn-card">
      <div class="tn-card-header">
        <span><i class="bi bi-grid me-2"></i>Assigned Categories</span>
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCatsModal">
          <i class="bi bi-pencil me-1"></i>Edit
        </button>
      </div>
      <div class="tn-card-body">
        <?php if (empty($categories)): ?>
        <p class="text-muted small mb-0">No categories assigned.</p>
        <?php else: ?>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($categories as $cat): ?>
          <span class="tn-cat-badge" style="font-size:13px;padding:5px 14px">
            <?= Helper::e($cat['name']) ?>
          </span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- QUICK STATS -->
  <div class="col-lg-5">
    <div class="row g-3">
      <div class="col-6">
        <div class="tn-stat-card" style="--accent:#3b82f6">
          <div class="tn-stat-num"><?= number_format($total) ?></div>
          <div class="tn-stat-label">Total Articles</div>
        </div>
      </div>
      <div class="col-6">
        <div class="tn-stat-card" style="--accent:#10b981">
          <div class="tn-stat-num"><?= number_format(array_sum(array_map(fn($a) => $a['status']==='published'?1:0, $articles))) ?></div>
          <div class="tn-stat-label">Published</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- STATUS FILTER -->
<div class="d-flex gap-2 mb-3">
  <?php foreach (['' => 'All', 'review' => 'Under Review', 'published' => 'Published', 'draft' => 'Draft', 'rejected' => 'Rejected'] as $val => $label): ?>
  <a href="<?= $r ?>/admin/contributors/show/<?= $contributor['id'] ?><?= $val ? '?status='.$val : '' ?>"
     class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- ARTICLES TABLE -->
<div class="tn-card">
  <div class="tn-card-header">
    <span><i class="bi bi-file-earmark-text me-2"></i><?= number_format($total) ?> article<?= $total !== 1 ? 's' : '' ?></span>
  </div>
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Views</th><th>Submitted</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($articles)): ?>
        <tr><td colspan="6" class="text-center py-5 text-muted">No articles found</td></tr>
        <?php endif; ?>
        <?php foreach ($articles as $a): ?>
        <tr>
          <td>
            <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="tn-article-link">
              <?= Helper::e(mb_substr($a['title'], 0, 60)) ?>
            </a>
          </td>
          <td><span class="tn-cat-badge"><?= Helper::e($a['category_name']) ?></span></td>
          <td>
            <?php $sc = ['published'=>'success','review'=>'warning','draft'=>'secondary','rejected'=>'danger'][$a['status']] ?? 'secondary'; ?>
            <span class="badge bg-<?= $sc ?>"><?= ucfirst($a['status']) ?></span>
          </td>
          <td class="text-muted small"><?= number_format($a['view_count']) ?></td>
          <td class="text-muted small"><?= Helper::timeAgo($a['created_at']) ?></td>
          <td>
            <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil"></i>
            </a>
            <?php if ($a['status'] === 'review'): ?>
            <!-- Quick publish from contributor page -->
            <form action="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <input type="hidden" name="status" value="published">
              <button class="btn btn-sm btn-success" title="Publish"><i class="bi bi-check2"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div><!-- close tn-card -->
  <?php
$queryExtra = '';
include VIEW_PATH . '/partials/pagination.php';
?>