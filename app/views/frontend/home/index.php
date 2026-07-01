<?php
use App\Core\Helper;

function artImg(array $a, string $s = 'thumb'): string {
    $raw = $s === 'full' ? ($a['image_url'] ?? '') : ($a['thumb_url'] ?? $a['image_url'] ?? '');
    return $raw ? rtrim(ASSET_URL,'/').'/public/'.ltrim($raw,'/') : '';
}
function catClass(string $s): string {
    $m = ['tamil-nadu'=>'red','india'=>'blue','world'=>'teal','cinema'=>'purple',
          'sports'=>'green','technology'=>'blue','spiritual'=>'gold','jobs-education'=>'teal','business'=>'purple'];
    return 'cat-' . ($m[$s] ?? 'red');
}
function ta(string $d): string { return Helper::timeAgo($d); }
function xe(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/* ── Hero pool: 1 big (left) + 4 small (right 2x2 grid) ── */
$h1 = $hero ?? ($heroSide[0] ?? null);
$_sidePool = $heroSide ?? [];
if ($h1) {
    // Drop the side item that duplicates the big card, if any
    $_sidePool = array_values(array_filter($_sidePool, fn($x) => $x['id'] !== $h1['id']));
}
$h2 = $_sidePool[0] ?? null;
$h3 = $_sidePool[1] ?? null;
$h4 = $_sidePool[2] ?? null;
$h5 = $_sidePool[3] ?? null;

$catSections = [
    ['data'=>$tamilNadu??[], 'name'=>'Tamil Nadu',   'ta'=>'தமிழ்நாடு',       'slug'=>'tamil-nadu',      'color'=>'#C0001A'],
    ['data'=>$india??[],     'name'=>'India',         'ta'=>'இந்தியா',          'slug'=>'india',           'color'=>'#1877F2'],
    ['data'=>$world??[],     'name'=>'World',         'ta'=>'உலகம்',            'slug'=>'world',            'color'=>'#0891B2'],
    ['data'=>$cinema??[],    'name'=>'Cinema',        'ta'=>'சினிமா',           'slug'=>'cinema',          'color'=>'#7F77DD'],
    ['data'=>$sports??[],    'name'=>'Sports',        'ta'=>'விளையாட்டு',       'slug'=>'sports',          'color'=>'#1B6B2E'],
    ['data'=>$topStories??[],'name'=>'Top Stories',   'ta'=>'முக்கிய செய்திகள்','slug'=>'',               'color'=>'#C0001A'],
    ['data'=>$videos??[],    'name'=>'Videos',        'ta'=>'வீடியோ',           'slug'=>'video',           'color'=>'#FF0000'],
    ['data'=>$special??[],   'name'=>'Special Articles','ta'=>'சிறப்புக் கட்டுரைகள்','slug'=>'','url'=>$r.'/special-articles','color'=>'#7F4FE0','isSpecial'=>true],
];
?>

<!-- Live blogs -->
<?php /* ── Mobile Splash Screen — shows once per session ── */ ?>
<div id="mobileSplash" style="display:none">
  <div class="msplash-inner">
    <div class="msplash-logo">
      <div class="msplash-name-ta">தினத்துளிர்</div>
      <div class="msplash-name-en">Thinathulir</div>
      <div class="msplash-tagline">அரசியல் பழகு — அறம் செய்</div>
    </div>
    <div class="msplash-ad notranslate" translate="no">
      <div class="ad-rotator" data-slot="square_a" data-cat="0"></div>
    </div>
    <div class="msplash-bar-wrap">
      <div class="msplash-bar" id="splashBar"></div>
    </div>
    <button class="msplash-skip" id="splashSkip">Skip →</button>
  </div>
</div>

<style>
#mobileSplash {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: #C0001A;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  transition: opacity .6s ease;
}
#mobileSplash.fade-out { opacity: 0; pointer-events: none; }
.msplash-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  padding: 32px 20px;
  width: 100%;
  max-width: 360px;
}
.msplash-logo { text-align: center; }
.msplash-name-ta {
  font-family: 'Noto Sans Tamil', sans-serif;
  font-size: 36px;
  font-weight: 900;
  color: #fff;
  line-height: 1.1;
}
.msplash-name-en {
  font-family: 'Inter', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: rgba(255,255,255,.7);
  letter-spacing: 3px;
  text-transform: uppercase;
  margin-top: 6px;
}
.msplash-tagline {
  font-family: 'Noto Sans Tamil', sans-serif;
  font-size: 13px;
  color: rgba(255,255,255,.6);
  margin-top: 8px;
}
.msplash-ad {
  background: rgba(255,255,255,.08);
  border-radius: 12px;
  padding: 8px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 120px;
}
.msplash-bar-wrap {
  width: 100%;
  height: 3px;
  background: rgba(255,255,255,.2);
  border-radius: 2px;
  overflow: hidden;
}
.msplash-bar {
  height: 100%;
  width: 100%;
  background: #fff;
  border-radius: 2px;
  transform-origin: left;
  animation: splashProgress 15s linear forwards;
}
@keyframes splashProgress {
  from { transform: scaleX(1); }
  to   { transform: scaleX(0); }
}
.msplash-skip {
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.3);
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 20px;
  border-radius: 20px;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
}
.msplash-skip:active { background: rgba(255,255,255,.25); }
</style>

