<?php use App\Core\{Helper, CSRF}; ?>

<div class="af-topbar">
  <a href="<?= $r . $base ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <div class="af-topbar-title">Connect Article</div>
</div>

<div style="max-width:560px;margin:0 auto;padding:0 16px 60px">

  <!-- Preview of the photo news being connected -->
  <div class="tn-card mb-3">
    <div class="af-card-body d-flex gap-3 align-items-center">
      <?php if (!empty($item['image_path'])): ?>
      <img src="<?= rtrim(ASSET_URL,'/') ?>/public<?= Helper::e($item['image_path']) ?>"
           style="width:56px;height:72px;object-fit:cover;border-radius:6px;flex-shrink:0" alt="">
      <?php endif; ?>
      <div style="font-size:13px;font-weight:700;line-height:1.4"><?= Helper::e($item['title']) ?></div>
    </div>
  </div>

  <!-- Search + select an existing article -->
  <form method="POST" action="<?= $r . $base ?>/connect/<?= $item['id'] ?>">
    <?= CSRF::field() ?>

    <div class="tn-card mb-3">
      <div class="af-card-head">Search Articles</div>
      <div class="af-card-body">
        <input type="text" id="artSearch" class="af-input" placeholder="Type article title…" autocomplete="off">
        <input type="hidden" name="article_id" id="artSelectedId" required>

        <div id="artResults" class="mt-2"></div>

        <div id="artSelectedBox" class="mt-2" style="display:none;padding:10px 12px;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px">
          <span style="font-size:12px;font-weight:700;color:#065F46">✓ Selected: </span>
          <span id="artSelectedTitle" style="font-size:12px"></span>
          <button type="button" id="artClearSel" class="btn btn-sm btn-link text-danger p-0 ms-2">Change</button>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-danger w-100" id="artSubmitBtn" disabled>
      🔗 Connect to Selected Article
    </button>
  </form>

</div>

<style>
.art-result-item {
  padding: 10px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  margin-bottom: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: background .12s, border-color .12s;
}
.art-result-item:hover { background: #F9FAFB; border-color: #C0001A; }
.art-result-meta { font-size: 11px; color: #9CA3AF; margin-top: 2px; }
</style>

<script>
(function () {
  var search   = document.getElementById('artSearch');
  var results  = document.getElementById('artResults');
  var hidden   = document.getElementById('artSelectedId');
  var box      = document.getElementById('artSelectedBox');
  var titleEl  = document.getElementById('artSelectedTitle');
  var submit   = document.getElementById('artSubmitBtn');
  var clearBtn = document.getElementById('artClearSel');
  var base     = '<?= BASE_URL ?>/public';
  var timer;

  search.addEventListener('input', function () {
    clearTimeout(timer);
    var q = this.value.trim();
    if (q.length < 2) { results.innerHTML = ''; return; }
    timer = setTimeout(function () {
      fetch(base + '/admin/articles/suggest?q=' + encodeURIComponent(q))
        .then(function (r) { return r.json(); })
        .then(function (articles) {
          results.innerHTML = '';
          if (!articles.length) {
            results.innerHTML = '<div class="text-muted small py-2">No matching articles found.</div>';
            return;
          }
          articles.forEach(function (a) {
            var div = document.createElement('div');
            div.className = 'art-result-item';
            div.innerHTML = '<div>' + a.title + '</div>' +
                             '<div class="art-result-meta">' + a.status + (a.published_at ? ' · ' + a.published_at.substring(0,10) : '') + '</div>';
            div.addEventListener('click', function () {
              hidden.value = a.id;
              titleEl.textContent = a.title;
              box.style.display = 'block';
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
