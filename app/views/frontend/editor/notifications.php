<?php use App\Core\{Helper, Auth, CSRF};

$isAdmin = in_array(Auth::role(), ['admin','chief_editor']);

$icons = [
  'article_submitted'      => '📝',
  'article_approved'       => '✅',
  'article_rejected'       => '❌',
  'article_published'      => '🟢',
  'auto_published'         => '⚡',
  'edit_submitted'         => '✏️',
  'edit_approved'          => '✅',
  'edit_rejected'          => '❌',
  'escalated'              => '📤',
  'business_ad_submitted'  => '📢',
  'business_ad_approved'   => '✅',
  'business_ad_rejected'   => '❌',
];

// Determine article link base per role
$articleBase = $isAdmin ? '/admin/articles/edit/' : '/portal/articles/edit/';
$adBase      = $isAdmin ? '/admin/business-ads/show/' : '/portal/ads/show/';
$pnBase      = $isAdmin ? '/admin/photo-news/edit/' : '/portal/photo-news/edit/';

// Ad notification types
$adTypes = ['business_ad_submitted','business_ad_approved','business_ad_rejected'];
?>

<div class="portal-page-header">
  <h2 class="portal-page-title">🔔 Notifications</h2>
  <form method="POST" action="<?= $r ?>/portal/notifications/read">
    <?= CSRF::field() ?>
    <button class="btn btn-sm btn-outline-secondary">Mark all read</button>
  </form>
</div>

<div class="portal-card">
  <?php if (empty($notifications)): ?>
  <div class="portal-card-body text-center py-5" style="color:var(--portal-muted)">
    <div style="font-size:40px;margin-bottom:12px">🔔</div>
    <p>No notifications yet.</p>
  </div>
  <?php else: ?>
  <?php foreach ($notifications as $n):
    $isAd  = in_array($n['type'], $adTypes);
    $isPn  = in_array($n['type'], ['photo_news_submit','photo_news_resubmit']);
    $refId = $n['article_id'] ?? null;
    if ($isAd) {
      $viewUrl = $refId ? $r . $adBase . $refId : null;
    } elseif ($isPn) {
      $viewUrl = $refId ? $r . $pnBase . $refId : null;
    } else {
      $viewUrl = ($refId && !empty($n['article_title'])) ? $r . $articleBase . $refId : null;
    }
    $unread = !$n['is_read'];
  ?>
  <div class="notif-row<?= $unread ? ' notif-unread' : '' ?>">
    <div class="notif-icon"><?= $icons[$n['type']] ?? '🔔' ?></div>
    <div class="notif-body">
      <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
      <?php if ($isPn && $viewUrl): ?>
      <a href="<?= $viewUrl ?>" class="notif-sub-link">→ View Photo News</a>
      <?php elseif (!$isAd && !empty($n['article_title'])): ?>
      <a href="<?= $viewUrl ?>" class="notif-sub-link">
        → <?= htmlspecialchars(mb_substr($n['article_title'], 0, 70)) ?>
      </a>
      <?php endif; ?>
      <div class="notif-meta">
        <?= Helper::timeAgo($n['created_at']) ?>
        <?php if (!empty($n['from_name'])): ?> · <?= htmlspecialchars($n['from_name']) ?><?php endif; ?>
        <?php if ($unread): ?><span class="notif-dot"></span><?php endif; ?>
      </div>
    </div>
    <?php if ($viewUrl): ?>
    <a href="<?= $viewUrl ?>" class="btn btn-sm btn-outline-secondary notif-view-btn">View</a>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
