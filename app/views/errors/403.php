<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 — Access Denied</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#F5F5F0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{text-align:center;max-width:460px;width:100%}
.icon-wrap{width:96px;height:96px;border-radius:50%;background:#FDECEA;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:40px}
.code{font-size:72px;font-weight:900;color:#C0001A;line-height:1;margin-bottom:8px;letter-spacing:-2px}
.title{font-size:22px;font-weight:700;color:#1A1A1A;margin-bottom:8px}
.title-ta{font-family:'Noto Sans Tamil',sans-serif;font-size:18px;color:#6B6A64;margin-bottom:16px}
.desc{font-size:14px;color:#6B6A64;line-height:1.7;margin-bottom:28px}
.btn-wrap{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;transition:opacity .15s}
.btn-red{background:#C0001A;color:white}
.btn-red:hover{opacity:.9;color:white}
.btn-outline{background:white;color:#1A1A1A;border:1.5px solid #D8D6CE}
.btn-outline:hover{background:#F0EFE9;color:#1A1A1A}
.role-note{margin-top:24px;padding:12px 16px;background:white;border:1px solid #D8D6CE;border-radius:8px;font-size:12px;color:#9A9890;text-align:left}
.role-note strong{color:#1A1A1A;display:block;margin-bottom:4px}
</style>
</head>
<body>
<div class="wrap">
  <div class="icon-wrap">🔒</div>
  <div class="code">403</div>
  <div class="title">Access Denied</div>
  <div class="title-ta">அணுகல் மறுக்கப்பட்டது</div>
  <p class="desc">
    You don't have permission to view this page.<br>
    This area may require a different role or login.
  </p>
  <div class="btn-wrap">
    <a href="javascript:history.back()" class="btn btn-outline">
      <i class="bi bi-arrow-left"></i> Go Back
    </a>
    <?php
    $loginUrl = '/login';
    if (\App\Core\Auth::check()) {
        $role = \App\Core\Auth::role();
        $dashUrl = $role === 'admin' ? '/admin/dashboard' : '/portal/dashboard';
        echo '<a href="'.ASSET_URL.$dashUrl.'" class="btn btn-red"><i class="bi bi-speedometer2"></i> My Dashboard</a>';
    } else {
        echo '<a href="'.ASSET_URL.$loginUrl.'" class="btn btn-red"><i class="bi bi-box-arrow-in-right"></i> Sign In</a>';
    }
    ?>
  </div>

  <div class="role-note">
    <strong>Why am I seeing this?</strong>
    This page requires specific permissions — Admin, Editor, or Reporter access.
    Contact your administrator if you believe this is a mistake.
  </div>
</div>
</body>
</html>
