<?php
use App\Core\Helper;
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function artImg(array $a): string {
    return $a['thumb_url'] ?? $a['image_url'] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&q=80';
}
?>
<div class="main">
  <div class="cat-page-header">
    <div class="cat-page-bar" style="background:#6B6A64"></div>
    <div>
      <h1 class="cat-page-title">
        <?= $q ? '"' . e($q) . '" தேடல் முடிவுகள்' : 'தேடல்' ?>
      </h1>
      <?php if ($q): ?>
      <div class="cat-page-meta"><?= number_format($total) ?> முடிவுகள்</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- SEARCH BAR (large) -->
  <form action="<?= $r ?>/search" method="GET" class="search-page-bar">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="தேடு..." class="search-page-input" autofocus>
    <button type="submit" class="search-page-btn">தேடு 🔍</button>
  </form>

  <?php if ($q && empty($articles)): ?>
  <div class="empty-state">
    <div style="font-size:48px">🔍</div>
    <p>"<?= e($q) ?>" க்கு எந்த முடிவும் கிடைக்கவில்லை</p>
    <p style="font-size:13px;color:var(--gray-4)">வேறு வார்த்தைகளில் தேடுங்கள்</p>
  </div>
  <?php elseif (!empty($articles)): ?>
  <div class="top-stories-grid">
    <?php foreach ($articles as $a): ?>
    <a href="<?= $r ?>/article/<?= e($a['slug']) ?>" class="story-card">
      <img src="<?= artImg($a) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
      <div class="story-card-body">
        <span class="cat-tag cat-red"><?= e($a['category_name']) ?></span>
        <div class="story-card-title" style="margin-top:6px"><?= e($a['title']) ?></div>
        <div class="card-meta">
          <span><?= Helper::timeAgo($a['published_at']) ?></span>
          <?php if ($a['view_count'] > 0): ?><span>👁 <?= number_format($a['view_count']) ?></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <!-- PAGINATION -->
  <?php
$queryExtra = '&q='.urlencode($q??'');
include VIEW_PATH . '/partials/pagination.php';
?>
  <?php endif; ?>
</div>
