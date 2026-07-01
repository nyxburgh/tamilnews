<?php use App\Core\Helper; ?>
<?php
function srE(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$heE         = 'srE';
$accentColor = '#6B6A64';
$labelText   = $q ? srE($q) : 'தேடல்';

$heroArticles = array_slice($articles ?? [], 0, 5);
$gridArticles = array_slice($articles ?? [], 5);
$h1 = $heroArticles[0] ?? null;
$h2 = $heroArticles[1] ?? null;
$h3 = $heroArticles[2] ?? null;
$h4 = $heroArticles[3] ?? null;
$h5 = $heroArticles[4] ?? null;
?>

<!-- SEARCH BAR -->
<form action="<?= $r ?>/search" method="GET" class="search-page-bar search-form-mt">
  <input type="text" name="q" value="<?= srE($q) ?>" placeholder="தேடு..." class="search-page-input" autofocus>
  <button type="submit" class="search-page-btn">தேடு 🔍</button>
</form>

<?php if ($q): ?>
<div class="sec-head">
  <span class="sec-head-bar sec-head-bar-dyn" style="--ac:<?= $accentColor ?>"></span>
  <span class="sec-head-title">"<?= srE($q) ?>"</span>
  <span class="sec-head-ta sec-head-count"><?= number_format($total) ?> முடிவுகள்</span>
</div>
<?php endif; ?>

<?php if ($q && empty($articles)): ?>
<div class="empty-state">
  <div class="empty-icon">🔍</div>
  <p>"<?= srE($q) ?>" க்கு எந்த முடிவும் கிடைக்கவில்லை</p>
  <p class="empty-sub">வேறு வார்த்தைகளில் தேடுங்கள்</p>
</div>
<?php elseif (!empty($articles)): ?>

<?php include VIEW_PATH . '/partials/_hero_section.php'; ?>

<?php if (!empty($gridArticles)): ?>
<div class="g4 g4-mt">
  <?php foreach ($gridArticles as $a): ?>
  <a href="<?= $r ?>/article/<?= srE($a['slug']) ?>" class="nc <?= empty($a['image_url']) ? 'nc-no-img' : '' ?>">
    <?php if (!empty($a['image_url'])): ?>
    <img src="<?= srE(rtrim(ASSET_URL,'/').'/public'.($a['thumb_url'] ?: $a['image_url'])) ?>" alt="<?= srE($a['title']) ?>" loading="lazy">
    <?php endif; ?>
    <div class="nc-body">
      <span class="ctag"><?= srE($a['category_tamil'] ?: $a['category_name']) ?></span>
      <div class="nc-title <?= empty($a['image_url']) ? 'nc-title-lg' : '' ?>"><?= srE($a['title']) ?></div>
      <?php if (empty($a['image_url']) && !empty($a['excerpt'])): ?>
      <div class="nc-no-img-excerpt"><?= srE(mb_substr(strip_tags($a['excerpt']), 0, 150)) ?></div>
      <?php endif; ?>
      <div class="hero4-meta notranslate" translate="no">
        <?= Helper::timeAgo($a['published_at']) ?>
        <?php if (($a['view_count']??0) > 0): ?> · 👁 <?= number_format($a['view_count']) ?><?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $queryExtra = '&q='.urlencode($q??''); include VIEW_PATH . '/partials/pagination.php'; ?>

<?php endif; ?>
