<?php use App\Core\{Helper, CSRF, Auth}; ?>

<?php if ($blog['status'] === 'active'): ?>
<div style="background:#C0001A;color:white;text-align:center;padding:8px;font-weight:700;font-size:13px;letter-spacing:1px">
  <span style="animation:blink 1s infinite;display:inline-block;width:8px;height:8px;border-radius:50%;background:white;margin-right:8px"></span>
  LIVE NOW · <?= Helper::e($blog['title']) ?> ·
  <a href="<?= $r ?>/live/<?= Helper::e($blog['slug']) ?>" target="_blank" style="color:rgba(255,255,255,.8);font-size:12px">Public Link ↗</a>
</div>
<?php endif; ?>

<div class="tn-page-header mt-3">
  <div>
    <h2 class="tn-page-title">
      <?php if ($blog['status'] === 'active'): ?>
      <span class="badge bg-danger me-2" style="font-size:13px">🔴 LIVE</span>
      <?php else: ?>
      <span class="badge bg-secondary me-2">ENDED</span>
      <?php endif; ?>
      <?= Helper::e($blog['title']) ?>
    </h2>
    <p class="tn-page-sub"><?= count($entries) ?> updates · <?= ucfirst($blog['type']) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $r ?>/live/<?= Helper::e($blog['slug']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-eye me-1"></i>Public View
    </a>
    <a href="<?= $r ?>/admin/live-blog" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>All Blogs</a>
    <?php if ($blog['status'] === 'active'): ?>
    <form action="<?= $r ?>/admin/live-blog/end/<?= $blog['id'] ?>" method="POST"
          onsubmit="return confirm('End this live blog? Readers will see it as ENDED.')">
      <?= CSRF::field() ?>
      <button class="btn btn-warning btn-sm fw-600">⏹ End Live</button>
    </form>
    <?php else: ?>
    <form action="<?= $r ?>/admin/live-blog/reactivate/<?= $blog['id'] ?>" method="POST">
      <?= CSRF::field() ?>
      <button class="btn btn-success btn-sm fw-600">▶ Reactivate</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<!-- SCOREBOARD (sports/election) -->
<?php if ($blog['team_home'] && $blog['team_away']): ?>
<div class="tn-card mb-4" style="border-color:rgba(192,0,26,.3)">
  <div class="tn-card-body py-3">
    <div class="d-flex align-items-center justify-content-center gap-4">
      <div class="text-center">
        <div style="font-size:18px;font-weight:700"><?= Helper::e($blog['team_home']) ?></div>
        <div style="font-size:36px;font-weight:900;color:#C0001A" id="scoreHome">
          <?= $blog['score_home'] ?? '—' ?>
        </div>
      </div>
      <div style="font-size:20px;color:#9A9890">VS</div>
      <div class="text-center">
        <div style="font-size:18px;font-weight:700"><?= Helper::e($blog['team_away']) ?></div>
        <div style="font-size:36px;font-weight:900;color:#1877F2" id="scoreAway">
          <?= $blog['score_away'] ?? '—' ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-4">
  <!-- POST NEW ENTRY -->
  <?php if ($blog['status'] === 'active'): ?>
  <div class="col-lg-5">
    <div class="tn-card" style="position:sticky;top:80px">
      <div class="tn-card-header"><span><i class="bi bi-send me-2"></i>Post Update</span></div>
      <div class="tn-card-body">
        <form action="<?= $r ?>/admin/live-blog/post-entry/<?= $blog['id'] ?>" method="POST" id="entryForm">
          <?= CSRF::field() ?>
          <div class="mb-3">
            <textarea name="content" id="entryContent" class="form-control" rows="4"
                      placeholder="Type your live update here..." required
                      style="font-family:'Noto Sans Tamil',sans-serif;font-size:15px"></textarea>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-sm-7">
              <input type="text" name="label" class="form-control form-control-sm"
                     placeholder="Label (e.g. WICKET, GOAL, RESULT)">
            </div>
            <div class="col-sm-5">
              <input type="color" name="label_color" value="#C0001A" class="form-control form-control-color form-control-sm w-100" title="Label Color">
            </div>
          </div>
          <?php if ($blog['team_home'] && $blog['team_away']): ?>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small"><?= Helper::e($blog['team_home']) ?> Score</label>
              <input type="text" name="score_home" class="form-control form-control-sm" placeholder="120/3">
            </div>
            <div class="col-6">
              <label class="form-label small"><?= Helper::e($blog['team_away']) ?> Score</label>
              <input type="text" name="score_away" class="form-control form-control-sm" placeholder="95/5">
            </div>
          </div>
          <?php endif; ?>
          <div class="mb-3">
            <input type="url" name="image_url" class="form-control form-control-sm"
                   placeholder="Image URL (optional — paste direct image link)">
          </div>
          <div class="mb-3">
            <input type="url" name="youtube_url" class="form-control form-control-sm"
                   placeholder="YouTube URL (optional — e.g. https://youtu.be/xxxx)">
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_pinned" value="1" id="isPinned">
            <label class="form-check-label" for="isPinned">📌 Pin this update (show at top)</label>
          </div>
          <button type="submit" class="btn btn-danger w-100 fw-700" id="postBtn">
            <i class="bi bi-send me-2"></i>Post Update
          </button>
        </form>
        <div class="mt-3 text-center text-muted small">
          <i class="bi bi-arrow-clockwise me-1"></i>Readers get updates every 15 seconds automatically
        </div>
        <hr class="mt-3">
        <div class="text-muted small">
          <strong>🔌 API Integration</strong><br>
          POST entries via API:<br>
          <code style="font-size:10px;word-break:break-all">
            POST <?= $r ?>/api/live/<?= $blog['id'] ?>/post<br>
            Body: content, label, score_home, score_away, is_pinned<br>
            Header: X-API-Key: [your api key]
          </code>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ENTRIES FEED -->
  <div class="col-lg-<?= $blog['status'] === 'active' ? '7' : '12' ?>">
    <div class="tn-card">
      <div class="tn-card-header">
        <span><i class="bi bi-list-ul me-2"></i>Updates Feed</span>
        <span class="badge bg-secondary"><?= count($entries) ?></span>
      </div>
      <div id="entriesFeed" style="max-height:600px;overflow-y:auto">
        <?php if (empty($entries)): ?>
        <div class="p-4 text-center text-muted" id="noEntries">
          <i class="bi bi-broadcast fs-1 d-block mb-2 text-danger"></i>
          No updates yet. Post your first update!
        </div>
        <?php endif; ?>
        <?php foreach ($entries as $entry): ?>
        <div class="live-entry-row" data-id="<?= $entry['id'] ?>">
          <div style="display:flex;gap:12px;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.07)">
            <div style="min-width:50px;text-align:center;flex-shrink:0">
              <div style="font-size:11px;color:#6b7280;font-weight:600"><?= date('h:i', strtotime($entry['created_at'])) ?></div>
              <div style="font-size:10px;color:#4b5563"><?= date('A', strtotime($entry['created_at'])) ?></div>
              <?php if ($entry['is_pinned']): ?><div>📌</div><?php endif; ?>
            </div>
            <div style="flex:1">
              <?php if ($entry['label']): ?>
              <div class="mb-1">
                <span style="display:inline-block;padding:2px 10px;border-radius:3px;font-size:11px;font-weight:700;letter-spacing:1px;color:white;background:<?= htmlspecialchars($entry['label_color'] ?? '#C0001A') ?>">
                  <?= htmlspecialchars($entry['label']) ?>
                </span>
              </div>
              <?php endif; ?>
              <div style="font-size:14px;font-family:'Noto Sans Tamil',sans-serif;line-height:1.6;color:#e6edf3">
                <?= nl2br(htmlspecialchars($entry['content'])) ?>
              </div>
              <?php if ($entry['score_home'] || $entry['score_away']): ?>
              <div style="font-size:12px;color:#9ca3af;margin-top:4px">
                📊 <?= htmlspecialchars($blog['team_home'] ?? 'Home') ?>: <?= htmlspecialchars($entry['score_home'] ?? '—') ?>
                &nbsp;|&nbsp;
                <?= htmlspecialchars($blog['team_away'] ?? 'Away') ?>: <?= htmlspecialchars($entry['score_away'] ?? '—') ?>
              </div>
              <?php endif; ?>
              <div style="font-size:11px;color:#6b7280;margin-top:6px">By <?= htmlspecialchars($entry['author_name'] ?? 'Admin') ?></div>
            </div>
            <div style="flex-shrink:0">
              <form action="<?= $r ?>/admin/live-blog/delete-entry/<?= $blog['id'] ?>" method="POST"
                    onsubmit="return confirm('Delete this entry?')">
                <?= CSRF::field() ?>
                <input type="hidden" name="entry_id" value="<?= $entry['id'] ?>">
                <button class="btn btn-link p-0 text-danger" style="font-size:12px"><i class="bi bi-trash"></i></button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
@keyframes slideIn { from{opacity:0;transform:translateY(-20px)} to{opacity:1;transform:translateY(0)} }
.live-entry-new { animation: slideIn .4s ease; }
</style>

<script>
const BLOG_ID  = <?= (int)$blog['id'] ?>;
const BASE     = '<?= $r ?>';
let latestId   = <?= (int)($entries[0]['id'] ?? 0) ?>;
let isActive   = <?= $blog['status'] === 'active' ? 'true' : 'false' ?>;

// AJAX form submit
document.getElementById('entryForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('postBtn');
  btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass me-2"></i>Posting...';
  const fd  = new FormData(this);
  const res = await fetch(this.action, { method: 'POST', body: fd,
    headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  const data = await res.json();
  if (data.success) {
    this.reset();
    if (data.entry) prependEntry(data.entry);
    latestId = Math.max(latestId, data.entry?.id || 0);
  }
  btn.disabled = false; btn.innerHTML = '<i class="bi bi-send me-2"></i>Post Update';
});

// Poll for new entries every 15s
if (isActive) {
  setInterval(async () => {
    const res  = await fetch(`${BASE}/api/live/${BLOG_ID}/poll?after=${latestId}`);
    const data = await res.json();
    if (data.status === 'ended') { isActive = false; }
    data.entries?.forEach(e => { prependEntry(e); latestId = Math.max(latestId, e.id); });
  }, 15000);
}

function prependEntry(e) {
  document.getElementById('noEntries')?.remove();
  const feed = document.getElementById('entriesFeed');
  const div  = document.createElement('div');
  div.className = 'live-entry-row live-entry-new';
  div.dataset.id = e.id;
  div.innerHTML = `
    <div style="display:flex;gap:12px;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.07);background:rgba(192,0,26,.06)">
      <div style="min-width:50px;text-align:center;flex-shrink:0">
        <div style="font-size:11px;color:#6b7280;font-weight:600">${e.time_fmt || 'Now'}</div>
      </div>
      <div style="flex:1">
        ${e.label ? `<div class="mb-1"><span style="display:inline-block;padding:2px 10px;border-radius:3px;font-size:11px;font-weight:700;color:white;background:${e.label_color || '#C0001A'}">${e.label}</span></div>` : ''}
        <div style="font-size:14px;font-family:'Noto Sans Tamil',sans-serif;line-height:1.6;color:#e6edf3">${e.content.replace(/\n/g,'<br>')}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">By ${e.author_name || 'Admin'}</div>
      </div>
    </div>`;
  feed.prepend(div);
}
</script>
