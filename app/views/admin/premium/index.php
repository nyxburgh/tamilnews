<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Premium Articles</h2>
    <p class="tn-page-sub">Articles locked behind reader login</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $r ?>/admin/premium/plans" class="btn btn-outline-secondary">
      <i class="bi bi-tag me-2"></i>Plans
    </a>
    <a href="<?= $r ?>/admin/premium/subscribers" class="btn btn-outline-secondary">
      <i class="bi bi-people me-2"></i>Subscribers
    </a>
  </div>
</div>

<!-- STATS -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="tn-stat-card" style="--accent:#E8A000">
      <div class="tn-stat-icon"><i class="bi bi-lock"></i></div>
      <div class="tn-stat-num"><?= $stats['total_premium_articles'] ?></div>
      <div class="tn-stat-label">Premium Articles</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="tn-stat-card" style="--accent:#10b981">
      <div class="tn-stat-icon"><i class="bi bi-people"></i></div>
      <div class="tn-stat-num"><?= $stats['total_subscribers'] ?></div>
      <div class="tn-stat-label">Active Subscribers</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="tn-stat-card" style="--accent:#3b82f6">
      <div class="tn-stat-icon"><i class="bi bi-tag"></i></div>
      <div class="tn-stat-num"><?= $stats['total_plans'] ?></div>
      <div class="tn-stat-label">Active Plans</div>
    </div>
  </div>
</div>

<div class="tn-card">
  <div class="tn-card-header">
    <span><i class="bi bi-lock me-2"></i><?= number_format($total) ?> premium article<?= $total !== 1 ? 's' : '' ?></span>
    <a href="<?= $r ?>/admin/articles" class="btn btn-sm btn-outline-secondary">Mark articles premium from Articles list →</a>
  </div>
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Views</th><th>Marked By</th><th>Published</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($articles)): ?>
        <tr><td colspan="7" class="text-center py-5 text-muted">
          No premium articles yet. Go to <a href="<?= $r ?>/admin/articles">Articles</a> and toggle 🔒 on any article.
        </td></tr>
        <?php endif; ?>
        <?php foreach ($articles as $a): ?>
        <tr>
          <td>
            <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="tn-article-link">
              <?= Helper::e(mb_substr($a['title'], 0, 55)) ?>
            </a>
            <span class="badge ms-1" style="background:#E8A000;color:#1A1A1A;font-size:10px">🔒 Premium</span>
          </td>
          <td><span class="tn-cat-badge"><?= Helper::e($a['category_name']) ?></span></td>
          <td>
            <?php $sc = ['published'=>'success','draft'=>'secondary','review'=>'warning'][$a['status']] ?? 'secondary'; ?>
            <span class="badge bg-<?= $sc ?>"><?= ucfirst($a['status']) ?></span>
          </td>
          <td class="text-muted small"><?= number_format($a['view_count']) ?></td>
          <td class="text-muted small"><?= Helper::e($a['set_by_name'] ?? '—') ?></td>
          <td class="text-muted small"><?= $a['published_at'] ? Helper::timeAgo($a['published_at']) : '—' ?></td>
          <td>
            <form action="<?= $r ?>/admin/premium/toggle/<?= $a['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-warning" title="Remove premium">🔓 Remove</button>
            </form>
            <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