<script>
(function () {
  var splash = document.getElementById('mobileSplash');
  var skip   = document.getElementById('splashSkip');
  if (!splash) return;

  // Mobile only + first visit this session
  if (window.innerWidth > 768 || sessionStorage.getItem('splash_shown')) {
    splash.remove();
    return;
  }

  splash.style.display = 'flex';
  sessionStorage.setItem('splash_shown', '1');

  function dismiss() {
    splash.classList.add('fade-out');
    setTimeout(function () { splash.remove(); }, 650);
  }

  var timer = setTimeout(dismiss, 15000);

  skip.addEventListener('click', function () {
    clearTimeout(timer);
    dismiss();
  });
}());
</script>


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

  <!-- LEFT: 1 big featured card -->
  <?php if ($h1): ?>
  <a href="<?= $r ?>/article/<?= xe($h1['slug']) ?>" class="hero4-big-v2">
    <img src="<?= artImg($h1, 'full') ?>" alt="<?= xe($h1['title']) ?>" loading="eager">
    <div class="hero4-big-v2-body">
      <?php if (!empty($h1['is_breaking'])): ?>
      <div class="breaking-badge">
        <span class="ticker-dot"></span>BREAKING
      </div>
      <?php endif; ?>
      <span class="ctag <?= catClass($h1['category_slug'] ?? '') ?>">
        <?= xe($h1['category_tamil'] ?: $h1['category_name']) ?>
      </span>
      <div class="hero4-card-title"><?= xe($h1['title']) ?></div>
      <?php if (!empty($h1['excerpt'])): ?>
      <div class="hero4-card-excerpt"><?= xe(mb_substr(strip_tags($h1['excerpt']), 0, 110)) ?></div>
      <?php endif; ?>
      <div class="hero4-meta notranslate" translate="no"><?= ta($h1['published_at']) ?></div>
    </div>
  </a>
  <?php endif; ?>

  <!-- RIGHT: 2x2 grid of regular news cards (same style as category pages) -->
  <div class="hero4-right-v2">
    <?php foreach ([$h2, $h3, $h4] as $a):
      if (!$a) continue; ?>
    <a href="<?= $r ?>/article/<?= xe($a['slug']) ?>" class="nc <?= empty($a['image_url']) ? 'nc-no-img' : '' ?>">
      <?php if (!empty($a['image_url'])): ?>
      <img src="<?= artImg($a) ?>" alt="<?= xe($a['title']) ?>" loading="lazy">
      <?php endif; ?>
      <div class="nc-body">
        <span class="ctag <?= catClass($a['category_slug'] ?? '') ?>">
          <?= xe($a['category_tamil'] ?: $a['category_name']) ?>
        </span>
        <div class="nc-title"><?= xe($a['title']) ?></div>
        <?php if (empty($a['image_url']) && !empty($a['excerpt'])): ?>
        <div class="nc-no-img-excerpt"><?= xe(mb_substr(strip_tags($a['excerpt']), 0, 140)) ?></div>
        <?php endif; ?>
        <div class="hero4-meta notranslate" translate="no"><?= ta($a['published_at']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

</div><!-- /hero4-grid -->



<!-- ══════════════════════════════════════════
     CATEGORY SECTIONS — 4-col grid each
════════════════════════════════════════════ -->
<?php $loop = 0; foreach ($catSections as $sec):
  if (empty($sec['data'])) continue; ?>

<div class="sec-head">
  <span class="sec-head-bar sec-head-bar-dyn" style="--ac:<?= $sec['color'] ?>"></span>
  <span class="sec-head-title"><?= $sec['name'] ?></span>
  <span class="sec-head-ta"><?= $sec['ta'] ?></span>
  <?php $secMoreUrl = $sec['url'] ?? ($sec['slug'] ? $r.'/tamil-news/'.$sec['slug'] : null); ?>
  <?php if ($secMoreUrl): ?>
  <a href="<?= $secMoreUrl ?>" class="sec-head-more">மேலும் →</a>
  <?php endif; ?>
</div>

<?php
  $_adPos  = rand(0, 3);                              // random slot: 0=1st 1=2nd 2=3rd 3=4th
  $_adSlot = ($loop % 2 === 0) ? 'square_a' : 'square_b'; // alternate slot per section
  $loop    = ($loop ?? 0) + 1;
?>
<div class="g4">
  <?php foreach (array_slice($sec['data'], 0, 3) as $_gi => $a):
    if ($_gi === $_adPos): ?>
  <div class="nc nc-ad notranslate" translate="no">
    <span class="nc-ad-label">Ad</span>
    <div class="ad-rotator" data-slot="<?= $_adSlot ?>" data-cat="<?= $sec['data'][0]['category_id'] ?? ($categoryId ?? 0) ?>"></div>
  </div>
  <?php endif; ?>
  <a href="<?= $r ?>/article/<?= xe($a['slug']) ?>" class="nc <?= empty($a['image_url']) && empty($a['youtube_video_id']) ? 'nc-no-img' : '' ?>">
    <?php if (!empty($a['youtube_video_id'])): ?>
    <div class="nc-video-thumb">
      <img src="https://img.youtube.com/vi/<?= xe($a['youtube_video_id']) ?>/hqdefault.jpg"
           alt="<?= xe($a['title']) ?>" loading="lazy">
      <div class="nc-play">▶</div>
    </div>
    <?php elseif (!empty($a['image_url'])): ?>
    <img src="<?= artImg($a) ?>" alt="<?= xe($a['title']) ?>" loading="lazy">
    <?php endif; ?>
    <div class="nc-body">
      <?php if (!empty($sec['isSpecial'])): ?>
      <span class="ctag ctag-accent" style="--ac:<?= $sec['color'] ?>">சிறப்பு கட்டுரை</span>
      <?php else: ?>
      <span class="ctag <?= catClass($sec['slug'] ?: ($a['category_slug'] ?? '')) ?>">
        <?= xe($a['category_tamil'] ?: $a['category_name']) ?>
      </span>
      <?php endif; ?>
      <div class="nc-title"><?= xe($a['title']) ?></div>
      <?php if (empty($a['image_url']) && empty($a['youtube_video_id']) && !empty($a['excerpt'])): ?>
      <div class="nc-no-img-excerpt"><?= xe(mb_substr(strip_tags($a['excerpt']), 0, 150)) ?></div>
      <?php endif; ?>
      <div class="hero4-meta notranslate" translate="no">
        <?= ta($a['published_at']) ?>
        <?php if (($a['view_count'] ?? 0) > 0): ?>
        · 👁 <?= number_format($a['view_count']) ?>
        <?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
  <?php if ($_adPos >= 3): // if random pos was 4th, show ad after all 3 news ?>
  <div class="nc nc-ad notranslate" translate="no">
    <span class="nc-ad-label">Ad</span>
    <div class="ad-rotator" data-slot="<?= $_adSlot ?>" data-cat="<?= $sec['data'][0]['category_id'] ?? ($categoryId ?? 0) ?>"></div>
  </div>
  <?php endif; ?>
</div>

<?php endforeach; ?>
