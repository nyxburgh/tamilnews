<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Categories</h2>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCatModal">
    <i class="bi bi-plus-circle me-2"></i>Add Category
  </button>
</div>
<div class="tn-card">
  <div class="tn-card-header"><span><i class="bi bi-grid-3x3-gap me-2"></i><?= count($categories) ?> categories — drag to reorder</span></div>
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th width="30"></th><th>Name</th><th>Tamil</th><th>Slug</th><th>Parent</th><th>Articles</th><th>Active</th><th>Actions</th></tr></thead>
      <tbody id="catSortable">
        <?php foreach ($categories as $cat): ?>
        <tr data-id="<?= $cat['id'] ?>">
          <td class="drag-handle text-muted" style="cursor:grab"><i class="bi bi-grip-vertical"></i></td>
          <td><strong><?= Helper::e($cat['name']) ?></strong></td>
          <td><?= Helper::e($cat['name_tamil'] ?? '—') ?></td>
          <td><code><?= Helper::e($cat['slug']) ?></code></td>
          <td><?= Helper::e($cat['parent_name'] ?? '—') ?></td>
          <td><span class="badge bg-secondary">—</span></td>
          <td><span class="badge <?= $cat['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $cat['is_active'] ? 'Yes' : 'No' ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editCat(<?= htmlspecialchars(json_encode($cat)) ?>)">
              <i class="bi bi-pencil"></i>
            </button>
            <form action="<?= $r ?>/admin/categories/delete/<?= $cat['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete category?')">
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

<!-- ADD MODAL -->
<div class="modal fade" id="addCatModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= $r ?>/admin/categories/create" method="POST">
        <?= CSRF::field() ?>
        <div class="modal-header"><h5 class="modal-title">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Tamil Name</label><input type="text" name="name_tamil" class="form-control" placeholder="தமிழ் பெயர்"></div>
          <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" placeholder="auto-generated"></div>
          <div class="mb-3"><label class="form-label">Parent</label>
            <select name="parent_id" class="form-select">
              <option value="">None (top-level)</option>
              <?php foreach ($categories as $cat): ?>
              <?php if (!$cat['parent_id']): ?>
              <option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option>
              <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Create Category</button></div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editCatModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editCatForm" method="POST">
        <?= CSRF::field() ?>
        <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" id="editName" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Tamil Name</label><input type="text" name="name_tamil" id="editTamil" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" id="editSlug" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="editDesc" class="form-control" rows="2"></textarea></div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1">
            <label class="form-check-label" for="editActive">Active</label>
          </div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function editCat(cat) {
  document.getElementById('editCatForm').action = '/admin/categories/edit/' + cat.id;
  document.getElementById('editName').value  = cat.name;
  document.getElementById('editTamil').value = cat.name_tamil || '';
  document.getElementById('editSlug').value  = cat.slug;
  document.getElementById('editDesc').value  = cat.description || '';
  document.getElementById('editActive').checked = cat.is_active == 1;
  new bootstrap.Modal(document.getElementById('editCatModal')).show();
}

// Drag-sort
const sortable = Sortable.create(document.getElementById('catSortable'), {
  handle: '.drag-handle', animation: 150,
  onEnd: function() {
    const ids = [...document.querySelectorAll('#catSortable tr')].map(r => r.dataset.id);
    fetch(r + '/admin/categories/sort', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ ids })
    });
  }
});
</script>
