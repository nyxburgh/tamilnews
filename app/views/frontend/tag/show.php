<?php use App\Core\Helper; ?>
<div class="main">
  <div class="cat-page-header">
    <div class="cat-page-bar" style="background:#6B6A64"></div>
    <div>
      <h1 class="cat-page-title">🏷️ <?= htmlspecialchars($tag['name']) ?>
        <?php if ($tag['name_tamil']): ?>
        <span class="cat-page-en"><?= htmlspecialchars($tag['name_tamil']) ?></span>
        <?php endif; ?>
      </h1>
      <div class="cat-page-meta"><?= number_format($total) ?> articles · <?= number_format($tag['usage_count']) ?> total uses</div>
    </div>
  </div>

  <?php if (empty($articles)): ?>
  <div class="empty-state"><div style="font-size:48px">🏷️</div><p>No articles found for this tag</p></div>
  <?php else: ?>
  <div class="top-stories-grid">
    <?php foreach ($articles as $a): ?>
    <a href="<?= $r ?>/article/<?= htmlspecialchars($a['slug']) ?>" class="story-card">
      <img src="<?= $a['thumb_url'] ?? $a['image_url'] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&q=80' ?>"
           alt="<?= htmlspecialchars($a['title']) ?>" loading="lazy">
      <div class="story-card-body">
        <span class="cat-tag cat-red"><?= htmlspecialchars($a['category_name']) ?></span>
        <div class="story-card-title" style="margin-top:6px"><?= htmlspecialchars($a['title']) ?></div>
        <div class="card-meta">
          <span><?= Helper::timeAgo($a['published_at']) ?></span>
          <?php if ($a['view_count'] > 0): ?><span>👁 <?= number_format($a['view_count']) ?></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php
$queryExtra = '';
include VIEW_PATH . '/partials/pagination.php';
?>
</div>
