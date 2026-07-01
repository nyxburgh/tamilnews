<?php use App\Core\{Helper, CSRF, Auth};
$role    = Auth::role();
$isAdmin = in_array($role, ['admin','chief_editor','editor']);
?>

<div class="af-topbar">
  <a href="<?= $r . $base ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <div class="af-topbar-title"><?= $isEdit ? 'Edit Photo News' : 'Add Photo News' ?></div>
</div>

<form method="POST"
      action="<?= $isEdit ? BASE_URL.'/public'.$base.'/edit/'.($item['id']??0) : BASE_URL.'/public'.$base.'/create' ?>"
      enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <?php if (!empty($articleId)): ?>
  <input type="hidden" name="article_id" value="<?= (int)$articleId ?>">
  <?php endif; ?>

  <div style="max-width:600px;margin:0 auto;padding:0 16px 60px">

    <!-- Title + Slug -->
    <div class="tn-card mb-3">
      <div class="af-card-head">Title <span style="color:#C0001A">*</span></div>
      <div class="af-card-body">
        <input type="text" name="title" id="pnTitle" class="af-input"
               value="<?= Helper::e($item['title'] ?? '') ?>"
               placeholder="Photo news headline in Tamil or English…" required autofocus>
        <div class="d-flex gap-2 mt-2">
          <input type="text" name="slug" id="pnSlug" class="af-input"
                 value="<?= Helper::e($item['slug'] ?? '') ?>"
                 placeholder="slug (auto-generated)" style="flex:1;font-size:12px">
          <button type="button" id="pnRegen" class="af-slug-regen" title="Regenerate">↺</button>
        </div>
      </div>
    </div>

    <!-- Image -->
    <div class="tn-card mb-3">
      <div class="af-card-head">Photo / Image</div>
      <div class="af-card-body">
        <?php if (!empty($item['image_path'])): ?>
        <div class="mb-2">
          <img src="<?= rtrim(ASSET_URL,'/') ?>/public<?= Helper::e($item['image_path']) ?>"
               style="max-width:180px;border-radius:8px;display:block;border:1px solid #E8E6E0" alt="">
          <small class="text-muted">Upload new to replace</small>
        </div>
        <?php endif; ?>
        <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
        <div class="form-text">Auto-compressed & converted to PNG. Max 5MB.</div>
      </div>
    </div>

    <!-- Tags — same concept as article (chips + search) -->
    <div class="tn-card mb-3">
      <div class="af-card-head">Tags</div>
      <div class="af-card-body">
        <div id="pnTagPicker" class="tn-tag-picker">
          <?php foreach ($tags as $t): ?>
          <div class="tn-tag-item" data-id="<?= $t['id'] ?>">
            <?= Helper::e($t['name_tamil'] ?: $t['name']) ?> <i class="bi bi-x"></i>
          </div>
          <?php endforeach; ?>
        </div>
        <div id="pnTagHidden">
          <?php foreach ($tags as $t): ?>
          <input type="hidden" name="tag_ids[]" value="<?= $t['id'] ?>">
          <?php endforeach; ?>
        </div>
        <input type="text" id="pnTagSearch" class="af-input mt-2"
               placeholder="Search & add tags…" autocomplete="off">
        <div id="pnTagSuggestions" class="tn-tag-suggestions"></div>
      </div>
    </div>

    <!-- Status + Submit -->
    <div class="tn-card">
      <div class="af-card-body d-flex gap-3 align-items-center flex-wrap">
        <?php if ($isAdmin): ?>
        <select name="status" class="af-select" style="max-width:150px">
          <option value="published" <?= ($item['status']??'published')==='published'?'selected':'' ?>>Published</option>
          <option value="draft"     <?= ($item['status']??'')==='draft'?'selected':'' ?>>Draft</option>
        </select>
        <?php else: ?>
        <input type="hidden" name="status" value="draft">
        <?php
          $apSt = $item['approval_status'] ?? null;
          if ($apSt === 'pending') {
              echo '<span class="small text-warning">⏳ Pending review by Chief Editor</span>';
          } elseif ($apSt === 'rejected') {
              echo '<span class="small text-danger">❌ Rejected — edit and resubmit</span>';
          } else {
              echo '<span class="small text-muted">Will be sent to Chief Editor for review</span>';
          }
        ?>
        <?php endif; ?>
        <button type="submit" class="btn btn-danger flex-fill">
          <?php if ($isAdmin): ?>
            <?= $isEdit ? '💾 Update' : '✅ Publish' ?>
          <?php else: ?>
            <?= $isEdit ? '📤 Resubmit for Review' : '📤 Submit for Review' ?>
          <?php endif; ?>
        </button>
      </div>
    </div>

  </div>
</form>

