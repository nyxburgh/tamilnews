<?php use App\Core\Helper;
$siteUrl  = rtrim(BASE_URL,'/') . '/public';
$assetUrl = rtrim(ASSET_URL,'/');
?>

<div class="breadcrumb">
  <a href="<?= $siteUrl ?>/">முகப்பு</a>
  <span>›</span>
  <span>பட செய்திகள்</span>
</div>

<div class="pn-page">

  <div class="pn-page-head">
    <h1 class="pn-page-title">📸 பட செய்திகள்</h1>
    <p class="pn-page-sub">Visual news — click any card to view and share</p>
  </div>

  <?php if (empty($cards)): ?>
  <div style="text-align:center;padding:80px 0;color:#9CA3AF;font-size:14px">No photo news yet.</div>
  <?php else: ?>

  <div class="pn-grid">
    <?php foreach ($cards as $i => $card): ?>
    <button class="pn-card" onclick="pnOpen(<?= $i ?>)">
      <div class="pn-card-img-wrap">
        <img src="<?= $assetUrl ?>/public<?= htmlspecialchars($card['image_path']) ?>"
             alt="<?= htmlspecialchars($card['title']) ?>" loading="lazy" class="pn-card-img">
        <div class="pn-card-hover"><i class="bi bi-fullscreen"></i></div>
      </div>
      <div class="pn-card-title"><?= htmlspecialchars($card['title']) ?></div>
    </button>
    <?php endforeach; ?>
  </div>

  <?php if ($total > $per): ?>
  <div class="pn-pagination">
    <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>" class="pn-pg-btn">← முந்தைய</a><?php endif; ?>
    <span class="pn-pg-info">Page <?= $page ?> / <?= ceil($total/$per) ?></span>
    <?php if ($page * $per < $total): ?><a href="?page=<?= $page+1 ?>" class="pn-pg-btn">அடுத்து →</a><?php endif; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Lightbox -->
