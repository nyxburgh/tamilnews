<!DOCTYPE html>
<html lang="ta" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/public/favicon.svg">
<link rel="apple-touch-icon" href="<?= ASSET_URL ?>/public/assets/img/logo-192.png">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — வேள் சுடர்</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;500;600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/public/assets/css/admin.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/public/assets/css/ads.css" rel="stylesheet">
<meta name="csrf-token" content="<?= \App\Core\CSRF::token() ?>">
<meta name="base-url"   content="<?= ASSET_URL ?>">
<link rel="stylesheet" href="<?= ASSET_URL ?>/public/assets/css/article-form.css">
</head>
<script>
// Admin panel: redirect mobile users to portal
if (window.innerWidth < 1024 && document.cookie.indexOf("admin_mobile_ok") < 0) {
  const warn = document.createElement("div");
  warn.id = "adminMobWarn";
  warn.style.cssText = "position:fixed;inset:0;background:#1A1A1A;color:#fff;z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center";
  warn.innerHTML = "<div style='font-size:48px;margin-bottom:16px'>🖥️</div><h2 style='font-family:sans-serif;margin-bottom:12px'>Admin Panel</h2><p style='font-size:14px;color:#9A9890;margin-bottom:20px'>Admin panel is optimised for desktop view.<br>Please use a desktop browser.</p><a href='<?= ASSET_URL ?>/public/portal/dashboard' style='background:#C0001A;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;font-size:14px'>Go to Staff Portal</a><br><br><button onclick='document.cookie="admin_mobile_ok=1";document.getElementById("adminMobWarn").remove()' style='background:none;border:none;color:#9A9890;font-size:12px;cursor:pointer;margin-top:8px'>Continue anyway</button>";
  document.body.appendChild(warn);
}
</script>
<body>

<div class="tn-sidebar-overlay" id="sidebarOverlay"></div>

