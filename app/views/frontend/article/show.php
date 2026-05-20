<?php
use App\Core\Helper;
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function artImg(array $a, string $size='thumb'): string {
    $path = $size === 'full' ? ($a['image_url'] ?? '') : ($a['thumb_url'] ?? $a['image_url'] ?? '');
    return $path ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=600&q=80';
}
function catClass(string $slug): string {
    $map = ['tamil-nadu'=>'red','india'=>'blue','world'=>'teal','cinema'=>'purple','sports'=>'green','technology'=>'blue'];
    return 'cat-' . ($map[$slug] ?? 'red');
}
$whatsappMsg = urlencode($article['title'] . "\n\n" . (isset($_SERVER['HTTP_HOST']) ? 'https://'.$_SERVER['HTTP_HOST'].'/article/'.$article['slug'] : ''));
$tags     = array_filter(explode('||', $article['tag_names'] ?? ''));
$tagSlugs = array_filter(explode('||', $article['tag_slugs'] ?? ''));
$isVideo  = !empty($article['youtube_video_id']);
?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a href="<?= $r ?>/">முகப்பு</a>
  <span>›</span>
  <a href="<?= $r ?>/tamil-news/<?= e($article['category_slug'] ?? '') ?>"><?= e($article['category_tamil'] ?: $article['category_name']) ?></a>
  <span>›</span>
  <span><?= e(mb_substr($article['title'], 0, 50)) ?>…</span>
</div>

