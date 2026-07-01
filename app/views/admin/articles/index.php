<?php use App\Core\{Helper, Auth, CSRF};
$_artBase = Auth::role() === 'admin' ? '/admin/articles' : '/portal/all-articles';
$_pnBase  = Auth::role() === 'admin' ? '/admin/photo-news' : '/portal/photo-news';
?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Articles</h2>
    <p class="tn-page-sub">Manage all content across the portal</p>
  </div>
  <a href="<?= $r ?>/admin/articles/create" class="btn btn-primary">
    <i class="bi bi-plus-circle me-2"></i>New Article
  </a>
</div>

<!-- FILTERS -->
<div class="tn-card mb-4">
  <div class="tn-card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-sm-4">
        <input type="text" name="search" class="form-control" placeholder="Search articles…" value="<?= Helper::e($filters['search']) ?>">
      </div>
      <div class="col-sm-2">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <?php foreach (['draft','review','published','scheduled','rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-2">
        <select name="category_id" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= (int)$filters['category_id'] === $cat['id'] ? 'selected' : '' ?>><?= Helper::e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-2">
        <select name="content_type" class="form-select">
          <option value="">All Types</option>
          <?php foreach (['news','video','short_news','live_update','gallery','special'] as $t): ?>
          <option value="<?= $t ?>" <?= $filters['content_type'] === $t ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$t)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        <a href="<?= $r ?>/admin/articles" class="btn btn-outline-secondary" title="Clear filters"><i class="bi bi-x"></i></a>
        <?php $pendingCount = []; // pending_edit removed from schema ?>
        <?php if (!empty($pendingCount)): ?>
        <a href="<?= $r ?>/admin/articles/pending-edits" class="btn btn-sm btn-warning fw-600 ms-auto">
          ✏️ <?= count($pendingCount) ?> Pending Edits
        </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<!-- BULK ACTION FORM -->
<form id="bulkForm" action="<?= $r ?>/admin/articles/bulk" method="POST">
  <?= CSRF::field() ?>
  <input type="hidden" name="action" id="bulkAction">

<div class="tn-card">
  <div class="tn-card-header">
    <span>
      <i class="bi bi-file-earmark-text me-2"></i>
      <?= number_format($total) ?> article<?= $total !== 1 ? 's' : '' ?>
      <a href="<?= $r ?>/admin/articles/create" class="btn btn-sm btn-primary ms-2">
        <i class="bi bi-plus-circle me-1"></i>New
      </a>
    </span>
    <div class="d-flex gap-2 d-none" id="bulkActionBtns">
      <span class="small text-muted align-self-center me-1" id="bulkSelCount"></span>
      <?php if (Auth::can('publish_articles')): ?>
      <button type="button" class="btn btn-sm btn-success" onclick="bulkDo('publish')">
        <i class="bi bi-check2-all me-1"></i>Publish
      </button>
      <?php endif; ?>
      <button type="button" class="btn btn-sm btn-secondary" onclick="bulkDo('draft')">
        <i class="bi bi-archive me-1"></i>Draft
      </button>
      <button type="button" class="btn btn-sm btn-danger" onclick="bulkDo('delete')">
        <i class="bi bi-trash me-1"></i>Delete
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead>
        <tr>
          <th width="36"><input type="checkbox" id="checkAll" class="form-check-input"></th>
          <th>Title</th>
          <th>Category</th>
          <th>Author</th>
          <th>Type</th>
          <th>Status</th>
          <th>Breaking</th>
          <th>Date</th>
          <th width="90">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($articles)): ?>
        <tr><td colspan="9" class="text-center py-5 text-muted">No articles found</td></tr>
        <?php endif; ?>
        <?php foreach ($articles as $a): ?>
        <tr>
          <td><input type="checkbox" name="ids[]" value="<?= $a['id'] ?>" class="form-check-input row-check"></td>
          <td>
            <a href="<?= $r . $_artBase ?>/edit/<?= $a['id'] ?>" class="tn-article-link">
              <?= Helper::e(mb_substr($a['title'], 0, 60)) ?>
            </a>
          </td>
          <td><span class="tn-cat-badge"><?= Helper::e($a['category_name']) ?></span></td>
          <td class="text-muted small"><?= Helper::e($a['author_name']) ?></td>
          <td><span class="badge bg-dark text-light"><?= ucwords(str_replace('_',' ',$a['content_type'])) ?></span></td>
          <td>
            <?php
            $statusMap = ['published'=>'success','draft'=>'secondary','review'=>'warning','scheduled'=>'info','rejected'=>'danger'];
            $sc = $statusMap[$a['status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $sc ?>"><?= ucfirst($a['status']) ?></span>
          </td>
          <td>
            <form action="<?= $r ?>/admin/articles/toggle-breaking/<?= $a['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button type="submit" class="btn btn-link p-0 text-decoration-none" title="Click to toggle breaking news">
                <?php if ($a['is_breaking']): ?>
                <span class="badge bg-danger">BREAKING</span>
                <?php else: ?>
                <span class="badge bg-light text-muted border" style="cursor:pointer">Set Breaking</span>
                <?php endif; ?>
              </button>
            </form>
          </td>
          <td class="text-muted small" title="<?= date('d M Y, h:i A', strtotime($a['created_at'])) ?>">
            <?= Helper::timeAgo($a['created_at']) ?>
          </td>
          <td>
            <a href="<?= $r . $_artBase ?>/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
              <i class="bi bi-pencil"></i>
            </a>
            <?php if (!empty($pnLinked[$a['id']])): ?>
            <a href="<?= $r . $_pnBase ?>/edit/<?= $pnLinked[$a['id']] ?>" class="btn btn-sm btn-primary" title="View Photo News">
              <i class="bi bi-camera-fill"></i>
            </a>
            <?php else: ?>
            <a href="<?= $r . $_pnBase ?>/create?article_id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Add new Photo News">
              <i class="bi bi-camera"></i>
            </a>
            <a href="<?= $r . $_pnBase ?>/connect-from-article/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Connect existing Photo News">
              <i class="bi bi-link-45deg"></i>
            </a>
            <?php endif; ?>
            <form action="<?= $r ?>/admin/articles/delete/<?= $a['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete this article?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- PAGINATION -->
<?php
$queryExtra = http_build_query(array_filter([
    'status'      => $filters['status']      ?? '',
    'category_id' => $filters['category_id'] ?? '',
    'search'      => $filters['search']      ?? '',
]));
if ($queryExtra) $queryExtra = '&' . $queryExtra;
include VIEW_PATH . '/partials/pagination.php';
?>
</div><!-- close tn-card -->
</form>

<script>
document.getElementById('checkAll')?.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
  updateBulkVisibility();
});
document.querySelectorAll('.row-check').forEach(function(cb) {
  cb.addEventListener('change', updateBulkVisibility);
});
function updateBulkVisibility() {
  const checked = document.querySelectorAll('.row-check:checked');
  const wrap  = document.getElementById('bulkActionBtns');
  const count = document.getElementById('bulkSelCount');
  if (checked.length > 0) {
    wrap.classList.remove('d-none');
    count.textContent = checked.length + ' selected';
  } else {
    wrap.classList.add('d-none');
  }
}
function bulkDo(action) {
  const checked = document.querySelectorAll('.row-check:checked');
  if (!checked.length) { alert('Select at least one article.'); return; }
  if (action === 'delete' && !confirm('Delete ' + checked.length + ' article(s)?')) return;
  document.getElementById('bulkAction').value = action;
  document.getElementById('bulkForm').submit();
}
</script>

<!-- REJECT MODAL -->
<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form id="rejectForm" method="POST">
    <?= \App\Core\CSRF::field() ?>
    <div class="modal-header"><h5 class="modal-title">Reject Article</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">Reason for rejection <small class="text-muted">(optional — reporter will see this)</small></label>
      <textarea name="reason" class="form-control" rows="3" placeholder="Explain what needs to be fixed..."></textarea>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <button type="submit" class="btn btn-danger">Reject Article</button>
    </div>
  </form>
</div></div></div>

<script>
function rejectArticle(id) {
  document.getElementById('rejectForm').action = '<?= $r ?>/admin/articles/reject/' + id;
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>