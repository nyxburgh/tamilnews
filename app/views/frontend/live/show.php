<?php use App\Core\Helper; ?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a href="<?= $r ?>/">முகப்பு</a>
  <span>›</span>
  <span>Live</span>
  <span>›</span>
  <span><?= htmlspecialchars($blog['title']) ?></span>
</div>

<!-- LIVE HEADER -->
<div class="live-header">
  <?php if ($blog['status'] === 'active'): ?>
  <div class="live-badge-wrap">
    <span class="live-badge-dot"></span>
    <span class="live-badge-text">LIVE</span>
  </div>
  <?php else: ?>
  <div class="live-ended-badge">ENDED</div>
  <?php endif; ?>
  <h1 class="live-title"><?= htmlspecialchars($blog['title']) ?></h1>
  <?php if ($blog['description']): ?>
  <p class="live-desc"><?= htmlspecialchars($blog['description']) ?></p>
  <?php endif; ?>
  <div class="live-meta">
    <span>Started: <?= Helper::formatDate($blog['created_at'], 'd M Y, h:i A') ?></span>
    <?php if ($blog['ended_at']): ?>
    <span>· Ended: <?= Helper::formatDate($blog['ended_at'], 'h:i A') ?></span>
    <?php endif; ?>
    <span>· <span id="entryCount"><?= count($entries) ?></span> updates</span>
  </div>
</div>

<!-- SCOREBOARD -->
<?php if ($blog['team_home'] && $blog['team_away']): ?>
<div class="live-scoreboard">
  <div class="live-score-team">
    <div class="live-score-name"><?= htmlspecialchars($blog['team_home']) ?></div>
    <div class="live-score-num" id="scoreHome" style="color:var(--red)">
      <?= htmlspecialchars($blog['score_home'] ?? '—') ?>
    </div>
  </div>
  <div class="live-score-vs">VS</div>
  <div class="live-score-team">
    <div class="live-score-name"><?= htmlspecialchars($blog['team_away']) ?></div>
    <div class="live-score-num" id="scoreAway" style="color:#1877F2">
      <?= htmlspecialchars($blog['score_away'] ?? '—') ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- LIVE NOTIFICATION BAR -->
<?php if ($blog['status'] === 'active'): ?>
<div class="live-notify-bar" id="liveNotifyBar" style="display:none">
  <span class="live-notify-dot"></span>
  <span id="liveNotifyText">New updates available</span>
  <button onclick="loadNewEntries()" class="live-notify-btn">↑ Load Updates</button>
</div>
<?php endif; ?>

<!-- SHARE BAR -->
<div class="live-share-bar">
  <span style="font-size:13px;color:var(--gray-4)">Share:</span>
  <?php
  $shareUrl  = (defined('BASE_URL') ? BASE_URL . '/public' : '') . '/live/' . $blog['slug'];
  $shareText = urlencode('🔴 LIVE: ' . $blog['title'] . ' | Tamil News');
  $waUrl     = 'https://wa.me/?text=' . $shareText . '%20' . urlencode($shareUrl);
  $fbUrl     = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareUrl);
  ?>
  <a href="<?= $waUrl ?>" target="_blank" class="share-btn share-wa">💬 WhatsApp</a>
  <a href="<?= $fbUrl ?>" target="_blank" class="share-btn share-fb">📘 Facebook</a>
  <button class="share-btn share-copy" onclick="navigator.clipboard?.writeText('<?= htmlspecialchars($shareUrl) ?>').then(()=>alert('Link copied!'))">🔗 Copy</button>
</div>

<!-- PINNED ENTRIES -->
<?php $pinned = array_filter($entries, fn($e) => $e['is_pinned']); ?>
<?php if (!empty($pinned)): ?>
<div class="live-pinned-section">
  <div class="live-pinned-label">📌 Key Updates</div>
  <?php foreach ($pinned as $entry): ?>
  <div class="live-pinned-item">
    <?php if ($entry['label']): ?>
    <span class="live-entry-label" style="background:<?= htmlspecialchars($entry['label_color'] ?? '#C0001A') ?>">
      <?= htmlspecialchars($entry['label']) ?>
    </span>
    <?php endif; ?>
    <span class="live-pinned-text"><?= htmlspecialchars($entry['content']) ?></span>
    <span class="live-entry-time"><?= date('h:i A', strtotime($entry['created_at'])) ?></span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- MAIN FEED -->
