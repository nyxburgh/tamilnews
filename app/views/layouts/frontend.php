<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($metaTitle ?? ($siteName ?? 'தினத்துளிர்')) ?></title>
<?php if (!empty($metaDesc)): ?><meta name="description" content="<?= htmlspecialchars($metaDesc) ?>"><?php endif; ?>
<?php if (!empty($canonical)): ?><link rel="canonical" href="<?= htmlspecialchars($canonical) ?>"><?php endif; ?>
<meta property="og:url" content="<?= htmlspecialchars($_canonical) ?>">
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>
<meta property="og:type"        content="website">
<meta property="og:title"       content="<?= htmlspecialchars($_metaTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($_ogDesc) ?>">
<meta property="og:image"       content="<?= htmlspecialchars($_ogImage) ?>">
<meta property="og:image:width" content="300">
<meta property="og:image:height"content="150">
<meta property="og:image:type"  content="image/jpeg">
<meta name="csrf-token"         content="<?= \App\Core\CSRF::token() ?>">
<?php
$_siteUrl   = defined('BASE_URL') ? BASE_URL . '/public' : '';
$_siteName  = $siteName ?? 'தினத்துளிர்';
$_metaTitle = $metaTitle ?? $_siteName;
$_metaDesc  = $metaDesc  ?? '';
$_canonical = $canonical ?? $_siteUrl . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_ogImage   = $ogImage   ?? '';
// Fallback OG image — site logo or default share card
if (empty($_ogImage)) {
    $_ogImage = (defined('BASE_URL') ? BASE_URL : '') . '/public/uploads/vaqua.jpeg';
}
$_ogDesc = !empty($_metaDesc) ? $_metaDesc : 'தமிழ்நாட்டின் நம்பகமான டிஜிட்டல் செய்தி தளம். அரசியல் பழகு · அறம் செய்.';
$_isArticle = isset($article) && !empty($article);
$_pubDate   = $_isArticle ? $article['published_at'] ?? '' : '';
$_modDate   = $_isArticle ? $article['updated_at']   ?? $article['published_at'] ?? '' : '';
$_author    = $_isArticle ? ($article['contributor_name'] ?: $article['author_name'] ?? $_siteName) : $_siteName;
$_keywords  = $_isArticle ? ($article['tag_names'] ?? '') : '';
$_keywords  = str_replace('||', ', ', $_keywords);
?>
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:site"        content="@thinathulir">
<meta name="twitter:title"       content="<?= htmlspecialchars($_metaTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($_ogDesc) ?>">
<meta name="twitter:image"       content="<?= htmlspecialchars($_ogImage) ?>">
<meta property="og:site_name" content="<?= htmlspecialchars($_siteName) ?>">
<meta property="og:locale" content="ta_IN">
<?php if ($_isArticle): ?>
<meta property="article:published_time" content="<?= htmlspecialchars($_pubDate) ?>">
<meta property="article:modified_time" content="<?= htmlspecialchars($_modDate) ?>">
<meta property="article:author" content="<?= htmlspecialchars($_author) ?>">
<meta property="article:section" content="<?= htmlspecialchars($article['category_name'] ?? '') ?>">
<?php if ($_keywords): ?><meta property="article:tag" content="<?= htmlspecialchars($_keywords) ?>"><?php endif; ?>
<?php endif; ?>
<meta name="keywords" content="<?= $_keywords ? htmlspecialchars($_keywords).', ' : '' ?>தமிழ் செய்தி, Tamil News">
<?php if ($_isArticle): ?>
<meta name="news_keywords" content="<?= htmlspecialchars($_keywords ?: ($article['category_name'] ?? '')) ?>">
<meta name="author" content="<?= htmlspecialchars($_author) ?>">
<?php endif; ?>
<meta name="language" content="Tamil">
<meta http-equiv="content-language" content="ta">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<script type="application/ld+json">
<?php if ($_isArticle): ?>
{"@context":"https://schema.org","@type":"NewsArticle","headline":<?= json_encode($article['title'] ?? '', JSON_UNESCAPED_UNICODE) ?>,"url":<?= json_encode($_canonical) ?>,"datePublished":<?= json_encode($_pubDate) ?>,"dateModified":<?= json_encode($_modDate) ?>,"author":{"@type":"Person","name":<?= json_encode($_author, JSON_UNESCAPED_UNICODE) ?>},"publisher":{"@type":"Organization","name":<?= json_encode($_siteName, JSON_UNESCAPED_UNICODE) ?>},"inLanguage":"ta"}
<?php else: ?>
{"@context":"https://schema.org","@type":"WebSite","name":<?= json_encode($_siteName, JSON_UNESCAPED_UNICODE) ?>,"url":<?= json_encode($_siteUrl.'/') ?>,"inLanguage":"ta"}
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
/* ── ALL REQUIRED VARIABLES ── */
$reader         = \App\Core\Session::get('reader');
$siteName       = $siteName ?? 'தினத்துளிர்';
$currentPath    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$breakingTicker = $breaking ?? [];
$navCats        = $navCategories ?? [];
$assetUrl       = ASSET_URL;
$baseUrl        = BASE_URL;
$r              = ASSET_URL;

