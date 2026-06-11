<?php use App\Core\{Helper, CSRF}; ?>
<div class="contribute-auth-wrap">
  <div class="contribute-auth-box">
    <div class="contribute-auth-logo">
      <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;font-size:24px">தினத்</span><span style="color:#fff;background:#C0001A;padding:2px 8px;border-radius:4px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;font-size:24px;margin-left:3px">துளிர்</span>
      <div style="font-size:12px;color:#9A9890;margin-top:6px">Contributor Portal</div>
    </div>
    <h5 class="text-center mb-4">Sign In</h5>
    <form method="POST" action="<?= $r ?>/contribute/login">
      <?= CSRF::field() ?>
      <div class="mb-3">
        <label class="form-label fw-600">Email</label>
        <input type="email" name="email" class="form-control" required autofocus placeholder="your@email.com">
      </div>
      <div class="mb-3">
        <label class="form-label fw-600">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
    </form>
    <hr>
    <p class="text-center text-muted small">New contributor? <a href="<?= $r ?>/contribute/register">Register here</a></p>
    <p class="text-center mt-2"><a href="<?= $r ?>/" class="text-muted small">← Back to site</a></p>
  </div>
</div>
