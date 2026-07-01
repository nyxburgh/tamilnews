<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">✏️ Edit Special Category</h2>
  </div>
  <a href="<?= $r ?>/admin/special-categories" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back
  </a>
</div>

<div class="tn-card" style="max-width:600px">
  <div class="tn-card-body">
    <form action="<?= $r ?>/admin/special-categories/update/<?= $special['id'] ?>" method="POST">
      <?= CSRF::field() ?>

      <div class="mb-3">
        <label class="form-label fw-600">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control"
               value="<?= Helper::e($special['title']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-600">Title (Tamil)</label>
        <input type="text" name="title_tamil" class="form-control"
               style="font-family:'Noto Sans Tamil',sans-serif"
               value="<?= Helper::e($special['title_tamil'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label fw-600">Type</label>
        <select name="type" class="form-select">
          <?php foreach (['election','festival','sports','budget','event','disaster'] as $t): ?>
          <option value="<?= $t ?>" <?= ($special['type']??'')===$t?'selected':'' ?>>
            <?= ucfirst($t) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-600">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= Helper::e($special['description'] ?? '') ?></textarea>
      </div>

      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" value="1"
                 id="isActive" <?= !empty($special['is_active']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="isActive">Active (visible on site)</label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-save me-2"></i>Update Special Category
      </button>
    </form>
  </div>
</div>
