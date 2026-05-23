<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($metaTitle ?? ($siteName ?? 'Tamil News')) ?></title>
<?php if (!empty($metaDesc)): ?><meta name="description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
<?php if (!empty($canonical)): ?>
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
<?php endif; ?>
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?= htmlspecialchars($metaTitle ?? '') ?>">
<meta name="csrf-token" content="<?= \App\Core\CSRF::token() ?>">
<?php
$_siteUrl   = defined('BASE_URL') ? BASE_URL . '/public' : '';
$_siteName  = $siteName ?? 'Tamil News Portal';
$_metaTitle = $metaTitle ?? $_siteName;
$_metaDesc  = $metaDesc ?? '';
$_canonical = $canonical ?? $_siteUrl . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_ogImage   = $ogImage ?? '';
$_isArticle = isset($article) && !empty($article);
$_pubDate   = $_isArticle ? $article['published_at'] ?? '' : '';
$_modDate   = $_isArticle ? $article['updated_at'] ?? $article['published_at'] ?? '' : '';
$_author    = $_isArticle ? ($article['contributor_name'] ?: $article['author_name'] ?? $_siteName) : $_siteName;
$_keywords  = $_isArticle ? ($article['tag_names'] ?? '') : '';
$_keywords  = str_replace('||', ', ', $_keywords);
?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($_metaTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($_metaDesc) ?>">
<?php if ($_ogImage): ?><meta name="twitter:image" content="<?= htmlspecialchars($_ogImage) ?>"><?php endif; ?>
<meta property="og:site_name" content="<?= htmlspecialchars($_siteName) ?>">
<meta property="og:locale" content="ta_IN">
<?php if ($_isArticle): ?>
<meta property="og:type" content="article">
<meta property="article:published_time" content="<?= htmlspecialchars($_pubDate) ?>">
<meta property="article:modified_time"  content="<?= htmlspecialchars($_modDate) ?>">
<meta property="article:author"         content="<?= htmlspecialchars($_author) ?>">
<meta property="article:section"        content="<?= htmlspecialchars($article['category_name'] ?? '') ?>">
<?php if ($_keywords): ?><meta property="article:tag" content="<?= htmlspecialchars($_keywords) ?>"><?php endif; ?>
<?php endif; ?>
<meta name="keywords" content="<?= $_keywords ? htmlspecialchars($_keywords).', ' : '' ?>தமிழ் செய்தி, Tamil News">
<?php if ($_isArticle): ?>
<meta name="news_keywords" content="<?= htmlspecialchars($_keywords ?: ($article['category_name'] ?? '')) ?>">
<meta name="author"        content="<?= htmlspecialchars($_author) ?>">
<meta name="publish_date"  content="<?= htmlspecialchars($_pubDate) ?>">
<?php endif; ?>
<meta name="language" content="Tamil">
<meta http-equiv="content-language" content="ta">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<script type="application/ld+json">
<?php if ($_isArticle): ?>
{"@context":"https://schema.org","@type":"<?= !empty($article['youtube_video_id']) ? 'VideoObject' : 'NewsArticle' ?>","headline":<?= json_encode($article['title'] ?? '', JSON_UNESCAPED_UNICODE) ?>,"description":<?= json_encode(mb_substr(strip_tags($article['excerpt'] ?? ''), 0, 200), JSON_UNESCAPED_UNICODE) ?>,"url":<?= json_encode($_canonical) ?>,<?php if ($_ogImage): ?>"image":<?= json_encode($_ogImage) ?>,<?php endif; ?>"datePublished":<?= json_encode($_pubDate) ?>,"dateModified":<?= json_encode($_modDate) ?>,"author":{"@type":"Person","name":<?= json_encode($_author, JSON_UNESCAPED_UNICODE) ?>},"publisher":{"@type":"Organization","name":<?= json_encode($_siteName, JSON_UNESCAPED_UNICODE) ?>,"logo":{"@type":"ImageObject","url":<?= json_encode($_siteUrl.'/assets/images/logo.png') ?>}},"inLanguage":"ta","keywords":<?= json_encode($_keywords, JSON_UNESCAPED_UNICODE) ?>,"articleSection":<?= json_encode($article['category_name'] ?? '', JSON_UNESCAPED_UNICODE) ?>}
<?php else: ?>
{"@context":"https://schema.org","@type":"WebSite","name":<?= json_encode($_siteName, JSON_UNESCAPED_UNICODE) ?>,"url":<?= json_encode($_siteUrl.'/') ?>,"inLanguage":"ta","potentialAction":{"@type":"SearchAction","target":{"@type":"EntryPoint","urlTemplate":<?= json_encode($_siteUrl.'/search?q={search_term_string}') ?>},"query-input":"required name=search_term_string"}}
<?php endif; ?>
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Noto+Sans+Tamil:wght@400;600;700&family=Oswald:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/css/frontend.css">
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/css/masthead.css">
</head>
<body>
<?php
$reader         = \App\Core\Session::get('reader');
$siteName       = $siteName ?? 'தமிழ் செய்தி';
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$breakingTicker = $breaking ?? [];
$navCats        = $navCategories ?? [];
$assetUrl       = ASSET_URL;
$baseUrl        = BASE_URL;
$r              = ASSET_URL;

// Ad slots — safe load
$adMastheadLeft = $adMastheadRight = $adHeaderBanner = null;
try {
    $adModel = new \App\Models\AdModel();
    if (method_exists($adModel, 'getSlot')) {
        $adMastheadLeft  = $adModel->getSlot('masthead_left');
        $adMastheadRight = $adModel->getSlot('masthead_right');
        $adHeaderBanner  = $adModel->getSlot('header_banner');
    }
} catch (\Exception $e) {}

// Tamil date
$days_ta   = ['ஞாயிறு','திங்கள்','செவ்வாய்','புதன்','வியாழன்','வெள்ளி','சனி'];
$months_ta = ['ஜனவரி','பிப்ரவரி','மார்ச்','ஏப்ரல்','மே','ஜூன்','ஜூலை','ஆகஸ்ட்','செப்டம்பர்','அக்டோபர்','நவம்பர்','டிசம்பர்'];
$tamilDate = $days_ta[date('w')].', '.date('d').' '.$months_ta[date('n')-1].' '.date('Y');
?>

<!-- ══ NAV BAR — STICKY TOP (desktop) ══ -->
<nav class="nav">
  <div class="nav-inner">
    <a href="<?= $baseUrl ?>/public/" class="nav-link <?= ($currentPath === parse_url($baseUrl,PHP_URL_PATH).'/public/' || $currentPath === parse_url($baseUrl,PHP_URL_PATH).'/public') ? 'active' : '' ?>">முகப்பு</a>
    <?php foreach ($navCats as $cat): ?>
    <?php if ($cat['parent_id']) continue; ?>
    <a href="<?= $baseUrl ?>/public/tamil-news/<?= htmlspecialchars($cat['slug']) ?>"
       class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], $cat['slug']) ? 'active' : '' ?>">
      <?= htmlspecialchars($cat['name_tamil'] ?: $cat['name']) ?>
    </a>
    <?php endforeach; ?>
    <!-- Search + Login — desktop only, hidden on mobile -->
    <div class="nav-actions">
      <form class="nav-search" action="<?= $baseUrl ?>/public/search" method="GET">
        <input type="text" name="q" placeholder="தேடு..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit">🔍</button>
      </form>
      <?php if ($reader): ?>
      <div class="nav-user-wrap" style="position:relative">
        <?php if (!empty($reader['avatar'])): ?>
        <img src="<?= htmlspecialchars($reader['avatar']) ?>" class="nav-user-avatar" onclick="toggleDropdown()" alt="">
        <?php else: ?>
        <div class="nav-user-avatar nav-user-init" onclick="toggleDropdown()"><?= strtoupper(substr($reader['name'],0,1)) ?></div>
        <?php endif; ?>
        <div class="user-dropdown" id="userDropdown">
          <div class="user-dropdown-header">
            <div class="user-dropdown-name"><?= htmlspecialchars($reader['name']) ?></div>
            <div class="user-dropdown-email"><?= htmlspecialchars($reader['email']) ?></div>
          </div>
          <a href="<?= $baseUrl ?>/public/auth/reader/logout" class="user-dropdown-item logout">🚪 வெளியேறு</a>
        </div>
      </div>
      <?php else: ?>
      <button class="nav-login-btn" onclick="openModal()">
        <svg width="14" height="14" viewBox="0 0 18 18"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/><path d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/></svg>
        உள்நுழை
      </button>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- ══ NEWSPAPER MASTHEAD ══ -->
