<?php
use App\Core\Helper;

function artImg(array $a, string $s = 'thumb'): string {
    $p = $s === 'full' ? ($a['image_url'] ?? '') : ($a['thumb_url'] ?? $a['image_url'] ?? '');
    return $p ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=600&q=80';
}
function catClass(string $s): string {
    $m = ['tamil-nadu'=>'red','india'=>'blue','world'=>'teal','cinema'=>'purple',
          'sports'=>'green','technology'=>'blue','spiritual'=>'gold','jobs-education'=>'teal','business'=>'purple'];
    return 'cat-' . ($m[$s] ?? 'red');
}
function ta(string $d): string { return Helper::timeAgo($d); }
function xe(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/* ── Hero pool ── */
$h1 = $hero ?? ($heroSide[0] ?? null);
$h2 = $heroSide[0] ?? ($topStories[0] ?? null);
$h3 = $heroSide[1] ?? ($topStories[1] ?? null);
$h4 = $heroSide[2] ?? ($topStories[2] ?? null);
/* prevent h1 === h2 duplicate */
if ($h1 && $h2 && $h1['id'] === $h2['id']) {
    $h2 = $heroSide[1] ?? ($topStories[0] ?? null);
    $h3 = $heroSide[2] ?? ($topStories[1] ?? null);
    $h4 = $heroSide[3] ?? ($topStories[2] ?? null);
}

$catSections = [
    ['data'=>$tamilNadu??[], 'name'=>'Tamil Nadu',   'ta'=>'தமிழ்நாடு',       'slug'=>'tamil-nadu',      'color'=>'#C0001A'],
    ['data'=>$india??[],     'name'=>'India',         'ta'=>'இந்தியா',          'slug'=>'india',           'color'=>'#1877F2'],
    ['data'=>$world??[],     'name'=>'World',         'ta'=>'உலகம்',            'slug'=>'world',            'color'=>'#0891B2'],
    ['data'=>$cinema??[],    'name'=>'Cinema',        'ta'=>'சினிமா',           'slug'=>'cinema',          'color'=>'#7F77DD'],
    ['data'=>$sports??[],    'name'=>'Sports',        'ta'=>'விளையாட்டு',       'slug'=>'sports',          'color'=>'#1B6B2E'],
    ['data'=>$topStories??[],'name'=>'Top Stories',   'ta'=>'முக்கிய செய்திகள்','slug'=>'',               'color'=>'#C0001A'],
    ['data'=>$videos??[],    'name'=>'Videos',        'ta'=>'வீடியோ',           'slug'=>'video',           'color'=>'#FF0000'],
];
?>

<!-- Live blogs -->
<?php if (!empty($liveBlogs)): ?>
<div class="live-blogs-bar">
  <?php foreach ($liveBlogs as $lb): ?>
  <a href="<?= $r ?>/live/<?= xe($lb['slug']) ?>" class="live-blog-banner">
    <div class="live-blog-banner-dot"></div>
    <div class="live-blog-banner-label">LIVE</div>
    <div class="live-blog-banner-title"><?= xe($lb['title']) ?></div>
    <div class="live-blog-banner-meta"><?= $lb['entry_count'] ?> updates</div>
    <div class="live-blog-banner-follow">Follow Live →</div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════
     HERO SECTION
     Left (1fr): 1 big tall card
     Right (1fr): 3 stacked small cards
     Sidebar (240px): from layout
════════════════════════════════════════════ -->
<div class="hero4-grid">

  <!-- LEFT: big feature card -->
  <?php if ($h1): ?>
  <a href="<?= $r ?>/article/<?= xe($h1['slug']) ?>" class="hero4-big">
    <img src="<?= artImg($h1, 'full') ?>" alt="<?= xe($h1['title']) ?>" loading="eager">
    <div class="hero4-big-body">
      <?php if (!empty($h1['is_breaking'])): ?>
      <div class="breaking-badge" style="font-size:10px;margin-bottom:4px">
        <span class="ticker-dot"></span>BREAKING
      </div>
      <?php endif; ?>
      <span class="ctag <?= catClass($h1['category_slug'] ?? '') ?>">
        <?= xe($h1['category_tamil'] ?: $h1['category_name']) ?>
      </span>
      <div class="hero4-big-title"><?= xe($h1['title']) ?></div>
      <?php if (!empty($h1['excerpt'])): ?>
      <div class="hero4-big-excerpt"><?= xe(mb_substr(strip_tags($h1['excerpt']), 0, 100)) ?></div>
      <?php endif; ?>
      <div class="hero4-meta"><?= ta($h1['published_at']) ?></div>
    </div>
  </a>
  <?php else: ?>
  <div class="hero4-big hero4-empty"></div>
  <?php endif; ?>

  <!-- RIGHT: 3 stacked small cards -->
  <div class="hero4-right">
    <?php foreach ([$h2, $h3, $h4] as $a):
      if (!$a) continue; ?>
    <a href="<?= $r ?>/article/<?= xe($a['slug']) ?>" class="hero4-sm">
      <img src="<?= artImg($a) ?>" alt="<?= xe($a['title']) ?>" loading="lazy">
      <div class="hero4-sm-body">
        <span class="ctag <?= catClass($a['category_slug'] ?? '') ?>">
          <?= xe($a['category_tamil'] ?: $a['category_name']) ?>
        </span>
        <div class="hero4-sm-title"><?= xe($a['title']) ?></div>
        <div class="hero4-meta"><?= ta($a['published_at']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

</div><!-- /hero4-grid -->

<!-- Mobile sidebar ad -->
<div class="mob-sidebar-ad">
  <div class="ad-rotator" data-slot="square" data-cat="<?= $categoryId ?? 0 ?>"></div>
</div>

<!-- ══════════════════════════════════════════
     CATEGORY SECTIONS — 4-col grid each
════════════════════════════════════════════ -->
<?php foreach ($catSections as $sec):
  if (empty($sec['data'])) continue; ?>

<div class="sec-head">
  <span class="sec-head-bar" style="background:<?= $sec['color'] ?>"></span>
  <span class="sec-head-title"><?= $sec['name'] ?></span>
  <span class="sec-head-ta"><?= $sec['ta'] ?></span>
  <?php if ($sec['slug']): ?>
  <a href="<?= $r ?>/tamil-news/<?= $sec['slug'] ?>" class="sec-head-more">மேலும் →</a>
  <?php endif; ?>
</div>

<div class="g4">
  <?php foreach (array_slice($sec['data'], 0, 8) as $a): ?>
  <a href="<?= $r ?>/article/<?= xe($a['slug']) ?>" class="nc">
    <?php if (!empty($a['youtube_video_id'])): ?>
    <div class="nc-video-thumb">
      <img src="https://img.youtube.com/vi/<?= xe($a['youtube_video_id']) ?>/hqdefault.jpg"
           alt="<?= xe($a['title']) ?>" loading="lazy">
      <div class="nc-play">▶</div>
    </div>
    <?php else: ?>
    <img src="<?= artImg($a) ?>" alt="<?= xe($a['title']) ?>" loading="lazy">
    <?php endif; ?>
    <div class="nc-body">
      <span class="ctag <?= catClass($sec['slug'] ?: ($a['category_slug'] ?? '')) ?>">
        <?= xe($a['category_tamil'] ?: $a['category_name']) ?>
      </span>
      <div class="nc-title"><?= xe($a['title']) ?></div>
      <div class="hero4-meta">
        <?= ta($a['published_at']) ?>
        <?php if (($a['view_count'] ?? 0) > 0): ?>
        · 👁 <?= number_format($a['view_count']) ?>
        <?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<?php endforeach; ?>
