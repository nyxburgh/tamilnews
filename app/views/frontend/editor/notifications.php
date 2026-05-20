<?php use App\Core\Helper; ?>
<div class="portal-page-header">
  <h2 class="portal-page-title">🔔 Notifications</h2>
  <form method="POST" action="<?= $r ?>/portal/notifications/read">
    <?= \App\Core\CSRF::field() ?>
    <button class="btn btn-sm btn-outline-secondary">Mark all read</button>
  </form>
</div>

<div class="portal-card">
  <?php if (empty($notifications)): ?>
  <div class="portal-card-body text-center py-5" style="color:var(--portal-muted)">
    <div style="font-size:40px;margin-bottom:12px">🔔</div>
    <p>No notifications yet</p>
  </div>
  <?php else: ?>
  <?php
  $icons = [
    'article_submitted' => '📝',
    'article_approved'  => '✅',
    'article_rejected'  => '❌',
    'article_published' => '🟢',
    'auto_published'    => '⚡',
    'edit_submitted'    => '✏️',
    'edit_approved'     => '✅',
    'edit_rejected'     => '❌',
    'escalated'         => '📤',
  ];
  ?>
  <?php foreach ($notifications as $n): ?>
  <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 20px;border-bottom:1px solid var(--portal-gray1);<?= !$n['is_read'] ? 'background:rgba(59,130,246,.04)' : '' ?>">
    <div style="font-size:22px;flex-shrink:0;line-height:1;margin-top:2px"><?= $icons[$n['type']] ?? '🔔' ?></div>
    <div style="flex:1">
      <div style="font-size:13.5px;font-weight:<?= $n['is_read'] ? '400' : '600' ?>;color:var(--portal-text)">
        <?= htmlspecialchars($n['message']) ?>
      </div>
      <?php if (!empty($n['article_title'])): ?>
      <a href="<?= $r ?>/admin/articles/edit/<?= $n['article_id'] ?>"
         style="font-size:12px;color:var(--portal-muted);margin-top:2px;display:block">
        → <?= htmlspecialchars(mb_substr($n['article_title'], 0, 60)) ?>
      </a>
      <?php endif; ?>
      <div style="font-size:11px;color:var(--portal-muted);margin-top:4px">
        <?= Helper::timeAgo($n['created_at']) ?>
        <?php if (!empty($n['from_name'])): ?>· by <?= htmlspecialchars($n['from_name']) ?><?php endif; ?>
        <?php if (!$n['is_read']): ?><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#3b82f6;margin-left:6px;vertical-align:middle"></span><?php endif; ?>
      </div>
    </div>
    <?php if (!empty($n['article_id'])): ?>
    <a href="<?= $r ?>/admin/articles/edit/<?= $n['article_id'] ?>"
       class="btn btn-sm btn-outline-secondary" style="flex-shrink:0">View</a>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