<div class="pn-lb-overlay" id="pnOverlay">
  <button class="pn-lb-close" id="pnClose">✕</button>
  <button class="pn-lb-nav pn-lb-prev" id="pnPrev">‹</button>
  <button class="pn-lb-nav pn-lb-next" id="pnNext">›</button>
  <div class="pn-lb-box">
    <div class="pn-lb-img-wrap">
      <img id="pnLbImg" src="" alt="">
    </div>
    <div class="pn-lb-info">
      <p class="pn-lb-title" id="pnLbTitle"></p>

      <!-- Full news link — shown only if article exists -->
      <a id="pnFullNews" href="#" class="pn-full-news" style="display:none" target="_blank">
        📰 Full News படிக்க →
      </a>

      <!-- Share actions -->
      <div class="pn-lb-share">
        <!-- Web Share API — shares image directly on mobile -->
        <button id="pnShareImg" class="pn-share-btn pn-share-img">
          <i class="bi bi-share-fill"></i> Share Image
        </button>
        <!-- Download image -->
        <a id="pnDownload" href="#" download class="pn-share-btn pn-dl">
          <i class="bi bi-download"></i> Download
        </a>
        <!-- Copy link -->
        <button id="pnCopy" class="pn-share-btn pn-copy">
          <i class="bi bi-link-45deg"></i> <span id="pnCopyTxt">Copy Link</span>
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.pn-page{max-width:1200px;margin:0 auto;padding:16px 16px 80px;}
.pn-page-head{text-align:center;margin-bottom:24px;}
.pn-page-title{font-family:'Noto Sans Tamil',sans-serif;font-size:26px;font-weight:900;color:#C0001A;margin:0;}
.pn-page-sub{font-size:13px;color:#6B6A64;margin:4px 0 0;}
.pn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;}
@media(max-width:580px){.pn-grid{grid-template-columns:repeat(2,1fr);gap:10px;}}
.pn-card{background:#fff;border:none;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.08);cursor:pointer;transition:transform .15s,box-shadow .15s;text-align:left;padding:0;}
.pn-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.14);}
.pn-card-img-wrap{position:relative;aspect-ratio:3/4;overflow:hidden;background:#F5F5F0;}
.pn-card-img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .2s;}
.pn-card:hover .pn-card-img{transform:scale(1.04);}
.pn-card-hover{position:absolute;inset:0;background:rgba(0,0,0,0);display:flex;align-items:center;justify-content:center;transition:background .2s;}
.pn-card:hover .pn-card-hover{background:rgba(0,0,0,.28);}
.pn-card-hover i{color:#fff;font-size:22px;opacity:0;transition:opacity .15s;}
.pn-card:hover .pn-card-hover i{opacity:1;}
.pn-card-title{font-family:'Noto Sans Tamil',sans-serif;font-size:12px;font-weight:700;color:#1A1A1A;padding:8px 10px 10px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.pn-pagination{display:flex;align-items:center;justify-content:center;gap:16px;margin-top:28px;}
.pn-pg-btn{background:#C0001A;color:#fff;padding:8px 20px;border-radius:6px;text-decoration:none;font-weight:700;font-size:13px;}
.pn-pg-info{font-size:13px;color:#6B6A64;}

/* Lightbox */
.pn-lb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;}
.pn-lb-overlay.open{display:flex;}
.pn-lb-close{position:fixed;top:16px;right:20px;background:rgba(255,255,255,.15);color:#fff;border:none;width:40px;height:40px;border-radius:50%;font-size:20px;cursor:pointer;z-index:2;}
.pn-lb-close:hover{background:rgba(255,255,255,.3);}
.pn-lb-nav{position:fixed;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.15);color:#fff;border:none;width:48px;height:48px;border-radius:50%;font-size:28px;cursor:pointer;z-index:2;}
.pn-lb-nav:hover{background:rgba(255,255,255,.3);}
.pn-lb-prev{left:16px;} .pn-lb-next{right:16px;}
.pn-lb-nav:disabled{opacity:.25;cursor:default;}
.pn-lb-box{background:#fff;border-radius:16px;max-width:440px;width:calc(100% - 120px);max-height:90vh;overflow-y:auto;}
.pn-lb-img-wrap{overflow:hidden;border-radius:16px 16px 0 0;}
.pn-lb-img-wrap img{width:100%;display:block;}
.pn-lb-info{padding:14px 16px 16px;}
.pn-lb-title{font-family:'Noto Sans Tamil',sans-serif;font-size:14px;font-weight:800;color:#1A1A1A;line-height:1.5;margin:0 0 10px;}
.pn-full-news{display:flex;align-items:center;padding:9px 14px;background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;color:#C0001A;font-size:13px;font-weight:700;text-decoration:none;margin-bottom:12px;}
.pn-full-news:hover{background:#FEE2E2;}
.pn-lb-share{display:flex;gap:8px;flex-wrap:wrap;}
.pn-share-btn{display:inline-flex;align-items:center;gap:5px;padding:8px 13px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;border:none;font-family:'Inter',sans-serif;}
.pn-share-img{background:#C0001A;color:#fff;}
.pn-dl{background:#1D4ED8;color:#fff;}
.pn-copy{background:#F3F4F6;color:#1A1A1A;}
@media(max-width:600px){.pn-lb-box{max-width:calc(100% - 80px);}.pn-lb-prev{left:6px;}.pn-lb-next{right:6px;}}
</style>

<script>
<?php
$jsCards = array_map(function($c) use ($assetUrl, $siteUrl) {
    return [
        'img'         => $assetUrl . '/public' . $c['image_path'],
        'imgPath'     => $c['image_path'],
        'title'       => $c['title'],
        'articleUrl'  => $c['article_slug'] ? $siteUrl . '/article/' . $c['article_slug'] : null,
        'pageUrl'     => $siteUrl . '/photo-news',
    ];
}, $cards);
?>
var PN_CARDS  = <?= json_encode($jsCards, JSON_UNESCAPED_UNICODE) ?>;
var pnCurrent = 0;

function pnOpen(idx) {
  pnCurrent = idx;
  pnRender();
  document.getElementById('pnOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function pnClose() {
  document.getElementById('pnOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function pnRender() {
  var c = PN_CARDS[pnCurrent];
  if (!c) return;

  var img = document.getElementById('pnLbImg');
  img.src = c.img;
  img.alt = c.title;

  document.getElementById('pnLbTitle').textContent = c.title;

  /* Full news link */
  var fnEl = document.getElementById('pnFullNews');
  if (c.articleUrl) {
    fnEl.href = c.articleUrl;
    fnEl.style.display = 'flex';
  } else {
    fnEl.style.display = 'none';
  }

  /* Download */
  document.getElementById('pnDownload').href = c.img;
  document.getElementById('pnDownload').download = 'thinathulir-photo-news.png';

  /* Nav buttons */
  document.getElementById('pnPrev').disabled = pnCurrent === 0;
  document.getElementById('pnNext').disabled = pnCurrent === PN_CARDS.length - 1;
  document.getElementById('pnCopyTxt').textContent = 'Copy Link';
}

/* Share image via Web Share API (mobile) — fallback to download */
document.getElementById('pnShareImg').addEventListener('click', async function () {
  var c = PN_CARDS[pnCurrent];
  if (!c) return;

  /* Try Web Share API with file */
  if (navigator.share && navigator.canShare) {
    try {
      var res  = await fetch(c.img);
      var blob = await res.blob();
      var file = new File([blob], 'thinathulir-photo.png', { type: blob.type });
      if (navigator.canShare({ files: [file] })) {
        await navigator.share({
          files: [file],
          title: c.title,
          text:  c.title,
        });
        return;
      }
    } catch(e) { /* fall through */ }
  }

  /* Fallback: trigger download */
  var a = document.createElement('a');
  a.href = c.img; a.download = 'thinathulir-photo.png';
  a.click();
});

/* Copy link */
document.getElementById('pnCopy').addEventListener('click', function () {
  var url = PN_CARDS[pnCurrent]?.articleUrl || PN_CARDS[pnCurrent]?.pageUrl || window.location.href;
  navigator.clipboard?.writeText(url).then(function () {
    document.getElementById('pnCopyTxt').textContent = '✓ Copied!';
    setTimeout(function () { document.getElementById('pnCopyTxt').textContent = 'Copy Link'; }, 2000);
  });
});

document.getElementById('pnClose').addEventListener('click', pnClose);
document.getElementById('pnPrev').addEventListener('click', function () {
  if (pnCurrent > 0) { pnCurrent--; pnRender(); }
});
document.getElementById('pnNext').addEventListener('click', function () {
  if (pnCurrent < PN_CARDS.length - 1) { pnCurrent++; pnRender(); }
});
document.getElementById('pnOverlay').addEventListener('click', function (e) {
  if (e.target === this) pnClose();
});
document.addEventListener('keydown', function (e) {
  if (!document.getElementById('pnOverlay').classList.contains('open')) return;
  if (e.key === 'Escape') pnClose();
  if (e.key === 'ArrowLeft'  && pnCurrent > 0)                  { pnCurrent--; pnRender(); }
  if (e.key === 'ArrowRight' && pnCurrent < PN_CARDS.length - 1){ pnCurrent++; pnRender(); }
});
</script>
