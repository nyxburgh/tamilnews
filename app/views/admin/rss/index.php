<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">RSS Feeds</h2>
  <div class="d-flex gap-2">
    <a href="<?= $r ?>/admin/rss/imports" class="btn btn-outline-secondary"><i class="bi bi-inbox me-2"></i>Review Queue</a>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFeedModal">
      <i class="bi bi-plus-circle me-2"></i>Add Feed
    </button>
  </div>
</div>

<div class="tn-card mb-4">
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th>Name</th><th>URL</th><th>Category</th><th>Interval</th><th>Last Fetched</th><th>Active</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($feeds)): ?>
        <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-rss fs-1 d-block mb-3"></i>No RSS feeds configured</td></tr>
        <?php endif; ?>
        <?php foreach ($feeds as $f): ?>
        <tr>
          <td><strong><?= Helper::e($f['name']) ?></strong></td>
          <td><a href="<?= Helper::e($f['url']) ?>" target="_blank" class="text-muted small text-truncate d-block" style="max-width:200px"><?= Helper::e($f['url']) ?></a></td>
          <td><span class="tn-cat-badge"><?= Helper::e($f['category_name']) ?></span></td>
          <td><?= $f['fetch_interval'] ?> min</td>
          <td class="text-muted small"><?= $f['last_fetched_at'] ? Helper::timeAgo($f['last_fetched_at']) : 'Never' ?></td>
          <td><span class="badge <?= $f['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $f['is_active'] ? 'Yes' : 'No' ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="editFeed(<?= htmlspecialchars(json_encode($f)) ?>)"><i class="bi bi-pencil"></i></button>
            <form action="<?= $r ?>/admin/rss/delete/<?= $f['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete feed?')"><?= CSRF::field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD FEED MODAL -->
<div class="modal fade" id="addFeedModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form action="<?= $r ?>/admin/rss/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add RSS Feed</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Feed Name *</label><input type="text" name="name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">RSS URL *</label><input type="url" name="url" class="form-control" placeholder="https://example.com/feed.xml" required></div>
    <div class="mb-3"><label class="form-label">Category</label>
      <select name="category_id" class="form-select">
        <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="mb-0"><label class="form-label">Fetch Interval (minutes)</label><input type="number" name="fetch_interval" class="form-control" value="30" min="5" max="1440"></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Add Feed</button></div>
  </form>
</div></div></div>

<!-- EDIT FEED MODAL -->
<div class="modal fade" id="editFeedModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form id="editFeedForm" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Edit Feed</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" id="efName" class="form-control"></div>
    <div class="mb-3"><label class="form-label">URL</label><input type="url" name="url" id="efUrl" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Category</label>
      <select name="category_id" id="efCategory" class="form-select">
        <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">Interval (min)</label><input type="number" name="fetch_interval" id="efInterval" class="form-control"></div>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="efActive">
      <label class="form-check-label" for="efActive">Active</label>
    </div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
  </form>
</div></div></div>

<script>
function editFeed(f) {
  document.getElementById('editFeedForm').action = '/admin/rss/edit/' + f.id;
  document.getElementById('efName').value     = f.name;
  document.getElementById('efUrl').value      = f.url;
  document.getElementById('efCategory').value = f.category_id;
  document.getElementById('efInterval').value = f.fetch_interval;
  document.getElementById('efActive').checked = f.is_active == 1;
  new bootstrap.Modal(document.getElementById('editFeedModal')).show();
}
</script>
