<?php use App\Core\{Helper, CSRF}; ?>
<div class="contribute-auth-wrap">
  <div class="contribute-auth-box">
    <div class="contribute-auth-logo">
      <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;font-size:24px">தினத்</span><span style="color:#fff;background:#C0001A;padding:2px 8px;border-radius:4px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;font-size:24px;margin-left:3px">துளிர்</span>
      <div style="font-size:12px;color:#9A9890;margin-top:6px">Contributor Portal</div>
    </div>
    <h5 class="text-center mb-4">Register as Contributor</h5>
    <form method="POST" action="<?= $r ?>/contribute/register">
      <?= CSRF::field() ?>
      <div class="mb-3">
        <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required autofocus placeholder="Your name">
      </div>
      <div class="mb-3">
        <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" required placeholder="your@email.com">
      </div>
      <div class="mb-3">
        <label class="form-label fw-600">Password <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control" required placeholder="Min 8 characters">
      </div>
      <div class="mb-3">
        <label class="form-label fw-600">Confirm Password <span class="text-danger">*</span></label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
    </form>
    <p class="text-center text-muted small">Already registered? <a href="<?= $r ?>/contribute/login">Sign in</a></p>
  </div>
</div>