<header class="header">
  <canvas id="headerCanvas"></canvas>

  <!-- MASTHEAD: [AD LEFT] [LOGO] [AD RIGHT] — desktop -->
  <div class="masthead">
    <div class="masthead-ad">
      <?php if (!empty($adMastheadLeft['ad_code'])): ?>
        <?= $adMastheadLeft['ad_code'] ?>
      <?php else: ?>
        <span>Advertisement<br>200 × 80</span>
      <?php endif; ?>
    </div>
    <div class="masthead-center">
      <a href="<?= $baseUrl ?>/public/" class="vel-logo-link">
        <div class="vel-brand-wrap">
          <?php if (file_exists(dirname(dirname(dirname(__FILE__))).'/public/assets/images/vel-logo.png')): ?>
          <img src="<?= $assetUrl ?>/assets/images/vel-logo.png" class="vel-img" alt="வேள் சுடர்">
          <?php else: ?>
          <div class="vel-monogram">
            <span class="vel-mono-v">V</span><span class="vel-mono-s">S</span>
          </div>
          <?php endif; ?>
          <div>
            <div class="vel-logo">
              <span class="vel-word1">வேள்</span><span class="vel-word2">சுடர்</span>
            </div>
            <div class="vel-tagline">அரசியல் பழகு &nbsp;·&nbsp; அறம் செய்</div>
          </div>
        </div>
      </a>
    </div>
    <div class="masthead-ad">
      <?php if (!empty($adMastheadRight['ad_code'])): ?>
        <?= $adMastheadRight['ad_code'] ?>
      <?php else: ?>
        <span>Advertisement<br>200 × 80</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- MOBILE ONLY: single square ad below logo -->
  <div class="mobile-square-ad">
    <?php if (!empty($adMastheadLeft['ad_code'])): ?>
      <?= $adMastheadLeft['ad_code'] ?>
    <?php else: ?>
      <div style="font-size:10px;color:#9A9890">Advertisement</div>
    <?php endif; ?>
  </div>

  <!-- REG + DATE -->
  <div class="masthead-meta-bar">
    <div class="masthead-meta-inner">
      <span class="masthead-reg">பதிவு எண்: TN/2024/12345 &nbsp;|&nbsp; Reg. No: TN/2024/12345</span>
      <span class="masthead-edition">Online Edition</span>
      <span class="masthead-date"><?= $tamilDate ?> &nbsp;|&nbsp; <?= date('d F Y') ?></span>
    </div>
  </div>

  <!-- DOUBLE RULE -->
  <div class="masthead-rule"></div>
  <div class="masthead-rule-thin"></div>

  <!-- BREAKING TICKER -->
  <?php if (!empty($breakingTicker)): ?>
  <div class="ticker-bar">
    <div class="ticker-label"><span class="ticker-dot"></span> BREAKING</div>
    <div class="ticker-track">
      <div class="ticker-inner" id="tickerInner">
        <?php foreach (array_merge($breakingTicker, $breakingTicker) as $b): ?>
        <a href="<?= $baseUrl ?>/public/article/<?= htmlspecialchars($b['slug']) ?>" class="ticker-item">
          <?= htmlspecialchars($b['title']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- MOBILE ONLY: horizontal banner ad below ticker -->
  <div class="mobile-banner-ad">
    <?php if (!empty($adHeaderBanner['ad_code'])): ?>
      <?= $adHeaderBanner['ad_code'] ?>
    <?php else: ?>
      <div style="font-size:10px;color:#9A9890;padding:8px;text-align:center">Advertisement · 320×50</div>
    <?php endif; ?>
  </div>

</header>

<!-- MOBILE FLOATING AD (above bottom nav) -->
<div class="mobile-ad-float" id="mobileAdFloat">
  <div class="mobile-ad-float-content">
    <?php if (!empty($adHeaderBanner['ad_code'])): ?>
      <?= $adHeaderBanner['ad_code'] ?>
    <?php elseif (!empty($adMastheadLeft['ad_code'])): ?>
      <?= $adMastheadLeft['ad_code'] ?>
    <?php else: ?>
      Advertisement
    <?php endif; ?>
  </div>
  <button class="mobile-ad-float-close" onclick="closeMobileAd()" aria-label="Close">✕</button>
</div>

<main><?= $content ?></main>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-top">
      <div>
        <div class="footer-brand-name"><?= htmlspecialchars($siteName) ?></div>
        <div class="footer-brand-sub">அரசியல் பழகு · அறம் செய்</div>
        <div class="footer-desc">தமிழ்நாட்டின் நம்பகமான டிஜிட்டல் செய்தி தளம்.</div>
      </div>
      <div>
        <div class="footer-col-title">Categories</div>
        <?php foreach (array_slice($navCats, 0, 5) as $cat): ?>
        <?php if ($cat['parent_id']) continue; ?>
        <a href="<?= $baseUrl ?>/public/tamil-news/<?= htmlspecialchars($cat['slug']) ?>" class="footer-link">
          <?= htmlspecialchars($cat['name_tamil'] ?: $cat['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
      <div>
        <div class="footer-col-title">Services</div>
        <a href="<?= $baseUrl ?>/public/sitemap.xml" class="footer-link">Sitemap</a>
        <a href="<?= $baseUrl ?>/public/sitemap-news.xml" class="footer-link">Google News</a>
        <a href="<?= $baseUrl ?>/public/newspaper" class="footer-link">இ-பேப்பர்</a>
      </div>
      <div>
        <div class="footer-col-title">Portal</div>
        <a href="<?= $baseUrl ?>/public/contribute/login" class="footer-link">Contributor Login</a>
        <a href="<?= $baseUrl ?>/public/admin/login" class="footer-link">Admin</a>
        <a href="#" class="footer-link">Privacy Policy</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. All rights reserved.</span>
      <div class="footer-social">
        <a href="#" class="social-btn">📘</a>
        <a href="#" class="social-btn">🐦</a>
        <a href="#" class="social-btn">📸</a>
        <a href="#" class="social-btn">📺</a>
      </div>
    </div>
  </div>
</footer>

<nav class="mobile-bottom-nav">
  <div class="mobile-bottom-nav-inner">
    <a href="<?= $baseUrl ?>/public/" class="mob-nav-item">
      <div class="mob-nav-icon">🏠</div><div class="mob-nav-label">முகப்பு</div>
    </a>
    <a href="<?= $baseUrl ?>/public/tamil-news/breaking" class="mob-nav-item breaking-btn">
      <?php if (!empty($breakingTicker)): ?><div class="mob-nav-badge"><?= count($breakingTicker) ?></div><?php endif; ?>
      <div class="mob-nav-icon">🔴</div><div class="mob-nav-label">உடனடி</div>
    </a>
    <a href="<?= $baseUrl ?>/public/search" class="mob-nav-item">
      <div class="mob-nav-icon">🔍</div><div class="mob-nav-label">தேடல்</div>
    </a>
    <div class="mob-nav-item" onclick="<?= $reader ? 'toggleDropdown()' : 'openModal()' ?>">
      <div class="mob-nav-icon"><?= $reader ? '🟢' : '👤' ?></div>
      <div class="mob-nav-label"><?= $reader ? htmlspecialchars(explode(' ',$reader['name'])[0]) : 'உள்நுழை' ?></div>
    </div>
  </div>
</nav>

<!-- LOGIN MODAL -->
<div class="modal-overlay" id="loginModal" onclick="handleOverlayClick(event)">
  <div class="modal-box">
    <div class="modal-header">
      <button class="modal-close" onclick="closeModal()">✕</button>
      <div class="vel-logo" style="font-size:22px;justify-content:center">
        <span class="vel-word1">வேள்</span><span class="vel-word2">சுடர்</span>
      </div>
      <div class="vel-tagline" style="color:#6B6A64;font-size:11px;text-align:center">அரசியல் பழகு · அறம் செய்</div>
    </div>
    <div class="modal-body">
      <div class="modal-title">Welcome Back!</div>
      <div class="modal-subtitle">செய்திகளை மதிப்பிட உள்நுழையுங்கள்</div>
      <div class="modal-benefit"><span class="modal-benefit-icon">⭐</span> செய்திகளை மதிப்பிடுங்கள்</div>
      <div class="modal-benefit"><span class="modal-benefit-icon">💬</span> கருத்துகள் சொல்லுங்கள்</div>
      <div class="modal-benefit"><span class="modal-benefit-icon">🔔</span> உடனடி அறிவிப்புகள்</div>
      <div class="modal-divider">Sign in with</div>
      <a href="<?= $baseUrl ?>/public/auth/reader/login?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="google-btn">
        <svg width="20" height="20" viewBox="0 0 18 18"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/><path d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/></svg>
        Google மூலம் உள்நுழைக
      </a>
      <div class="modal-note">உள்நுழைவதன் மூலம் <a href="#">விதிமுறைகளை</a> ஏற்கிறீர்கள்.</div>
    </div>
  </div>
</div>

<script src="<?= ASSET_URL ?>/assets/js/frontend.js"></script>
<script>
function closeMobileAd() {
  const el = document.getElementById('mobileAdFloat');
  if (!el) return;
  el.style.transition = 'opacity .3s, transform .3s';
  el.style.opacity = '0'; el.style.transform = 'translateY(100%)';
  setTimeout(() => el.style.display = 'none', 300);
  sessionStorage.setItem('mobileAdClosed','1');
}
if (sessionStorage.getItem('mobileAdClosed')) {
  const el = document.getElementById('mobileAdFloat');
  if (el) el.style.display = 'none';
}
(function() {
  const canvas = document.getElementById('headerCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, particles = [];
  function resize() { W = canvas.width = canvas.parentElement.offsetWidth; H = canvas.height = canvas.parentElement.offsetHeight; }
  class Particle {
    constructor() { this.reset(); }
    reset() { this.x=Math.random()*W; this.y=H+10; this.size=Math.random()*2+0.5; this.speedY=-(Math.random()*0.6+0.2); this.speedX=(Math.random()-0.5)*0.4; this.life=1; this.decay=Math.random()*0.008+0.004; this.hue=Math.random()<0.7?0:30; }
    update() { this.x+=this.speedX; this.y+=this.speedY; this.life-=this.decay; if(this.life<=0||this.y<-10)this.reset(); }
    draw() { ctx.save(); ctx.globalAlpha=this.life; ctx.fillStyle=this.hue===0?`rgba(220,30,30,${this.life})`:`rgba(255,140,0,${this.life})`; ctx.shadowColor=this.hue===0?'#C0001A':'#FF8C00'; ctx.shadowBlur=6; ctx.beginPath(); ctx.arc(this.x,this.y,this.size,0,Math.PI*2); ctx.fill(); ctx.restore(); }
  }
  function init() { resize(); particles=Array.from({length:60},()=>{ const p=new Particle(); p.y=Math.random()*H; return p; }); }
  function animate() { ctx.clearRect(0,0,W,H); particles.forEach(p=>{ p.update(); p.draw(); }); requestAnimationFrame(animate); }
  window.addEventListener('resize',resize,{passive:true}); init(); animate();
})();
</script>
</body>
</html>
