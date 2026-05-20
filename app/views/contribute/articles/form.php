<?php use App\Core\{Helper, CSRF}; ?>
<div class="portal-page-header">
  <div>
    <h2 class="portal-page-title"><?= $isEdit ? 'Edit Article' : 'Submit Article' ?></h2>
    <p style="font-size:13px;color:var(--portal-muted);margin:2px 0 0"><?= $isEdit ? 'Update your draft before submitting' : 'Article goes to editor review after submission' ?></p>
  </div>
  <a href="<?= $r ?>/contribute/articles" class="portal-back-btn"><i class="bi bi-arrow-left"></i> Back to Articles</a>
</div>

<form method="POST" action="<?= $r ?>/contribute/articles/<?= $isEdit ? 'edit/'.$article['id'] : 'create' ?>" id="articleForm">
  <?= CSRF::field() ?>
  <div class="row g-4">

    <!-- LEFT -->
    <div class="col-xl-8">
      <div class="portal-card mb-4">
        <div class="portal-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Article Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control form-control-lg"
                   placeholder="Enter your article headline…"
                   value="<?= Helper::e($article['title'] ?? '') ?>" required>
          </div>
          <div>
            <label class="form-label fw-600">URL Slug</label>
            <div class="input-group">
              <span class="input-group-text text-muted">/article/</span>
              <input type="text" name="slug" class="form-control"
                     value="<?= Helper::e($article['slug'] ?? '') ?>" placeholder="auto-generated">
            </div>
          </div>
        </div>
      </div>

      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-pencil-square me-2"></i>Article Content <span class="text-danger">*</span></span></div>
        <div class="portal-card-body">
          <textarea id="content" name="content"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-text-paragraph me-2"></i>Excerpt</span></div>
        <div class="portal-card-body">
          <textarea name="excerpt" class="form-control" rows="3"
                    placeholder="Brief summary (auto-generated if left blank)…"><?= Helper::e($article['excerpt'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- YOUTUBE -->
      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-youtube text-danger me-2"></i>YouTube Video (optional)</span></div>
        <div class="portal-card-body">
          <input type="url" name="youtube_url" class="form-control"
                 placeholder="https://youtube.com/watch?v=..."
                 value="<?= Helper::e($article['youtube_url'] ?? '') ?>">
          <small class="text-muted">Attach a YouTube video to support your article</small>
        </div>
      </div>

      <!-- SEO -->
      <div class="portal-card">
        <div class="portal-card-header"><span><i class="bi bi-search me-2"></i>SEO (optional)</span></div>
        <div class="portal-card-body">
          <div class="mb-3">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" maxlength="300"
                   value="<?= Helper::e($article['meta_title'] ?? '') ?>" placeholder="SEO title override…">
          </div>
          <div>
            <label class="form-label">Meta Description</label>
            <textarea name="meta_desc" class="form-control" rows="2" maxlength="500"
                      placeholder="SEO description…"><?= Helper::e($article['meta_desc'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="col-xl-4">
      <!-- SUBMIT -->
      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-send me-2"></i>Submit</span></div>
        <div class="portal-card-body">
          <div class="cntr-status-info mb-3 p-3 rounded" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2)">
            <i class="bi bi-info-circle text-warning me-2"></i>
            <span class="small text-muted">Articles go directly to <strong>editor review</strong> upon submission.</span>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn fw-600" style="background:#10b981;color:white">
              <i class="bi bi-send me-2"></i><?= $isEdit ? 'Update & Resubmit' : 'Submit for Review' ?>
            </button>
            <a href="<?= $r ?>/contribute/articles" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </div>

      <!-- CATEGORY -->
      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-folder me-2"></i>Category <span class="text-danger">*</span></span></div>
        <div class="portal-card-body">
          <?php if (empty($categories)): ?>
          <p class="text-muted small mb-0">No categories assigned. Contact admin to assign categories.</p>
          <?php else: ?>
          <?php
          $cParents = []; $cChildMap = [];
          foreach ($categories as $cat) {
            if (!$cat['parent_id']) $cParents[] = $cat;
            else $cChildMap[$cat['parent_id']][] = $cat;
          }
          $curCatId = (int)($article['category_id'] ?? 0);
          $curParent = 0; $curChild = 0;
          foreach ($categories as $cat) {
            if ($cat['id'] === $curCatId) {
              if ($cat['parent_id']) { $curParent = (int)$cat['parent_id']; $curChild = $curCatId; }
              else { $curParent = $curCatId; }
            }
          }
          ?>
          <select id="cParentSel" onchange="cLoadSubs(this.value)" id="cParentSel" onchange="cLoadSubs(this.value)" class="form-select mb-2" onchange="cLoadSubs(this.value)">
            <option value="">-- Category தேர்வு செய்யுங்கள் --</option>
            <?php foreach ($cParents as $cat): ?>
            <option value="<?= $cat['id'] ?>"
                    data-children="<?= htmlspecialchars(json_encode($cChildMap[$cat['id']] ?? [])) ?>"
                    <?= $curParent === (int)$cat['id'] ? 'selected' : '' ?>>
              <?= Helper::e($cat['name_tamil'] ?: $cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div id="cSubcatWrap" style="<?= empty($cChildMap[$curParent]) ? 'display:none' : '' ?>">
            <select name="category_id" id="cSubSel" class="form-select">
              <option value="<?= $curParent ?>">-- அனைத்தும் (subcat இல்லை) --</option>
              <?php foreach ($cChildMap[$curParent] ?? [] as $sub): ?>
              <option value="<?= $sub['id'] ?>" <?= $curChild === (int)$sub['id'] ? 'selected' : '' ?>>
                <?= Helper::e($sub['name_tamil'] ?: $sub['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <input type="hidden" name="category_id" id="cCatFallback" value="<?= $curParent ?: '' ?>">
          <?php endif; ?>
        </div>
      </div>

      <!-- TAGS -->
      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-tags me-2"></i>Tags</span></div>
        <div class="portal-card-body">
          <div id="tagPicker" class="tn-tag-picker mb-2">
            <?php foreach ($tags as $tag): ?>
            <div style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(16,185,129,.12);color:#10b981" data-id="<?= $tag['id'] ?>"><?= Helper::e($tag['name']) ?> <i class="bi bi-x"></i></div>
            <?php endforeach; ?>
          </div>
          <div id="selectedTagIds">
            <?php foreach ($tags as $tag): ?>
            <input type="hidden" name="tag_ids[]" value="<?= $tag['id'] ?>">
            <?php endforeach; ?>
          </div>
          <input type="text" id="tagSearch" class="form-control form-control-sm" placeholder="Search and add tags…">
          <div id="tagSuggestions" style="background:white;border:1px solid #D8D6CE;border-radius:6px;margin-top:4px;max-height:200px;overflow-y:auto"></div>
        </div>
      </div>

      <!-- TIPS -->
      <div class="portal-card">
        <div class="portal-card-header"><span><i class="bi bi-lightbulb me-2"></i>Writing Tips</span></div>
        <div class="portal-card-body">
          <ul class="list-unstyled small text-muted mb-0" style="line-height:2.2">
            <li>📝 Aim for 400+ words</li>
            <li>🔤 Use clear, simple language</li>
            <li>📷 Add image via YouTube link</li>
            <li>🏷️ Add 3–5 relevant tags</li>
            <li>📋 Fill the excerpt for sharing</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js">
function cLoadSubs(parentId) {
  const wrap = document.getElementById('cSubcatWrap');
  const sel  = document.getElementById('cSubSel');
  const fb   = document.getElementById('cCatFallback');
  const pSel = document.getElementById('cParentSel');
  const children = JSON.parse(pSel.options[pSel.selectedIndex]?.dataset.children || '[]');
  fb.value = parentId;
  if (children.length > 0) {
    wrap.style.display = '';
    sel.innerHTML = '<option value="' + parentId + '">-- அனைத்தும் (subcat இல்லை) --</option>';
    children.forEach(s => sel.innerHTML += '<option value="' + s.id + '">' + (s.name_tamil || s.name) + '</option>');
    sel.name = 'category_id'; fb.name = 'cat_fb_ignore';
  } else {
    wrap.style.display = 'none';
    sel.name = 'cat_sub_ignore'; fb.name = 'category_id';
  }
}
document.addEventListener('DOMContentLoaded', () => {
  const p = document.getElementById('cParentSel');
  if (p && p.value) cLoadSubs(p.value);
  else if (p) { document.getElementById('cSubSel').name='cat_sub_ignore'; document.getElementById('cCatFallback').name='category_id'; }
});
</script>
<script>
const r = document.querySelector('meta[name="csrf-token"]')?.closest('head')?.querySelector('meta[name="base-url"]')?.content || '<?= $r ?>';
</script>
<script>
tinymce.init({
  selector: '#content', height: 460, skin: 'oxide-dark', content_css: 'dark',
  plugins: 'lists link image code',
  toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter | bullist numlist | link | code',
  menubar: false, branding: false,
});

// Tag search
const r = '<?= $r ?>';
let tagDebounce;
document.getElementById('tagSearch')?.addEventListener('input', function() {
  clearTimeout(tagDebounce);
  const q = this.value.trim();
  if (!q) { document.getElementById('tagSuggestions').innerHTML = ''; return; }
  tagDebounce = setTimeout(async () => {
    const res  = await fetch(r + '/admin/tags/suggest?q=' + encodeURIComponent(q));
    const tags = await res.json();
    const box  = document.getElementById('tagSuggestions');
    box.innerHTML = tags.map(t => `<div style="padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #F0EFE9" data-id="${t.id}" data-name="${t.name}">${t.name}</div>`).join('') || '<div class="p-2 text-muted small">No tags found</div>';
    box.querySelectorAll('.tn-tag-suggest-item').forEach(el => {
      el.addEventListener('click', () => addTag(el.dataset.id, el.dataset.name));
    });
  }, 300);
});

function addTag(id, name) {
  if (document.querySelector(`.tn-tag-item[data-id="${id}"]`)) return;
  const picker = document.getElementById('tagPicker');
  const hidden = document.getElementById('selectedTagIds');
  const div    = document.createElement('div');
  div.className = 'tn-tag-item'; div.dataset.id = id;
  div.innerHTML = `${name} <i class="bi bi-x"></i>`;
  div.querySelector('i').onclick = () => { div.remove(); document.querySelector(`input[value="${id}"]`)?.remove(); };
  picker.appendChild(div);
  const inp = document.createElement('input'); inp.type='hidden'; inp.name='tag_ids[]'; inp.value=id;
  hidden.appendChild(inp);
  document.getElementById('tagSearch').value = '';
  document.getElementById('tagSuggestions').innerHTML = '';
}
document.querySelectorAll('.tn-tag-item i').forEach(btn => {
  btn.addEventListener('click', function() {
    const item = this.closest('.tn-tag-item');
    document.querySelector(`input[name="tag_ids[]"][value="${item.dataset.id}"]`)?.remove();
    item.remove();
  });
});
</script>
