<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/public/favicon.svg">
<link rel="apple-touch-icon" href="<?= ASSET_URL ?>/public/assets/img/logo-192.png">
<title><?= htmlspecialchars($pageTitle ?? 'Portal') ?> — Tamil News Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/public/assets/css/portal.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/public/assets/css/ads.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/public/assets/css/article-form.css" rel="stylesheet">
<meta name="csrf-token"  content="<?= \App\Core\CSRF::token() ?>">
<meta name="base-url"    content="<?= ASSET_URL ?>">
</head>
<body class="portal-body">

<?php
/* ── Context ── */
$isContributor = \App\Core\Session::has('contributor_id');
if ($isContributor) {
    $portalUser = \App\Core\Session::get('contributor');
    $role       = 'contributor';
    $logoutUrl  = ASSET_URL . '/contribute/logout';
    $writeUrl   = ASSET_URL . '/contribute/articles/create';
    $articlesUrl= ASSET_URL . '/contribute/articles';
} else {
    $auth        = \App\Core\Auth::user();
    $role        = \App\Core\Auth::role() ?? 'reporter';
    $logoutUrl   = ASSET_URL . '/logout';
    $writeUrl    = ASSET_URL . '/portal/write';
    $articlesUrl = ASSET_URL . '/portal/all-articles';
    $portalUser  = $auth;
}

$r       = rtrim(ASSET_URL, '/') . '/public';
$baseUrl = BASE_URL;
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$userName  = $portalUser['name']  ?? 'User';
$userEmail = $portalUser['email'] ?? '';

$roleColors = [
    'admin'=>'#C0001A','chief_editor'=>'#7C3AED','editor'=>'#1877F2',
    'district_editor'=>'#0891B2','category_editor'=>'#0891B2',
    'senior_reporter'=>'#047857','reporter'=>'#1B6B2E',
    'ads_manager'=>'#B45309','ad_owner'=>'#D97706','contributor'=>'#10B981',
];
$roleLabels = [
    'admin'=>'Admin','chief_editor'=>'Chief Editor','editor'=>'Editor',
    'district_editor'=>'District Editor','category_editor'=>'Category Editor',
    'senior_reporter'=>'Sr. Reporter','reporter'=>'Reporter',
    'ads_manager'=>'Ads Manager','ad_owner'=>'Ad Owner','contributor'=>'Contributor',
];
$roleIcons = [
    'admin'=>'⚙️','chief_editor'=>'👑','editor'=>'✏️',
    'district_editor'=>'🗺️','category_editor'=>'📂',
    'senior_reporter'=>'⭐','reporter'=>'📝',
    'ads_manager'=>'📣','ad_owner'=>'📢','contributor'=>'✍️',
];
$roleColor = $roleColors[$role] ?? '#6B6A64';
$roleLabel = $roleLabels[$role] ?? ucfirst($role);
$roleIcon  = $roleIcons[$role]  ?? '👤';

/* ── Role capabilities ── */
$isAdOwner      = ($role === 'ad_owner');
$isAdmin        = ($role === 'admin');
$isChiefEditor  = ($role === 'chief_editor');
$isEditor       = in_array($role, ['editor','district_editor','category_editor']);
$isReporter     = in_array($role, ['reporter','senior_reporter','contributor']);
$canManageAds   = in_array($role, ['admin','chief_editor','editor','reporter','ads_manager']);
$canReview      = in_array($role, ['admin','chief_editor','editor','district_editor','category_editor']);
$canMedia       = !$isContributor && !$isAdOwner;
$canPush        = in_array($role, ['admin','chief_editor']);
$canSeeAllArts  = in_array($role, ['admin','chief_editor','editor','district_editor','category_editor']);

/* ── Dashboard URL ── */
$dashUrl = $isContributor ? $r.'/contribute/dashboard'
         : ($isAdOwner    ? $r.'/portal/my-ads'
         :                  $r.'/portal/dashboard');

/* ── Notification count ── */
$notifCount = 0;
try {
    $notifModel = new \App\Models\NotificationModel();
    $notifCount = $notifModel->unreadCount(!$isContributor ? (\App\Core\Auth::id() ?? 0) : 0);
} catch(\Exception $e) {}

function pActive(string $p, string $c): string {
    return str_contains($c, $p) ? 'active' : '';
}
?>

<!-- ══════════════════════════════════
     DESKTOP TOPBAR
