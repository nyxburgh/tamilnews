<?php use App\Core\Helper; ?>
<div class="main">
  <div class="cat-page-header">
    <div class="cat-page-bar" style="background:#C0001A"></div>
    <div>
      <h1 class="cat-page-title"><?= Helper::e($special['title_tamil'] ?: $special['title']) ?></h1>
      <?php if (!empty($special['description'])): ?>
      <p class="cat-page-desc"><?= Helper::e($special['description']) ?></p>
      <?php endif; ?>
      <div class="cat-page-meta"><?= number_format($total) ?> செய்திகள்</div>
    </div>
  </div>

  <?php if (empty($articles)): ?>
  <div class="empty-state"><div style="font-size:48px">📰</div><p>No articles yet</p></div>
  <?php else: ?>
  <div class="top-stories-grid">
    <?php foreach ($articles as $a): ?>
    <a href="<?= $r ?>/article/<?= Helper::e($a['slug']) ?>" class="story-card">
      <img src="<?= $a['image_url'] ?? 'https://via.placeholder.com/400x250' ?>"
           alt="<?= Helper::e($a['title']) ?>" loading="lazy">
      <div class="story-card-body">
        <span class="cat-tag cat-red"><?= Helper::e($a['category_name']) ?></span>
        <div class="story-card-title"><?= Helper::e($a['title']) ?></div>
        <div class="card-meta">
          <span><?= Helper::timeAgo($a['published_at']) ?></span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php
  $queryExtra = '';
  include VIEW_PATH . '/partials/pagination.php';
  ?>
  <?php endif; ?>
</div>