<nav class="tn-sidebar" id="sidebar">

  <!-- LOGO HEADER -->
  <div class="tn-sidebar-header">
    <div class="tn-logo">
      <span class="tn-logo-icon"><i class="bi bi-newspaper"></i></span>
      <div>
        <div class="tn-logo-title" style="font-family:'Noto Sans Tamil',sans-serif">
          <span style="color:#C0001A;font-weight:900">வேள்</span><span style="background:#C0001A;color:#fff;padding:0 5px;border-radius:3px;margin-left:2px;font-weight:900">சுடர்</span>
        </div>
        <div class="tn-logo-sub">Admin Panel</div>
      </div>
    </div>
  </div>

  <?php
  $role      = \App\Core\Auth::role();
  $current   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $routeBase = BASE_URL . '/public';
  function isActive(string $path, string $current): string {
      return str_contains($current, $path) ? 'active' : '';
  }
  ?>

  <!-- SCROLLABLE NAV BODY -->
  <div class="tn-sidebar-body">

    <div class="tn-nav-label">Main</div>
    <a href="<?= $routeBase ?>/admin/dashboard" class="tn-nav-item <?= isActive('/admin/dashboard', $current) ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= $routeBase ?>/admin/articles" class="tn-nav-item <?= isActive('/admin/articles', $current) ?>">
      <i class="bi bi-file-earmark-text"></i> Articles
      <?php try {
        $rc = (int)\App\Core\Database::getInstance()->query("SELECT COUNT(*) FROM tn_articles WHERE status='review'")->fetchColumn();
        if ($rc > 0): ?><span class="tn-badge"><?= $rc ?></span><?php endif;
      } catch(\Exception $e) {} ?>
    </a>
    <a href="<?= $routeBase ?>/admin/media" class="tn-nav-item <?= isActive('/admin/media', $current) ?>">
      <i class="bi bi-images"></i> Media Library
    </a>

    <?php if (\App\Core\Auth::can('manage_categories')): ?>
    <div class="tn-nav-label mt-2">Content</div>
    <a href="<?= $routeBase ?>/admin/categories" class="tn-nav-item <?= isActive('/admin/categories', $current) ?>">
      <i class="bi bi-grid-3x3-gap"></i> Categories
    </a>
    <a href="<?= $routeBase ?>/admin/special-categories" class="tn-nav-item <?= isActive('/admin/special-categories', $current) ?>">
      <i class="bi bi-flag"></i> Special Categories
    </a>
    <a href="<?= $routeBase ?>/admin/tags" class="tn-nav-item <?= isActive('/admin/tags', $current) ?>">
      <i class="bi bi-tags"></i> Tags
    </a>
    <a href="<?= $routeBase ?>/admin/locations" class="tn-nav-item <?= isActive('/admin/locations', $current) ?>">
      <i class="bi bi-geo-alt"></i> Locations
    </a>
    <?php endif; ?>

    <?php if (\App\Core\Auth::can('manage_live_blog')): ?>
    <div class="tn-nav-label mt-2">Publishing</div>
    <a href="<?= $routeBase ?>/admin/live-blog" class="tn-nav-item <?= isActive('/admin/live-blog', $current) ?>">
      <i class="bi bi-broadcast"></i> Live Blog
      <?php try {
        $lc = (int)\App\Core\Database::getInstance()->query("SELECT COUNT(*) FROM tn_live_blogs WHERE status='active'")->fetchColumn();
        if ($lc > 0): ?><span class="tn-badge" style="background:#10b981"><?= $lc ?></span><?php endif;
      } catch(\Exception $e) {} ?>
    </a>
    <a href="<?= $routeBase ?>/admin/premium" class="tn-nav-item <?= isActive('/admin/premium', $current) ?>">
      <i class="bi bi-lock"></i> Premium
    </a>
    <a href="<?= $routeBase ?>/admin/newspaper" class="tn-nav-item <?= isActive('/admin/newspaper', $current) ?>">
      <i class="bi bi-newspaper"></i> E-Paper Archive
    </a>
    <a href="<?= $routeBase ?>/admin/print" class="tn-nav-item <?= isActive('/admin/print', $current) ?>">
      <i class="bi bi-printer"></i> Print Editions
    </a>
    <?php endif; ?>

    <?php if (\App\Core\Auth::can('send_push')): ?>
    <div class="tn-nav-label mt-2">Engage</div>
    <a href="<?= $routeBase ?>/admin/push" class="tn-nav-item <?= isActive('/admin/push', $current) ?>">
      <i class="bi bi-bell"></i> Push Notifications
    </a>
    <?php endif; ?>

    <?php if (\App\Core\Auth::can('view_analytics')): ?>
    <div class="tn-nav-label mt-2">Insights</div>
    <a href="<?= $routeBase ?>/admin/analytics" class="tn-nav-item <?= isActive('/admin/analytics', $current) ?>">
      <i class="bi bi-bar-chart-line"></i> Analytics
    </a>
    <?php endif; ?>

    <div class="tn-nav-label mt-2">Advertising</div>
    <a href="<?= $routeBase ?>/admin/business-ads" class="tn-nav-item <?= isActive('/admin/business-ads', $current) ?>">
      <i class="bi bi-megaphone"></i> Business Ads
    </a>
    <a href="<?= $routeBase ?>/admin/ad-defaults" class="tn-nav-item <?= isActive('/admin/ad-defaults', $current) ?>">
      <i class="bi bi-image"></i> Ad Defaults
    </a>
    <a href="<?= $routeBase ?>/admin/packages" class="tn-nav-item <?= isActive('/admin/packages', $current) ?>">
      <i class="bi bi-box-seam"></i> Ad Packages
    </a>

    <div class="tn-nav-label mt-2">Tools</div>
    <a href="<?= $routeBase ?>/admin/widgets" class="tn-nav-item <?= isActive('/admin/widgets', $current) ?>">
      <i class="bi bi-layout-sidebar-reverse"></i> Widgets
    </a>
    <a href="<?= $routeBase ?>/admin/polls" class="tn-nav-item <?= isActive('/admin/polls', $current) ?>">
      <i class="bi bi-bar-chart-steps"></i> Polls
    </a>
    <a href="<?= $routeBase ?>/admin/rates" class="tn-nav-item <?= isActive('/admin/rates', $current) ?>">
      <i class="bi bi-currency-rupee"></i> Live Rates
    </a>
    <a href="<?= $routeBase ?>/admin/performance" class="tn-nav-item <?= isActive('/admin/performance', $current) ?>">
      <i class="bi bi-graph-up-arrow"></i> Performance
      <?php try { $bac=(new \App\Models\BusinessAdModel())->pendingCount(); if($bac>0): ?><span class="tn-badge"><?= $bac ?></span><?php endif; } catch(\Exception $e) {} ?>
    </a>

    <?php if (\App\Core\Auth::can('manage_contributors')): ?>
    <div class="tn-nav-label mt-2">People</div>
    <a href="<?= $routeBase ?>/admin/contributors" class="tn-nav-item <?= isActive('/admin/contributors', $current) ?>">
      <i class="bi bi-person-badge"></i> Contributors
      <?php try {
        $pc = (new \App\Models\ContributorModel())->pendingApprovalCount();
        if ($pc > 0): ?><span class="tn-badge"><?= $pc ?></span><?php endif;
      } catch(\Exception $e) {} ?>
    </a>
    <?php endif; ?>

    <?php if (\App\Core\Auth::can('manage_youtube')): ?>
    <div class="tn-nav-label mt-2">Automation</div>
    <a href="<?= $routeBase ?>/admin/youtube" class="tn-nav-item <?= isActive('/admin/youtube', $current) ?>">
      <i class="bi bi-youtube"></i> YouTube
    </a>
    <a href="<?= $routeBase ?>/admin/rss" class="tn-nav-item <?= isActive('/admin/rss', $current) ?>">
      <i class="bi bi-rss"></i> RSS Feeds
    </a>
    <?php endif; ?>

    <?php if (\App\Core\Auth::can('manage_settings')): ?>
    <div class="tn-nav-label mt-2">System</div>
    <a href="<?= $routeBase ?>/admin/users" class="tn-nav-item <?= isActive('/admin/users', $current) ?>">
      <i class="bi bi-people"></i> Users
    </a>
    <a href="<?= $routeBase ?>/admin/ads" class="tn-nav-item <?= isActive('/admin/ads', $current) ?>">
      <i class="bi bi-megaphone"></i> Ad Slots
    </a>
    <a href="<?= $routeBase ?>/admin/settings" class="tn-nav-item <?= isActive('/admin/settings', $current) ?>">
      <i class="bi bi-gear"></i> Settings
    </a>
    <?php endif; ?>

  </div>
  <!-- END SCROLLABLE BODY -->

  <!-- RATES WIDGET — above sticky footer -->
  <?php
  $_adminRates = [];
  try { $_adminRates = (new \App\Models\RateModel())->allForWidget(); } catch (\Exception $e) {}
  if (!empty($_adminRates)): ?>
  <div class="tn-sidebar-rates notranslate" translate="no">
    <?php foreach ($_adminRates as $_ar):
      if (in_array($_ar['type'], ['currency_usd','currency_gbp','currency_eur'])) continue;
      $icons = ['gold'=>'🥇','silver'=>'🥈','petrol'=>'⛽','diesel'=>'🛢️'];
      $icon = $icons[$_ar['type']] ?? '📊';
    ?>
    <div class="tn-sidebar-rate-item">
      <span class="tn-sr-icon"><?= $icon ?></span>
      <span class="tn-sr-label"><?= ucfirst($_ar['type']) ?></span>
      <span class="tn-sr-val">₹<?= number_format((float)$_ar['value'], $_ar['type']==='gold' ? 0 : 2) ?></span>
    </div>
    <?php endforeach; ?>
    <a href="<?= $routeBase ?>/admin/rates" class="tn-sr-edit">✏️ Update</a>
  </div>
  <?php endif; ?>

  <!-- STICKY FOOTER — always visible at bottom -->
  <div class="tn-sidebar-footer">
    <div class="tn-user-info">
      <div class="tn-user-avatar"><?= strtoupper(substr($auth['name'] ?? 'A', 0, 1)) ?></div>
      <div style="min-width:0;flex:1;overflow:hidden">
        <div class="tn-user-name"><?= htmlspecialchars($auth['name'] ?? '') ?></div>
        <div class="tn-user-role"><?= ucfirst(str_replace('_',' ',$auth['role_slug'] ?? '')) ?></div>
      </div>
      <a href="<?= $routeBase ?>/admin/logout" class="tn-logout" title="Logout">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>
  </div>

</nav>

<!-- MAIN CONTENT AREA -->
<div class="tn-main" id="main">

  <div class="tn-topbar">
    <button class="tn-sidebar-toggle" id="sidebarToggle">
      <i class="bi bi-list"></i>
    </button>
    <nav aria-label="breadcrumb" class="ms-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= $routeBase ?>/admin/dashboard">Admin</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle ?? '') ?></li>
      </ol>
    </nav>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="d-none d-md-inline text-muted small"><?= date('d M Y') ?></span>
      <a href="<?= BASE_URL ?>/public/" target="_blank" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-box-arrow-up-right"></i> View Site
      </a>
    </div>
  </div>

  <?php
  $alertType = \App\Core\Session::getFlash('alert_type');
  $alertMsg  = \App\Core\Session::getFlash('alert_msg');
  if ($alertType && $alertMsg):
  ?>
  <div class="tn-alert-wrap">
    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($alertMsg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
  <?php endif; ?>

  <div class="tn-content"><?= $content ?></div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>const r = '<?= rtrim(ASSET_URL, '/') ?>/public';</script>
<script src="<?= ASSET_URL ?>/public/assets/js/admin.js"></script>
</body>
</html>
