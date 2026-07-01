<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login — Tamil News Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/assets/css/portal.css" rel="stylesheet">
</head>
<body style="min-height:100vh;background:#F5F5F0;display:flex;align-items:center;justify-content:center;padding:20px">

<div style="width:100%;max-width:420px">

  <!-- LOGO -->
  <div style="text-align:center;margin-bottom:32px">
    <div style="display:inline-flex;align-items:center;gap:12px;text-decoration:none">
      <div style="width:48px;height:48px;background:#C0001A;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px">📰</div>
      <div style="text-align:left">
        <div style="font-family:'Noto Sans Tamil',sans-serif;font-size:22px;font-weight:700;color:#1A1A1A;line-height:1">Tamil News</div>
        <div style="font-size:11px;color:#9A9890;letter-spacing:1.5px;text-transform:uppercase">Staff Portal</div>
      </div>
    </div>
  </div>

  <!-- CARD -->
  <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);padding:32px">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:6px;color:#1A1A1A">Welcome back</h2>
    <p style="font-size:13px;color:#9A9890;margin-bottom:24px">Sign in to your staff account</p>

    <?php
    $alertType = \App\Core\Session::getFlash('alert_type');
    $alertMsg  = \App\Core\Session::getFlash('alert_msg');
    if ($alertType && $alertMsg):
    ?>
    <div class="alert alert-<?= $alertType ?> py-2 px-3 mb-3 rounded-3 small">
      <?= htmlspecialchars($alertMsg) ?>
    </div>
    <?php endif; ?>

    <form action="<?= ASSET_URL ?>/login" method="POST">
      <?= \App\Core\CSRF::field() ?>

      <div class="mb-3">
        <label class="form-label fw-600 small">Email Address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control"
                 placeholder="your@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 required autofocus>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-600 small">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control"
                 placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn btn-danger w-100 py-2 fw-600">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
      </button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:12px;color:#9A9890">
      External contributor?
      <a href="<?= ASSET_URL ?>/contribute/login" style="color:#C0001A;font-weight:600">Contributor Login →</a>
    </div>
  </div>

  <!-- ROLE HINTS -->
  <div style="display:flex;gap:8px;margin-top:16px;justify-content:center;flex-wrap:wrap">
    <span style="font-size:11px;color:#9A9890;background:#fff;padding:4px 12px;border-radius:20px;border:1px solid #D8D6CE">
      ✏️ Editor
    </span>
    <span style="font-size:11px;color:#9A9890;background:#fff;padding:4px 12px;border-radius:20px;border:1px solid #D8D6CE">
      📝 Reporter
    </span>
    <span style="font-size:11px;color:#9A9890;background:#fff;padding:4px 12px;border-radius:20px;border:1px solid #D8D6CE">
      📍 District Editor
    </span>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
