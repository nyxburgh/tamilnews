<?php use App\Core\{Helper, Auth, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title"><?= $isEdit ? 'Edit Article' : 'New Article' ?></h2>
    <p class="tn-page-sub"><?= $isEdit ? 'ID #' . $article['id'] . ' · ' . Helper::e($article['slug'] ?? '') : 'Create a new article' ?></p>
  </div>
  <?php
  $backUrl = \App\Core\Auth::role() === 'admin'
    ? $r . '/admin/articles'
    : $r . '/portal/articles';
  ?>
  <a href="<?= $backUrl ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<form method="POST" action="<?= $r ?>/admin/articles/<?= $isEdit ? 'edit/' . $article['id'] : 'create' ?>" id="articleForm">
  <?= CSRF::field() ?>

  <div class="row g-4">
    <!-- LEFT: MAIN CONTENT -->
    <div class="col-xl-8">

      <!-- TITLE -->
      <div class="tn-card mb-4">
        <div class="tn-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Article Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="titleInput" class="form-control form-control-lg"
                   placeholder="Tamil or English headline…"
                   value="<?= Helper::e($article['title'] ?? '') ?>" required>
          </div>
          <div class="mb-0">
            <label class="form-label fw-600">URL Slug</label>
            <div class="input-group">
              <span class="input-group-text text-muted">/article/</span>
              <input type="text" name="slug" id="slugInput" class="form-control"
                     value="<?= Helper::e($article['slug'] ?? '') ?>" placeholder="auto-generated">
              <button type="button" class="btn btn-outline-secondary" id="regenSlug" title="Regenerate">
                <i class="bi bi-arrow-clockwise"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- CONTENT EDITOR -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-pencil-square me-2"></i>Content</span></div>
        <div class="tn-card-body">
          <textarea id="content" name="content"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- EXCERPT -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-text-paragraph me-2"></i>Excerpt</span></div>
        <div class="tn-card-body">
          <textarea name="excerpt" class="form-control" rows="3"
                    placeholder="Short summary (auto-generated if left blank)…"><?= Helper::e($article['excerpt'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- SEO -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-search me-2"></i>SEO Overrides</span></div>
        <div class="tn-card-body">
          <div class="mb-3">
            <label class="form-label">Meta Title <small class="text-muted">(leave blank to use article title)</small></label>
            <input type="text" name="meta_title" class="form-control"
                   value="<?= Helper::e($article['meta_title'] ?? '') ?>" maxlength="300"
                   placeholder="SEO title…">
          </div>
          <div>
            <label class="form-label">Meta Description</label>
            <textarea name="meta_desc" class="form-control" rows="2" maxlength="500"
                      placeholder="SEO description…"><?= Helper::e($article['meta_desc'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

    </div>

    <!-- RIGHT: META PANEL -->
    <div class="col-xl-4">

      <!-- PUBLISH -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-send me-2"></i>Publish</span></div>
        <div class="tn-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Status</label>
            <select name="status" class="form-select" id="statusSelect">
              <option value="draft" <?= ($article['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
              <?php if (Auth::role() === 'editor' || Auth::role() === 'admin'): ?>
              <option value="review" <?= ($article['status'] ?? '') === 'review' ? 'selected' : '' ?>>Send for Review</option>
              <?php endif; ?>
              <?php if (Auth::can('publish_articles')): ?>
              <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
              <option value="scheduled" <?= ($article['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
              <?php endif; ?>
            </select>
          </div>
          <div id="scheduledAtWrap" class="mb-3" style="display:none">
            <label class="form-label">Publish At</label>
            <input type="datetime-local" name="scheduled_at" class="form-control"
                   value="<?= $article['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($article['scheduled_at'])) : '' ?>">
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary fw-600">
              <i class="bi bi-save me-2"></i><?= $isEdit ? 'Update Article' : 'Create Article' ?>
            </button>
            <?php if ($isEdit && \App\Core\Auth::can('publish_articles')): ?>
            <?php if (!empty($article['slug'])): ?>
            <a href="<?= $r ?>/article/<?= Helper::e($article['slug'] ?? '') ?>" target="_blank" class="btn btn-outline-secondary">
              <i class="bi bi-eye me-2"></i>Preview
            </a>
            <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- CATEGORY & LOCATION -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-folder me-2"></i>Classification</span></div>
        <div class="tn-card-body">
          <?php
          // Build parent → children map
          $parentCats = [];
          $childMap   = [];
          foreach ($categories as $cat) {
            if (!$cat['parent_id']) {
              $parentCats[] = $cat;
            } else {
              $childMap[$cat['parent_id']][] = $cat;
            }
          }
          // Find current selected category's parent
          $currentCatId  = (int)($article['category_id'] ?? 0);
          $selectedParent = 0;
          $selectedChild  = 0;
          foreach ($categories as $cat) {
            if ($cat['id'] === $currentCatId) {
              if ($cat['parent_id']) {
                $selectedParent = (int)$cat['parent_id'];
                $selectedChild  = $currentCatId;
              } else {
                $selectedParent = $currentCatId;
              }
            }
          }
          ?>
          <!-- PARENT CATEGORY -->
          <div class="mb-3">
            <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
            <select name="parent_category_id" id="parentCatSelect" class="form-select" onchange="loadSubcats(this.value)">
              <option value="">-- Select Category --</option>
              <?php foreach ($parentCats as $cat): ?>
              <option value="<?= $cat['id'] ?>"
                      data-children="<?= htmlspecialchars(json_encode($childMap[$cat['id']] ?? [])) ?>"
                      <?= $selectedParent === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= Helper::e($cat['name_tamil'] ?: $cat['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- SUBCATEGORY (shown only if parent has children) -->
          <input type="hidden" name="category_id" id="finalCategoryId" value="<?= $currentCatId ?: '' ?>">
          <div class="mb-3" id="subcatWrap" style="<?= empty($childMap[$selectedParent]) ? 'display:none' : '' ?>">
            <label class="form-label fw-600">Subcategory</label>
            <select id="subcatSelect" class="form-select">
              <option value="<?= $selectedParent ?>">-- All (no subcat) --</option>
              <?php foreach ($childMap[$selectedParent] ?? [] as $sub): ?>
              <option value="<?= $sub['id'] ?>" <?= $selectedChild === (int)$sub['id'] ? 'selected' : '' ?>>
                <?= Helper::e($sub['name_tamil'] ?: $sub['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>">
          <div class="mb-3">
            <label class="form-label fw-600">Content Type</label>
            <select name="content_type" class="form-select">
              <?php foreach (['news','video','short_news','live_update','gallery'] as $t): ?>
              <option value="<?= $t ?>" <?= ($article['content_type'] ?? 'news') === $t ? 'selected' : '' ?>>
                <?= ucwords(str_replace('_',' ',$t)) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label">City / Location</label>
            <select name="city_id" class="form-select">
              <option value="">None</option>
              <?php foreach ($cities as $city): ?>
              <option value="<?= $city['id'] ?>" <?= ($article['city_id'] ?? 0) == $city['id'] ? 'selected' : '' ?>>
                <?= Helper::e($city['name']) ?> (<?= Helper::e($city['district_name']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- FEATURED IMAGE -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-image me-2"></i>Featured Image</span></div>
        <div class="tn-card-body">
          <input type="hidden" name="media_id" id="mediaId" value="<?= $article['media_id'] ?? '' ?>">

          <!-- IMAGE PREVIEW -->
          <div id="imagePreview" class="mb-3 <?= empty($article['image_url']) ? 'd-none' : '' ?>">
            <div style="position:relative;display:inline-block;width:100%">
              <img src="<?= !empty($article['image_url']) ? htmlspecialchars($article['image_url']) : '' ?>"
                   id="previewImg" class="img-fluid rounded" alt=""
                   style="max-height:200px;width:100%;object-fit:cover">
              <button type="button" onclick="clearImage()"
                style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.6);color:white;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-size:14px">&#10005;</button>
            </div>
          </div>

          <!-- DRAG & DROP UPLOAD ZONE -->
          <div id="uploadZone"
            style="border:2px dashed #D8D6CE;border-radius:8px;padding:24px;text-align:center;cursor:pointer;transition:border-color .2s;<?= !empty($article['image_url']) ? 'display:none' : '' ?>"
            onclick="document.getElementById('directUpload').click()"
            ondragover="event.preventDefault();this.style.borderColor='#10b981'"
            ondragleave="this.style.borderColor=''"
            ondrop="handleImageDrop(event)">
            <div id="uploadZoneContent">
              <div style="font-size:32px;margin-bottom:8px">&#128444;&#65039;</div>
              <div style="font-weight:600;margin-bottom:4px">Click to upload or drag &amp; drop</div>
              <div style="font-size:12px;color:#6B6A64">JPG, PNG, WebP &mdash; max 5MB</div>
            </div>
            <div id="uploadProgress" style="display:none;padding:0 12px">
              <div style="font-size:13px;margin-bottom:8px">&#8987; Uploading...</div>
              <div style="background:#F0EFE9;border-radius:4px;height:6px">
                <div id="uploadBar" style="background:#10b981;height:6px;border-radius:4px;width:0%;transition:width .3s"></div>
              </div>
            </div>
          </div>
          <input type="file" id="directUpload" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="uploadImage(this.files[0])">

          <?php if (\App\Core\Auth::role() === 'admin'): ?>
          <div style="text-align:center;margin-top:10px;font-size:12px;color:#9A9890">
            or <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="openMediaModal()">
              <i class="bi bi-folder2-open me-1"></i>Media Library
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- TAGS -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-tags me-2"></i>Tags</span></div>
        <div class="tn-card-body">
          <div id="tagPicker" class="tn-tag-picker">
            <?php foreach ($tags as $tag): ?>
            <div class="tn-tag-item" data-id="<?= $tag['id'] ?>"><?= Helper::e($tag['name']) ?> <i class="bi bi-x"></i></div>
            <?php endforeach; ?>
          </div>
          <div id="selectedTagIds">
            <?php foreach ($tags as $tag): ?>
            <input type="hidden" name="tag_ids[]" value="<?= $tag['id'] ?>">
            <?php endforeach; ?>
          </div>
          <input type="text" id="tagSearch" class="form-control mt-2" placeholder="Search tags…">
          <div id="tagSuggestions" class="tn-tag-suggestions"></div>
        </div>
      </div>

      <!-- FLAGS -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-flag me-2"></i>Flags</span></div>
        <div class="tn-card-body">
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_breaking" value="1" id="isBreaking"
                   <?= !empty($article['is_breaking']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="isBreaking"><i class="bi bi-lightning-charge text-danger me-1"></i>Breaking News</label>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_editors_pick" value="1" id="isEditorsPick"
                   <?= !empty($article['is_editors_pick']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="isEditorsPick"><i class="bi bi-star text-warning me-1"></i>Editor's Pick</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured"
                   <?= !empty($article['is_featured']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="isFeatured"><i class="bi bi-pin-angle text-primary me-1"></i>Featured</label>
          </div>
        </div>
      </div>

      <!-- YOUTUBE EMBED -->
      <div class="tn-card mb-4">
        <div class="tn-card-header"><span><i class="bi bi-youtube text-danger me-2"></i>YouTube Embed</span></div>
        <div class="tn-card-body">
          <input type="url" name="youtube_url" class="form-control"
                 placeholder="https://youtube.com/watch?v=..."
                 value="<?= Helper::e($article['youtube_url'] ?? '') ?>">
          <small class="text-muted">Paste YouTube URL to embed video</small>
        </div>
      </div>

    </div>
  </div>
</form>

<!-- MEDIA MODAL -->
<div class="modal fade" id="mediaModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Media Library</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="mediaModalBody">
        <div class="text-center py-5"><div class="spinner-border"></div></div>
      </div>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tinymce@6/skins/ui/oxide-dark/skin.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js">function loadSubcats(parentId) {
  const wrap=document.getElementById('subcatWrap');
  const sub=document.getElementById('subcatSelect');
  const fid=document.getElementById('finalCategoryId');
  const pSel=document.getElementById('parentCatSelect');
  const children=JSON.parse(pSel.options[pSel.selectedIndex]?.dataset.children||'[]');
  fid.value=parentId;
  if(children.length){
    wrap.style.display='';
    sub.innerHTML='<option value="">-- No subcategory --</option>';
    children.forEach(s=>{const o=new Option(s.name_tamil||s.name,s.id);sub.add(o);});
    sub.onchange=()=>{fid.value=sub.value||parentId;};
  } else { wrap.style.display='none'; }
}
document.addEventListener('DOMContentLoaded',()=>{
  const sp=<?= (int)($selectedParent??0) ?>,sc=<?= (int)($selectedChild??0) ?>;
  if(sp){loadSubcats(sp);if(sc)setTimeout(()=>{const s=document.getElementById('subcatSelect');if(s){s.value=sc;document.getElementById('finalCategoryId').value=sc;}},60);}
  document.getElementById('articleForm')?.addEventListener('submit',e=>{
    if(!document.getElementById('finalCategoryId')?.value){
      e.preventDefault();alert('Please select a category.');
      document.getElementById('parentCatSelect')?.focus();
    }
  });
});
</script>
<script>
tinymce.init({
  selector: '#content',
  height: 500,
  skin: 'oxide-dark',
  content_css: 'dark',
  plugins: 'lists link image media table code fullscreen',
  toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | table | code fullscreen',
  menubar: false,
  branding: false,
});

// Slug generation
const titleInput = document.getElementById('titleInput');
const slugInput  = document.getElementById('slugInput');
const slugDirty  = <?= $isEdit ? 'true' : 'false' ?>;

titleInput?.addEventListener('input', function() {
  if (!slugDirty && !<?= $isEdit ? 'true' : 'false' ?>) {
    slugInput.value = this.value.toLowerCase().replace(/\s+/g,'-').replace(/[^\w\-]/g,'').replace(/\-+/g,'-');
  }
});

document.getElementById('regenSlug')?.addEventListener('click', function() {
  slugInput.value = titleInput.value.toLowerCase().replace(/\s+/g,'-').replace(/[^\w\-]/g,'').replace(/\-+/g,'-');
});

// Schedule toggle
document.getElementById('statusSelect')?.addEventListener('change', function() {
  document.getElementById('scheduledAtWrap').style.display = this.value === 'scheduled' ? 'block' : 'none';
});
if (document.getElementById('statusSelect')?.value === 'scheduled') {
  document.getElementById('scheduledAtWrap').style.display = 'block';
}

// Tag search
let tagDebounce;
document.getElementById('tagSearch')?.addEventListener('input', function() {
  clearTimeout(tagDebounce);
  const q = this.value.trim();
  if (!q) { document.getElementById('tagSuggestions').innerHTML = ''; return; }
  tagDebounce = setTimeout(async () => {
    const res = await fetch(r + '/admin/tags/suggest?q=' + encodeURIComponent(q));
    const tags = await res.json();
    const box  = document.getElementById('tagSuggestions');
    box.innerHTML = tags.map(t => `<div class="tn-tag-suggest-item" data-id="${t.id}" data-name="${t.name}">${t.name}${t.name_tamil ? ' · ' + t.name_tamil : ''}</div>`).join('') || '<div class="p-2 text-muted small">No tags found</div>';
    box.querySelectorAll('.tn-tag-suggest-item').forEach(el => {
      el.addEventListener('click', () => addTag(el.dataset.id, el.dataset.name));
    });
  }, 300);
});

function addTag(id, name) {
  if (document.querySelector(`[data-id="${id}"].tn-tag-item`)) return;
  const picker = document.getElementById('tagPicker');
  const hidden = document.getElementById('selectedTagIds');
  const div    = document.createElement('div');
  div.className = 'tn-tag-item'; div.dataset.id = id;
  div.innerHTML = `${name} <i class="bi bi-x"></i>`;
  div.querySelector('i').onclick = () => { div.remove(); document.querySelector(`input[name="tag_ids[]"][value="${id}"]`)?.remove(); };
  picker.appendChild(div);
  const inp = document.createElement('input'); inp.type='hidden'; inp.name='tag_ids[]'; inp.value=id;
  hidden.appendChild(inp);
  document.getElementById('tagSearch').value = '';
  document.getElementById('tagSuggestions').innerHTML = '';
}

document.querySelectorAll('.tn-tag-item i').forEach(btn => {
  btn.addEventListener('click', function() {
    const item = this.closest('.tn-tag-item');
    const id   = item.dataset.id;
    item.remove();
    document.querySelector(`input[name="tag_ids[]"][value="${id}"]`)?.remove();
  });
});

// Media modal
function openMediaModal() {
  const modal = new bootstrap.Modal(document.getElementById('mediaModal'));
  fetch(r + '/admin/media/modal')
    .then(r => r.text())
    .then(html => { document.getElementById('mediaModalBody').innerHTML = html; });
  modal.show();
}


// ── DIRECT IMAGE UPLOAD ───────────────────────────────
async function uploadImage(file) {
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) { alert('File too large. Max 5MB.'); return; }

  const zone     = document.getElementById('uploadZone');
  const progress = document.getElementById('uploadProgress');
  const zContent = document.getElementById('uploadZoneContent');
  const bar      = document.getElementById('uploadBar');

  zContent.style.display = 'none';
  progress.style.display = 'block';

  // Simulate progress
  let pct = 0;
  const ticker = setInterval(() => { pct = Math.min(pct + 10, 80); bar.style.width = pct + '%'; }, 150);

  const fd = new FormData();
  fd.append('file', file);
  fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

  try {
    const res  = await fetch(r + '/admin/media/upload', { method: 'POST', body: fd });
    const data = await res.json();
    clearInterval(ticker);
    bar.style.width = '100%';

    if (data.success && data.media) {
      document.getElementById('mediaId').value = data.media.id;
      const preview = document.getElementById('imagePreview');
      const img     = document.getElementById('previewImg');
      img.src       = r.replace('/public','') + data.media.filepath;
      preview.classList.remove('d-none');
      zone.style.display = 'none';
    } else {
      zContent.style.display = 'block';
      progress.style.display = 'none';
      bar.style.width = '0%';
      alert(data.error || 'Upload failed. Try again.');
    }
  } catch(e) {
    clearInterval(ticker);
    zContent.style.display = 'block';
    progress.style.display = 'none';
    bar.style.width = '0%';
    alert('Upload failed. Check your connection.');
  }
}

function handleImageDrop(e) {
  e.preventDefault();
  document.getElementById('uploadZone').style.borderColor = '';
  const file = e.dataTransfer?.files[0];
  if (file && file.type.startsWith('image/')) uploadImage(file);
}

function clearImage() {
  document.getElementById('mediaId').value = '';
  document.getElementById('previewImg').src = '';
  document.getElementById('imagePreview').classList.add('d-none');
  document.getElementById('uploadZone').style.display = '';
  document.getElementById('uploadZoneContent').style.display = 'block';
  document.getElementById('uploadProgress').style.display = 'none';
  document.getElementById('uploadBar').style.width = '0%';
  document.getElementById('directUpload').value = '';
}

function selectMedia(id, url) {
  document.getElementById('mediaId').value = id;
  const img = document.getElementById('previewImg');
  img.src   = url;
  document.getElementById('imagePreview').classList.remove('d-none');
  document.getElementById('uploadZone').style.display = 'none';
  bootstrap.Modal.getInstance(document.getElementById('mediaModal'))?.hide();
}

</script>
