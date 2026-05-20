<?php use App\Core\{Helper, CSRF}; ?>
<div class="portal-page-header">
  <div>
    <h2 class="portal-page-title">My Articles</h2>
    <p style="font-size:13px;color:var(--portal-muted);margin:2px 0 0"><?= number_format($total) ?> total submissions</p>
  </div>
  <a href="<?= $r ?>/contribute/articles/create" class="btn btn-success">
    <i class="bi bi-plus-circle me-2"></i>Submit New
  </a>
</div>

<!-- STATUS TABS -->
<div class="d-flex gap-2 mb-4">
  <?php foreach (['' => 'All', 'draft' => 'Draft', 'review' => 'Under Review', 'published' => 'Published', 'rejected' => 'Rejected'] as $val => $label): ?>
  <a href="<?= $r ?>/contribute/articles<?= $val ? '?status=' . $val : '' ?>"
     class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="portal-card">
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead>
        <tr><th>Title</th><th>Category</th><th>Status</th><th>Submitted</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($articles)): ?>
        <tr><td colspan="5" class="text-center py-5 text-muted">
          No articles found. <a href="<?= $r ?>/contribute/articles/create">Submit your first article →</a>
        </td></tr>
        <?php endif; ?>
        <?php foreach ($articles as $a): ?>
        <tr>
          <td>
            <div class="fw-500"><?= Helper::e(mb_substr($a['title'], 0, 65)) ?></div>
            <div class="text-muted small">/article/<?= Helper::e($a['slug']) ?></div>
          </td>
          <td><span style="font-size:11px;color:#10b981;font-weight:600"><?= Helper::e($a['category_name']) ?></span></td>
          <td>
            <?php $sc = ['published'=>'success','review'=>'warning','draft'=>'secondary','rejected'=>'danger'][$a['status']] ?? 'secondary'; ?>
            <span class="badge bg-<?= $sc ?>"><?= ucfirst($a['status']) ?></span>
            <?php if ($a['status'] === 'review'): ?>
            <div class="text-muted" style="font-size:11px">Awaiting editor review</div>
            <?php elseif ($a['status'] === 'published'): ?>
            <div class="text-muted" style="font-size:11px"><?= Helper::formatDate($a['published_at'], 'd M Y') ?></div>
            <?php endif; ?>
          </td>
          <td class="text-muted small"><?= Helper::timeAgo($a['created_at']) ?></td>
          <td>
            <?php if ($a['status'] === 'published'): ?>
            <a href="<?= $r ?>/article/<?= Helper::e($a['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View live">
              <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <?php elseif ($a['status'] === 'draft'): ?>
            <a href="<?= $r ?>/contribute/articles/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
              <i class="bi bi-pencil"></i>
            </a>
            <form action="<?= $r ?>/contribute/articles/delete/<?= $a['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete this article?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php
$queryExtra = $status ? '&status='.$status : '';
include VIEW_PATH . '/partials/pagination.php';
?>
</div>
