<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Editor') ?> — Tamil News Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/assets/css/editor_portal.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/assets/css/ads.css" rel="stylesheet">
<meta name="csrf-token" content="<?= \App\Core\CSRF::token() ?>">
<meta name="base-url"   content="<?= ASSET_URL ?>">
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/css/article-form.css">
</head>
<body class="ep-body">

<?php
$auth      = \App\Core\Auth::user();
$role      = \App\Core\Auth::role();
$r         = ASSET_URL;
$baseUrl   = BASE_URL;
$current   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function epActive(string $path, string $current): string {
    return str_contains($current, $path) ? 'active' : '';
}

$notifCount = 0;
try {
    $notifCount = (new \App\Models\NotificationModel())->unreadCount(\App\Core\Auth::id() ?? 0);
} catch (\Exception $e) {}
?>

<!-- TOPBAR -->
<div class="ep-topbar">
  <div class="ep-topbar-inner">
    <button class="ep-sidebar-toggle" id="epSidebarToggle">☰</button>
    <a href="<?= $r ?>/portal/dashboard" class="ep-logo" style="text-decoration:none">
      <div class="ep-logo-icon">✏️</div>
      <div>
        <div class="ep-logo-title">
          <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900">தினத்</span><span style="color:#fff;background:#C0001A;padding:0 5px;border-radius:3px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;margin-left:2px">துளிர்</span>
        </div>
        <?php $epRoleLabels=["admin"=>"Admin","chief_editor"=>"Chief Editor","editor"=>"Editor","district_editor"=>"District Editor","category_editor"=>"Category Editor","senior_reporter"=>"Sr. Reporter","reporter"=>"Reporter"]; ?>
        <div class="ep-logo-sub"><?= $epRoleLabels[$role] ?? ucfirst(str_replace("_"," ",$role)) ?></div>
      </div>
    </a>
    <div class="ep-topbar-right">
      <a href="<?= $baseUrl ?>/public/" target="_blank" class="ep-topbar-btn">
        <i class="bi bi-box-arrow-up-right"></i> View Site
      </a>
      <a href="<?= $r ?>/portal/notifications" class="ep-notif-btn">
        <i class="bi bi-bell"></i>
        <?php if ($notifCount > 0): ?>
        <span class="ep-notif-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
        <?php endif; ?>
      </a>
      <div class="ep-user" onclick="toggleEpMenu()">
        <div class="ep-user-avatar"><?= strtoupper(substr($auth['name'] ?? 'E', 0, 1)) ?></div>
        <span class="ep-user-name d-none d-md-inline"><?= htmlspecialchars(explode(' ', $auth['name'] ?? '')[0]) ?></span>
        <div class="ep-user-dropdown" id="epUserMenu">
          <div class="ep-user-dropdown-header">
            <div class="fw-600"><?= htmlspecialchars($auth['name'] ?? '') ?></div>
            <div style="font-size:11px;color:#6B6A64"><?= htmlspecialchars($auth['email'] ?? '') ?></div>
            <span class="ep-role-badge"><?= $epRoleLabels[$role] ?? ucfirst(str_replace('_',' ',$role)) ?></span>
          </div>
          <a href="<?= $r ?>/portal/profile"  class="ep-user-dropdown-item"><i class="bi bi-person me-2"></i>My Profile</a>
          <a href="<?= $r ?>/logout"           class="ep-user-dropdown-item" style="color:#C0001A"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="ep-layout">
  <!-- SIDEBAR -->
  <div class="ep-sidebar" id="epSidebar">
    <div class="ep-sidebar-overlay" id="epSidebarOverlay"></div>
    <nav class="ep-nav">

      <div class="ep-nav-label">Editorial</div>
      <a href="<?= $r ?>/portal/dashboard" class="ep-nav-item <?= epActive('/portal/dashboard',$current) ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
        <?php if ($notifCount > 0): ?><span class="ep-badge"><?= $notifCount ?></span><?php endif; ?>
      </a>
      <a href="<?= $r ?>/portal/all-articles" class="ep-nav-item <?= (epActive('/portal/all-articles',$current) && !str_contains($current,'/pending')) ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i> All Articles
      </a>
      <a href="<?= $r ?>/portal/write" class="ep-nav-item">
        <i class="bi bi-plus-circle"></i> New Article
      </a>
      <a href="<?= $r ?>/portal/all-articles?status=review" class="ep-nav-item <?= ($_GET['status']??'')==='review'?'active':'' ?>">
        <i class="bi bi-hourglass-split"></i> Review Queue
        <?php
        try {
            $rc = (new \App\Models\ArticleModel())->countByStatus('review');
            if ($rc > 0): ?><span class="ep-badge ep-badge-warn"><?= $rc ?></span><?php endif;
        } catch (\Exception $e) {}
        ?>
      </a>
      <a href="<?= $r ?>/portal/all-articles/pending-edits" class="ep-nav-item <?= epActive('/pending-edits',$current) ?>">
        <i class="bi bi-pencil-square"></i> Pending Edits
      </a>

      <div class="ep-nav-label">Content</div>
      <a href="<?= $r ?>/portal/categories" class="ep-nav-item <?= epActive('/portal/categories',$current) ?>">
        <i class="bi bi-grid-3x3-gap"></i> Categories
      </a>
      <a href="<?= $r ?>/portal/tags" class="ep-nav-item <?= epActive('/portal/tags',$current) ?>">
        <i class="bi bi-tags"></i> Tags
      </a>
      <a href="<?= $r ?>/portal/media" class="ep-nav-item <?= epActive('/portal/media',$current) ?>">
        <i class="bi bi-images"></i> Media Library
      </a>
      <a href="<?= $r ?>/portal/special-categories" class="ep-nav-item <?= epActive('/portal/special-categories',$current) ?>">
        <i class="bi bi-flag"></i> Special Categories
      </a>

      <div class="ep-nav-label">Live & Premium</div>
      <a href="<?= $r ?>/portal/live-blog" class="ep-nav-item <?= epActive('/portal/live-blog',$current) ?>">
        <i class="bi bi-broadcast"></i> Live Blog
        <?php
        try {
            $lc = (int)\App\Core\Database::getInstance()->query("SELECT COUNT(*) FROM tn_live_blogs WHERE status='live'")->fetchColumn();
            if ($lc > 0): ?><span class="ep-badge ep-badge-live"><?= $lc ?></span><?php endif;
        } catch (\Exception $e) {}
        ?>
      </a>
      <a href="<?= $r ?>/portal/premium" class="ep-nav-item <?= epActive('/portal/premium',$current) ?>">
        <i class="bi bi-lock"></i> Premium Articles
      </a>

      <div class="ep-nav-label">People</div>
      <a href="<?= $r ?>/portal/contributors" class="ep-nav-item <?= epActive('/portal/contributors',$current) ?>">
        <i class="bi bi-person-badge"></i> Contributors
        <?php
        try {
            $pc = (new \App\Models\ContributorModel())->pendingApprovalCount();
            if ($pc > 0): ?><span class="ep-badge ep-badge-warn"><?= $pc ?></span><?php endif;
        } catch (\Exception $e) {}
        ?>
      </a>

      <div class="ep-nav-label">Insights</div>
      <a href="<?= $r ?>/portal/analytics" class="ep-nav-item <?= epActive('/portal/analytics',$current) ?>">
        <i class="bi bi-bar-chart-line"></i> Analytics
      </a>
      <a href="<?= $r ?>/admin/push" class="ep-nav-item <?= epActive('/admin/push',$current) ?>">
        <i class="bi bi-bell"></i> Push Notifications
      </a>

      <div class="ep-nav-label">Newspaper</div>
      <a href="<?= $r ?>/portal/newspaper" class="ep-nav-item <?= epActive('/portal/newspaper',$current) ?>">
        <i class="bi bi-newspaper"></i> E-Paper Archive
      </a>
