<?php use App\Core\{Helper, CSRF}; ?>

<div class="af-topbar">
  <a href="<?= $r ?>/<?= \App\Core\Auth::role()==='admin' ? 'admin/articles' : 'portal/all-articles' ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <div class="af-topbar-title">Connect Photo News</div>
</div>

<div style="max-width:560px;margin:0 auto;padding:0 16px 60px">

  <form method="POST" action="<?= $r . $base ?>/connect-from-article/<?= $articleId ?>">
    <?= CSRF::field() ?>

    <div class="tn-card mb-3">
      <div class="af-card-head">Search Photo News (unconnected only)</div>
      <div class="af-card-body">
        <input type="text" id="pnSearch" class="af-input" placeholder="Type photo news title…" autocomplete="off">
        <input type="hidden" name="photo_news_id" id="pnSelectedId" required>

        <div id="pnResults" class="mt-2"></div>

        <div id="pnSelectedBox" class="mt-2" style="display:none;padding:10px 12px;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;display:flex;align-items:center;gap:10px">
          <img id="pnSelectedImg" src="" style="width:40px;height:54px;object-fit:cover;border-radius:4px;display:none" alt="">
          <div style="flex:1">
            <span style="font-size:12px;font-weight:700;color:#065F46">✓ Selected: </span>
            <span id="pnSelectedTitle" style="font-size:12px"></span>
          </div>
          <button type="button" id="pnClearSel" class="btn btn-sm btn-link text-danger p-0">Change</button>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-danger w-100" id="pnSubmitBtn" disabled>
      🔗 Connect to Selected Photo News
    </button>
  </form>

</div>

<style>
.pn-result-item {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  margin-bottom: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: background .12s, border-color .12s;
}
.pn-result-item:hover { background: #F9FAFB; border-color: #C0001A; }
.pn-result-thumb { width: 36px; height: 48px; object-fit: cover; border-radius: 4px; flex-shrink: 0; background: #F5F5F0; }
</style>

<script>
(function () {
  var search   = document.getElementById('pnSearch');
  var results  = document.getElementById('pnResults');
  var hidden   = document.getElementById('pnSelectedId');
  var box      = document.getElementById('pnSelectedBox');
  var titleEl  = document.getElementById('pnSelectedTitle');
  var imgEl    = document.getElementById('pnSelectedImg');
  var submit   = document.getElementById('pnSubmitBtn');
  var clearBtn = document.getElementById('pnClearSel');
  var base     = '<?= BASE_URL ?>/public';
  var assetUrl = '<?= rtrim(ASSET_URL,"/") ?>/public';
  var timer;

  search.addEventListener('input', function () {
    clearTimeout(timer);
    var q = this.value.trim();
    if (q.length < 2) { results.innerHTML = ''; return; }
    timer = setTimeout(function () {
      fetch(base + '<?= $base ?>/suggest-unlinked?q=' + encodeURIComponent(q))
        .then(function (r) { return r.json(); })
        .then(function (items) {
          results.innerHTML = '';
          if (!items.length) {
            results.innerHTML = '<div class="text-muted small py-2">No unconnected photo news found.</div>';
            return;
          }
          items.forEach(function (p) {
            var div = document.createElement('div');
            div.className = 'pn-result-item';
            var imgSrc = p.image_path ? assetUrl + p.image_path : '';
            div.innerHTML = (imgSrc ? '<img src="' + imgSrc + '" class="pn-result-thumb">' : '<div class="pn-result-thumb"></div>') +
                             '<div>' + p.title + '</div>';
            div.addEventListener('click', function () {
              hidden.value = p.id;
              titleEl.textContent = p.title;
              if (imgSrc) { imgEl.src = imgSrc; imgEl.style.display = 'block'; } else { imgEl.style.display = 'none'; }
              box.style.display = 'flex';
              results.innerHTML = '';
              search.value = '';
              submit.disabled = false;
            });
            results.appendChild(div);
          });
        });
    }, 300);
  });

  clearBtn.addEventListener('click', function () {
    hidden.value = '';
    box.style.display = 'none';
    submit.disabled = true;
  });
}());
</script>
