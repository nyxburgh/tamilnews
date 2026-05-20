<?php
use App\Core\Helper;
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function artImg(array $a): string {
    return $a['thumb_url'] ?? $a['image_url'] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&q=80';
}
?>
<div class="main">

  <!-- CATEGORY HEADER -->
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

  <!-- SUBCATEGORY PILLS (mobile: top, desktop: side) -->
  <?php if (!empty($subcategories)): ?>
  <!-- Mobile pills — shown on top on small screens -->
  <div class="subcat-pills">
    <a href="<?= $r ?>/tamil-news/<?= htmlspecialchars($category['slug']) ?>"
       class="subcat-pill <?= empty($activeSubSlug) ? 'active' : '' ?>">அனைத்தும்</a>
    <?php foreach ($subcategories as $sub): ?>
    <a href="<?= $r ?>/tamil-news/<?= htmlspecialchars($category['slug']) ?>?sub=<?= htmlspecialchars($sub['slug']) ?>"
       class="subcat-pill <?= $activeSubSlug === $sub['slug'] ? 'active' : '' ?>">
      <?= htmlspecialchars($sub['name_tamil'] ?: $sub['name']) ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="<?= !empty($subcategories) ? 'cat-with-side' : 'two-col' ?>">

    <?php if (!empty($subcategories)): ?>
    <!-- DESKTOP SIDE MENU -->
    <aside class="cat-side-menu">
      <div class="cat-side-title">பிரிவுகள்</div>
      <a href="<?= $r ?>/tamil-news/<?= htmlspecialchars($category['slug']) ?>"
         class="cat-side-item <?= empty($activeSubSlug) ? 'active' : '' ?>">
        <i class="bi bi-grid-3x3-gap"></i> அனைத்தும்
      </a>
      <?php foreach ($subcategories as $sub): ?>
      <a href="<?= $r ?>/tamil-news/<?= htmlspecialchars($category['slug']) ?>?sub=<?= htmlspecialchars($sub['slug']) ?>"
         class="cat-side-item <?= $activeSubSlug === $sub['slug'] ? 'active' : '' ?>">
        <i class="bi bi-chevron-right"></i>
        <?= htmlspecialchars($sub['name_tamil'] ?: $sub['name']) ?>
      </a>
      <?php endforeach; ?>
    </aside>
    <?php endif; ?>

    <div>
      <!-- ARTICLES GRID -->
      <?php if (empty($articles)): ?>
      <div class="empty-state">
        <div style="font-size:48px">📰</div>
        <p>இந்த பிரிவில் இன்னும் செய்திகள் இல்லை</p>
      </div>
      <?php else: ?>
      <div class="top-stories-grid">
        <?php foreach ($articles as $a): ?>
        <a href="<?= $r ?>/article/<?= e($a['slug']) ?>" class="story-card">
          <img src="<?= artImg($a) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
          <div class="story-card-body">
            <?php if ($a['is_breaking']): ?>
            <div class="breaking-badge" style="font-size:10px;padding:2px 8px;margin-bottom:6px"><span class="ticker-dot"></span> BREAKING</div>
            <?php endif; ?>
            <div class="story-card-title"><?= e($a['title']) ?></div>
            <?php if ($a['excerpt']): ?>
            <div class="story-card-excerpt"><?= e($a['excerpt']) ?></div>
            <?php endif; ?>
            <div class="card-meta">
              <span><?= Helper::timeAgo($a['published_at']) ?></span>
              <?php if ($a['view_count'] > 0): ?><span>👁 <?= number_format($a['view_count']) ?></span><?php endif; ?>
              <?php if ($a['rating_avg'] > 0): ?><span>⭐ <?= number_format((float)$a['rating_avg'], 1) ?></span><?php endif; ?>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- PAGINATION -->
      <?php
$queryExtra = '';
include VIEW_PATH . '/partials/pagination.php';
?>
      <?php endif; ?>
    </div>
  </div>
</div>
