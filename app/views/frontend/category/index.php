<?php use App\Core\Helper; ?>
<?php
function catE(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$heE = 'catE';

$catColor = $category['color'] ?? '#C0001A';
$catSlug  = $category['slug'] ?? '';
$catName  = $category['name_tamil'] ?: $category['name'];
$labelText = $catName;
$accentColor = $catColor;

$heroArticles = array_slice($articles ?? [], 0, 5);
$gridArticles = array_slice($articles ?? [], 5);
$h1 = $heroArticles[0] ?? null;
$h2 = $heroArticles[1] ?? null;
$h3 = $heroArticles[2] ?? null;
$h4 = $heroArticles[3] ?? null;
$h5 = $heroArticles[4] ?? null;
?>

<!-- CATEGORY HEADER -->
<div class="sec-head sec-head-mt">
  <span class="sec-head-bar sec-head-bar-dyn" style="--ac:<?= $catColor ?>"></span>
  <span class="sec-head-title"><?= catE($category['name']) ?></span>
  <span class="sec-head-ta"><?= catE($catName) ?></span>
  <span class="sec-head-ta sec-head-count">(<?= number_format($total) ?> செய்திகள்)</span>
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
  <div class="empty-icon">📰</div>
  <p>இந்த பிரிவில் இன்னும் செய்திகள் இல்லை</p>
</div>
<?php else: ?>

<?php include VIEW_PATH . '/partials/_hero_section.php'; ?>

<!-- REST: 4-col grid -->
<?php if (!empty($gridArticles)): ?>
<div class="sec-head sec-head-mt2">
  <span class="sec-head-bar sec-head-bar-dyn" style="--ac:<?= $catColor ?>"></span>
  <span class="sec-head-title">More</span>
  <span class="sec-head-ta"><?= catE($catName) ?> செய்திகள்</span>
</div>
<div class="g4">
  <?php foreach ($gridArticles as $a): ?>
  <a href="<?= $r ?>/article/<?= catE($a['slug']) ?>" class="nc <?= empty($a['image_url']) ? 'nc-no-img' : '' ?>">
    <?php if (!empty($a['image_url'])): ?>
    <img src="<?= catE(rtrim(ASSET_URL,'/').'/public'.($a['thumb_url'] ?: $a['image_url'])) ?>" alt="<?= catE($a['title']) ?>" loading="lazy">
    <?php endif; ?>
    <div class="nc-body">
      <span class="ctag ctag-accent" style="--ac:<?= $catColor ?>"><?= catE($catName) ?></span>
      <div class="nc-title <?= empty($a['image_url']) ? 'nc-title-lg' : '' ?>"><?= catE($a['title']) ?></div>
      <?php if (empty($a['image_url']) && !empty($a['excerpt'])): ?>
      <div class="nc-no-img-excerpt"><?= catE(mb_substr(strip_tags($a['excerpt']), 0, 150)) ?></div>
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

<!-- INFINITE SCROLL SENTINEL -->
<div id="catScrollSentinel" class="scroll-sentinel"></div>
<div id="catScrollSpinner" class="scroll-spinner" style="display:none">
  <span class="scroll-spinner-dot"></span>
  <span class="scroll-spinner-dot"></span>
  <span class="scroll-spinner-dot"></span>
</div>
<div id="catScrollEnd" class="scroll-end-msg" style="display:none">— முடிந்தது —</div>

<?php endif; ?>

<!-- DISTRICT WIDGET — Tamil Nadu category only -->
<?php if (!empty($isTamilNadu) && !empty($districts)): ?>
<div class="district-widget">
  <div class="sec-head sec-head-mt">
    <span class="sec-head-bar-dyn" style="--ac:#C0001A"></span>
    <span class="sec-head-title">மாவட்டங்கள்</span>
    <span class="sec-head-ta">Districts</span>
  </div>
  <div class="district-grid notranslate" translate="no">
    <a href="<?= $r ?>/tamil-news/<?= catE($catSlug) ?>"
       class="district-chip <?= !$activeDistrictId ? 'active' : '' ?>">
      அனைத்தும்
    </a>
    <?php foreach ($districts as $d): ?>
    <a href="<?= $r ?>/tamil-news/<?= catE($catSlug) ?>?district=<?= $d['id'] ?>"
       class="district-chip <?= $d['id'] == $activeDistrictId ? 'active' : '' ?>">
      <?= catE($d['name']) ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
