<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Tags <span class="text-muted fw-300 fs-5">(<?= number_format($total) ?>)</span></h2>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTagModal"><i class="bi bi-plus-circle me-2"></i>Add Tag</button>
</div>
<div class="tn-card">
  <div class="table-responsive"><table class="table tn-table mb-0">
    <thead><tr><th>Name</th><th>Tamil</th><th>Slug</th><th>Usage</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($tags as $tag): ?>
    <tr>
      <td><strong><?= Helper::e($tag['name']) ?></strong></td>
      <td><?= Helper::e($tag['name_tamil'] ?? '—') ?></td>
      <td><code><?= Helper::e($tag['slug']) ?></code></td>
      <td><span class="badge bg-secondary"><?= $tag['usage_count'] ?></span></td>
      <td>
        <button class="btn btn-sm btn-outline-primary" onclick="editTag(<?= htmlspecialchars(json_encode($tag)) ?>)"><i class="bi bi-pencil"></i></button>
        <form action="<?= $r ?>/admin/tags/delete/<?= $tag['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete?')"><?= CSRF::field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<div class="modal fade" id="addTagModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form action="<?= $r ?>/admin/tags/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add Tag</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
    <div class="mb-0"><label class="form-label">Tamil Name</label><input type="text" name="name_tamil" class="form-control"></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Create</button></div>
  </form>
</div></div></div>
<div class="modal fade" id="editTagModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form id="editTagForm" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Edit Tag</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" id="etName" class="form-control"></div>
    <div class="mb-0"><label class="form-label">Tamil Name</label><input type="text" name="name_tamil" id="etTamil" class="form-control"></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
  </form>
</div></div></div>
<script>function editTag(t){document.getElementById('editTagForm').action='/admin/tags/edit/'+t.id;document.getElementById('etName').value=t.name;document.getElementById('etTamil').value=t.name_tamil||'';new bootstrap.Modal(document.getElementById('editTagModal')).show();}</script>
