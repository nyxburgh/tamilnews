<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Portal') ?> — Tamil News Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/assets/css/portal.css" rel="stylesheet">
<meta name="csrf-token" content="<?= \App\Core\CSRF::token() ?>">
<meta name="base-url"   content="<?= ASSET_URL ?>">
  <style id="portal-white-theme">
    body { background:#F5F5F2 !important; color:#1A1A1A !important; }
    .tn-card { background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.06); }
    .tn-page-title, .tn-page-sub { color:#1A1A1A !important; }
    .portal-wrap { background:#F5F5F2; min-height:100vh; }
    @media(max-width:768px){
      .portal-main{padding:10px !important}
      .tn-table{font-size:12px}
      .tn-table th,.tn-table td{padding:7px !important}
      .btn{font-size:13px}
    }
  </style>
</head>
<body class="portal-body">

<?php
$isContributor = \App\Core\Session::has('contributor_id');
if ($isContributor) {
    $portalUser = \App\Core\Session::get('contributor');
    $role       = 'contributor';
    $logoutUrl  = ASSET_URL . '/contribute/logout';
    $writeUrl   = ASSET_URL . '/contribute/articles/create';
} else {
    $auth       = \App\Core\Auth::user();
    $role       = \App\Core\Auth::role() ?? 'reporter';
    $logoutUrl  = ASSET_URL . '/logout';
    $writeUrl   = ASSET_URL . '/admin/articles/create';
    $portalUser = $auth;
}
$userName  = $portalUser['name']  ?? 'User';
$userEmail = $portalUser['email'] ?? '';
$userAvatar= $portalUser['avatar'] ?? null;

$r        = ASSET_URL;
$baseUrl  = BASE_URL;
$current  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$roleColors = ['admin'=>'#C0001A','chief_editor'=>'#7C3AED','editor'=>'#1877F2','district_editor'=>'#0891B2','category_editor'=>'#0891B2','senior_reporter'=>'#047857','reporter'=>'#1B6B2E','ads_manager'=>'#B45309','contributor'=>'#10b981'];
$roleColor  = $roleColors[$role] ?? '#6B6A64';
$roleLabels = ['admin'=>'Admin','chief_editor'=>'Chief Editor','editor'=>'Editor','district_editor'=>'District Editor','category_editor'=>'Category Editor','senior_reporter'=>'Sr. Reporter','reporter'=>'Reporter','ads_manager'=>'Ads Manager','contributor'=>'Contributor'];
$roleLabel  = $roleLabels[$role] ?? ucfirst($role);
$roleIcons  = ['admin'=>'⚙️','chief_editor'=>'👑','editor'=>'✏️','district_editor'=>'🗺️','category_editor'=>'📂','senior_reporter'=>'⭐','reporter'=>'📝','ads_manager'=>'📣','contributor'=>'✍️'];
$roleIcon   = $roleIcons[$role] ?? '👤';

$dashUrl    = $isContributor ? $r.'/contribute/dashboard' : $r.'/portal/dashboard';
$articlesUrl= $isContributor ? $r.'/contribute/articles'  : $r.'/portal/articles';

function pActive(string $path, string $current): string {
    return str_contains($current, $path) ? 'active' : '';
}
?>

<!-- TOPBAR (desktop) -->
<div class="portal-topbar">
  <div class="portal-topbar-inner">
    <a href="<?= $dashUrl ?>" class="portal-logo">
      <div class="portal-logo-icon" style="background:<?= $roleColor ?>"><?= $roleIcon ?></div>
      <div>
        <div class="portal-logo-title">
          <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900">தினத்</span><span style="color:#fff;background:#C0001A;padding:0 5px;border-radius:3px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;margin-left:2px">துளிர்</span>
        </div>
        <div class="portal-logo-sub" style="color:<?= $roleColor ?>"><?= $roleLabel ?> Portal</div>
      </div>
    </a>

    <!-- DESKTOP NAV only -->
    <nav class="portal-nav">
      <a href="<?= $dashUrl ?>" class="portal-nav-link <?= pActive('/dashboard',$current) ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
      <?php if (!$isContributor && in_array($role, ['admin','chief_editor','editor','district_editor','category_editor'])): ?>
      <a href="<?= $r ?>/admin/articles" class="portal-nav-link <?= (str_contains($current,'/admin/articles') && !str_contains($current,'review')) ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i> All Articles
      </a>
      <?php else: ?>
      <a href="<?= $articlesUrl ?>" class="portal-nav-link <?= pActive('/articles',$current) ?>">
        <i class="bi bi-file-earmark-text"></i> My Articles
      </a>
      <?php endif; ?>
      <?php if (!$isContributor && in_array($role, ['admin','chief_editor','editor','district_editor','category_editor'])): ?>
      <a href="<?= $r ?>/admin/articles?status=review" class="portal-nav-link <?= ($_GET['status']??'')=='review'?'active':'' ?>">
        <i class="bi bi-hourglass-split"></i> Review Queue
      </a>
      <?php endif; ?>
      <a href="<?= $writeUrl ?>" class="portal-nav-link">
        <i class="bi bi-plus-circle"></i>
        <?= $isContributor ? 'Submit Article' : 'Write' ?>
      </a>
      <?php if (!$isContributor && in_array($role, ['admin','chief_editor','editor','district_editor','category_editor'])): ?>
      <a href="<?= $r ?>/admin/articles?status=review" class="portal-nav-link <?= ($_GET['status']??'')==='review'?'active':'' ?>">
        <i class="bi bi-hourglass-split"></i> Review Queue
      </a>
      <a href="<?= $r ?>/admin/media" class="portal-nav-link <?= pActive('/admin/media',$current) ?>">
        <i class="bi bi-images"></i> Media
      </a>
      <?php endif; ?>
    </nav>

    <div class="portal-topbar-right">
      <a href="<?= $baseUrl ?>/public/" target="_blank" class="portal-view-site">
        <i class="bi bi-box-arrow-up-right"></i>
        <span class="d-none d-md-inline">View Site</span>
      </a>
      <?php
      $notifCount = 0;
      try {
        $notifModel = new \App\Models\NotificationModel();
        $notifCount = $notifModel->unreadCount(!$isContributor ? (\App\Core\Auth::id() ?? 0) : 0);
      } catch(\Exception $e) {}
      ?>
      <a href="<?= $r ?>/admin/business-ads" class="portal-nav-link <?= str_contains($current,'/business-ads')?'active':'' ?>">
      <i class="bi bi-megaphone"></i> Ads
    </a>
    <a href="<?= $r ?>/portal/notifications" class="portal-notif-btn" title="Notifications">
        <i class="bi bi-bell"></i>
        <?php if ($notifCount > 0): ?>
        <span class="portal-notif-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
        <?php endif; ?>
      </a>
      <?php if ($role === 'admin'): ?>
      <a href="<?= $r ?>/admin/dashboard" class="portal-admin-btn">
        <i class="bi bi-gear"></i>
        <span class="d-none d-md-inline">Admin Panel</span>
      </a>
      <?php endif; ?>
      <!-- User avatar + dropdown -->
      <div class="portal-user" onclick="togglePortalMenu()">
        <?php if ($userAvatar): ?>
        <img src="<?= htmlspecialchars($userAvatar) ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover" alt="">
        <?php else: ?>
        <div class="portal-user-avatar" style="background:<?= $roleColor ?>">
          <?= strtoupper(substr($userName,0,1)) ?>
        </div>
        <?php endif; ?>
        <span class="portal-user-name d-none d-md-inline"><?= htmlspecialchars(explode(' ',$userName)[0]) ?></span>
        <div class="portal-user-dropdown" id="portalUserMenu">
          <div class="portal-user-dropdown-header">
            <div class="fw-600"><?= htmlspecialchars($userName) ?></div>
            <div style="font-size:11px;color:#6B6A64"><?= htmlspecialchars($userEmail) ?></div>
            <span class="portal-role-badge" style="background:<?= $roleColor ?>"><?= $roleLabel ?></span>
          </div>
          <a href="<?= $r ?>/portal/profile" class="portal-user-dropdown-item">
            <i class="bi bi-person me-2"></i>My Profile
          </a>
          <a href="<?= $logoutUrl ?>" class="portal-user-dropdown-item" style="color:#C0001A">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </a>
        </div>
      </div>
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

<!-- PAGE CONTENT -->
<div class="portal-content"><?= $content ?></div>

<!-- DESKTOP FOOTER -->
<div class="portal-footer">
  <span>© <?= date('Y') ?> Tamil News Portal</span>
  <span>Logged in as <strong style="color:<?= $roleColor ?>"><?= $roleLabel ?></strong></span>
</div>

<!-- MOBILE STICKY FOOTER (5 icons) -->
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
    <div class="portal-mob-write-btn" style="background:<?= $roleColor ?>">
      <i class="bi bi-pencil-square"></i>
    </div>
    <span>Write</span>
  </a>
  <a href="<?= $r ?>/admin/business-ads" class="portal-mob-item <?= str_contains($current,'/business-ads') ? 'active' : '' ?>">
    <i class="bi bi-megaphone"></i>
    <span>Ads</span>
  </a>
  <div class="portal-mob-item" onclick="openPortalMenu()">
    <i class="bi bi-grid-3x3-gap-fill"></i>
    <span>Menu</span>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Portal Menu Bottom Sheet -->
<div class="portal-bottom-overlay" id="portalBottomOverlay" onclick="closePortalMenu()"></div>
<div class="portal-bottom-sheet" id="portalBottomSheet">
  <div class="portal-bottom-sheet-handle"></div>
  <!-- User Info -->
  <div class="portal-bottom-user">
    <div class="portal-bottom-user-avatar" style="background:<?= $roleColor ?>">
      <?= strtoupper(substr($userName,0,1)) ?>
    </div>
    <div>
      <div class="portal-bottom-user-name"><?= htmlspecialchars($userName) ?></div>
      <div class="portal-bottom-user-role" style="color:<?= $roleColor ?>"><?= $roleLabel ?></div>
    </div>
  </div>
  <div class="portal-bottom-divider"></div>
  <!-- Menu items -->
  <a href="<?= $r ?>/portal/profile" class="portal-bottom-item">
    <i class="bi bi-person-circle"></i> Profile
  </a>
  <a href="<?= $r ?>/portal/notifications" class="portal-bottom-item">
    <i class="bi bi-bell"></i> Notifications
  </a>
  <?php if (!$isContributor): ?>
  <a href="<?= $r ?>/admin/settings" class="portal-bottom-item">
    <i class="bi bi-gear"></i> Settings
  </a>
  <?php endif; ?>
  <div class="portal-bottom-divider"></div>
  <a href="<?= $logoutUrl ?>" class="portal-bottom-item portal-bottom-logout">
    <i class="bi bi-box-arrow-right"></i> Logout
  </a>
</div>




<script src="<?= ASSET_URL ?>/assets/js/portal-nav.js"></script>
</body>
</html>
