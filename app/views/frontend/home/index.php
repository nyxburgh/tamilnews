<?php use App\Core\Helper;

// Helper: image fallback
function artImg(array $a, string $size = 'thumb'): string {
    $path = $size === 'full' ? ($a['image_url'] ?? '') : ($a['thumb_url'] ?? $a['image_url'] ?? '');
    return $path ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=600&q=80';
}

// Helper: category tag class
function catClass(string $slug): string {
    $map = ['tamil-nadu'=>'red','india'=>'blue','world'=>'teal','cinema'=>'purple',
            'sports'=>'green','technology'=>'blue','spiritual'=>'gold','jobs'=>'teal'];
    return 'cat-' . ($map[$slug] ?? 'red');
}

function timeAgo(string $date): string {
    return \App\Core\Helper::timeAgo($date);
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>

<div class="main">

  <!-- AD SLOT: Header Banner -->
  <?php if (!empty($ads['header']['ad_code'])): ?>
  <div class="ad-slot ad-header"><?= $ads['header']['ad_code'] ?></div>
  <?php else: ?>
  <div class="ad-slot" style="height:80px;margin-bottom:16px">
    <div class="ad-placeholder">Advertisement · 728×90</div>
  </div>
  <?php endif; ?>

  <!-- LIVE BLOG BANNER — shown only when active -->
  <?php if (!empty($liveBlogs)): ?>
  <div class="live-blogs-bar">
    <?php foreach ($liveBlogs as $lb): ?>
    <a href="<?= $r ?>/live/<?= htmlspecialchars($lb['slug']) ?>" class="live-blog-banner">
      <div class="live-blog-banner-dot"></div>
      <div class="live-blog-banner-label">LIVE</div>
      <div class="live-blog-banner-title"><?= htmlspecialchars($lb['title']) ?></div>
      <div class="live-blog-banner-meta"><?= $lb['entry_count'] ?> updates</div>
      <?php if ($lb['team_home'] && $lb['team_away']): ?>
      <div class="live-blog-banner-score">
        <?= htmlspecialchars($lb['team_home']) ?>
        <?php if ($lb['score_home'] || $lb['score_away']): ?>
        <span><?= htmlspecialchars($lb['score_home'] ?? '—') ?></span>
        vs
        <span><?= htmlspecialchars($lb['score_away'] ?? '—') ?></span>
        <?php else: ?> vs <?php endif; ?>
        <?= htmlspecialchars($lb['team_away']) ?>
      </div>
      <?php endif; ?>
      <div class="live-blog-banner-follow">Follow Live →</div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- HERO GRID -->
  <div class="hero-grid">
    <!-- HERO MAIN -->
    <?php if ($hero): ?>
    <a href="<?= $r ?>/article/<?= e($hero['slug']) ?>" class="hero-main">
      <img src="<?= artImg($hero, 'full') ?>" alt="<?= e($hero['title']) ?>" loading="eager">
      <div class="hero-main-overlay">
        <?php if ($hero['is_breaking']): ?>
        <div class="breaking-badge"><span class="ticker-dot"></span> BREAKING</div>
        <?php elseif ($hero['is_editors_pick']): ?>
        <div class="breaking-badge badge-gold">✦ Editor's Pick</div>
        <?php endif; ?>
        <div class="hero-main-title"><?= e($hero['title']) ?></div>
        <div class="hero-main-meta">
          <span><?= e($hero['category_tamil'] ?: $hero['category_name']) ?></span>
          <span><?= timeAgo($hero['published_at']) ?></span>
          <?php if ($hero['view_count'] > 0): ?>
          <span>👁 <?= number_format($hero['view_count']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endif; ?>

    <!-- HERO SIDE CARDS -->
    <?php foreach (array_slice($heroSide, 0, 2) as $s): ?>
    <a href="<?= $r ?>/article/<?= e($s['slug']) ?>" class="hero-side-card">
      <img src="<?= artImg($s) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
      <div class="hero-side-card-body">
        <span class="cat-tag <?= catClass($s['category_slug'] ?? '') ?>"><?= e($s['category_tamil'] ?: $s['category_name']) ?></span>
        <div class="hero-side-card-title"><?= e($s['title']) ?></div>
        <div class="card-meta">
          <span><?= timeAgo($s['published_at']) ?></span>
          <?php if ($s['view_count'] > 0): ?><span>👁 <?= number_format($s['view_count']) ?></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- TOP STORIES -->
  <?php if (!empty($topStories)): ?>
  <div class="section-head">
    <div class="section-head-bar"></div>
    <div class="section-head-title">Top Stories</div>
    <div class="section-head-ta">முக்கிய செய்திகள்</div>
    <div class="section-head-line"></div>
  </div>
  <div class="top-stories-grid">
    <?php foreach ($topStories as $s): ?>
    <a href="<?= $r ?>/article/<?= e($s['slug']) ?>" class="story-card">
      <img src="<?= artImg($s) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
      <div class="story-card-body">
        <span class="cat-tag <?= catClass($s['category_slug'] ?? '') ?>"><?= e($s['category_tamil'] ?: $s['category_name']) ?></span>
        <div class="story-card-title" style="margin-top:6px"><?= e($s['title']) ?></div>
        <?php if ($s['excerpt']): ?>
        <div class="story-card-excerpt"><?= e($s['excerpt']) ?></div>
        <?php endif; ?>
        <div class="card-meta">
          <span><?= timeAgo($s['published_at']) ?></span>
          <?php if ($s['view_count'] > 0): ?><span>👁 <?= number_format($s['view_count']) ?></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- MAIN TWO-COL: TAMIL NADU + SIDEBAR -->
  <div class="two-col">
    <!-- LEFT: TAMIL NADU BLOCK -->
    <div>
      <?php if (!empty($tamilNadu)): ?>
      <div class="section-head">
        <div class="section-head-bar"></div>
        <div class="section-head-title">Tamil Nadu</div>
        <div class="section-head-ta">தமிழ்நாடு</div>
        <div class="section-head-line"></div>
        <a href="<?= $r ?>/tamil-news/tamil-nadu" class="section-head-more">மேலும் →</a>
      </div>
      <div class="cat-block">
        <?php $main = $tamilNadu[0]; ?>
        <a href="<?= $r ?>/article/<?= e($main['slug']) ?>" class="story-card" style="margin-bottom:12px">
          <img src="<?= artImg($main, 'full') ?>" alt="<?= e($main['title']) ?>" loading="lazy" style="height:220px">
          <div class="story-card-body">
            <?php if ($main['is_breaking']): ?><div class="breaking-badge" style="font-size:10px;padding:2px 8px;margin-bottom:6px"><span class="ticker-dot"></span> BREAKING</div><?php endif; ?>
            <div class="story-card-title"><?= e($main['title']) ?></div>
            <div class="card-meta"><span><?= timeAgo($main['published_at']) ?></span></div>
          </div>
        </a>
        <div class="horiz-list">
          <?php foreach (array_slice($tamilNadu, 1) as $a): ?>
          <a href="<?= $r ?>/article/<?= e($a['slug']) ?>" class="horiz-item">
            <img class="horiz-img" src="<?= artImg($a) ?>" alt="" loading="lazy">
            <div class="horiz-body">
              <?php if ($a['is_breaking']): ?><span class="breaking-badge" style="font-size:9px;padding:2px 6px;margin-bottom:4px"><span class="ticker-dot"></span> BREAKING</span><?php endif; ?>
              <div class="horiz-title"><?= e($a['title']) ?></div>
              <div class="horiz-meta"><?= timeAgo($a['published_at']) ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- INDIA BLOCK -->
      <?php if (!empty($india)): ?>
      <div class="section-head">
        <div class="section-head-bar" style="background:#1877F2"></div>
        <div class="section-head-title">India</div>
        <div class="section-head-ta">இந்தியா</div>
        <div class="section-head-line"></div>
        <a href="<?= $r ?>/tamil-news/india" class="section-head-more">மேலும் →</a>
      </div>
      <div class="top-stories-grid" style="margin-bottom:28px">
        <?php foreach ($india as $a): ?>
        <a href="<?= $r ?>/article/<?= e($a['slug']) ?>" class="story-card">
          <img src="<?= artImg($a) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
          <div class="story-card-body">
            <span class="cat-tag cat-blue"><?= e($a['category_tamil'] ?: $a['category_name']) ?></span>
            <div class="story-card-title" style="margin-top:6px"><?= e($a['title']) ?></div>
            <div class="card-meta"><span><?= timeAgo($a['published_at']) ?></span></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="sidebar">
      <!-- TRENDING -->
      <?php if (!empty($trending)): ?>
      <div class="sidebar-widget">
        <div class="sidebar-widget-head">🔥 Trending Now</div>
        <?php foreach ($trending as $i => $t): ?>
        <a href="<?= $r ?>/article/<?= e($t['slug']) ?>" class="sidebar-trending-item">
          <div class="sidebar-trending-num"><?= $i + 1 ?></div>
          <div>
            <div class="sidebar-trending-title"><?= e($t['title']) ?></div>
            <div class="sidebar-trending-time"><?= timeAgo($t['published_at']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- AD SIDEBAR -->
      <?php if (!empty($ads['sidebar']['ad_code'])): ?>
      <div class="sidebar-widget" style="overflow:hidden"><?= $ads['sidebar']['ad_code'] ?></div>
      <?php else: ?>
      <div class="ad-sidebar"><div>Advertisement<br>300×250</div></div>
      <?php endif; ?>

      <!-- EDITOR'S PICKS -->
      <?php if (!empty($editorsPick)): ?>
      <div class="sidebar-widget">
        <div class="sidebar-widget-head">✦ Editor's Picks</div>
        <?php foreach ($editorsPick as $ep): ?>
        <a href="<?= $r ?>/article/<?= e($ep['slug']) ?>" class="horiz-item" style="padding:10px 14px;border-bottom:1px solid var(--gray-1)">
          <img class="horiz-img" src="<?= artImg($ep) ?>" alt="" loading="lazy" style="width:70px;height:55px">
          <div class="horiz-body">
            <span class="breaking-badge badge-gold" style="font-size:9px;padding:2px 6px;margin-bottom:4px">✦ Pick</span>
            <div class="horiz-title" style="font-size:12.5px"><?= e($ep['title']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- VIDEO SECTION -->
  <?php if (!empty($videos)): ?>
  <div class="section-head">
    <div class="section-head-bar" style="background:#FF0000"></div>
    <div class="section-head-title">Videos</div>
    <div class="section-head-ta">வீடியோ செய்திகள்</div>
    <div class="section-head-line"></div>
    <a href="<?= $r ?>/tamil-news/video" class="section-head-more">மேலும் →</a>
  </div>
  <div class="video-grid">
    <?php foreach ($videos as $v): ?>
    <a href="<?= $r ?>/video/<?= e($v['slug']) ?>" class="video-card">
      <div class="video-thumb">
        <img src="https://img.youtube.com/vi/<?= e($v['youtube_video_id']) ?>/hqdefault.jpg" alt="<?= e($v['title']) ?>" loading="lazy">
        <div class="video-play">▶</div>
      </div>
      <div class="video-title"><?= e(mb_substr($v['title'], 0, 65)) ?></div>
      <div class="video-meta"><?= timeAgo($v['published_at']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- SPORTS BLOCK -->
  <?php if (!empty($sports)): ?>
  <div class="section-head" style="margin-top:28px">
    <div class="section-head-bar" style="background:#1B6B2E"></div>
    <div class="section-head-title">Sports</div>
    <div class="section-head-ta">விளையாட்டு</div>
    <div class="section-head-line"></div>
    <a href="<?= $r ?>/tamil-news/sports" class="section-head-more">மேலும் →</a>
  </div>
  <div class="top-stories-grid" style="margin-bottom:28px">
    <?php foreach ($sports as $a): ?>
    <a href="<?= $r ?>/article/<?= e($a['slug']) ?>" class="story-card">
      <img src="<?= artImg($a) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
      <div class="story-card-body">
        <span class="cat-tag cat-green">விளையாட்டு</span>
        <div class="story-card-title" style="margin-top:6px"><?= e($a['title']) ?></div>
        <div class="card-meta"><span><?= timeAgo($a['published_at']) ?></span></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- CINEMA BLOCK -->
  <?php if (!empty($cinema)): ?>
  <div class="section-head">
    <div class="section-head-bar" style="background:#7F77DD"></div>
    <div class="section-head-title">Cinema</div>
    <div class="section-head-ta">சினிமா</div>
    <div class="section-head-line"></div>
    <a href="<?= $r ?>/tamil-news/cinema" class="section-head-more">மேலும் →</a>
  </div>
  <div class="top-stories-grid" style="margin-bottom:28px">
    <?php foreach ($cinema as $a): ?>
    <a href="<?= $r ?>/article/<?= e($a['slug']) ?>" class="story-card">
      <img src="<?= artImg($a) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
      <div class="story-card-body">
        <span class="cat-tag cat-purple">சினிமா</span>
        <div class="story-card-title" style="margin-top:6px"><?= e($a['title']) ?></div>
        <div class="card-meta"><span><?= timeAgo($a['published_at']) ?></span></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div><!-- /.main -->
