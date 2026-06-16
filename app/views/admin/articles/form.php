<?php use App\Core\{Helper, Auth, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title"><?= $isEdit ? 'Edit Article' : 'New Article' ?></h2>
    <p class="tn-page-sub"><?= $isEdit ? 'ID #'.$article['id'].' · '.Helper::e($article['slug']??'') : 'Fill all fields below' ?></p>
  </div>
  <?php $backUrl = Auth::role()==='admin' ? $r.'/admin/articles' : $r.'/portal/articles'; ?>
  <a href="<?= $backUrl ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>

<form method="POST" action="<?= $r ?>/admin/articles/<?= $isEdit ? 'edit/'.$article['id'] : 'create' ?>" id="articleForm">
<?= CSRF::field() ?>

<div class="row g-3">

  <!-- ════ LEFT COLUMN ════ -->
  <div class="col-xl-8 col-lg-7">

    <!-- 1. CLASSIFICATION — Category first -->
    <div class="tn-card mb-3">
      <div class="tn-card-header"><span><i class="bi bi-folder2-open me-2"></i>Classification</span></div>
      <div class="tn-card-body">
        <div class="row g-3">

          <!-- Category -->
          <div class="col-md-6">
            <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
            <?php
            $parentCats = [];
            $childMap   = [];
            foreach ($categories as $cat) {
              if (!$cat['parent_id']) $parentCats[] = $cat;
              else $childMap[$cat['parent_id']][] = $cat;
            }
            $currentCatId   = (int)($article['category_id'] ?? 0);
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
            <select name="parent_category_id" id="parentCatSelect" class="form-select" onchange="loadSubcats(this.value)">
              <option value="">-- Select Category --</option>
              <?php foreach ($parentCats as $cat): ?>
              <option value="<?= $cat['id'] ?>"
                      data-children="<?= htmlspecialchars(json_encode($childMap[$cat['id']] ?? [])) ?>"
                      <?= $selectedParent===(int)$cat['id'] ? 'selected' : '' ?>>
                <?= Helper::e($cat['name_tamil'] ?: $cat['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" name="category_id" id="finalCategoryId" value="<?= $currentCatId ?: '' ?>">
          </div>

          <!-- Subcategory -->
          <div class="col-md-6" id="subcatWrap" style="<?= empty($childMap[$selectedParent]) ? 'visibility:hidden' : '' ?>">
            <label class="form-label fw-600">Subcategory</label>
            <select id="subcatSelect" class="form-select">
              <option value="<?= $selectedParent ?>">-- All --</option>
              <?php foreach ($childMap[$selectedParent] ?? [] as $sub): ?>
              <option value="<?= $sub['id'] ?>" <?= $selectedChild===(int)$sub['id'] ? 'selected' : '' ?>>
                <?= Helper::e($sub['name_tamil'] ?: $sub['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Content Type -->
          <div class="col-md-6">
            <label class="form-label fw-600">Content Type</label>
            <select name="content_type" class="form-select">
              <?php foreach (['news'=>'News','video'=>'Video','short_news'=>'Short News','live_update'=>'Live Update','gallery'=>'Gallery'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($article['content_type']??'news')===$v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- City / Location -->
          <div class="col-md-6">
            <label class="form-label fw-600">City / Location</label>
            <select name="city_id" class="form-select">
              <option value="">None</option>
              <?php foreach ($cities as $city): ?>
              <option value="<?= $city['id'] ?>" <?= ($article['city_id']??0)==$city['id'] ? 'selected' : '' ?>>
                <?= Helper::e($city['name']) ?><?= !empty($city['district_name']) ? ' ('.$city['district_name'].')' : '' ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>
      </div>
    </div>

    <!-- 2. TITLE & SLUG -->
    <div class="tn-card mb-3">
      <div class="tn-card-header"><span><i class="bi bi-card-heading me-2"></i>Title & Slug</span></div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Article Title <span class="text-danger">*</span></label>
          <input type="text" name="title" id="titleInput" class="form-control form-control-lg"
                 placeholder="Tamil or English headline…"
                 value="<?= Helper::e($article['title'] ?? '') ?>" required>
        </div>
        <div>
          <label class="form-label fw-600">URL Slug</label>
          <div class="input-group">
            <span class="input-group-text text-muted small">/article/</span>
            <input type="text" name="slug" id="slugInput" class="form-control"
                   value="<?= Helper::e($article['slug'] ?? '') ?>" placeholder="auto-generated">
            <button type="button" class="btn btn-outline-secondary" id="regenSlug" title="Regenerate">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. CONTENT EDITOR -->
    <div class="tn-card mb-3">
      <div class="tn-card-header"><span><i class="bi bi-pencil-square me-2"></i>Content</span></div>
      <div class="tn-card-body" style="padding:0">
        
<style>
.art-content-area {
  min-height: 520px;
  font-family: 'Noto Sans Tamil', 'Source Sans 3', sans-serif;
  font-size: 16px;
  line-height: 1.8;
  padding: 16px;
  border: 1px solid #D8D6CE;
  border-radius: 6px;
  resize: vertical;
  width: 100%;
  background: #FAFAF8;
}
.art-content-area:focus { border-color: #C0001A; outline: none; box-shadow: 0 0 0 2px rgba(192,0,26,.1); }
.art-toolbar { display:flex; flex-wrap:wrap; gap:4px; padding:8px; background:#F5F5F2; border:1px solid #D8D6CE; border-bottom:none; border-radius:6px 6px 0 0; }
.art-toolbar button { padding:4px 10px; font-size:12px; font-weight:600; border:1px solid #D8D6CE; background:#fff; border-radius:4px; cursor:pointer; color:#1A1A1A; }
.art-toolbar button:hover { background:#C0001A; color:#fff; border-color:#C0001A; }
.art-toolbar .sep { width:1px; background:#D8D6CE; margin:2px 4px; }
</style>
<div class="art-toolbar">
  <button type="button" onclick="fmt('bold')" title="Bold"><b>B</b></button>
  <button type="button" onclick="fmt('italic')" title="Italic"><i>I</i></button>
  <button type="button" onclick="fmt('underline')" title="Underline"><u>U</u></button>
  <span class="sep"></span>
  <button type="button" onclick="wrapTag('h2')" title="Heading">H2</button>
  <button type="button" onclick="wrapTag('h3')" title="Sub-heading">H3</button>
  <button type="button" onclick="wrapTag('p')" title="Paragraph">P</button>
  <span class="sep"></span>
  <button type="button" onclick="wrapTag('blockquote')" title="Quote">❝</button>
  <button type="button" onclick="insertUL()" title="Bullet List">• List</button>
  <span class="sep"></span>
  <button type="button" onclick="insertLink()" title="Link">🔗</button>
</div>
<script>
function fmt(cmd) {
  const ta = document.getElementById('content');
  const s = ta.selectionStart, e = ta.selectionEnd;
  const sel = ta.value.substring(s, e);
  const tags = { bold:'<strong>', italic:'<em>', underline:'<u>' };
  const close = { bold:'</strong>', italic:'</em>', underline:'</u>' };
  ta.setRangeText(tags[cmd] + sel + close[cmd], s, e, 'end');
}
function wrapTag(tag) {
  const ta = document.getElementById('content');
  const s = ta.selectionStart, e = ta.selectionEnd;
  const sel = ta.value.substring(s, e) || 'Text here';
  ta.setRangeText(`<${tag}>${sel}</${tag}>`, s, e, 'end');
}
function insertUL() {
  const ta = document.getElementById('content');
  const s = ta.selectionStart;
  ta.setRangeText('\n<ul>\n  <li>Item 1</li>\n  <li>Item 2</li>\n</ul>\n', s, s, 'end');
}
function insertLink() {
  const url = prompt('Enter URL:');
  if (!url) return;
  const ta = document.getElementById('content');
  const s = ta.selectionStart, e = ta.selectionEnd;
  const txt = ta.value.substring(s, e) || 'Link text';
  ta.setRangeText(`<a href="${url}">${txt}</a>`, s, e, 'end');
}
</script>
<textarea id="content" name="content" class="form-control art-content-area" style="border-radius:0 0 6px 6px"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- 4. EXCERPT -->
    <div class="tn-card mb-3">
      <div class="tn-card-header"><span><i class="bi bi-text-paragraph me-2"></i>Excerpt / Summary</span></div>
      <div class="tn-card-body">
        <textarea name="excerpt" class="form-control" rows="3"
                  placeholder="Short summary (auto-generated if blank)…"><?= Helper::e($article['excerpt'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- 5. SEO -->
    <div class="tn-card mb-3">
      <div class="tn-card-header"><span><i class="bi bi-search me-2"></i>SEO</span></div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label">Meta Title <small class="text-muted">(blank = article title)</small></label>
          <input type="text" name="meta_title" class="form-control"
                 value="<?= Helper::e($article['meta_title'] ?? '') ?>" maxlength="300">
        </div>
        <div>
          <label class="form-label">Meta Description</label>
          <textarea name="meta_desc" class="form-control" rows="2" maxlength="500"><?= Helper::e($article['meta_desc'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

  </div><!-- /col left -->

  <!-- ════ RIGHT COLUMN ════ -->
  <div class="col-xl-4 col-lg-5">
    <div class="art-right-sticky">

      <!-- PUBLISH & STATUS — top of right col -->
      <div class="tn-card mb-3">
        <div class="tn-card-header"><span><i class="bi bi-send me-2"></i>Publish</span></div>
        <div class="tn-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Status</label>
            <select name="status" class="form-select" id="statusSelect">
              <option value="draft"   <?= ($article['status']??'draft')==='draft'     ? 'selected':'' ?>>Draft</option>
              <option value="review"  <?= ($article['status']??'')==='review'          ? 'selected':'' ?>>Submit for Review</option>
              <?php if (Auth::can('publish_articles')): ?>
              <option value="published" <?= ($article['status']??'')==='published'    ? 'selected':'' ?>>Published</option>
              <option value="scheduled" <?= ($article['status']??'')==='scheduled'    ? 'selected':'' ?>>Scheduled</option>
              <?php endif; ?>
            </select>
          </div>
          <div id="scheduledAtWrap" class="mb-3" style="display:none">
            <label class="form-label">Publish At</label>
            <input type="datetime-local" name="scheduled_at" class="form-control"
                   value="<?= !empty($article['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($article['scheduled_at'])) : '' ?>">
          </div>
          <!-- SUBMIT BUTTON — always visible at top of right panel -->
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg fw-600">
              <i class="bi bi-save me-2"></i><?= $isEdit ? 'Update Article' : 'Create Article' ?>
            </button>
            <?php if ($isEdit && !empty($article['slug'])): ?>
            <a href="<?= $r ?>/article/<?= Helper::e($article['slug']) ?>" target="_blank" class="btn btn-outline-secondary">
              <i class="bi bi-eye me-2"></i>Preview
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- FEATURED IMAGE -->
      <div class="tn-card mb-3">
        <div class="tn-card-header"><span><i class="bi bi-image me-2"></i>Featured Image</span></div>
        <div class="tn-card-body">
          <input type="hidden" name="media_id" id="mediaId" value="<?= $article['media_id'] ?? '' ?>">
          <div id="imagePreview" class="mb-3 <?= empty($article['image_url']) ? 'd-none' : '' ?>">
            <div style="position:relative">
              <img src="<?= htmlspecialchars($article['image_url'] ?? '') ?>"
                   id="previewImg" class="img-fluid rounded" alt=""
                   style="max-height:180px;width:100%;object-fit:cover">
              <button type="button" onclick="clearImage()"
                style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.6);color:white;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-size:14px">✕</button>
            </div>
          </div>
          <div id="uploadZone"
            style="border:2px dashed #D8D6CE;border-radius:8px;padding:20px;text-align:center;cursor:pointer;<?= !empty($article['image_url']) ? 'display:none' : '' ?>"
            onclick="document.getElementById('directUpload').click()"
            ondragover="event.preventDefault();this.style.borderColor='#10b981'"
            ondragleave="this.style.borderColor=''"
            ondrop="handleImageDrop(event)">
            <div id="uploadZoneContent">
              <div style="font-size:28px;margin-bottom:6px">🖼️</div>
              <div style="font-weight:600;font-size:13px;margin-bottom:4px">Click or drag image</div>
              <div style="font-size:11px;color:#6B6A64">JPG, PNG, WebP — max 5MB</div>
              <div id="uploadError" style="display:none;color:#C0001A;font-size:11px;margin-top:4px"></div>
            </div>
            <div id="uploadProgress" style="display:none">
              <div style="font-size:12px;margin-bottom:6px">⏳ Uploading...</div>
              <div style="background:#F0EFE9;border-radius:4px;height:4px">
                <div id="uploadBar" style="background:#10b981;height:4px;border-radius:4px;width:0%;transition:width .3s"></div>
              </div>
            </div>
          </div>
          <input type="file" id="directUpload" accept="image/*" style="display:none" onchange="uploadImage(this.files[0])">
          <?php if (Auth::role() === 'admin'): ?>
          <div class="mt-2 text-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openMediaModal()">
              <i class="bi bi-folder2-open me-1"></i>Media Library
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- TAGS -->
      <div class="tn-card mb-3">
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
          <input type="text" id="tagSearch" class="form-control mt-2" placeholder="Search & add tags…">
          <div id="tagSuggestions" class="tn-tag-suggestions"></div>
        </div>
      </div>

      <!-- FLAGS -->
      <div class="tn-card mb-3">
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
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured"
                   <?= !empty($article['is_featured']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="isFeatured"><i class="bi bi-pin-angle text-primary me-1"></i>Featured</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_premium" value="1" id="isPremium"
                   <?= !empty($article['is_premium']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="isPremium"><i class="bi bi-lock text-warning me-1"></i>Premium</label>
          </div>
        </div>
      </div>

      <!-- YOUTUBE -->
      <div class="tn-card mb-3">
        <div class="tn-card-header"><span><i class="bi bi-youtube text-danger me-2"></i>YouTube</span></div>
        <div class="tn-card-body">
          <input type="url" name="youtube_url" class="form-control"
                 placeholder="https://youtube.com/watch?v=…"
                 value="<?= Helper::e($article['youtube_url'] ?? '') ?>">
          <small class="text-muted">Paste URL to embed video</small>
          <input type="hidden" name="youtube_video_id" id="youtubeVideoId" value="<?= Helper::e($article['youtube_video_id'] ?? '') ?>">
        </div>
      </div>

      <!-- BOTTOM SUBMIT (mobile convenience) -->
      <div class="d-grid mb-4">
        <button type="submit" class="btn btn-primary fw-600 btn-lg">
          <i class="bi bi-save me-2"></i><?= $isEdit ? 'Update Article' : 'Create Article' ?>
        </button>
      </div>

    </div><!-- /art-right-sticky -->
  </div><!-- /col right -->

</div><!-- /row -->
</form>

<?php
// Include media modal and JS only for admin
if (Auth::role() === 'admin'): ?>
<?php include VIEW_PATH . '/admin/media/_modal.php'; ?>
<?php endif; ?>

<script>
// Slug auto-generate
const titleInput = document.getElementById('titleInput');
const slugInput  = document.getElementById('slugInput');
titleInput?.addEventListener('input', () => {
  if (!slugInput.dataset.manual) {
    slugInput.value = titleInput.value
      .toLowerCase()
      .replace(/[^\w\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .substring(0, 120);
  }
});
slugInput?.addEventListener('input', () => { slugInput.dataset.manual = '1'; });
document.getElementById('regenSlug')?.addEventListener('click', () => {
  delete slugInput.dataset.manual;
  titleInput.dispatchEvent(new Event('input'));
});

// Subcategory loader
function loadSubcats(parentId) {
  const wrap = document.getElementById('subcatWrap');
  const sel  = document.getElementById('subcatSelect');
  const fin  = document.getElementById('finalCategoryId');
  const opt  = document.querySelector(`#parentCatSelect option[value="${parentId}"]`);
  const children = opt ? JSON.parse(opt.dataset.children || '[]') : [];
  fin.value = parentId;
  if (!children.length) {
    wrap.style.visibility = 'hidden';
    return;
  }
  wrap.style.visibility = 'visible';
  sel.innerHTML = `<option value="${parentId}">-- All --</option>`;
  children.forEach(c => {
    sel.innerHTML += `<option value="${c.id}">${c.name_tamil || c.name}</option>`;
  });
  sel.onchange = () => { fin.value = sel.value; };
}
// Init subcats on load
document.getElementById('parentCatSelect')?.dispatchEvent(new Event('change'));

// Scheduled date toggle
document.getElementById('statusSelect')?.addEventListener('change', function() {
  document.getElementById('scheduledAtWrap').style.display = this.value === 'scheduled' ? 'block' : 'none';
});
if (document.getElementById('statusSelect')?.value === 'scheduled') {
  document.getElementById('scheduledAtWrap').style.display = 'block';
}

// Image upload
function uploadImage(file) {
  if (!file) return;
  const fd = new FormData();
  fd.append('image', file);
  fd.append('_token', document.querySelector('[name=_token]')?.value || document.querySelector('[name=csrf_token]')?.value || '');
  document.getElementById('uploadZoneContent').style.display = 'none';
  document.getElementById('uploadProgress').style.display = 'block';
  const bar = document.getElementById('uploadBar');
  let p = 0;
  const timer = setInterval(() => { if (p < 80) { p += 10; bar.style.width = p + '%'; } }, 150);
  fetch('<?= $r ?>/admin/media/upload-ajax', { method:'POST', body:fd })
    .then(r => r.json())
    .then(d => {
      clearInterval(timer);
      if (d.success) {
        document.getElementById('mediaId').value = d.media_id;
        document.getElementById('previewImg').src = d.url;
        document.getElementById('imagePreview').classList.remove('d-none');
        document.getElementById('uploadZone').style.display = 'none';
        bar.style.width = '100%';
      } else {
        const errEl = document.getElementById('uploadError');
        if (errEl) { errEl.textContent = d.error || 'Upload failed'; errEl.style.display = 'block'; }
        resetUploadZone();
      }
    })
    .catch(() => { clearInterval(timer); resetUploadZone(); resetUploadZone(); });
}
function handleImageDrop(e) {
  e.preventDefault();
  e.currentTarget.style.borderColor = '';
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) uploadImage(file);
}
function clearImage() {
  document.getElementById('mediaId').value = '';
  document.getElementById('imagePreview').classList.add('d-none');
  document.getElementById('uploadZone').style.display = 'block';
  resetUploadZone();
}
function resetUploadZone() {
  document.getElementById('uploadZoneContent').style.display = 'block';
  document.getElementById('uploadProgress').style.display = 'none';
  document.getElementById('uploadBar').style.width = '0%';
}

// Tag search
const tagSearch = document.getElementById('tagSearch');
const tagSugBox  = document.getElementById('tagSuggestions');
tagSearch?.addEventListener('input', function() {
  const q = this.value.trim();
  if (!q) { tagSugBox.innerHTML = ''; return; }
  fetch(`<?= $r ?>/admin/tags/search?q=${encodeURIComponent(q)}`)
    .then(r => r.json())
    .then(tags => {
      tagSugBox.innerHTML = tags.map(t =>
        `<div class="tn-tag-suggestion" onclick="addTag(${t.id},'${t.name.replace(/'/g,"\\'")}')">
          ${t.name}</div>`
      ).join('');
    });
});
function addTag(id, name) {
  if (document.querySelector(`[data-id="${id}"]`)) return;
  const picker = document.getElementById('tagPicker');
  const selIds = document.getElementById('selectedTagIds');
  const div = document.createElement('div');
  div.className = 'tn-tag-item'; div.dataset.id = id;
  div.innerHTML = `${name} <i class="bi bi-x"></i>`;
  div.querySelector('i').onclick = () => {
    div.remove();
    selIds.querySelector(`[value="${id}"]`)?.remove();
  };
  picker.appendChild(div);
  const inp = document.createElement('input');
  inp.type = 'hidden'; inp.name = 'tag_ids[]'; inp.value = id;
  selIds.appendChild(inp);
  tagSearch.value = ''; tagSugBox.innerHTML = '';
}
document.getElementById('tagPicker')?.addEventListener('click', e => {
  const item = e.target.closest('.tn-tag-item');
  if (e.target.tagName === 'I' && item) {
    const id = item.dataset.id;
    document.querySelector(`#selectedTagIds [value="${id}"]`)?.remove();
    item.remove();
  }
});
</script>

<?php
// Admin-only: add sticky right panel CSS override
?>
<style>
.art-right-sticky {
  position: sticky;
  top: 60px;
}
@media (max-width: 991px) {
  /* Mobile: right col flows normally after left, no sticky */
  .art-right-sticky { position: static; }
  .col-xl-8, .col-xl-4 { /* already handled by Bootstrap col-12 */ }
}
</style>

<script>
tinymce.init({
  selector: '#content',
  height: 500,
  plugins: [
    'anchor', 'autolink', 'charmap', 'codesample', 'emoticons',
    'image', 'link', 'lists', 'media', 'searchreplace',
    'table', 'visualblocks', 'wordcount'
  ],
  toolbar: 'undo redo | blocks fontsize | bold italic underline | ' +
           'alignleft aligncenter alignright alignjustify | ' +
           'bullist numlist | link image media table | ' +
           'removeformat | searchreplace',
  toolbar_mode: 'wrap',
  content_style: 'body { font-family: Noto Sans Tamil, Source Sans 3, sans-serif; font-size: 16px; line-height: 1.7; direction: ltr; }',
  language: 'en',
  directionality: 'ltr',
  statusbar: true,
  branding: false,
  promotion: false,
  paste_as_text: false,
  images_upload_url: '<?= $r ?>/admin/media/upload-ajax',
  images_upload_handler: function(blobInfo, progress) {
    return new Promise((resolve, reject) => {
      const fd = new FormData();
      fd.append('image', blobInfo.blob(), blobInfo.filename());
      fd.append('csrf_token', document.querySelector('[name=csrf_token]')?.value || '');
      fetch('<?= $r ?>/admin/media/upload-ajax', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (d.success) resolve(d.url); else reject(d.error || 'Upload failed'); })
        .catch(e => reject(e.toString()));
    });
  },
  setup: function(editor) {
    // Sync content back to textarea on form submit
    document.getElementById('articleForm')?.addEventListener('submit', function() {
      editor.save();
    });
  }
});
</script>
