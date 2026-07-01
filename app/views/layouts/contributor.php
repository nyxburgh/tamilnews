<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Contributor') ?> — Tamil News Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/assets/css/portal.css" rel="stylesheet">
<meta name="csrf-token" content="<?= \App\Core\CSRF::token() ?>">
<meta name="base-url"   content="<?= ASSET_URL ?>">
</head>
<body class="portal-body">

<?php
$contributor = \App\Core\Session::get('contributor');
$isLoggedIn  = !empty($contributor['id']);
$r           = ASSET_URL;
$baseUrl     = BASE_URL;
$current     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$green       = '#10b981';

$dashUrl     = $r . '/contribute/dashboard';
$articlesUrl = $r . '/contribute/articles';
$writeUrl    = $r . '/contribute/articles/create';
?>

<!-- TOPBAR -->
<div class="portal-topbar">
  <div class="portal-topbar-inner">
    <a href="<?= $dashUrl ?>" class="portal-logo">
      <div class="portal-logo-icon" style="background:<?= $green ?>">✍️</div>
      <div>
        <div class="portal-logo-title">
          <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900">வேள்</span><span style="color:#fff;background:#C0001A;padding:0 5px;border-radius:3px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;margin-left:2px">சுடர்</span>
        </div>
        <div class="portal-logo-sub" style="color:<?= $green ?>">Contributor</div>
      </div>
    </a>

    <!-- DESKTOP NAV -->
    <?php if ($isLoggedIn): ?>
    <nav class="portal-nav">
      <a href="<?= $dashUrl ?>"     class="portal-nav-link <?= str_contains($current,'/dashboard') ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
      <a href="<?= $articlesUrl ?>" class="portal-nav-link <?= (str_contains($current,'/articles') && !str_contains($current,'/create')) ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i> My Articles
      </a>
      <a href="<?= $writeUrl ?>"    class="portal-nav-link <?= str_contains($current,'/create') ? 'active' : '' ?>">
        <i class="bi bi-plus-circle"></i> Submit Article
      </a>
    </nav>
    <?php endif; ?>

    <div class="portal-topbar-right">
      <a href="<?= $baseUrl ?>/public/" target="_blank" class="portal-view-site">
        <i class="bi bi-box-arrow-up-right"></i>
        <span class="d-none d-md-inline">View Site</span>
      </a>
      <?php if ($isLoggedIn): ?>
      <div class="portal-user" onclick="togglePortalMenu()">
        <?php if (!empty($contributor['avatar'])): ?>
        <img src="<?= htmlspecialchars($contributor['avatar']) ?>"
             style="width:30px;height:30px;border-radius:50%;object-fit:cover" alt="">
        <?php else: ?>
        <div class="portal-user-avatar" style="background:<?= $green ?>">
          <?= strtoupper(substr($contributor['name'] ?? 'C', 0, 1)) ?>
        </div>
        <?php endif; ?>
        <span class="portal-user-name d-none d-md-inline">
          <?= htmlspecialchars(explode(' ', $contributor['name'] ?? '')[0]) ?>
        </span>
        <div class="portal-user-dropdown" id="portalUserMenu">
          <div class="portal-user-dropdown-header">
            <div class="fw-600"><?= htmlspecialchars($contributor['name'] ?? '') ?></div>
            <div style="font-size:11px;color:#6B6A64"><?= htmlspecialchars($contributor['email'] ?? '') ?></div>
            <span class="portal-role-badge mt-1 d-inline-block" style="background:<?= $green ?>">Contributor</span>
          </div>
          <a href="<?= $r ?>/contribute/logout" class="portal-user-dropdown-item" style="color:#C0001A">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- FLASH ALERT -->
<?php
$alertType = \App\Core\Session::getFlash('alert_type');
$alertMsg  = \App\Core\Session::getFlash('alert_msg');
if ($alertType && $alertMsg):
?>
<div style="max-width:1200px;margin:12px auto 0;padding:0 20px">
  <div class="alert alert-<?= $alertType ?> alert-dismissible fade show mb-0">
    <?= htmlspecialchars($alertMsg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<!-- CONTENT -->
<div class="portal-content"><?= $content ?></div>

<!-- DESKTOP FOOTER -->
<div class="portal-footer">
  <span>© <?= date('Y') ?> Tamil News Portal</span>
  <span>Logged in as <strong style="color:<?= $green ?>">Contributor</strong></span>
</div>

<!-- MOBILE STICKY FOOTER — 3 icons, only when logged in -->
<?php if ($isLoggedIn): ?>
<nav class="portal-mob-footer">
  <a href="<?= $dashUrl ?>" class="portal-mob-item <?= str_contains($current,'/dashboard') ? 'active' : '' ?>">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard</span>
  </a>
  <a href="<?= $articlesUrl ?>" class="portal-mob-item <?= (str_contains($current,'/articles') && !str_contains($current,'/create')) ? 'active' : '' ?>">
    <i class="bi bi-file-earmark-text"></i>
    <span>Articles</span>
  </a>
  <a href="<?= $writeUrl ?>" class="portal-mob-write">
    <div class="portal-mob-write-btn" style="background:<?= $green ?>">
      <i class="bi bi-plus-lg"></i>
    </div>
    <span>Write</span>
  </a>
  <a href="<?= $r ?>/admin/media" class="portal-mob-item <?= str_contains($current,'/media') ? 'active' : '' ?>">
    <i class="bi bi-images"></i>
    <span>Media</span>
  </a>
  <a href="<?= $r ?>/portal/profile" class="portal-mob-item <?= str_contains($current,'/profile') ? 'active' : '' ?>">
    <i class="bi bi-person-circle"></i>
    <span>Profile</span>
  </a>
</nav>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const r = '<?= ASSET_URL ?>';
function togglePortalMenu() {
  document.getElementById('portalUserMenu')?.classList.toggle('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.portal-user'))
    document.getElementById('portalUserMenu')?.classList.remove('open');
});
</script>
</body>
</html>
