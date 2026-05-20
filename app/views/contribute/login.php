<div class="tn-login-wrap">
  <div class="tn-login-card">
    <div class="tn-login-brand">
      <div class="tn-login-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
        <i class="bi bi-pen"></i>
      </div>
      <h1 class="tn-login-title">Contributor Portal</h1>
      <p class="tn-login-sub">Tamil News Portal — Sign in to submit articles</p>
    </div>

    <?php
    $alertType = \App\Core\Session::getFlash('alert_type');
    $alertMsg  = \App\Core\Session::getFlash('alert_msg');
    if ($alertType && $alertMsg):
    ?>
    <div class="alert alert-<?= $alertType ?> py-2 px-3 mb-4 rounded-3">
      <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($alertMsg) ?>
    </div>
    <?php endif; ?>

    <a href="<?= $r ?>/contribute/auth/google" class="btn btn-light w-100 py-2 fw-600 d-flex align-items-center justify-content-center gap-2">
      <svg width="18" height="18" viewBox="0 0 48 48">
        <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.8 2.5 30.3 0 24 0 14.6 0 6.6 5.5 2.7 13.5l7.8 6C12.4 13.3 17.8 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4 7.1-10 7.1-17z"/>
        <path fill="#FBBC05" d="M10.5 28.5c-.5-1.5-.8-3-.8-4.5s.3-3 .8-4.5l-7.8-6C1 16.5 0 20.1 0 24s1 7.5 2.7 10.5l7.8-6z"/>
        <path fill="#34A853" d="M24 48c6.3 0 11.6-2.1 15.5-5.7l-7.5-5.8c-2.1 1.4-4.8 2.2-8 2.2-6.2 0-11.5-3.8-13.5-9.2l-7.8 6C6.6 42.5 14.6 48 24 48z"/>
      </svg>
      Continue with Google
    </a>

    <hr class="my-4" style="border-color:rgba(255,255,255,0.08)">

    <p class="text-center text-muted small mb-0">
      New contributor? Your account must be pre-approved by admin before you can sign in.
    </p>
  </div>
</div>