══════════════════════════════════ -->
<div class="portal-topbar">
  <div class="portal-topbar-inner">

    <!-- Logo -->
    <a href="<?= $dashUrl ?>" class="portal-logo">
      <div class="portal-logo-icon" style="background:<?= $roleColor ?>"><?= $roleIcon ?></div>
      <div>
        <div class="portal-logo-title">
          <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900">தினத்</span><span style="color:#fff;background:#C0001A;padding:0 5px;border-radius:3px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;margin-left:2px">துளிர்</span>
        </div>
        <div class="portal-logo-sub" style="color:<?= $roleColor ?>"><?= $roleLabel ?> Portal</div>
      </div>
    </a>

    <!-- Desktop Nav -->
    <nav class="portal-nav">
      <a href="<?= $dashUrl ?>" class="portal-nav-link <?= pActive('/dashboard',$current) ?>">
        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
      </a>

      <?php if ($canReview): ?>
      <a href="<?= $articlesUrl ?>?status=review" class="portal-nav-link <?= ($_GET['status']??'')==='review' ? 'active' : '' ?>">
        <i class="bi bi-hourglass-split"></i><span>Review<?php if ($notifCount > 0): ?> <em class="portal-nav-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></em><?php endif; ?></span>
      </a>
      <?php endif; ?>

      <?php if ($canSeeAllArts): ?>
      <a href="<?= $articlesUrl ?>" class="portal-nav-link <?= pActive('/all-articles',$current) && !str_contains($current,'review') ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i><span>Articles</span>
      </a>
      <?php elseif (!$isAdOwner): ?>
      <a href="<?= $articlesUrl ?>" class="portal-nav-link <?= pActive('/all-articles',$current) ?>">
        <i class="bi bi-file-earmark-text"></i><span>My Articles</span>
      </a>
      <?php endif; ?>

      <a href="<?= $r ?>/portal/photo-news" class="portal-nav-link <?= pActive('/photo-news',$current) ?>">
        <i class="bi bi-camera"></i><span>Photo News</span>
      </a>
      <?php if (!$isAdOwner): ?>
      <a href="<?= $writeUrl ?>" class="portal-nav-link <?= str_contains($current,'/write')||str_contains($current,'/create') ? 'active' : '' ?>">
        <i class="bi bi-plus-circle"></i><span>Write</span>
      </a>
      <?php endif; ?>

      <?php if ($canManageAds): ?>
      <a href="<?= $r ?>/portal/ads" class="portal-nav-link <?= pActive('/portal/ads',$current) ?>">
        <i class="bi bi-megaphone"></i><span>Ads</span>
      </a>
      <?php endif; ?>

      <?php if ($isAdOwner): ?>
      <a href="<?= $r ?>/portal/my-ads" class="portal-nav-link <?= pActive('/my-ads',$current) ?>">
        <i class="bi bi-megaphone"></i><span>My Ads</span>
      </a>
      <?php endif; ?>
    </nav>

    <!-- Right actions -->
    <div class="portal-topbar-right">
      <a href="<?= $baseUrl ?>/public/" target="_blank" class="portal-view-site" title="View Site">
        <i class="bi bi-box-arrow-up-right"></i>
      </a>
      <a href="<?= $r ?>/portal/notifications" class="portal-notif-btn" title="Notifications">
        <i class="bi bi-bell"></i>
        <?php if ($notifCount > 0): ?>
        <span class="portal-notif-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
        <?php endif; ?>
      </a>
      <?php if ($isAdmin): ?>
      <a href="<?= $r ?>/admin/dashboard" class="portal-admin-btn" title="Admin Panel">
        <i class="bi bi-gear"></i>
      </a>
      <?php endif; ?>
      <!-- User dropdown -->
      <div class="portal-user" onclick="togglePortalMenu()">
        <div class="portal-user-avatar" style="background:<?= $roleColor ?>">
          <?= strtoupper(substr($userName,0,1)) ?>
        </div>
        <span class="portal-user-name d-none d-lg-inline"><?= htmlspecialchars(explode(' ',$userName)[0]) ?></span>
        <div class="portal-user-dropdown" id="portalUserMenu">
          <div class="portal-user-dropdown-header">
            <div class="fw-600"><?= htmlspecialchars($userName) ?></div>
            <div style="font-size:11px;color:#6B6A64"><?= htmlspecialchars($userEmail) ?></div>
            <span class="portal-role-badge" style="background:<?= $roleColor ?>"><?= $roleLabel ?></span>
          </div>
          <a href="<?= $r ?>/portal/profile" class="portal-user-dropdown-item">
            <i class="bi bi-person me-2"></i>Profile
          </a>
          <a href="<?= $logoutUrl ?>" class="portal-user-dropdown-item" style="color:#C0001A">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </a>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Flash -->
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

<!-- Content -->
<div class="portal-content"><?= $content ?></div>

<!-- Desktop footer -->
<div class="portal-footer">
  <span>© <?= date('Y') ?> Tamil News Portal</span>
  <span style="color:<?= $roleColor ?>;font-weight:700"><?= $roleLabel ?></span>
</div>

<!-- ══════════════════════════════════
     MOBILE FOOTER: Home|Articles|Write|Ads|Menu
