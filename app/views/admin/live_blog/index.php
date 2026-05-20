<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Live Blog</h2>
    <p class="tn-page-sub">Real-time updates for elections, matches & breaking events</p>
  </div>
  <a href="<?= $r ?>/admin/live-blog/create" class="btn btn-danger">
    <span style="animation:blink 1s infinite;display:inline-block;width:8px;height:8px;border-radius:50%;background:white;margin-right:8px"></span>
    Start Live Blog
  </a>
</div>

<?php
$active = array_filter($blogs, fn($b) => $b['status'] === 'active');
$ended  = array_filter($blogs, fn($b) => $b['status'] === 'ended');
?>

<?php if (!empty($active)): ?>
<div class="mb-4">
  <h6 class="text-danger fw-700 mb-3"><i class="bi bi-circle-fill me-2" style="font-size:10px;animation:blink 1s infinite"></i>CURRENTLY LIVE</h6>
  <div class="row g-3">
    <?php foreach ($active as $b): ?>
    <div class="col-md-6 col-lg-4">
      <div class="tn-card" style="border:2px solid #C0001A">
        <div class="tn-card-header" style="background:rgba(192,0,26,.06)">
          <div>
            <span class="badge bg-danger me-2">🔴 LIVE</span>
            <strong><?= Helper::e($b['title']) ?></strong>
          </div>
          <span class="badge bg-secondary"><?= $b['entry_count'] ?> updates</span>
        </div>
        <div class="tn-card-body">
          <div class="text-muted small mb-3">
            <?= ucfirst($b['type']) ?>
            <?php if ($b['team_home'] && $b['team_away']): ?>
            · <?= Helper::e($b['team_home']) ?> vs <?= Helper::e($b['team_away']) ?>
            <?php endif; ?>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= $r ?>/admin/live-blog/manage/<?= $b['id'] ?>" class="btn btn-sm btn-danger flex-grow-1">
              <i class="bi bi-broadcast me-1"></i>Manage Live
            </a>
            <a href="<?= $r ?>/live/<?= Helper::e($b['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-eye"></i>
            </a>
            <form action="<?= $r ?>/admin/live-blog/end/<?= $b['id'] ?>" method="POST"
                  onsubmit="return confirm('End this live blog?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-warning">End</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($ended)): ?>
<div class="tn-card">
  <div class="tn-card-header"><span><i class="bi bi-archive me-2"></i>Past Live Blogs</span></div>
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th>Title</th><th>Type</th><th>Updates</th><th>Article</th><th>Started</th><th>Ended</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($ended as $b): ?>
        <tr>
          <td><strong><?= Helper::e($b['title']) ?></strong></td>
          <td><span class="badge bg-secondary"><?= ucfirst($b['type']) ?></span></td>
          <td><?= $b['entry_count'] ?></td>
          <td>
            <?php if ($b['article_slug']): ?>
            <a href="<?= $r ?>/article/<?= Helper::e($b['article_slug']) ?>" target="_blank" class="text-muted small">
              <?= Helper::e(mb_substr($b['article_title'], 0, 30)) ?>
            </a>
            <?php else: ?>
            <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
          <td class="text-muted small"><?= Helper::formatDate($b['created_at'], 'd M, h:i A') ?></td>
          <td class="text-muted small"><?= $b['ended_at'] ? Helper::formatDate($b['ended_at'], 'd M, h:i A') : '—' ?></td>
          <td>
            <a href="<?= $r ?>/admin/live-blog/manage/<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
            <form action="<?= $r ?>/admin/live-blog/reactivate/<?= $b['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-success" title="Reactivate"><i class="bi bi-arrow-clockwise"></i></button>
            </form>
            <form action="<?= $r ?>/admin/live-blog/delete/<?= $b['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete live blog and all entries?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (empty($blogs)): ?>
<div class="tn-card">
  <div class="tn-card-body text-center py-5 text-muted">
    <i class="bi bi-broadcast fs-1 d-block mb-3"></i>
    No live blogs yet. Start one for election results, cricket matches, or breaking events.
  </div>
</div>
<?php endif; ?>
