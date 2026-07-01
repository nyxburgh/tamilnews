<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">YouTube Import Queue</h2>
  <a href="<?= $r ?>/admin/youtube" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<!-- STATUS FILTER -->
<div class="d-flex gap-2 mb-4">
  <?php foreach ([''=>'All', 'pending'=>'Pending', 'imported'=>'Imported', 'skipped'=>'Skipped'] as $val => $label): ?>
  <a href="<?= $r ?>/admin/youtube/imports<?= $val ? '?status=' . $val : '' ?>"
     class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="tn-card">
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th>Thumbnail</th><th>Title</th><th>Channel</th><th>Published</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($imports)): ?>
        <tr><td colspan="6" class="text-center py-5 text-muted">No imports found</td></tr>
        <?php endif; ?>
        <?php foreach ($imports as $imp): ?>
        <tr>
          <td>
            <?php if ($imp['thumbnail']): ?>
            <img src="<?= Helper::e($imp['thumbnail']) ?>" style="width:80px;height:45px;object-fit:cover;border-radius:4px" loading="lazy">
            <?php else: ?>
            <div class="bg-dark rounded d-flex align-items-center justify-content-center" style="width:80px;height:45px"><i class="bi bi-youtube text-danger"></i></div>
            <?php endif; ?>
          </td>
          <td>
            <a href="https://youtube.com/watch?v=<?= Helper::e($imp['video_id']) ?>" target="_blank" class="tn-article-link">
              <?= Helper::e(mb_substr($imp['title'], 0, 60)) ?>
            </a>
            <div class="text-muted small"><code><?= Helper::e($imp['video_id']) ?></code></div>
          </td>
          <td class="text-muted small"><?= Helper::e($imp['channel_name']) ?></td>
          <td class="text-muted small"><?= $imp['published_at'] ? Helper::formatDate($imp['published_at'], 'd M Y') : '—' ?></td>
          <td>
            <?php $sc = ['pending'=>'warning','imported'=>'success','skipped'=>'secondary'][$imp['status']] ?? 'secondary'; ?>
            <span class="badge bg-<?= $sc ?>"><?= ucfirst($imp['status']) ?></span>
          </td>
          <td>
            <?php if ($imp['status'] === 'pending'): ?>
            <form action="<?= $r ?>/admin/youtube/imports/publish/<?= $imp['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-success" title="Mark Imported"><i class="bi bi-check2"></i></button>
            </form>
            <?php elseif ($imp['article_id']): ?>
            <a href="<?= $r ?>/admin/articles/edit/<?= $imp['article_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Article">
              <i class="bi bi-pencil"></i>
            </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php
$queryExtra = '';
include VIEW_PATH . '/partials/pagination.php';
?>
</div>
