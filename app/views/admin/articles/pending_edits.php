<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">✏️ Pending Edits</h2>
    <p class="tn-page-sub">Articles with submitted edits awaiting approval</p>
  </div>
  <a href="<?= $r ?>/admin/articles" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<?php if (empty($edits)): ?>
<div class="tn-card"><div class="tn-card-body text-center py-5 text-muted">
  <i class="bi bi-check-circle fs-1 d-block mb-3"></i>No pending edits
</div></div>
<?php else: ?>
<div class="tn-card">
  <table class="table tn-table mb-0">
    <thead><tr><th>Article</th><th>Category</th><th>Submitted By</th><th>Submitted At</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($edits as $a): ?>
      <tr>
        <td>
          <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="tn-article-link">
            <?= Helper::e(mb_substr($a['title'],0,60)) ?>
          </a>
          <span class="badge ms-1" style="background:#FEF4E0;color:#A06800;font-size:10px">✏️ Edit Pending</span>
        </td>
        <td><span class="tn-cat-badge"><?= Helper::e($a['category_name']) ?></span></td>
        <td class="text-muted small"><?= Helper::e($a['editor_name']) ?></td>
        <td class="text-muted small"><?= Helper::timeAgo($a['pending_edit_at']) ?></td>
        <td>
          <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Review</a>
          <form action="<?= $r ?>/admin/articles/approve-edit/<?= $a['id'] ?>" method="POST" class="d-inline">
            <?= CSRF::field() ?>
            <button class="btn btn-sm btn-success me-1">✓ Apply Edit</button>
          </form>
          <form action="<?= $r ?>/admin/articles/reject-edit/<?= $a['id'] ?>" method="POST" class="d-inline">
            <?= CSRF::field() ?>
            <button class="btn btn-sm btn-outline-danger">✗ Reject</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
