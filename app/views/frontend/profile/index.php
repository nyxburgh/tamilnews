<?php
use App\Core\{Helper, CSRF};
$roleColors = ['admin'=>'#C0001A','editor'=>'#1877F2','reporter'=>'#1B6B2E'];
$roleColor  = $roleColors[$user['role_slug']] ?? '#C0001A';
?>
<div style="max-width:600px;margin:0 auto">
  <h2 style="font-size:22px;font-weight:700;margin-bottom:24px">👤 My Profile</h2>

  <div class="portal-card mb-4">
    <div class="portal-card-body text-center" style="padding:32px">
      <div class="portal-profile-avatar" style="background:<?= $roleColor ?>">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
      </div>
      <h3 style="font-size:20px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($user['name']) ?></h3>
      <div style="font-size:13px;color:var(--portal-muted)"><?= htmlspecialchars($user['email']) ?></div>
      <span class="portal-badge mt-2 d-inline-block" style="background:<?= $roleColor ?>1A;color:<?= $roleColor ?>">
        <?= htmlspecialchars($user['role_name']) ?>
      </span>
      <?php if ($user['last_login']): ?>
      <div style="font-size:12px;color:var(--portal-muted);margin-top:8px">Last login: <?= Helper::timeAgo($user['last_login']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="portal-card">
    <div class="portal-card-header">✏️ Update Profile</div>
    <div class="portal-card-body">
      <form action="<?= $r ?>/portal/profile/update" method="POST">
        <?= CSRF::field() ?>
        <div class="mb-3">
          <label class="form-label fw-600">Full Name</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-600">Email</label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
          <small class="text-muted">Email cannot be changed. Contact admin.</small>
        </div>
        <div class="mb-4">
          <label class="form-label fw-600">New Password <small class="text-muted">(leave blank to keep current)</small></label>
          <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" minlength="8">
        </div>
        <button type="submit" class="btn btn-danger fw-600">Save Changes</button>
      </form>
    </div>
  </div>
</div>
