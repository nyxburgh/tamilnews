<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">RSS Import Queue</h2>
  <a href="<?= $r ?>/admin/rss" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to Feeds</a>
</div>

<div class="d-flex gap-2 mb-4">
  <?php foreach (['pending'=>'Pending', 'imported'=>'Imported', 'skipped'=>'Skipped', ''=>'All'] as $val => $label): ?>
  <a href="<?= $r ?>/admin/rss/imports<?= $val ? '?status=' . $val : '' ?>"
     class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="tn-card">
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th>Title</th><th>Feed</th><th>Fetched</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($imports)): ?>
        <tr><td colspan="5" class="text-center py-5 text-muted">Queue is empty</td></tr>
        <?php endif; ?>
        <?php foreach ($imports as $imp): ?>
        <tr>
          <td>
            <a href="<?= Helper::e($imp['source_url']) ?>" target="_blank" class="tn-article-link">
              <?= Helper::e(mb_substr($imp['title'], 0, 70)) ?>
            </a>
          </td>
          <td class="text-muted small"><?= Helper::e($imp['feed_name']) ?></td>
          <td class="text-muted small"><?= Helper::timeAgo($imp['fetched_at']) ?></td>
          <td>
            <?php $sc = ['pending'=>'warning','imported'=>'success','skipped'=>'secondary'][$imp['status']] ?? 'secondary'; ?>
            <span class="badge bg-<?= $sc ?>"><?= ucfirst($imp['status']) ?></span>
          </td>
          <td>
            <?php if ($imp['status'] === 'pending'): ?>
            <form action="<?= $r ?>/admin/rss/imports/publish/<?= $imp['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check2"></i> Approve</button>
            </form>
            <form action="<?= $r ?>/admin/rss/imports/skip/<?= $imp['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-secondary" title="Skip"><i class="bi bi-x"></i></button>
            </form>
            <?php elseif ($imp['article_id']): ?>
            <a href="<?= $r ?>/admin/articles/edit/<?= $imp['article_id'] ?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil"></i> Edit Article
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
