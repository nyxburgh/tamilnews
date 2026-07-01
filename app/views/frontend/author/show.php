<?php use App\Core\Helper; ?>
<?php
function auE(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$heE         = 'auE';
$accentColor = '#C0001A';
$labelText   = $author['name'];

$heroArticles = array_slice($articles ?? [], 0, 5);
$gridArticles = array_slice($articles ?? [], 5);
$h1 = $heroArticles[0] ?? null;
$h2 = $heroArticles[1] ?? null;
$h3 = $heroArticles[2] ?? null;
$h4 = $heroArticles[3] ?? null;
$h5 = $heroArticles[4] ?? null;
?>

<!-- AUTHOR HEADER -->
<div class="sec-head sec-head-mt">
  <span class="sec-head-bar sec-head-bar-dyn" style="--ac:<?= $accentColor ?>"></span>
  <div class="author-header-inline">
    <?php if (!empty($author['avatar'])): ?>
    <img src="<?= auE($author['avatar']) ?>" class="author-header-avatar" alt="">
    <?php else: ?>
    <div class="author-header-initials"><?= strtoupper(substr($author['name'],0,1)) ?></div>
    <?php endif; ?>
    <div>
      <span class="sec-head-title"><?= auE($author['name']) ?></span>
      <span class="author-header-role"><?= auE($author['role_name'] ?? '') ?></span>
      <span class="sec-head-ta sec-head-count">(<?= number_format($total) ?> articles)</span>
    </div>
  </div>
</div>

<?php if (empty($articles)): ?>
<div class="empty-state"><div class="empty-icon">📝</div><p>No articles published yet</p></div>
<?php else: ?>

<?php include VIEW_PATH . '/partials/_hero_section.php'; ?>

<?php if (!empty($gridArticles)): ?>
<div class="g4 g4-mt">
  <?php foreach ($gridArticles as $a): ?>
  <a href="<?= $r ?>/article/<?= auE($a['slug']) ?>" class="nc <?= empty($a['image_url']) ? 'nc-no-img' : '' ?>">
    <?php if (!empty($a['image_url'])): ?>
    <img src="<?= auE($a['thumb_url'] ?: $a['image_url']) ?>" alt="<?= auE($a['title']) ?>" loading="lazy">
    <?php endif; ?>
    <div class="nc-body">
      <span class="ctag"><?= auE($a['category_tamil'] ?: $a['category_name']) ?></span>
      <div class="nc-title <?= empty($a['image_url']) ? 'nc-title-lg' : '' ?>"><?= auE($a['title']) ?></div>
      <?php if (empty($a['image_url']) && !empty($a['excerpt'])): ?>
      <div class="nc-no-img-excerpt"><?= auE(mb_substr(strip_tags($a['excerpt']), 0, 150)) ?></div>
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
