<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📋 Manage Edition</h2>
    <p class="tn-page-sub">
      <strong><?= Helper::e($edition['title']) ?></strong> ·
      <?= date('d M Y', strtotime($edition['edition_date'])) ?> ·
      <?= count($articles) ?> articles
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= $r ?>/admin/print/select/<?= $edition['id'] ?>" class="btn btn-outline-primary">
      <i class="bi bi-ui-checks me-1"></i> Add More Articles
    </a>
    <a href="<?= $r ?>/admin/print" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i>
    </a>
  </div>
</div>

<!-- STATUS UPDATE -->
<div class="tn-card mb-3">
  <div class="tn-card-body py-2 d-flex align-items-center gap-3 flex-wrap">
    <span class="fw-600 small">Status:</span>
    <?php
    $badges = ['draft'=>'secondary','ready'=>'success','printed'=>'dark'];
    $labels = ['draft'=>'Draft','ready'=>'Ready to Print','printed'=>'Printed'];
    ?>
    <span class="badge bg-<?= $badges[$edition['status']] ?> fs-6">
      <?= $labels[$edition['status']] ?>
    </span>
    <form action="<?= $r ?>/admin/print/status/<?= $edition['id'] ?>" method="POST" class="d-flex gap-2 ms-auto">
      <?= CSRF::field() ?>
      <select name="status" class="form-select form-select-sm" style="width:160px">
        <option value="draft"   <?= $edition['status']==='draft'   ?'selected':'' ?>>Draft</option>
        <option value="ready"   <?= $edition['status']==='ready'   ?'selected':'' ?>>Ready to Print</option>
        <option value="printed" <?= $edition['status']==='printed' ?'selected':'' ?>>Printed</option>
      </select>
      <button class="btn btn-sm btn-primary">Update</button>
    </form>
  </div>
</div>

<?php if (empty($articles)): ?>
<div class="tn-card">
  <div class="tn-card-body text-center py-5">
    <div style="font-size:48px">📭</div>
    <p class="text-muted mt-2">No articles selected yet.</p>
    <a href="<?= $r ?>/admin/print/select/<?= $edition['id'] ?>" class="btn btn-primary mt-2">
      Select Articles
    </a>
  </div>
</div>
<?php else: ?>

<div class="tn-card">
  <div class="tn-card-header">
    <span>Selected Articles <small class="text-muted ms-2">drag to reorder</small></span>
    <span class="badge bg-primary"><?= count($articles) ?></span>
  </div>
  <div id="sortableList">
    <?php foreach ($articles as $i => $a): ?>
    <div class="pe-article-row" data-id="<?= $a['id'] ?>">
      <div class="pe-drag-handle" title="Drag to reorder">⣿</div>
      <div class="pe-order-num"><?= $i + 1 ?></div>
      <?php
      $imgPath = !empty($a['image_url']) ? ASSET_URL . '/uploads/' . ltrim($a['image_url'],'/uploads/') : null;
      ?>
      <?php if ($imgPath): ?>
      <img src="<?= htmlspecialchars($imgPath) ?>" class="pe-thumb" alt="">
      <?php else: ?>
      <div class="pe-thumb-placeholder">📰</div>
      <?php endif; ?>
      <div class="pe-article-info">
        <div class="pe-article-title"><?= Helper::e($a['title']) ?></div>
        <div class="pe-article-meta">
          <span class="tn-cat-badge"><?= Helper::e($a['category_tamil'] ?: $a['category_name']) ?></span>
          <span><?= date('d M Y', strtotime($a['published_at'])) ?></span>
          <?php if ($a['word_count']): ?>
          <span><?= number_format($a['word_count']) ?> words</span>
          <?php endif; ?>
          <span>By <?= Helper::e($a['author_name']) ?></span>
        </div>
      </div>
      <form action="<?= $r ?>/admin/print/toggle-article/<?= $edition['id'] ?>" method="POST" class="ms-auto">
        <?= CSRF::field() ?>
        <input type="hidden" name="article_id" value="<?= $a['id'] ?>">
        <input type="hidden" name="action" value="remove">
        <button class="btn btn-sm btn-outline-danger" title="Remove">
          <i class="bi bi-x-lg"></i>
        </button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="padding:12px 20px;border-top:1px solid var(--table-border,rgba(255,255,255,.08));display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
    <div style="font-size:13px;color:var(--text-muted)">
      Total: <strong><?= count($articles) ?></strong> articles ·
      ~<?= number_format(array_sum(array_column($articles,'word_count'))) ?> words
    </div>
    <button class="btn btn-sm btn-outline-success" onclick="saveOrder()">
      <i class="bi bi-save me-1"></i> Save Order
    </button>
  </div>
</div>
<?php endif; ?>

<!-- Remove via AJAX redirect fix -->
<input type="hidden" id="csrfToken" value="<?= CSRF::token() ?>">

<style>
.pe-article-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  border-bottom: 1px solid var(--table-border, rgba(255,255,255,.06));
  transition: background .12s;
}
.pe-article-row:hover { background: var(--row-hover, rgba(255,255,255,.03)); }
.pe-article-row.sortable-ghost { opacity: .4; background: rgba(59,130,246,.1); }
.pe-drag-handle {
  cursor: grab;
  color: var(--text-muted, #6b7280);
  font-size: 18px;
  flex-shrink: 0;
  line-height: 1;
  padding: 0 4px;
}
.pe-drag-handle:active { cursor: grabbing; }
.pe-order-num {
  width: 24px;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: var(--red, #C0001A);
  flex-shrink: 0;
}
.pe-thumb {
  width: 52px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
  flex-shrink: 0;
}
.pe-thumb-placeholder {
  width: 52px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--gray-bg, #F0EFE9);
  border-radius: 4px;
  font-size: 20px;
  flex-shrink: 0;
}
.pe-article-info { flex: 1; min-width: 0; }
.pe-article-title {
  font-size: 13.5px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pe-article-meta {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 3px;
  font-size: 11px;
  color: var(--text-muted, #6b7280);
  align-items: center;
}
@media (max-width: 768px) {
  .pe-thumb, .pe-thumb-placeholder { display: none; }
  .pe-article-title { font-size: 12px; white-space: normal; }
  .pe-order-num { display: none; }
}
</style>

<script>
// Drag-to-reorder using SortableJS
const sortable = new Sortable(document.getElementById('sortableList'), {
  handle: '.pe-drag-handle',
  animation: 150,
  ghostClass: 'sortable-ghost',
  onEnd: updateOrderNums,
});

function updateOrderNums() {
  document.querySelectorAll('.pe-article-row').forEach((row, i) => {
    const num = row.querySelector('.pe-order-num');
    if (num) num.textContent = i + 1;
  });
}

function saveOrder() {
  const ids = [...document.querySelectorAll('.pe-article-row')]
    .map(r => r.dataset.id).join(',');
  const csrf = document.getElementById('csrfToken').value;

  fetch('<?= $r ?>/admin/print/sort/<?= $edition['id'] ?>', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `order=${ids}&csrf_token=${csrf}`
  }).then(r => r.json()).then(data => {
    if (data.success) {
      const btn = document.querySelector('[onclick="saveOrder()"]');
      btn.innerHTML = '<i class="bi bi-check me-1"></i> Saved!';
      btn.classList.replace('btn-outline-success','btn-success');
      setTimeout(() => {
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Save Order';
        btn.classList.replace('btn-success','btn-outline-success');
      }, 2000);
    }
  });
}
</script>