/* Tamil date */
$_days   = ['ஞாயிறு','திங்கள்','செவ்வாய்','புதன்','வியாழன்','வெள்ளி','சனி'];
$_months = ['ஜனவரி','பிப்ரவரி','மார்ச்','ஏப்ரல்','மே','ஜூன்','ஜூலை','ஆகஸ்ட்','செப்டம்பர்','அக்டோபர்','நவம்பர்','டிசம்பர்'];
$tamilDate = $_days[date('w')] . ', ' . date('d') . ' ' . $_months[(int)date('n')-1] . ' ' . date('Y');

/* Ad slots — safe */
$adSquare = $adHorizontal = null;
try {
    $adModel = new \App\Models\AdModel();
    if (method_exists($adModel, 'getSlot')) {
        $adSquare     = $adModel->getSlot('square');
        $adHorizontal = $adModel->getSlot('horizontal');
    }
} catch (\Exception $e) {}

?>

<!-- ═══════════ DESKTOP NAV (sticky, hidden on mobile) ═══════════ -->
<nav class="nav">
  <div class="nav-inner">
    <a href="<?= $baseUrl ?>/public/" class="nav-link <?= $currentPath === parse_url($baseUrl, PHP_URL_PATH).'/public/' ? 'active' : '' ?>">முகப்பு</a>
    <?php foreach ($navCats as $cat): ?>
    <?php if ($cat['parent_id']) continue; ?>
    <a href="<?= $baseUrl ?>/public/tamil-news/<?= htmlspecialchars($cat['slug']) ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], $cat['slug']) ? 'active' : '' ?>">
      <?= htmlspecialchars($cat['name_tamil'] ?: $cat['name']) ?>
    </a>
    <?php endforeach; ?>
    <div class="nav-actions">
      <form class="nav-search" action="<?= $baseUrl ?>/public/search" method="GET">
        <input type="text" name="q" placeholder="தேடு..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit">🔍</button>
      </form>
      <?php if ($reader): ?>
      <div class="nav-user-wrap">
        <div class="nav-user-avatar nav-user-init" onclick="toggleDropdown()"><?= strtoupper(substr($reader['name'],0,1)) ?></div>
        <div class="user-dropdown" id="userDropdown">
          <div class="user-dropdown-header">
            <div class="user-dropdown-name"><?= htmlspecialchars($reader['name']) ?></div>
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

<!-- ═══════════ MOBILE STICKY TOP BAR (hidden on desktop) ═══════════ -->
<div class="mob-topbar">
  <a href="<?= $baseUrl ?>/public/" class="mob-topbar-logo">
    <span class="mob-logo-w1">தினத்</span><span class="mob-logo-w2">துளிர்</span>
  </a>
  <div class="mob-topbar-right">
    <div class="mob-topbar-date"><?= $tamilDate ?></div>
  </div>
