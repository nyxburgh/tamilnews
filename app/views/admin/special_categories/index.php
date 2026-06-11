<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Special Categories</h2>
    <p class="tn-page-sub">Elections, Festivals, Events — temporary pinned sections</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSpecialModal">
    <i class="bi bi-plus-circle me-2"></i>Add Special
  </button>
</div>

<div class="row g-3 mb-4">
  <?php if (empty($specials)): ?>
  <div class="col-12">
    <div class="tn-card"><div class="tn-card-body text-center py-5 text-muted">
      <i class="bi bi-flag fs-1 d-block mb-3"></i>
      No special categories yet. Create one for elections, festivals, breaking events.
    </div></div>
  </div>
  <?php endif; ?>
  <?php foreach ($specials as $s): ?>
  <div class="col-md-4 col-lg-3">
    <div class="special-cat-card" style="font-family:inherit">
      <div class="special-cat-banner" style="background:<?= htmlspecialchars($s['banner_color']) ?>"></div>
      <div class="special-cat-body">
        <div class="special-cat-icon"><?= htmlspecialchars($s['banner_icon'] ?? '📌') ?></div>
        <div class="special-cat-name"><?= Helper::e($s['name']) ?></div>
        <?php if ($s['name_tamil']): ?>
        <div style="font-size:13px;color:var(--text-muted);font-family:'Noto Sans Tamil',sans-serif"><?= Helper::e($s['name_tamil']) ?></div>
        <?php endif; ?>
        <div class="special-cat-type"><?= ucfirst($s['type']) ?></div>
        <div class="special-cat-meta d-flex flex-wrap gap-2 mt-2">
          <span class="badge <?= $s['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span>
          <?php if (!empty($s['is_pinned'])): ?><span class="badge bg-warning text-dark">📌 Pinned</span><?php endif; ?>
          <?php if ($s['ends_at']): ?>
          <span class="badge bg-info text-dark" style="font-size:10px">Ends <?= Helper::formatDate($s['ends_at'],'d M') ?></span>
          <?php endif; ?>
        </div>
        <div class="d-flex gap-2 mt-3">
          <a href="<?= $r ?>/admin/special-categories/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
            <i class="bi bi-pencil me-1"></i>Manage
          </a>
          <form action="<?= $r ?>/admin/special-categories/delete/<?= $s['id'] ?>" method="POST"
                onsubmit="return confirm('Delete this special category?')">
            <?= CSRF::field() ?>
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addSpecialModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
  <form action="<?= $r ?>/admin/special-categories/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add Special Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="row g-3">
      <div class="col-sm-6">
        <label class="form-label">Name (English) *</label>
        <input type="text" name="name" class="form-control" required placeholder="Tamil Nadu Elections 2026">
      </div>
      <div class="col-sm-6">
        <label class="form-label">Name (Tamil)</label>
        <input type="text" name="name_tamil" class="form-control" placeholder="தமிழ்நாடு தேர்தல் 2026">
      </div>
      <div class="col-sm-4">
        <label class="form-label">Type *</label>
        <select name="type" class="form-select">
          <option value="election">🗳️ Election</option>
          <option value="festival">🎉 Festival</option>
          <option value="event">📅 Special Event</option>
          <option value="disaster">⚠️ Emergency/Disaster</option>
          <option value="sports">🏆 Sports Event</option>
          <option value="budget">💰 Budget</option>
        </select>
      </div>
      <div class="col-sm-4">
        <label class="form-label">Banner Color</label>
        <input type="color" name="banner_color" class="form-control form-control-color w-100" value="#C0001A">
      </div>
      <div class="col-sm-4">
        <label class="form-label">Icon (emoji)</label>
        <input type="text" name="banner_icon" class="form-control" placeholder="🗳️" maxlength="4">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Brief description for the section header..."></textarea>
      </div>
      <div class="col-sm-6">
        <label class="form-label">Link to Base Category</label>
        <select name="category_id" class="form-select">
          <option value="">None</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="0" min="0">
      </div>
      <div class="col-sm-6">
        <label class="form-label">Starts At</label>
        <input type="datetime-local" name="starts_at" class="form-control">
      </div>
      <div class="col-sm-6">
        <label class="form-label">Ends At</label>
        <input type="datetime-local" name="ends_at" class="form-control">
      </div>
      <div class="col-sm-6">
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="scActive" checked>
          <label class="form-check-label" for="scActive">Active</label>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" name="is_pinned" value="1" id="scPinned">
          <label class="form-check-label" for="scPinned">📌 Pin to Homepage</label>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Create Special Category</button></div>
  </form>
</div></div></div>
