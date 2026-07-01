<?php use App\Core\Helper; ?>
<div class="main">
  <div class="cat-page-header">
    <div class="cat-page-bar"></div>
    <div>
      <h1 class="cat-page-title"><?= Helper::e($special['title_tamil'] ?: $special['title']) ?></h1>
      <?php if (!empty($special['description'])): ?>
      <p class="cat-page-desc"><?= Helper::e($special['description']) ?></p>
      <?php endif; ?>
      <div class="cat-page-meta"><?= number_format($total) ?> செய்திகள்</div>
    </div>
  </div>

  <?php if (empty($articles)): ?>
  <div class="empty-state"><div class="empty-icon">📰</div><p>No articles yet</p></div>
  <?php else: ?>
  <div class="top-stories-grid">
    <?php foreach ($articles as $a):
      $hasImg = !empty($a['image_url']); ?>
    <a href="<?= $r ?>/article/<?= Helper::e($a['slug']) ?>"
       class="story-card <?= $hasImg ? '' : 'story-card-no-img' ?>">
      <?php if ($hasImg): ?>
      <img src="<?= Helper::e($a['thumb_url'] ?: $a['image_url']) ?>"
           alt="<?= Helper::e($a['title']) ?>" loading="lazy">
      <?php endif; ?>
      <div class="story-card-body">
        <span class="ctag ctag-accent" style="--ac:#7F4FE0"><?= Helper::e($a['category_tamil'] ?: $a['category_name']) ?></span>
        <div class="story-card-title"><?= Helper::e($a['title']) ?></div>
        <?php if (!empty($a['excerpt'])): ?>
        <div class="story-card-excerpt"><?= Helper::e($hasImg ? mb_substr(strip_tags($a['excerpt']),0,100) : mb_substr(strip_tags($a['excerpt']),0,220)) ?></div>
        <?php endif; ?>
        <div class="card-meta">
          <span><?= Helper::timeAgo($a['published_at']) ?></span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php $queryExtra = ''; include VIEW_PATH . '/partials/pagination.php'; ?>
  <?php endif; ?>
</div>
