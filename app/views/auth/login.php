<div class="tn-login-wrap">
  <div class="tn-login-card">
    <div class="tn-login-brand">
      <div class="tn-login-icon"><i class="bi bi-newspaper"></i></div>
      <h1 class="tn-login-title">Tamil News Portal</h1>
      <p class="tn-login-sub">Admin Control Center</p>
    </div>

    <?php
    $alertType = \App\Core\Session::getFlash('alert_type');
    $alertMsg  = \App\Core\Session::getFlash('alert_msg');
    if ($alertType && $alertMsg):
    ?>
    <div class="alert alert-<?= $alertType ?> py-2 px-3 mb-4 rounded-3">
      <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($alertMsg) ?>
    </div>
    <?php endif; ?>

    <form action="<?= url('admin/login') ?>" method="POST">
      <?= \App\Core\CSRF::field() ?>
      <div class="mb-3">
        <label class="form-label fw-500">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="admin@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-500">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2 fw-600">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
      </button>
    </form>
  </div>
</div>
