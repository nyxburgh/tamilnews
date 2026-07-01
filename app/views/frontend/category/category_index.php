<?php
use App\Core\Helper;
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<div class="main">

  <div class="cat-page-header">
    <div class="cat-page-bar"></div>
    <div>
      <h1 class="cat-page-title">
        <?= e($category['name_tamil'] ?: $category['name']) ?>
        <?php if ($category['name_tamil']): ?>
        <span class="cat-page-en"><?= e($category['name']) ?></span>
        <?php endif; ?>
      </h1>
      <?php if (!empty($category['description'])): ?>
      <p class="cat-page-desc"><?= e($category['description']) ?></p>
      <?php endif; ?>
      <div class="cat-page-meta"><?= number_format($total) ?> செய்திகள்</div>
    </div>
  </div>

  <div class="two-col">
    <div>
      <?php if (empty($articles)): ?>
      <div class="empty-state"><div class="empty-icon">📰</div><p>இந்த பிரிவில் இன்னும் செய்திகள் இல்லை</p></div>
      <?php else: ?>
      <div class="top-stories-grid">
        <?php foreach ($articles as $a):
          $hasImg = !empty($a['image_url']); ?>
        <a href="<?= $r ?>/article/<?= e($a['slug']) ?>"
           class="story-card <?= $hasImg ? '' : 'story-card-no-img' ?>">
          <?php if ($hasImg): ?>
          <img src="<?= e(rtrim(ASSET_URL,'/').'/public'.($a['thumb_url'] ?: $a['image_url'])) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
          <?php endif; ?>
          <div class="story-card-body">
            <?php if ($a['is_breaking']): ?>
            <div class="breaking-badge breaking-badge-sm"><span class="ticker-dot"></span> BREAKING</div>
            <?php endif; ?>
            <div class="story-card-title"><?= e($a['title']) ?></div>
            <?php if (!empty($a['excerpt'])): ?>
            <div class="story-card-excerpt"><?= e($hasImg ? mb_substr(strip_tags($a['excerpt']),0,100) : mb_substr(strip_tags($a['excerpt']),0,220)) ?></div>
            <?php endif; ?>
            <div class="card-meta">
              <span><?= Helper::timeAgo($a['published_at']) ?></span>
              <?php if ($a['view_count'] > 0): ?><span>👁 <?= number_format($a['view_count']) ?></span><?php endif; ?>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php $queryExtra = ''; include VIEW_PATH . '/partials/pagination.php'; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
