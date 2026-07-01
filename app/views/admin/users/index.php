<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Users</h2>
    <p class="tn-page-sub"><?= number_format($total) ?> total users</p>
  </div>
  <a href="<?= $r ?>/admin/users/create" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add User</a>
</div>

<div class="tn-card">
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Articles</th><th>Badges</th><th>Status</th><th>Last Login</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="tn-user-avatar-sm"><?= strtoupper(substr($u['name'],0,1)) ?></div>
              <strong><?= Helper::e($u['name']) ?></strong>
            </div>
          </td>
          <td class="text-muted small"><?= Helper::e($u['email']) ?></td>
          <td>
            <?php $rc=['admin'=>'danger','editor'=>'primary','reporter'=>'success'][$u['role_slug']]??'secondary'; ?>
            <span class="badge bg-<?= $rc ?>"><?= Helper::e($u['role_name']) ?></span>
          </td>
          <td><span class="badge bg-secondary"><?= $u['article_count'] ?></span></td>
          <td>
            <!-- Badge assign -->
            <?php if (!empty($badges)): ?>
            <div class="dropdown">
              <button class="btn btn-xs btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">🏅 Badge</button>
              <ul class="dropdown-menu">
                <?php foreach ($badges as $badge): ?>
                <li>
                  <form action="<?= $r ?>/admin/users/badge/assign/<?= $u['id'] ?>" method="POST" class="d-inline">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="badge_id" value="<?= $badge['id'] ?>">
                    <button type="submit" class="dropdown-item">
                      <?= Helper::e($badge['icon']??'🏅') ?> <?= Helper::e($badge['name']) ?>
                    </button>
                  </form>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($u['is_blocked'] ?? false): ?>
            <span class="badge bg-danger">🚫 Blocked</span>
            <?php elseif ($u['is_active']): ?>
            <span class="badge bg-success">Active</span>
            <?php else: ?>
            <span class="badge bg-secondary">Inactive</span>
            <?php endif; ?>
          </td>
          <td class="text-muted small"><?= $u['last_login'] ? Helper::timeAgo($u['last_login']) : 'Never' ?></td>
          <td>
            <a href="<?= $r ?>/admin/users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>

            <?php if ($u['is_blocked'] ?? false): ?>
            <form action="<?= $r ?>/admin/users/unblock/<?= $u['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-success" title="Unblock"><i class="bi bi-unlock"></i></button>
            </form>
            <?php else: ?>
            <form action="<?= $r ?>/admin/users/block/<?= $u['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Block this user? They will not be able to login.')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-warning" title="Block"><i class="bi bi-lock"></i></button>
            </form>
            <?php endif; ?>

            <form action="<?= $r ?>/admin/users/delete/<?= $u['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete this user?')">
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
