<?php use App\Core\Helper; ?>
<?php
function spE(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$heE         = 'spE';
$accentColor = '#7F4FE0';
$labelText   = 'சிறப்பு கட்டுரை';

$heroArticles = array_slice($articles ?? [], 0, 5);
$gridArticles = array_slice($articles ?? [], 5);
$h1 = $heroArticles[0] ?? null;
$h2 = $heroArticles[1] ?? null;
$h3 = $heroArticles[2] ?? null;
$h4 = $heroArticles[3] ?? null;
$h5 = $heroArticles[4] ?? null;
?>

<div class="sec-head sec-head-mt">
  <span class="sec-head-bar sec-head-bar-dyn" style="--ac:<?= $accentColor ?>"></span>
  <span class="sec-head-title">Special Articles</span>
  <span class="sec-head-ta">சிறப்புக் கட்டுரைகள்</span>
  <span class="sec-head-ta sec-head-count">(<?= number_format($total) ?>)</span>
</div>

<?php if (empty($articles)): ?>
<div class="empty-state">
  <div class="empty-icon">✍️</div>
  <p>இன்னும் சிறப்புக் கட்டுரைகள் இல்லை</p>
</div>
<?php else: ?>

<?php include VIEW_PATH . '/partials/_hero_section.php'; ?>

<?php if (!empty($gridArticles)): ?>
<div class="sec-head sec-head-mt2">
  <span class="sec-head-bar sec-head-bar-dyn" style="--ac:<?= $accentColor ?>"></span>
  <span class="sec-head-title">More</span>
  <span class="sec-head-ta">சிறப்புக் கட்டுரைகள்</span>
</div>
<div class="g4">
  <?php foreach ($gridArticles as $a): ?>
  <a href="<?= $r ?>/article/<?= spE($a['slug']) ?>" class="nc <?= empty($a['image_url']) ? 'nc-no-img' : '' ?>">
    <?php if (!empty($a['image_url'])): ?>
    <img src="<?= spE(rtrim(ASSET_URL,'/').'/public'.($a['thumb_url'] ?: $a['image_url'])) ?>" alt="<?= spE($a['title']) ?>" loading="lazy">
    <?php endif; ?>
    <div class="nc-body">
      <span class="ctag ctag-accent" style="--ac:<?= $accentColor ?>">சிறப்பு</span>
      <div class="nc-title <?= empty($a['image_url']) ? 'nc-title-lg' : '' ?>"><?= spE($a['title']) ?></div>
      <?php if (empty($a['image_url']) && !empty($a['excerpt'])): ?>
      <div class="nc-no-img-excerpt"><?= spE(mb_substr(strip_tags($a['excerpt']), 0, 150)) ?></div>
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

<?php $queryExtra = ''; include VIEW_PATH . '/partials/pagination.php'; ?>

<?php endif; ?>
