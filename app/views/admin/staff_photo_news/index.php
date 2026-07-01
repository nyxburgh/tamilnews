<?php use App\Core\{Helper, CSRF, Auth}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📸 Photo News</h2>
    <p class="tn-page-sub">பட செய்திகள் — Visual news cards</p>
  </div>
  <a href="<?= $r . $base ?>/create" class="btn btn-danger btn-sm">+ Add Photo News</a>
</div>

<?php if (empty($items)): ?>
<div class="tn-card"><div class="tn-card-body text-center text-muted py-5">No photo news yet. <a href="<?= $r . $base ?>/create">Add one →</a></div></div>
<?php else: ?>

<div class="pn-staff-grid">
  <?php foreach ($items as $item): ?>
  <div class="pn-staff-card">

    <!-- Image -->
    <div class="pn-staff-img-wrap">
      <?php if ($item['image_path']): ?>
      <img src="<?= rtrim(ASSET_URL,'/') ?>/public<?= htmlspecialchars($item['image_path']) ?>"
           alt="" class="pn-staff-img">
      <?php else: ?>
      <div class="pn-staff-no-img">🖼</div>
      <?php endif; ?>
    </div>

    <!-- Title -->
    <div class="pn-staff-title"><?= Helper::e($item['title']) ?></div>

    <!-- Tags -->
    <?php if (!empty($item['tags'])): ?>
    <div class="pn-staff-tags">
      <?php foreach ($item['tags'] as $t): ?>
      <span class="pn-staff-tag"><?= Helper::e($t['name_tamil'] ?: $t['name']) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Approval status -->
    <?php
    $apSt    = $item['approval_status'] ?? 'pending';
    $apColor = ['approved'=>'#10B981','pending'=>'#F59E0B','rejected'=>'#EF4444'][$apSt] ?? '#9CA3AF';
    $canModerate = in_array(Auth::role(), ['admin','chief_editor','editor']);
    ?>
    <div style="padding:0 12px 6px">
      <span style="font-size:10px;font-weight:700;color:<?= $apColor ?>;text-transform:uppercase">
        ● <?= $apSt ?>
      </span>
    </div>

    <!-- Approve / Reject — moderators only, only when pending -->
    <?php if ($canModerate && $apSt === 'pending'): ?>
    <div class="pn-staff-actions" style="border-top:none;padding-top:0">
      <form method="POST" action="<?= $r . $base ?>/approve/<?= $item['id'] ?>" class="d-inline">
        <?= CSRF::field() ?>
        <button class="btn btn-xs btn-success" title="Approve & Publish">
          <i class="bi bi-check-circle"></i> Approve
        </button>
      </form>
      <form method="POST" action="<?= $r . $base ?>/reject/<?= $item['id'] ?>" class="d-inline"
            onsubmit="return confirm('Reject this photo news?')">
        <?= CSRF::field() ?>
        <button class="btn btn-xs btn-outline-danger" title="Reject">
          <i class="bi bi-x-circle"></i> Reject
        </button>
      </form>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="pn-staff-actions">
      <a href="<?= $r . $base ?>/edit/<?= $item['id'] ?>" class="btn btn-xs btn-outline-secondary" title="Edit">
        <i class="bi bi-pencil"></i> Edit
      </a>
      <form method="POST" action="<?= $r . $base ?>/delete/<?= $item['id'] ?>" class="d-inline"
            onsubmit="return confirm('Delete this photo news?')">
        <?= CSRF::field() ?>
        <button class="btn btn-xs btn-outline-danger" title="Delete">
          <i class="bi bi-trash"></i> Del
        </button>
      </form>
      <a href="<?= BASE_URL ?>/public/photo-news" target="_blank"
         class="btn btn-xs btn-outline-info" title="View in site">
        <i class="bi bi-eye"></i> View
      </a>
      <?php if (!empty($item['article_id'])): ?>
      <a href="<?= $r ?>/<?= Auth::role()==='admin' ? 'admin/articles/edit' : 'portal/all-articles/edit' ?>/<?= $item['article_id'] ?>"
         class="btn btn-xs btn-primary" title="View linked article">
        <i class="bi bi-file-earmark-check"></i> Article ✓
      </a>
      <?php else: ?>
      <a href="<?= $r . $base ?>/to-article/<?= $item['id'] ?>"
         class="btn btn-xs btn-outline-primary" title="Create new article from this">
        <i class="bi bi-file-earmark-plus"></i> New
      </a>
      <a href="<?= $r . $base ?>/connect/<?= $item['id'] ?>"
         class="btn btn-xs btn-outline-secondary" title="Connect to an already-existing article">
        <i class="bi bi-link-45deg"></i> Connect
      </a>
      <?php endif; ?>
    </div>

  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($total > $limit): ?>
<div class="d-flex justify-content-center gap-3 mt-4">
  <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>" class="btn btn-sm btn-outline-secondary">← Prev</a><?php endif; ?>
  <span class="small text-muted align-self-center">Page <?= $page ?> / <?= ceil($total/$limit) ?></span>
  <?php if ($page * $limit < $total): ?><a href="?page=<?= $page+1 ?>" class="btn btn-sm btn-outline-secondary">Next →</a><?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<style>
.pn-staff-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;}
.pn-staff-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;display:flex;flex-direction:column;}
.pn-staff-img-wrap{aspect-ratio:3/4;overflow:hidden;background:#F5F5F0;flex-shrink:0;}
.pn-staff-img{width:100%;height:100%;object-fit:cover;display:block;}
.pn-staff-no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:36px;color:#D1D5DB;}
.pn-staff-title{font-size:12px;font-weight:700;color:#1A1A1A;padding:10px 12px 4px;line-height:1.4;flex:1;}
.pn-staff-tags{padding:0 12px 6px;display:flex;flex-wrap:wrap;gap:4px;}
.pn-staff-tag{background:#F0F9FF;color:#0369A1;font-size:10px;padding:2px 6px;border-radius:10px;}
.pn-staff-actions{padding:8px 12px 12px;display:flex;flex-wrap:wrap;gap:4px;border-top:1px solid #F5F5F0;}
.btn-xs{padding:2px 8px;font-size:11px;}
</style>