<div class="live-feed-wrap">
  <div class="live-feed" id="liveFeed">
    <?php foreach ($entries as $entry): ?>
    <div class="live-entry <?= $entry['is_pinned'] ? 'live-entry-pinned' : '' ?>"
         data-id="<?= $entry['id'] ?>">
      <div class="live-entry-time-col">
        <div class="live-entry-time"><?= date('h:i', strtotime($entry['created_at'])) ?></div>
        <div class="live-entry-ampm"><?= date('A', strtotime($entry['created_at'])) ?></div>
        <?php if ($entry['is_pinned']): ?>
        <div class="live-entry-pin">📌</div>
        <?php endif; ?>
      </div>
      <div class="live-entry-body">
        <?php if ($entry['label']): ?>
        <div class="live-entry-label-wrap">
          <span class="live-entry-label"
                style="background:<?= htmlspecialchars($entry['label_color'] ?? '#C0001A') ?>">
            <?= htmlspecialchars($entry['label']) ?>
          </span>
        </div>
        <?php endif; ?>
        <div class="live-entry-content">
          <?= nl2br(htmlspecialchars($entry['content'])) ?>
        </div>
        <?php if ($entry['score_home'] || $entry['score_away']): ?>
        <div class="live-entry-score">
          📊 <?= htmlspecialchars($blog['team_home'] ?? '') ?>: <strong><?= htmlspecialchars($entry['score_home'] ?? '—') ?></strong>
          &nbsp;|&nbsp;
          <?= htmlspecialchars($blog['team_away'] ?? '') ?>: <strong><?= htmlspecialchars($entry['score_away'] ?? '—') ?></strong>
        </div>
        <?php endif; ?>
        <div class="live-entry-by">
          <span class="live-entry-dot-sm"></span>
          <?= htmlspecialchars($entry['author_name'] ?? 'Reporter') ?>
          · <?= Helper::timeAgo($entry['created_at']) ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($entries)): ?>
    <div class="live-empty">
      <div style="font-size:40px">🔴</div>
      <p style="margin-top:12px">Live updates will appear here. Stay tuned!</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- LINK TO ARTICLE -->
<?php if ($blog['article_slug']): ?>
<div style="text-align:center;padding:24px">
  <a href="<?= $r ?>/article/<?= htmlspecialchars($blog['article_slug']) ?>"
     style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:6px;background:var(--red);color:white;font-weight:600;text-decoration:none">
    📰 Read Full Article
  </a>
</div>
<?php endif; ?>

