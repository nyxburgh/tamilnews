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
<meta name="csrf-token" content="<?= \App\Core\CSRF::token() ?>">
<meta name="base-url"   content="<?= ASSET_URL ?>">
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
    <a href="<?= $r ?>/portal/dashboard" class="ep-logo">
      <div class="ep-logo-icon">✏️</div>
      <div>
        <div class="ep-logo-title">
          <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900">வேள்</span><span style="color:#fff;background:#C0001A;padding:0 5px;border-radius:3px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;margin-left:2px">சுடர்</span>
        </div>
        <div class="ep-logo-sub">Chief Editor</div>
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
            <span class="ep-role-badge">Chief Editor</span>
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
      <a href="<?= $r ?>/admin/articles" class="ep-nav-item <?= (epActive('/admin/articles',$current) && !str_contains($current,'/pending')) ? 'active' : '' ?>">
        <i class="bi bi-file-earmark-text"></i> All Articles
      </a>
      <a href="<?= $r ?>/admin/articles/create" class="ep-nav-item">
        <i class="bi bi-plus-circle"></i> New Article
      </a>
      <a href="<?= $r ?>/admin/articles?status=review" class="ep-nav-item <?= ($_GET['status']??'')==='review'?'active':'' ?>">
        <i class="bi bi-hourglass-split"></i> Review Queue
        <?php
        try {
            $rc = (new \App\Models\ArticleModel())->countByStatus('review');
            if ($rc > 0): ?><span class="ep-badge ep-badge-warn"><?= $rc ?></span><?php endif;
        } catch (\Exception $e) {}
        ?>
      </a>
      <a href="<?= $r ?>/admin/articles/pending-edits" class="ep-nav-item <?= epActive('/pending-edits',$current) ?>">
        <i class="bi bi-pencil-square"></i> Pending Edits
      </a>

      <div class="ep-nav-label">Content</div>
      <a href="<?= $r ?>/admin/categories" class="ep-nav-item <?= epActive('/admin/categories',$current) ?>">
        <i class="bi bi-grid-3x3-gap"></i> Categories
      </a>
      <a href="<?= $r ?>/admin/tags" class="ep-nav-item <?= epActive('/admin/tags',$current) ?>">
        <i class="bi bi-tags"></i> Tags
      </a>
      <a href="<?= $r ?>/admin/media" class="ep-nav-item <?= epActive('/admin/media',$current) ?>">
        <i class="bi bi-images"></i> Media Library
      </a>
      <a href="<?= $r ?>/admin/special-categories" class="ep-nav-item <?= epActive('/admin/special-categories',$current) ?>">
        <i class="bi bi-flag"></i> Special Categories
      </a>

      <div class="ep-nav-label">Live & Premium</div>
      <a href="<?= $r ?>/admin/live-blog" class="ep-nav-item <?= epActive('/admin/live-blog',$current) ?>">
        <i class="bi bi-broadcast"></i> Live Blog
        <?php
        try {
            $lc = (int)\App\Core\Database::getInstance()->query("SELECT COUNT(*) FROM tn_live_blogs WHERE status='active'")->fetchColumn();
            if ($lc > 0): ?><span class="ep-badge ep-badge-live"><?= $lc ?></span><?php endif;
        } catch (\Exception $e) {}
        ?>
      </a>
      <a href="<?= $r ?>/admin/premium" class="ep-nav-item <?= epActive('/admin/premium',$current) ?>">
        <i class="bi bi-lock"></i> Premium Articles
      </a>

      <div class="ep-nav-label">People</div>
      <a href="<?= $r ?>/admin/contributors" class="ep-nav-item <?= epActive('/admin/contributors',$current) ?>">
        <i class="bi bi-person-badge"></i> Contributors
        <?php
        try {
            $pc = (new \App\Models\ContributorModel())->pendingApprovalCount();
            if ($pc > 0): ?><span class="ep-badge ep-badge-warn"><?= $pc ?></span><?php endif;
        } catch (\Exception $e) {}
        ?>
      </a>

      <div class="ep-nav-label">Insights</div>
      <a href="<?= $r ?>/admin/analytics" class="ep-nav-item <?= epActive('/admin/analytics',$current) ?>">
        <i class="bi bi-bar-chart-line"></i> Analytics
      </a>
      <a href="<?= $r ?>/admin/push" class="ep-nav-item <?= epActive('/admin/push',$current) ?>">
        <i class="bi bi-bell"></i> Push Notifications
      </a>

      <div class="ep-nav-label">Newspaper</div>
      <a href="<?= $r ?>/admin/newspaper" class="ep-nav-item <?= epActive('/admin/newspaper',$current) ?>">
        <i class="bi bi-newspaper"></i> E-Paper Archive
      </a>
<div class="ep-nav-label">Print</div>
<a href="<?= $r ?>/admin/print" class="ep-nav-item <?= epActive('/admin/print',$current) ?>">
  <i class="bi bi-printer"></i> Print Editions
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const r = '<?= ASSET_URL ?>';
// Sidebar toggle
const epSidebar = document.getElementById('epSidebar');
const epOverlay = document.getElementById('epSidebarOverlay');
document.getElementById('epSidebarToggle')?.addEventListener('click', () => {
  epSidebar?.classList.toggle('open');
  epOverlay?.classList.toggle('open');
});
epOverlay?.addEventListener('click', () => {
  epSidebar?.classList.remove('open');
  epOverlay?.classList.remove('open');
});
// User menu
function toggleEpMenu() {
  document.getElementById('epUserMenu')?.classList.toggle('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.ep-user')) document.getElementById('epUserMenu')?.classList.remove('open');
});
// Admin JS compatibility
const sidebarToggle = { addEventListener: () => {} };
const sidebar = epSidebar;
</script>
</body>
</html>
