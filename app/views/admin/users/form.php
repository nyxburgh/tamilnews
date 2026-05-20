<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title"><?= $isEdit ? 'Edit User' : 'Add User' ?></h2>
  <a href="<?= $r ?>/admin/users" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<div class="row g-4">
  <!-- BASIC INFO -->
  <div class="col-lg-7">
    <div class="tn-card mb-4">
      <div class="tn-card-header"><span><i class="bi bi-person me-2"></i>Basic Info</span></div>
      <div class="tn-card-body">
        <form action="<?= $r ?>/admin/users/<?= $isEdit ? 'edit/'.$user['id'] : 'create' ?>" method="POST">
          <?= CSRF::field() ?>
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-600">Full Name *</label>
              <input type="text" name="name" class="form-control" required
                     value="<?= Helper::e($user['name'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-600">Email *</label>
              <input type="email" name="email" class="form-control" required
                     value="<?= Helper::e($user['email'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-600">Password <?= $isEdit ? '(leave blank to keep)' : '*' ?></label>
              <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>
                     placeholder="Min. 8 characters" minlength="8">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-600">Role *</label>
              <select name="role_id" class="form-select" id="roleSelect" onchange="handleRoleChange()">
                <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>"
                  <?= ($user['role_id'] ?? 3) == $role['id'] ? 'selected' : '' ?>>
                  <?= Helper::e($role['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-600">Assigned District</label>
              <select name="assigned_district_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($districts as $d): ?>
                <option value="<?= $d['id'] ?>"
                  <?= ($user['assigned_district_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                  <?= Helper::e($d['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Reporter's area — auto-tags their articles</small>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-600">Status</label>
              <select name="is_active" class="form-select">
                <option value="1" <?= ($user['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= !($user['is_active'] ?? 1) ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>
          <hr class="my-4">
          <!-- AUTO APPROVE — reporters only, set by chief editor/admin -->
          <?php if (\App\Core\Auth::can('set_auto_approve')): ?>
          <div class="form-check form-switch mb-3" id="autoApproveRow" style="<?= in_array($user['role_id'] ?? 3, [3]) ? '' : 'display:none' ?>">
            <input class="form-check-input" type="checkbox" name="auto_approve" value="1"
                   id="autoApprove" <?= !empty($user['auto_approve']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="autoApprove">
              <strong>⚡ Auto Approve</strong>
              <span class="text-muted small ms-2">— Articles publish instantly, chief editor gets notification</span>
            </label>
          </div>
          <?php endif; ?>

          <!-- ASSIGN TO DISTRICT EDITOR — reporters only, set by chief editor/admin -->
          <?php if (\App\Core\Auth::can('assign_reporters') && !empty($districtEditors)): ?>
          <div class="mb-3" id="assignEditorRow" style="<?= in_array($user['role_id'] ?? 3, [3]) ? '' : 'display:none' ?>">
            <label class="form-label fw-600">
              Assign to District Editor
              <span class="text-muted small ms-2">— This reporter's articles go to this editor for approval</span>
            </label>
            <select name="assigned_district_editor_id" class="form-select">
              <option value="">-- Auto-assign (by district/fallback to editor) --</option>
              <?php foreach ($districtEditors as $de): ?>
              <option value="<?= $de['id'] ?>" <?= ($user['assigned_district_editor_id'] ?? 0) == $de['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($de['name']) ?> (<?= htmlspecialchars($de['role_name']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary fw-600 px-4">
            <i class="bi bi-save me-2"></i><?= $isEdit ? 'Update User' : 'Create User' ?>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- EDITOR PERMISSIONS -->
  <div class="col-lg-5" id="permSection" style="<?= in_array($user['role_id'] ?? 3, [2,4,5,6]) ? '' : 'display:none' ?>">
    <div class="tn-card mb-4">
      <div class="tn-card-header">
        <span><i class="bi bi-shield-check me-2"></i>Approval Permissions</span>
        <span class="badge bg-info">Editors Only</span>
      </div>
      <div class="tn-card-body">
        <?php if (!empty($perms)): ?>
        <div class="mb-3">
          <label class="form-label fw-600 small text-muted">CURRENT PERMISSIONS</label>
          <?php foreach ($perms as $p): ?>
          <div class="d-flex align-items-center gap-2 mb-2 p-2" style="background:var(--bs-dark-bg-subtle,#2d3748);border-radius:6px">
            <span class="badge <?= $p['perm_type']==='district' ? 'bg-primary' : 'bg-success' ?>">
              <?= ucfirst($p['perm_type']) ?>
            </span>
            <span class="small flex-grow-1">
              <?= Helper::e($p['district_name'] ?? $p['category_name'] ?? 'Unknown') ?>
            </span>
            <?php if ($p['can_publish']): ?>
            <span class="badge bg-warning text-dark">Can Publish</span>
            <?php endif; ?>
            <form action="<?= $r ?>/admin/users/perm-remove/<?= $user['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <input type="hidden" name="perm_id" value="<?= $p['id'] ?>">
              <button class="btn btn-xs btn-outline-danger"><i class="bi bi-x"></i></button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <hr>
        <?php endif; ?>

        <?php if ($isEdit): ?>
        <form action="<?= $r ?>/admin/users/edit/<?= $user['id'] ?>" method="POST">
          <?= CSRF::field() ?>
          <!-- hidden fields to re-submit basic info (workaround for two forms) -->
          <input type="hidden" name="role_id" value="<?= $user['role_id'] ?>">
          <input type="hidden" name="name" value="<?= Helper::e($user['name']) ?>">
          <input type="hidden" name="email" value="<?= Helper::e($user['email']) ?>">
          <input type="hidden" name="is_active" value="<?= $user['is_active'] ?>">
          <input type="hidden" name="assigned_district_id" value="<?= $user['assigned_district_id'] ?? '' ?>">
        <?php endif; ?>

          <label class="form-label fw-600">Add District Permissions</label>
          <div style="max-height:180px;overflow-y:auto;border:1px solid var(--bs-border-color);border-radius:6px;padding:10px" class="mb-3">
            <?php foreach ($districts as $d): ?>
            <?php $checked = in_array($d['id'], array_column(array_filter($perms, fn($p) => $p['perm_type']==='district'), 'ref_id')); ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="perm_districts[]"
                     value="<?= $d['id'] ?>" id="d<?= $d['id'] ?>" <?= $checked ? 'checked' : '' ?>>
              <label class="form-check-label small" for="d<?= $d['id'] ?>">
                📍 <?= Helper::e($d['name']) ?>
              </label>
            </div>
            <?php endforeach; ?>
          </div>

          <label class="form-label fw-600">Add Category Permissions</label>
          <div style="max-height:180px;overflow-y:auto;border:1px solid var(--bs-border-color);border-radius:6px;padding:10px" class="mb-3">
            <?php foreach ($categories as $c): ?>
            <?php $checked = in_array($c['id'], array_column(array_filter($perms, fn($p) => $p['perm_type']==='category'), 'ref_id')); ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="perm_categories[]"
                     value="<?= $c['id'] ?>" id="c<?= $c['id'] ?>" <?= $checked ? 'checked' : '' ?>>
              <label class="form-check-label small" for="c<?= $c['id'] ?>">
                📂 <?= Helper::e($c['name_tamil'] ?: $c['name']) ?>
              </label>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="can_publish" value="1" id="canPublish">
            <label class="form-check-label" for="canPublish">
              <strong>Can Publish</strong> <small class="text-muted">(no chief editor approval needed)</small>
            </label>
          </div>

          <?php if ($isEdit): ?>
          <button type="submit" class="btn btn-info fw-600 w-100">
            <i class="bi bi-shield-check me-2"></i>Save Permissions
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function handleRoleChange() {
  const role    = parseInt(document.getElementById('roleSelect').value);
  const section = document.getElementById('permSection');
  // Show permissions for editor roles (2=editor, 4=district_editor, 5=category_editor, 6=sub_editor)
  section.style.display = [2,4,5].includes(role) ? '' : 'none';
}
</script>