<style>
/* ── LIVE BLOG FRONTEND STYLES ─────────────────── */
.live-header { max-width:860px;margin:16px auto 20px;padding:0 20px; }
.live-badge-wrap { display:inline-flex;align-items:center;gap:8px;margin-bottom:12px; }
.live-badge-dot  { width:10px;height:10px;border-radius:50%;background:#C0001A;animation:blink 1s infinite; }
.live-badge-text { font-family:'Oswald',sans-serif;font-size:14px;font-weight:700;color:#C0001A;letter-spacing:2px; }
.live-ended-badge { display:inline-block;padding:3px 14px;background:#D8D6CE;color:#6B6A64;font-family:'Oswald',sans-serif;font-size:13px;font-weight:700;letter-spacing:1px;border-radius:3px;margin-bottom:12px; }
.live-title { font-family:'Noto Sans Tamil',sans-serif;font-size:26px;font-weight:700;color:#1A1A1A;line-height:1.4;margin-bottom:8px; }
.live-desc  { font-size:15px;color:#5A5A5A;margin-bottom:10px; }
.live-meta  { font-size:12px;color:#9A9890; }

.live-scoreboard { max-width:460px;margin:0 auto 20px;padding:20px;background:white;border:1px solid #D8D6CE;border-radius:10px;display:flex;align-items:center;justify-content:center;gap:30px;text-align:center; }
.live-score-name { font-size:14px;font-weight:700;margin-bottom:6px; }
.live-score-num  { font-size:42px;font-weight:900;line-height:1;font-family:'Anton',sans-serif; }
.live-score-vs   { font-size:16px;color:#9A9890;font-weight:600; }

.live-notify-bar { background:#1B6B2E;color:white;display:flex;align-items:center;justify-content:center;gap:12px;padding:10px 20px;font-size:13px;font-weight:600;max-width:860px;margin:0 auto 12px;border-radius:6px; }
.live-notify-dot { width:8px;height:8px;border-radius:50%;background:white;animation:blink 1s infinite;flex-shrink:0; }
.live-notify-btn { background:rgba(255,255,255,.2);border:none;color:white;padding:4px 14px;border-radius:4px;cursor:pointer;font-weight:600;font-size:12px; }
.live-notify-btn:hover { background:rgba(255,255,255,.3); }

.live-share-bar { max-width:860px;margin:0 auto 16px;padding:0 20px;display:flex;align-items:center;gap:8px;flex-wrap:wrap; }

.live-pinned-section { max-width:860px;margin:0 auto 20px;padding:0 20px; }
.live-pinned-label { font-size:12px;font-weight:700;color:#6B6A64;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px; }
.live-pinned-item { display:flex;align-items:center;gap:8px;padding:8px 12px;background:#FEF4E0;border-radius:6px;margin-bottom:6px;flex-wrap:wrap; }
.live-pinned-text { font-size:13px;font-family:'Noto Sans Tamil',sans-serif;flex:1; }

.live-feed-wrap { max-width:860px;margin:0 auto;padding:0 20px 40px; }
.live-feed { }

.live-entry { display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #F0EFE9;position:relative; }
.live-entry:first-child { border-top:2px solid #C0001A; }
.live-entry-pinned { background:rgba(192,0,26,.02); }
.live-entry-new { animation:slideInLive .4s ease; }

.live-entry-time-col { min-width:48px;text-align:center;flex-shrink:0; }
.live-entry-time { font-size:14px;font-weight:700;color:#1A1A1A; }
.live-entry-ampm { font-size:10px;color:#9A9890;text-transform:uppercase; }
.live-entry-pin  { font-size:12px;margin-top:4px; }

.live-entry-body { flex:1; }
.live-entry-label-wrap { margin-bottom:6px; }
.live-entry-label {
  display:inline-block;padding:2px 10px;border-radius:3px;
  font-size:11px;font-weight:700;letter-spacing:1px;color:white;
  font-family:'Oswald',sans-serif;
}
.live-entry-content { font-family:'Noto Sans Tamil',sans-serif;font-size:15px;line-height:1.7;color:#1A1A1A;margin-bottom:8px; }
.live-entry-score { font-size:12px;color:#5A5A5A;margin-bottom:6px;padding:6px 10px;background:#F0EFE9;border-radius:4px;display:inline-block; }
.live-entry-by { display:flex;align-items:center;gap:6px;font-size:11px;color:#9A9890; }
.live-entry-dot-sm { width:5px;height:5px;border-radius:50%;background:#C0001A;flex-shrink:0; }

.live-empty { text-align:center;padding:60px 20px;color:#9A9890; }

@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
@keyframes slideInLive { from{opacity:0;transform:translateY(-16px)} to{opacity:1;transform:translateY(0)} }
</style>

<script>
const BLOG_ID = <?= (int)$blog['id'] ?>;
const BASE    = '<?= defined('BASE_URL') ? BASE_URL . '/public' : '' ?>';
let latestId  = <?= (int)$latestId ?>;
let pendingEntries = [];
let isActive  = <?= $blog['status'] === 'active' ? 'true' : 'false' ?>;

<?php if ($blog['status'] === 'active'): ?>
// Poll every 15 seconds
setInterval(async () => {
  try {
    const res  = await fetch(`${BASE}/api/live/${BLOG_ID}/poll?after=${latestId}`);
    const data = await res.json();
    if (data.status === 'ended') { isActive = false; document.querySelector('.live-badge-wrap')?.remove(); }
    if (data.entries?.length) {
      pendingEntries = [...data.entries, ...pendingEntries];
      latestId = Math.max(latestId, ...data.entries.map(e => e.id));
      // Show notification bar
      const bar = document.getElementById('liveNotifyBar');
      if (bar) {
        bar.style.display = 'flex';
        document.getElementById('liveNotifyText').textContent = `${pendingEntries.length} new update${pendingEntries.length > 1 ? 's' : ''}`;
      }
      // Update count
      const total = document.querySelectorAll('.live-entry').length + pendingEntries.length;
      document.getElementById('entryCount').textContent = total;
    }
  } catch(e) {}
}, 15000);
<?php endif; ?>

function loadNewEntries() {
  const feed = document.getElementById('liveFeed');
  const emptyEl = feed.querySelector('.live-empty');
  if (emptyEl) emptyEl.remove();

  pendingEntries.forEach(e => {
    const div = document.createElement('div');
    div.className = 'live-entry live-entry-new';
    div.dataset.id = e.id;
    div.innerHTML = `
      <div class="live-entry-time-col">
        <div class="live-entry-time">${e.time_fmt || ''}</div>
        <div class="live-entry-ampm"></div>
      </div>
      <div class="live-entry-body">
        ${e.label ? `<div class="live-entry-label-wrap"><span class="live-entry-label" style="background:${e.label_color||'#C0001A'}">${e.label}</span></div>` : ''}
        <div class="live-entry-content">${e.content.replace(/\n/g,'<br>')}</div>
        <div class="live-entry-by"><span class="live-entry-dot-sm"></span>${e.author_name||'Reporter'} · just now</div>
      </div>`;
    feed.prepend(div);
  });

  pendingEntries = [];
  const bar = document.getElementById('liveNotifyBar');
  if (bar) bar.style.display = 'none';
  window.scrollTo({ top: feed.offsetTop - 80, behavior: 'smooth' });
}
</script>
