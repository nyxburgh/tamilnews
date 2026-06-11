<?php
use App\Core\Helper;

function catE(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function catImg(array $a, string $s='thumb'): string {
    $p = $s==='full' ? ($a['image_url']??'') : ($a['thumb_url']??$a['image_url']??'');
    return $p ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=600&q=80';
}
function catCC(string $s): string {
    $m=['tamil-nadu'=>'red','india'=>'blue','world'=>'teal','cinema'=>'purple','sports'=>'green','technology'=>'blue','spiritual'=>'gold'];
    return 'cat-'.($m[$s]??'red');
}

// Split: first 4 for hero, rest for grid
$heroArticles = array_slice($articles ?? [], 0, 4);
$gridArticles = array_slice($articles ?? [], 4);
$h1 = $heroArticles[0] ?? null;
$h2 = $heroArticles[1] ?? null;
$h3 = $heroArticles[2] ?? null;
$h4 = $heroArticles[3] ?? null;
$catColor  = $category['color'] ?? '#C0001A';
$catSlug   = $category['slug'] ?? '';
$catName   = $category['name_tamil'] ?: $category['name'];
?>

<!-- CATEGORY HEADER -->
<div class="sec-head" style="margin-top:14px">
  <span class="sec-head-bar" style="background:<?= $catColor ?>"></span>
  <span class="sec-head-title"><?= catE($category['name']) ?></span>
  <span class="sec-head-ta"><?= catE($catName) ?></span>
  <span class="sec-head-ta" style="margin-left:4px;color:#9A9890">(<?= number_format($total) ?> செய்திகள்)</span>
</div>

<!-- SUBCATEGORY PILLS -->
<?php if (!empty($subcategories)): ?>
<div class="subcat-pills">
  <a href="<?= $r ?>/tamil-news/<?= catE($catSlug) ?>"
     class="subcat-pill <?= empty($activeSubSlug) ? 'active' : '' ?>">அனைத்தும்</a>
  <?php foreach ($subcategories as $sub): ?>
  <a href="<?= $r ?>/tamil-news/<?= catE($catSlug) ?>?sub=<?= catE($sub['slug']) ?>"
     class="subcat-pill <?= $activeSubSlug === $sub['slug'] ? 'active' : '' ?>">
    <?= catE($sub['name_tamil'] ?: $sub['name']) ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($articles)): ?>
<div class="empty-state">
  <div style="font-size:48px">📰</div>
  <p>இந்த பிரிவில் இன்னும் செய்திகள் இல்லை</p>
</div>
<?php else: ?>

<!-- HERO: same pattern as home — left big + right 3 stacked -->
<?php if ($h1): ?>
<div class="hero4-grid">

  <!-- Left: big feature card -->
  <a href="<?= $r ?>/article/<?= catE($h1['slug']) ?>" class="hero4-big">
    <img src="<?= catImg($h1,'full') ?>" alt="<?= catE($h1['title']) ?>" loading="eager">
    <div class="hero4-big-body">
      <?php if (!empty($h1['is_breaking'])): ?>
      <div class="breaking-badge" style="font-size:10px;margin-bottom:4px"><span class="ticker-dot"></span>BREAKING</div>
      <?php endif; ?>
      <span class="ctag <?= catCC($catSlug) ?>"><?= catE($catName) ?></span>
      <div class="hero4-big-title"><?= catE($h1['title']) ?></div>
      <?php if (!empty($h1['excerpt'])): ?>
      <div class="hero4-big-excerpt"><?= catE(mb_substr(strip_tags($h1['excerpt']),0,100)) ?></div>
      <?php endif; ?>
      <div class="hero4-meta"><?= Helper::timeAgo($h1['published_at']) ?></div>
    </div>
  </a>

  <!-- Right: 3 stacked small cards -->
  <div class="hero4-right">
    <?php foreach ([$h2,$h3,$h4] as $a): ?>
    <?php if (!$a) continue; ?>
    <a href="<?= $r ?>/article/<?= catE($a['slug']) ?>" class="hero4-sm">
      <img src="<?= catImg($a) ?>" alt="<?= catE($a['title']) ?>" loading="lazy">
      <div class="hero4-sm-body">
        <span class="ctag <?= catCC($catSlug) ?>"><?= catE($catName) ?></span>
        <div class="hero4-sm-title"><?= catE($a['title']) ?></div>
        <div class="hero4-meta"><?= Helper::timeAgo($a['published_at']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

</div>
<?php endif; ?>

<!-- REST OF ARTICLES: 4-col grid -->
<?php if (!empty($gridArticles)): ?>
<div class="sec-head" style="margin-top:16px">
  <span class="sec-head-bar" style="background:<?= $catColor ?>"></span>
  <span class="sec-head-title">More</span>
  <span class="sec-head-ta"><?= catE($catName) ?> செய்திகள்</span>
</div>
<div class="g4">
  <?php foreach ($gridArticles as $a): ?>
  <a href="<?= $r ?>/article/<?= catE($a['slug']) ?>" class="nc">
    <img src="<?= catImg($a) ?>" alt="<?= catE($a['title']) ?>" loading="lazy">
    <div class="nc-body">
      <span class="ctag <?= catCC($catSlug) ?>"><?= catE($catName) ?></span>
      <div class="nc-title"><?= catE($a['title']) ?></div>
      <div class="hero4-meta">
        <?= Helper::timeAgo($a['published_at']) ?>
        <?php if (($a['view_count']??0) > 0): ?> · 👁 <?= number_format($a['view_count']) ?><?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- PAGINATION -->
<?php $queryExtra = ''; include VIEW_PATH . '/partials/pagination.php'; ?>

<?php endif; ?>