<div class="ep-nav-label">Print</div>
<a href="<?= $r ?>/admin/print" class="ep-nav-item <?= epActive('/admin/print',$current) ?>">
  <i class="bi bi-printer"></i> Print Editions
</a>
      <div class="ep-nav-label">Advertising</div>
      <a href="<?= $r ?>/portal/ads" class="ep-nav-item <?= epActive('/portal/ads',$current) ?>">
        <i class="bi bi-megaphone"></i> Business Ads
        <?php try { $bac=(new \App\Models\BusinessAdModel())->pendingCount(); if($bac>0): ?><span class="ep-badge"><?= $bac ?></span><?php endif; } catch(\Exception $e) {} ?>
      </a>

      <div class="ep-nav-label">My Work</div>
      <a href="<?= $r ?>/portal/articles" class="ep-nav-item <?= epActive('/portal/articles',$current) ?>">
        <i class="bi bi-person-lines-fill"></i> My Articles
      </a>
      <a href="<?= $r ?>/portal/notifications" class="ep-nav-item <?= epActive('/portal/notifications',$current) ?>">
        <i class="bi bi-bell"></i> Notifications
      </a>
    </nav>
  </div>

  <!-- MAIN CONTENT -->
  <div class="ep-main" id="epMain">
    <!-- FLASH ALERT -->
    <?php
    $alertType = \App\Core\Session::getFlash('alert_type');
    $alertMsg  = \App\Core\Session::getFlash('alert_msg');
    if ($alertType && $alertMsg):
    ?>
    <div class="ep-alert-wrap">
      <div class="alert alert-<?= $alertType ?> alert-dismissible fade show mb-0">
        <?= htmlspecialchars($alertMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
    <?php endif; ?>

    <div class="ep-content">
      <?= $content ?>
    </div>
  </div>
</div>

<!-- MOBILE STICKY FOOTER (Chief Editor — 5 icons) -->
<nav class="ep-mob-footer">
  <a href="<?= $r ?>/portal/dashboard" class="ep-mob-item <?= str_contains($current,'/dashboard') ? 'active' : '' ?>">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard</span>
  </a>
  <a href="<?= $r ?>/portal/all-articles" class="ep-mob-item <?= (epActive('/portal/all-articles',$current) && !str_contains($current,'/create')) ? 'active' : '' ?>">
    <i class="bi bi-file-earmark-text"></i>
    <span>Articles</span>
  </a>
  <a href="<?= $r ?>/portal/write" class="ep-mob-write">
    <div class="ep-mob-write-btn"><i class="bi bi-pencil-square"></i></div>
    <span>Write</span>
  </a>
  <a href="<?= $r ?>/portal/media" class="ep-mob-item <?= epActive('/portal/media',$current) ? 'active' : '' ?>">
    <i class="bi bi-images"></i>
    <span>Media</span>
  </a>
  <div class="ep-mob-item" onclick="document.getElementById('epSidebarToggle')?.click()">
    <i class="bi bi-grid-3x3-gap-fill"></i>
    <span>Menu</span>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="<?= ASSET_URL ?>/assets/js/portal-nav.js"></script>
</body>
</html>