<script>
(function(){
  /* Slug */
  var titleEl  = document.getElementById('pnTitle');
  var slugEl   = document.getElementById('pnSlug');
  var regen    = document.getElementById('pnRegen');
  var touched  = <?= !empty($item['slug']) ? 'true' : 'false' ?>;

  function mkSlug(s){
    // Strip Tamil/non-ASCII → romanise what's left → clean special chars
    var ascii = s.toLowerCase()
      .replace(/[^\x00-\x7F]+/g, '')   // remove Tamil/Unicode script
      .replace(/[^a-z0-9\s-]+/g, '')    // remove remaining special chars
      .trim()
      .replace(/[\s-]+/g, '-')           // spaces → hyphens
      .replace(/^-|-$/g, '');
    if (ascii.length >= 3) return ascii;
    // Tamil-only title: use pn-{item-id} so it's readable, not a timestamp
    var itemId = <?= (int)($item['id'] ?? 0) ?>;
    return itemId ? 'pn-' + itemId : 'pn-' + Math.floor(Date.now()/1000);
  }
  titleEl.addEventListener('input', function(){ if(!touched) slugEl.value = mkSlug(this.value); });
  slugEl.addEventListener('input', function(){ touched = true; });
  regen.addEventListener('click', function(){ touched=false; slugEl.value=mkSlug(titleEl.value); });

  /* Tags — identical concept to article form */
  var picker  = document.getElementById('pnTagPicker');
  var hidden  = document.getElementById('pnTagHidden');
  var search  = document.getElementById('pnTagSearch');
  var suggest = document.getElementById('pnTagSuggestions');
  var base    = '<?= BASE_URL ?>/public';

  function pnCsrf() {
    return document.querySelector('[name=_token]')?.value || '';
  }

  function addTag(id, label) {
    if (hidden.querySelector('input[value="' + id + '"]')) return;
    var chip = document.createElement('div');
    chip.className = 'tn-tag-item'; chip.dataset.id = id;
    chip.innerHTML = label + ' <i class="bi bi-x"></i>';
    chip.querySelector('i').addEventListener('click', function () {
      chip.remove();
      var h = hidden.querySelector('input[value="' + id + '"]');
      if (h) h.remove();
    });
    picker.appendChild(chip);
    var inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'tag_ids[]'; inp.value = id;
    hidden.appendChild(inp);
    search.value = ''; suggest.innerHTML = ''; suggest.style.display = 'none';
  }

  /* Wire remove (x) on already-selected chips */
  picker.querySelectorAll('.tn-tag-item').forEach(function (chip) {
    chip.querySelector('i')?.addEventListener('click', function () {
      var id = chip.dataset.id;
      chip.remove();
      var h = hidden.querySelector('input[value="' + id + '"]');
      if (h) h.remove();
    });
  });

  search.addEventListener('input', function () {
    var q = this.value.trim();
    if (!q) { suggest.innerHTML = ''; suggest.style.display = 'none'; return; }

    fetch(base + '/admin/tags/suggest?q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(function (tags) {
        suggest.innerHTML = '';
        tags.forEach(function (t) {
          var label = t.name_tamil || t.name;
          var div = document.createElement('div');
          div.className = 'tn-tag-suggest-item';
          div.textContent = label;
          div.addEventListener('click', function () { addTag(t.id, label); });
          suggest.appendChild(div);
        });

        // No match — inline quick-create (English + Tamil)
        if (!tags.length) {
          var box = document.createElement('div');
          box.className = 'tn-tag-quickadd';
          box.innerHTML =
            '<input type="text" placeholder="English name" class="form-control form-control-sm" id="pnQcEn" value="' + q + '">' +
            '<input type="text" placeholder="தமிழ் பெயர் (Tamil)" class="form-control form-control-sm" id="pnQcTa">' +
            '<button type="button" class="btn btn-sm btn-primary w-100">+ Create Tag</button>';
          box.querySelector('button').addEventListener('click', function () {
            var en = document.getElementById('pnQcEn').value.trim();
            var ta = document.getElementById('pnQcTa').value.trim();
            if (!en && !ta) return;
            var fd = new FormData();
            fd.append('_token', pnCsrf());
            fd.append('name', en);
            fd.append('name_tamil', ta);
            fetch(base + '/admin/tags/quick-create', { method: 'POST', body: fd })
              .then(function (r) { return r.json(); })
              .then(function (d) {
                if (d.success) { addTag(d.id, d.name_tamil || d.name); }
              });
          });
          suggest.appendChild(box);
        }

        suggest.style.display = suggest.children.length ? 'block' : 'none';
      });
  });

  document.addEventListener('click', function (e) {
    if (!search.contains(e.target) && !suggest.contains(e.target)) {
      suggest.style.display = 'none';
    }
  });
}());
</script>