══════════════════════════════════ -->
<nav class="portal-mob-footer" style="--role-color:<?= $roleColor ?>">

  <a href="<?= $dashUrl ?>" class="portal-mob-item <?= str_contains($current,'/dashboard') ? 'active' : '' ?>">
    <i class="bi bi-house-fill"></i><span>Home</span>
  </a>

  <a href="<?= $articlesUrl ?><?= $canReview ? '?status=review' : '' ?>"
     class="portal-mob-item portal-mob-review <?= pActive('/all-articles',$current)||($_GET['status']??'')==='review' ? 'active' : '' ?>">
    <i class="bi bi-<?= $canReview ? 'journal-check' : 'file-earmark-text' ?>"></i>
    <span><?= $canReview ? 'Review' : 'Articles' ?></span>
    <?php if ($notifCount > 0 && $canReview): ?>
    <span class="portal-mob-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
    <?php endif; ?>
  </a>

  <a href="<?= $writeUrl ?>" class="portal-mob-write">
    <div class="portal-mob-write-btn"><i class="bi bi-plus-lg"></i></div>
    <span>Write</span>
  </a>

  <?php if ($isAdOwner): ?>
  <a href="<?= $r ?>/portal/my-ads" class="portal-mob-item <?= pActive('/my-ads',$current) ?>">
    <i class="bi bi-megaphone-fill"></i><span>My Ads</span>
  </a>
  <?php else: ?>
  <a href="<?= $r ?>/portal/ads" class="portal-mob-item <?= pActive('/portal/ads',$current) ?>">
    <i class="bi bi-megaphone-fill"></i><span>Ads</span>
  </a>
  <?php endif; ?>

  <button class="portal-mob-item" onclick="openPortalMenu()">
    <i class="bi bi-person-circle"></i><span>Menu</span>
  </button>

</nav>

<!-- ══════════════════════════════════
     MOBILE BOTTOM SHEET (More menu)
══════════════════════════════════ -->
<div class="portal-bottom-overlay" id="portalBottomOverlay" onclick="closePortalMenu()"></div>
<div class="portal-bottom-sheet" id="portalBottomSheet">
  <div class="portal-bottom-sheet-handle"></div>

  <!-- User card -->
  <div class="portal-bottom-user">
    <div class="portal-bottom-user-avatar" style="background:<?= $roleColor ?>">
      <?= strtoupper(substr($userName,0,1)) ?>
    </div>
    <div>
      <div class="portal-bottom-user-name"><?= htmlspecialchars($userName) ?></div>
      <div class="portal-bottom-user-role" style="color:<?= $roleColor ?>"><?= $roleLabel ?></div>
    </div>
    <a href="<?= $baseUrl ?>/public/" target="_blank"
       class="ms-auto btn btn-sm btn-outline-secondary" style="font-size:11px">
      <i class="bi bi-box-arrow-up-right"></i> Site
    </a>
  </div>

  <div class="portal-bottom-divider"></div>

  <!-- Profile -->
  <a href="<?= $r ?>/portal/profile" class="portal-bottom-item">
    <i class="bi bi-person-circle"></i> Profile
  </a>

  <!-- Notifications (always shown, with badge) -->
  <a href="<?= $r ?>/portal/notifications" class="portal-bottom-item">
    <i class="bi bi-bell<?= $notifCount > 0 ? '-fill text-danger' : '' ?>"></i>
    Notifications
    <?php if ($notifCount > 0): ?>
    <span class="badge bg-danger ms-auto"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
    <?php endif; ?>
  </a>

  <?php if ($canMedia): ?>
  <a href="<?= $r ?>/portal/media" class="portal-bottom-item">
    <i class="bi bi-images"></i> Media Library
  </a>
  <?php endif; ?>

  <?php if ($canManageAds): ?>
  <a href="<?= $r ?>/portal/ads/sponsored-news" class="portal-bottom-item">
    <i class="bi bi-newspaper"></i> Sponsored Queue
  </a>
  <?php endif; ?>
  <a href="<?= $r ?>/portal/photo-news" class="portal-bottom-item <?= pActive('/photo-news',$current) ?>">
    <i class="bi bi-camera"></i> Photo News
  </a>

  <?php if ($canPush): ?>
  <a href="<?= $r ?>/admin/push" class="portal-bottom-item">
    <i class="bi bi-send-fill"></i> Push Notifications
  </a>
  <?php endif; ?>

  <?php if ($isAdmin): ?>
  <a href="<?= $r ?>/admin/dashboard" class="portal-bottom-item">
    <i class="bi bi-gear"></i> Admin Panel
  </a>
  <?php endif; ?>

  <div class="portal-bottom-divider"></div>

  <a href="<?= $logoutUrl ?>" class="portal-bottom-item portal-bottom-logout">
    <i class="bi bi-box-arrow-right"></i> Logout
  </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSET_URL ?>/public/assets/js/portal-nav.js"></script>
</body>
</html>