</div>

<!-- ═══════════ MASTHEAD HEADER ═══════════ -->
<header class="header">
  <canvas id="headerCanvas"></canvas>

  <!-- DESKTOP ONLY: [300×250] [LOGO] [300×250] -->
  <div class="masthead">
    <div class="masthead-ad">
      <div class="ad-rotator" data-slot="square" data-category="<?= $categoryId ?? 0 ?>" data-default="<?= ASSET_URL ?>/uploads/vaqua.jpeg"><img src="<?= ASSET_URL ?>/uploads/vaqua.jpeg" alt="Advertisement" style="width:100%;height:auto;display:block;object-fit:contain"></div>
    </div>
    <div class="masthead-center">
      <a href="<?= $baseUrl ?>/public/" class="vel-logo-link">
        <div class="vel-brand-wrap">
          <div>
            <div class="vel-logo"><span class="vel-word1">தினத்</span><span class="vel-word2">துளிர்</span></div>
            <div class="vel-tagline">அரசியல் பழகு &nbsp;·&nbsp; அறம் செய்</div>
            <div class="masthead-sub-line">பதிவு எண்: TN/2024/12345 &nbsp;|&nbsp; <?= $tamilDate ?> &nbsp;|&nbsp; <?= date('d F Y') ?></div>
          </div>
        </div>
      </a>
    </div>
    <div class="masthead-ad">
      <div class="ad-rotator" data-slot="square" data-category="<?= $categoryId ?? 0 ?>" data-default="<?= ASSET_URL ?>/uploads/vaqua.jpeg"><img src="<?= ASSET_URL ?>/uploads/vaqua.jpeg" alt="Advertisement" style="width:100%;height:auto;display:block;object-fit:contain"></div>
    </div>
  </div>

  <!-- MOBILE ONLY: single ad (25vh) -->
  <div class="mobile-square-ad">
    <img src="<?= ASSET_URL ?>/uploads/vaqua.jpeg" alt="Advertisement" style="max-width:200px;height:auto;display:block;object-fit:contain">
  </div>

  <!-- DESKTOP double rule -->
  <div class="masthead-rule"></div>
  <div class="masthead-rule-thin"></div>

  <!-- BREAKING TICKER -->
  <?php if (!empty($breakingTicker)): ?>
  <div class="ticker-bar">
    <div class="ticker-label"><span class="ticker-dot"></span>BREAKING</div>
    <div class="ticker-track">
      <div class="ticker-inner" id="tickerInner">
        <?php foreach (array_merge($breakingTicker, $breakingTicker) as $b): ?>
        <a href="<?= $baseUrl ?>/public/article/<?= htmlspecialchars($b['slug']) ?>" class="ticker-item"><?= htmlspecialchars($b['title']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- DESKTOP ONLY: 728×100 banner -->
  <div class="header-banner-ad">
    <div class="header-banner-ad-inner"><div class="ad-rotator" data-slot="horizontal" data-category="<?= $categoryId ?? 0 ?>" data-default="<?= ASSET_URL ?>/uploads/vah.png"><img src="<?= ASSET_URL ?>/uploads/vah.png" alt="Advertisement" style="max-width:728px;width:100%;height:auto;max-height:100px;object-fit:contain;display:block"></div></div>
  </div>

</header>

<div class="page-layout-wrap" id="pageLayoutWrap">
<?php if (!empty($noSidebar)): ?>
  <!-- Article page: full width, no sidebar -->
  <main class="page-full"><?= $content ?></main>
<?php else: ?>
  <!-- All other pages: main + sidebar -->
  <div class="page-layout">
    <main class="page-main"><?= $content ?></main>
    <aside class="page-sidebar">
      <!-- Square Ad -->
      <div class="sb-widget">
        <img src="<?= ASSET_URL ?>/uploads/vaqua.jpeg" alt="Advertisement"
             style="width:100%;height:auto;object-fit:contain;display:block;border-radius:4px">
        <div class="sb-ad-label">Advertisement</div>
      </div>
      <!-- Trending -->
      <?php if (!empty($trending)): ?>
      <div class="sb-widget">
        <div class="sb-widget-head">🔥 Trending</div>
        <?php foreach ($trending as $i => $t): ?>
        <a href="<?= $baseUrl ?>/public/article/<?= htmlspecialchars($t['slug']) ?>" class="sb-item">
          <div class="sb-num"><?= $i+1 ?></div>
          <div>
            <div class="sb-title"><?= htmlspecialchars($t['title']) ?></div>
            <div class="sb-meta"><?= \App\Core\Helper::timeAgo($t['published_at']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <!-- Editor's Picks -->
      <?php if (!empty($editorsPick)): ?>
      <div class="sb-widget">
        <div class="sb-widget-head">✦ Editor's Picks</div>
        <?php foreach ($editorsPick as $ep): ?>
        <a href="<?= $baseUrl ?>/public/article/<?= htmlspecialchars($ep['slug']) ?>" class="sb-rc">
          <img src="<?= !empty($ep['thumb_url']) ? $ep['thumb_url'] : (!empty($ep['image_url']) ? $ep['image_url'] : 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=120&q=60') ?>"
               alt="" loading="lazy">
          <div class="sb-rc-body">
            <div class="sb-title"><?= htmlspecialchars($ep['title']) ?></div>
            <div class="sb-meta"><?= \App\Core\Helper::timeAgo($ep['published_at']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <!-- Breaking -->
      <?php if (!empty($breaking)): ?>
      <div class="sb-widget">
        <div class="sb-widget-head">🔴 Breaking</div>
        <?php foreach (array_slice($breaking,0,5) as $b): ?>
        <a href="<?= $baseUrl ?>/public/article/<?= htmlspecialchars($b['slug']) ?>" class="sb-item">
          <div class="sb-num" style="background:#C0001A;flex-shrink:0">🔴</div>
          <div>
            <div class="sb-title"><?= htmlspecialchars($b['title']) ?></div>
            <div class="sb-meta"><?= \App\Core\Helper::timeAgo($b['published_at']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </aside>
  </div>
<?php endif; ?>
</div>

<!-- FOOTER: copyright only -->
<footer class="site-footer">
  <div class="site-footer-inner">
    © <?= date('Y') ?> தினத்துளிர். All rights reserved.
    <span class="ftr-sep">|</span>
    <a href="<?= $baseUrl ?>/public/admin/login">Admin</a>
    <span class="ftr-sep">|</span>
    <a href="<?= $baseUrl ?>/public/contribute/login">Contribute</a>
    <span class="ftr-sep">|</span>
    <a href="#">Privacy Policy</a>
  </div>
</footer>

<!-- MOBILE BOTTOM NAV -->

<!-- MOBILE FLOATING RATE ICONS -->
<div class="mob-rate-icons" id="mobRateIcons">
  <div class="mob-rate-btn" onclick="toggleRate('gold')" title="Gold Rate">🥇</div>
  <div class="mob-rate-btn" onclick="toggleRate('silver')" title="Silver Rate">🥈</div>
  <div class="mob-rate-btn" onclick="toggleRate('petrol')" title="Petrol">⛽</div>
  <div class="mob-rate-btn" onclick="toggleRate('diesel')" title="Diesel">🚛</div>
  <div class="mob-rate-btn" onclick="toggleRate('currency_usd')" title="USD Rate">💵</div>
</div>
<!-- Rate Popup -->
<div class="mob-rate-popup" id="mobRatePopup" style="display:none">
  <div class="mob-rate-popup-inner">
    <button class="mob-rate-popup-close" onclick="closeRate()">✕</button>
    <div class="mob-rate-popup-icon" id="rateIcon">🥇</div>
    <div class="mob-rate-popup-label" id="rateLabel">Gold Rate</div>
    <div class="mob-rate-popup-value" id="rateValue">Loading...</div>
    <div class="mob-rate-popup-change" id="rateChange"></div>
    <div class="mob-rate-popup-city" id="rateCity"></div>
  </div>
</div>

<nav class="mobile-bottom-nav">
  <div class="mobile-bottom-nav-inner">
    <a href="<?= $baseUrl ?>/public/" class="mob-nav-item">
      <div class="mob-nav-icon">🏠</div><div class="mob-nav-label">முகப்பு</div>
    </a>
    <a href="<?= $baseUrl ?>/public/breaking" class="mob-nav-item">
      <?php if (!empty($breakingTicker)): ?><div class="mob-nav-badge"><?= count($breakingTicker) ?></div><?php endif; ?>
      <div class="mob-nav-icon">🔴</div><div class="mob-nav-label">BREAKING</div>
    </a>
    <a href="<?= $baseUrl ?>/public/search" class="mob-nav-item">
      <div class="mob-nav-icon">🔍</div><div class="mob-nav-label">தேடல்</div>
    </a>
    <div class="mob-nav-item" onclick="openDrawer()">
      <div class="mob-nav-icon">☰</div><div class="mob-nav-label">மெனு</div>
    </div>
  </div>
</nav>

<!-- MOBILE FLOATING AD — above bottom nav, shows on every page load -->
<div class="mob-float-ad" id="mobFloatAd">
  <div class="mob-float-ad-inner ad-rotator" data-slot="horizontal" data-category="<?= $categoryId ?? 0 ?>" data-default="<?= ASSET_URL ?>/uploads/vah.png"><img src="<?= ASSET_URL ?>/uploads/vah.png" alt="Advertisement" style="width:100%;height:66px;object-fit:contain;display:block"></div>
  <button class="mob-float-ad-close" onclick="closeMobAd(this)" aria-label="Close">✕</button>
</div>

<!-- DRAWER OVERLAY -->
<div class="mob-drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<!-- RIGHT DRAWER -->
<div class="mob-drawer" id="mobDrawer">
  <div class="mob-drawer-header">
    <span><span class="mob-logo-w1" style="font-size:18px">தினத்</span><span class="mob-logo-w2" style="font-size:18px">துளிர்</span></span>
    <button class="mob-drawer-close" onclick="closeDrawer()">✕</button>
  </div>
  <div class="mob-drawer-body">
    <a href="<?= $baseUrl ?>/public/" class="mob-drawer-link">🏠 முகப்பு</a>
    <?php foreach ($navCats as $cat): ?>
    <?php if ($cat['parent_id']) continue; ?>
    <a href="<?= $baseUrl ?>/public/tamil-news/<?= htmlspecialchars($cat['slug']) ?>" class="mob-drawer-link">
      <?= htmlspecialchars($cat['name_tamil'] ?: $cat['name']) ?>
    </a>
    <?php endforeach; ?>
    <div class="mob-drawer-divider"></div>
    <a href="<?= $baseUrl ?>/public/newspaper" class="mob-drawer-link">📰 இ-பேப்பர்</a>
    <a href="<?= $baseUrl ?>/public/search" class="mob-drawer-link">🔍 தேடல்</a>
    <a href="<?= $baseUrl ?>/public/contribute/login" class="mob-drawer-link">✍️ கட்டுரை எழுது</a>
    <div class="mob-drawer-divider"></div>
    <?php if ($reader): ?>
    <div class="mob-drawer-user">👤 <?= htmlspecialchars($reader['name']) ?></div>
    <a href="<?= $baseUrl ?>/public/auth/reader/logout" class="mob-drawer-link">🚪 வெளியேறு</a>
    <?php else: ?>
    <div class="mob-drawer-link" onclick="closeDrawer();openModal()" style="cursor:pointer">🔑 Google மூலம் உள்நுழைக</div>
    <?php endif; ?>
  </div>
</div>

<!-- LOGIN MODAL -->
<div class="modal-overlay" id="loginModal" onclick="handleOverlayClick(event)">
  <div class="modal-box">
    <div class="modal-header">
      <button class="modal-close" onclick="closeModal()">✕</button>
      <div class="vel-logo" style="font-size:20px;justify-content:center"><span class="vel-word1">தினத்</span><span class="vel-word2">துளிர்</span></div>
      <div class="vel-tagline" style="color:#6B6A64;font-size:11px;text-align:center">அரசியல் பழகு &nbsp;·&nbsp; அறம் செய்</div>
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
/* Canvas particles */
(function(){
  const c=document.getElementById('headerCanvas');if(!c)return;
  const ctx=c.getContext('2d');let W,H,P=[];
  function resize(){W=c.width=c.parentElement.offsetWidth;H=c.height=c.parentElement.offsetHeight;}
  class Pt{constructor(){this.r();}r(){this.x=Math.random()*W;this.y=H+10;this.s=Math.random()*2+.5;this.vy=-(Math.random()*.6+.2);this.vx=(Math.random()-.5)*.4;this.l=1;this.d=Math.random()*.008+.004;this.h=Math.random()<.7?0:30;}u(){this.x+=this.vx;this.y+=this.vy;this.l-=this.d;if(this.l<=0||this.y<-10)this.r();}w(){ctx.save();ctx.globalAlpha=this.l;ctx.fillStyle=this.h===0?`rgba(220,30,30,${this.l})`:`rgba(255,140,0,${this.l})`;ctx.shadowColor=this.h===0?'#C0001A':'#FF8C00';ctx.shadowBlur=6;ctx.beginPath();ctx.arc(this.x,this.y,this.s,0,Math.PI*2);ctx.fill();ctx.restore();}}
  function init(){resize();P=Array.from({length:60},()=>{const p=new Pt();p.y=Math.random()*H;return p;});}
  function loop(){ctx.clearRect(0,0,W,H);P.forEach(p=>{p.u();p.w();});requestAnimationFrame(loop);}
  window.addEventListener('resize',resize,{passive:true});init();loop();
})();

/* Drawer */
function openDrawer(){document.getElementById('mobDrawer').classList.add('open');document.getElementById('drawerOverlay').classList.add('open');document.body.style.overflow='hidden';}
function closeDrawer(){document.getElementById('mobDrawer').classList.remove('open');document.getElementById('drawerOverlay').classList.remove('open');document.body.style.overflow='';}

/* Ticker touch swipe */
(function(){
  const t=document.getElementById('tickerInner');if(!t)return;
  let sx=0,off=0;
  t.addEventListener('touchstart',e=>{sx=e.touches[0].clientX;t.style.animationPlayState='paused';const m=new DOMMatrix(getComputedStyle(t).transform);off=m.m41||0;},{passive:true});
  t.addEventListener('touchmove',e=>{t.style.transform=`translateX(${off+e.touches[0].clientX-sx}px)`;},{passive:true});
  t.addEventListener('touchend',e=>{const d=e.changedTouches[0].clientX-sx;off+=d;const half=t.scrollWidth/2;while(off>0)off-=half;while(off<-half)off+=half;t.style.transform='';t.style.animationPlayState='running';},{passive:true});
})();



// ── MOBILE FLOATING AD (shows every page load, close hides for session tab only) ──
function closeMobAd(btn) {
  const el = btn.closest('.mob-float-ad');
  if (el) { el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(()=>el.remove(),300); }
}


// ── RATE ICONS ────────────────────────────────────────
let ratesCache = null;
const rateConfig = {
  gold: { icon:'🥇', label:'Gold Rate', unit:'/gram' },
  silver: { icon:'🥈', label:'Silver Rate', unit:'/gram' },
  petrol: { icon:'⛽', label:'Petrol', unit:'/litre' },
  diesel: { icon:'🚛', label:'Diesel', unit:'/litre' },
  currency_usd: { icon:'💵', label:'USD Rate', unit:'/USD' },
};

function toggleRate(type) {
  const popup = document.getElementById('mobRatePopup');
  const cfg = rateConfig[type];
  document.getElementById('rateIcon').textContent  = cfg.icon;
  document.getElementById('rateLabel').textContent = cfg.label;
  document.getElementById('rateValue').textContent = 'Loading...';
  document.getElementById('rateChange').textContent = '';
  document.getElementById('rateCity').textContent = '';
  popup.style.display = 'block';

  const load = (rates) => {
    const r = rates.find(x => x.type === type);
    if (r) {
      document.getElementById('rateValue').textContent = '₹' + parseFloat(r.value).toFixed(2) + cfg.unit;
      if (r.change_val) {
        const pos = r.change_val >= 0;
        document.getElementById('rateChange').innerHTML =
          '<span style="color:' + (pos?'#10B981':'#EF4444') + '">' +
          (pos?'+':'') + parseFloat(r.change_val).toFixed(2) + ' (' +
          (r.change_pct ? parseFloat(r.change_pct).toFixed(2) + '%)' : ')') + '</span>';
      }
      if (r.city) document.getElementById('rateCity').textContent = r.city;
    } else {
      document.getElementById('rateValue').textContent = 'Not available';
    }
  };

  if (ratesCache) { load(ratesCache); return; }
  fetch('<?= $baseUrl ?>/public/api/rates')
    .then(r => r.json())
    .then(d => { if (d.success) { ratesCache = d.rates; load(ratesCache); } })
    .catch(() => { document.getElementById('rateValue').textContent = 'Unavailable'; });
}
function closeRate() { document.getElementById('mobRatePopup').style.display='none'; }


// ── AD ROTATION — one image at a time, 25s, zoom-out effect ──
const adPool  = {};
const adIndex = {};

function initAdRotators() {
  document.querySelectorAll('.ad-rotator').forEach(el => {
    const slot   = el.dataset.slot;
    const defImg = el.dataset.default;
    const img    = el.querySelector('img');
    if (!slot || !img) return;

    function startRotation(images) {
      adPool[slot]  = images;
      adIndex[slot] = 0;

      function showNext() {
        const cur = adPool[slot][adIndex[slot] % adPool[slot].length];
        // Zoom-out transition
        img.style.transition = 'none';
        img.style.transform  = 'scale(1.08)';
        img.style.opacity    = '1';
        // Start zoom-out
        requestAnimationFrame(() => {
          img.style.transition = 'transform 24s linear, opacity 0.5s ease';
          img.style.transform  = 'scale(1)';
        });
        // Swap image halfway through fade
        img.style.opacity = '0.85';
        setTimeout(() => {
          const src = cur.src.startsWith('http') ? cur.src : '<?= ASSET_URL ?>' + cur.src;
          if (img.src !== src) img.src = src;
          img.alt = cur.alt || 'Advertisement';
          const wrap = img.closest('a');
          if (wrap && cur.link && cur.link !== '#') wrap.href = cur.link;
          img.style.opacity = '1';
        }, 400);
        adIndex[slot]++;
      }

      showNext();
      setInterval(showNext, 25000);
    }

    // Load from API — flatten ALL images from ALL active ads into one pool
    const cacheKey = `${slot}_${el.dataset.category||0}`;
    if (adPool[cacheKey]) { startRotation(adPool[cacheKey]); return; }

    fetch(`<?= $baseUrl ?>/public/api/ads/${slot}?category_id=${el.dataset.category||0}`)
      .then(r => r.ok ? r.json() : null)
      .then(d => {
        const images = [];
        if (d && d.success && d.ads.length) {
          d.ads.forEach(ad => {
            ad.images.forEach(img => {
              images.push({ src: img.src, alt: img.alt, link: img.link });
            });
          });
        }
        // Fallback to default if no images
        if (!images.length) images.push({ src: defImg, alt: 'Advertisement', link: '#' });
        startRotation(images);
      })
      .catch(() => startRotation([{ src: defImg, alt: 'Advertisement', link: '#' }]));
  });
}

document.addEventListener('DOMContentLoaded', initAdRotators);

</script>
</body>
</html>