<div class="article-wrap">
  <!-- ARTICLE MAIN -->
  <div class="article-main">

    <!-- CATEGORY & TITLE -->
    <div class="art-category">
      <a href="<?= $r ?>/tamil-news/<?= e($article['category_slug'] ?? '') ?>"><?= e($article['category_name']) ?></a>
    </div>
    <h1 class="art-title"><?= e($article['title']) ?></h1>

    <!-- META ROW -->
    <div class="art-meta-row">
      <div class="art-author">
        <div class="art-author-avatar">
          <?php if (!empty($article['contributor_avatar'])): ?>
          <img src="<?= e($article['contributor_avatar']) ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover" alt="">
          <?php else: ?>
          <?= strtoupper(substr($article['contributor_name'] ?: $article['author_name'] ?: 'A', 0, 1)) ?>
          <?php endif; ?>
        </div>
        <span class="art-author-name"><?= e($article['contributor_name'] ?: $article['author_name'] ?: 'Admin') ?></span>
        <?php if (!empty($article['contributor_name'])): ?>
        <span class="contributor-badge-sm">Contributor</span>
        <?php endif; ?>
      </div>
      <span class="art-meta-sep">|</span>
      <span><?= Helper::formatDate($article['published_at'], 'd M Y, h:i A') ?></span>
      <span class="art-meta-sep">|</span>
      <span>⏱ <?= $article['read_time'] ?> நிமிடம்</span>
      <?php if ($article['view_count'] > 0): ?>
      <span class="art-meta-sep">|</span>
      <span>👁 <?= number_format($article['view_count']) ?></span>
      <?php endif; ?>
      <?php if ($ratingStats['total'] > 0): ?>
      <span class="art-meta-sep">|</span>
      <span>⭐ <?= number_format((float)$ratingStats['average'], 1) ?> (<?= $ratingStats['total'] ?>)</span>
      <?php endif; ?>
    </div>

    <!-- META + FONT SIZE ROW -->
    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px">
      <!-- Author byline -->
      <?php
      $authorSlug = strtolower(str_replace(' ', '-', $article['author_name'] ?? 'admin'));
      ?>
      <a href="<?= $r ?>/author/<?= htmlspecialchars($authorSlug) ?>"
         style="font-size:13px;color:var(--red);font-weight:600;text-decoration:none">
        By <?= htmlspecialchars($article['contributor_name'] ?: $article['author_name'] ?: 'Admin') ?>
      </a>

      <!-- Font size toggle -->
      <div class="font-size-toggle" title="Adjust font size">
        <span style="font-size:11px;color:var(--gray-4)">A</span>
        <button class="font-btn" onclick="setFont('sm')" id="fsm">A-</button>
        <button class="font-btn active" onclick="setFont('md')" id="fmd">A</button>
        <button class="font-btn" onclick="setFont('lg')" id="flg">A+</button>
        <button class="font-btn" onclick="setFont('xl')" id="fxl">A++</button>
      </div>
    </div>

    <!-- SHARE ROW -->
    <div class="share-row">
      <span class="share-label">Share:</span>
      <a href="https://wa.me/?text=<?= $whatsappMsg ?>" target="_blank" rel="noopener"
         class="share-btn share-wa" onclick="trackWA()">
        💬 WhatsApp
      </a>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://'.$_SERVER['HTTP_HOST'].'/article/'.$article['slug']) ?>"
         target="_blank" rel="noopener" class="share-btn share-fb">📘 Facebook</a>
      <a href="https://twitter.com/intent/tweet?text=<?= $whatsappMsg ?>"
         target="_blank" rel="noopener" class="share-btn share-tw">🐦 Twitter</a>
      <button class="share-btn share-copy" onclick="copyLink()">🔗 Copy</button>
    </div>

    <!-- HERO IMAGE or VIDEO -->
    <?php if ($isVideo): ?>
    <div class="video-embed-wrap">
      <iframe src="https://www.youtube.com/embed/<?= e($article['youtube_video_id']) ?>?rel=0"
              title="<?= e($article['title']) ?>"
              frameborder="0" allowfullscreen loading="lazy"></iframe>
    </div>
    <?php elseif (!empty($article['image_url'])): ?>
    <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>"
         class="art-hero-img" loading="eager">
    <?php endif; ?>

    <!-- IN-ARTICLE AD (after image) -->
    <?php if (!empty($ads['in_article_1']['ad_code'])): ?>
    <div class="ad-slot ad-inarticle"><?= $ads['in_article_1']['ad_code'] ?></div>
    <?php endif; ?>


    <!-- PREMIUM GATE -->
    <?php if (!empty($isPremiumLocked)): ?>
    <div class="premium-gate">
      <div class="premium-gate-icon">🔒</div>
      <div class="premium-gate-title">Premium Article</div>
      <div class="premium-gate-sub">இந்த செய்தியை படிக்க உள்நுழைக அல்லது premium சந்தா எடுக்கவும்</div>
      <div class="premium-gate-actions">
        <a href="<?= $r ?>/auth/reader/login?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
           class="premium-gate-btn-primary">
          <svg width="18" height="18" viewBox="0 0 18 18"><path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/><path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/><path d="M3.964 10.71A5.41 5.41 0 013.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/><path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 6.29C4.672 4.163 6.656 3.58 9 3.58z" fill="#EA4335"/></svg>
          Google மூலம் உள்நுழைக
        </a>
      </div>
      <div class="premium-gate-note">Already a subscriber? <a href="<?= $r ?>/auth/reader/login?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Sign in</a></div>
    </div>

    <!-- BLURRED CONTENT PREVIEW -->
    <div class="premium-blur-wrap">
      <div class="art-body premium-blurred">
        <?= $article['content'] ?>
      </div>
    </div>

    <?php else: ?>
    <!-- FULL CONTENT -->
    <div class="art-body">
      <?= $article['content'] ?>
    </div>
    <?php endif; ?>


    <!-- TAGS -->
    <?php if (!empty($tags)): ?>
    <div class="art-tags">
      <span class="art-tags-label">🏷️ Tags:</span>
      <?php foreach ($tags as $i => $tag): ?>
      <a href="<?= $r ?>/search?q=<?= urlencode($tag) ?>" class="art-tag"><?= e($tag) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- WHATSAPP SHARE (prominent) -->
    <div class="whatsapp-share-block">
      <a href="https://wa.me/?text=<?= $whatsappMsg ?>" target="_blank" rel="noopener"
         class="whatsapp-share-btn" onclick="trackWA()">
        <span style="font-size:22px">💬</span>
        <div>
          <div class="whatsapp-share-title">WhatsApp-ல் பகிரவும்</div>
          <div class="whatsapp-share-sub">Share this news with friends & family</div>
        </div>
        <span>→</span>
      </a>
    </div>

    <!-- RELATED ARTICLES -->
    <?php if (!empty($related)): ?>
    <div class="related-section">
      <div class="related-title">தொடர்புடைய செய்திகள்</div>
      <div class="related-grid">
        <?php foreach ($related as $r): ?>
        <a href="<?= $r ?>/article/<?= e($r['slug']) ?>" class="related-item">
          <img class="related-img" src="<?= artImg($r) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
          <div class="related-body">
            <div class="related-cat"><?= e($r['category_name']) ?></div>
            <div class="related-title-text"><?= e($r['title']) ?></div>
            <div class="related-time"><?= Helper::timeAgo($r['published_at']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- RATING & REVIEW SECTION -->
    <div class="rating-section" id="ratingSection">
      <div class="rating-section-head">
        <div class="rating-section-title">மதிப்பீடு மற்றும் கருத்துகள்</div>
        <div class="rating-section-sub">இந்த செய்தி உங்களுக்கு எவ்வளவு பயனுள்ளதாக இருந்தது?</div>
      </div>

      <!-- OVERALL RATING -->
      <?php if ($ratingStats['total'] > 0): ?>
      <div class="rating-overall">
        <div class="rating-big-num"><?= number_format((float)$ratingStats['average'], 1) ?></div>
        <div>
          <div class="overall-stars" id="overallStars">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <span style="color:<?= $i <= round($ratingStats['average']) ? '#E8A000' : '#D8D6CE' ?>;font-size:20px">★</span>
            <?php endfor; ?>
          </div>
          <div style="font-size:12px;color:var(--gray-4)"><?= number_format($ratingStats['total']) ?> மதிப்பீடுகள்</div>
        </div>
        <div class="rating-bars" id="ratingBars">
          <?php
          $barLabels = [5, 4, 3, 2, 1];
          $barFields = ['five', 'four', 'three', 'two', 'one'];
          $total     = $ratingStats['total'] ?: 1;
          foreach ($barLabels as $bi => $bv):
            $cnt = (int)($ratingStats[$barFields[$bi]] ?? 0);
            $pct = round($cnt / $total * 100);
          ?>
          <div class="rating-bar-row">
            <div class="rating-bar-label"><?= $bv ?>★</div>
            <div class="rating-bar-track"><div class="rating-bar-fill" style="width:<?= $pct ?>%"></div></div>
            <div class="rating-bar-count"><?= $cnt ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- WRITE REVIEW -->
      <?php if ($readerId): ?>
      <div class="review-form" id="reviewForm">
        <div class="review-form-title">உங்கள் மதிப்பீட்டை சமர்ப்பிக்கவும்</div>
        <div class="star-picker-row">
          <?php for ($i = 1; $i <= 5; $i++): ?>
          <span class="star-pick <?= $i <= $userRating ? 'active' : '' ?>"
                data-val="<?= $i ?>" onclick="pickStar(<?= $i ?>)">★</span>
          <?php endfor; ?>
          <span class="star-label" id="starLabel">
            <?= $userRating > 0 ? ['','மிகவும் மோசம்','மோசம்','சராசரி','நல்லது','மிகவும் நல்லது'][$userRating] : 'மதிப்பிடவும்' ?>
          </span>
        </div>
        <textarea id="reviewText" class="review-textarea"
                  rows="3" maxlength="500"
                  placeholder="உங்கள் கருத்தை பகிர்ந்து கொள்ளுங்கள்... (optional)"
                  onkeyup="updateCount()"></textarea>
        <div class="review-form-footer">
          <span class="char-count"><span id="charCount">0</span>/500</span>
          <button class="review-submit-btn" id="submitBtn"
                  onclick="submitReview(<?= $article['id'] ?>)"
                  <?= $userRating === 0 ? 'disabled' : '' ?>>
            சமர்ப்பி
          </button>
        </div>
      </div>
      <?php else: ?>
      <div class="login-prompt-box" id="loginPromptBox">
        <div class="login-prompt-icon">⭐</div>
        <div class="login-prompt-title">மதிப்பிட உள்நுழைக</div>
        <div class="login-prompt-sub">Google மூலம் உள்நுழைந்து உங்கள் கருத்தை பகிரவும்</div>
        <button class="login-prompt-btn" onclick="openModal()">
          Google மூலம் உள்நுழைக
        </button>
      </div>
      <?php endif; ?>

      <!-- REVIEWS LIST -->
      <?php if (!empty($reviews)): ?>
      <div class="reviews-list-head">
        <span id="reviewCount"><?= count($reviews) ?> கருத்துகள்</span>
      </div>
      <div id="reviewsList">
        <?php foreach ($reviews as $rv): ?>
        <div class="review-card">
          <div class="review-card-top">
            <?php if ($rv['reader_avatar']): ?>
            <img src="<?= e($rv['reader_avatar']) ?>" class="reviewer-avatar" alt="">
            <?php else: ?>
            <div class="reviewer-avatar" style="background:var(--charcoal);color:white;display:flex;align-items:center;justify-content:center;font-weight:600">
              <?= strtoupper(substr($rv['reader_name'] ?? 'R', 0, 1)) ?>
            </div>
            <?php endif; ?>
            <div class="reviewer-info">
              <div class="reviewer-name"><?= e($rv['reader_name'] ?? 'Reader') ?></div>
              <div class="reviewer-meta">
                <div class="reviewer-stars">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="reviewer-star <?= $i <= $rv['rating'] ? 'on' : '' ?>">★</span>
                  <?php endfor; ?>
                </div>
                <span><?= Helper::timeAgo($rv['created_at']) ?></span>
              </div>
            </div>
          </div>
          <?php if ($rv['review']): ?>
          <div class="review-text"><?= e($rv['review']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- /article-main -->

  <!-- SIDEBAR -->
  <div class="article-sidebar">
    <div class="sidebar-sticky">

      <!-- AD SIDEBAR -->
      <?php if (!empty($ads['sidebar']['ad_code'])): ?>
      <div class="sidebar-widget" style="overflow:hidden;margin-bottom:16px"><?= $ads['sidebar']['ad_code'] ?></div>
      <?php else: ?>
      <div class="ad-sidebar" style="margin-bottom:16px"><div>Advertisement<br>300×250</div></div>
      <?php endif; ?>

      <!-- TRENDING -->
      <?php if (!empty($trending)): ?>
      <div class="sidebar-widget">
        <div class="sidebar-widget-head">🔥 Trending Now</div>
        <?php foreach ($trending as $i => $t): ?>
        <a href="<?= $r ?>/article/<?= e($t['slug']) ?>" class="sidebar-trending-item">
          <div class="sidebar-trending-num"><?= $i + 1 ?></div>
          <div>
            <div class="sidebar-trending-title"><?= e($t['title']) ?></div>
            <div class="sidebar-trending-time"><?= Helper::timeAgo($t['published_at']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div><!-- /article-wrap -->

<script>
window._baseUrl = '<?= $r ?>';
const ARTICLE_ID = <?= (int)$article['id'] ?>;
const CSRF_TOKEN = '<?= e($csrf) ?>';
let selectedStar = <?= (int)$userRating ?>;
const starLabels = ['','மிகவும் மோசம்','மோசம்','சராசரி','நல்லது','மிகவும் நல்லது'];

function pickStar(val) {
  selectedStar = val;
  document.querySelectorAll('.star-pick').forEach((s,i) => s.classList.toggle('active', i < val));
  const lbl = document.getElementById('starLabel');
  if (lbl) lbl.textContent = starLabels[val];
  const btn = document.getElementById('submitBtn');
  if (btn) btn.disabled = false;
}

function updateCount() {
  const len = document.getElementById('reviewText')?.value.length || 0;
  const el  = document.getElementById('charCount');
  if (el) el.textContent = len;
}

async function submitReview(articleId) {
  if (!selectedStar) return;
  const review = document.getElementById('reviewText')?.value || '';
  const btn    = document.getElementById('submitBtn');
  if (btn) { btn.disabled = true; btn.textContent = 'சமர்ப்பிக்கிறது...'; }

  const fd = new FormData();
  fd.append('article_id', articleId);
  fd.append('rating',     selectedStar);
  fd.append('review',     review);
  fd.append('_token',     CSRF_TOKEN);

  try {
    const res  = await fetch(window._baseUrl + '/api/rate', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      showToast('✓ உங்கள் மதிப்பீடு சமர்ப்பிக்கப்பட்டது! நன்றி.');
      if (btn) btn.textContent = '✓ சமர்ப்பிக்கப்பட்டது';
    } else {
      if (btn) { btn.disabled = false; btn.textContent = 'சமர்ப்பி'; }
    }
  } catch {
    if (btn) { btn.disabled = false; btn.textContent = 'சமர்ப்பி'; }
  }
}

function trackWA() {
  fetch(window._baseUrl + '/api/rate', { method: 'POST', body: new URLSearchParams({ article_id: ARTICLE_ID, _token: CSRF_TOKEN, action: 'whatsapp' }) }).catch(()=>{});
}

function copyLink() {
  navigator.clipboard?.writeText(window.location.href).then(() => showToast('✓ Link copied!'));
}

// Star picker hover
document.querySelectorAll('.star-pick').forEach(s => {
  s.addEventListener('mouseover', () => {
    const v = parseInt(s.dataset.val);
    document.querySelectorAll('.star-pick').forEach((x,i) => x.style.color = i<v ? 'var(--star-on)' : 'var(--star-off)');
  });
  s.addEventListener('mouseout', () => {
    document.querySelectorAll('.star-pick').forEach((x,i) => x.style.color = i<selectedStar ? 'var(--star-on)' : 'var(--star-off)');
  });
});

// ── FONT SIZE TOGGLE ──────────────────────────────────
function setFont(size) {
  const body = document.querySelector('.art-body');
  if (!body) return;
  ['sm','md','lg','xl'].forEach(s => {
    body.classList.remove('art-body-' + s);
    document.getElementById('f' + s)?.classList.remove('active');
  });
  body.classList.add('art-body-' + size);
  document.getElementById('f' + size)?.classList.add('active');
  localStorage.setItem('tn_font_size', size);
}
// Restore saved preference
(function() {
  const saved = localStorage.getItem('tn_font_size');
  if (saved) setFont(saved);
})();
</script>
