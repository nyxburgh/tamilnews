<?php use App\Core\{Helper, Auth, CSRF};
$isAdmin = Auth::role()==='admin';
$backUrl = $isAdmin ? BASE_URL.'/public/admin/articles' : BASE_URL.'/public/portal/all-articles';
// Prefill from Photo News
if (!empty($_prefill['title'] ?? '')) {
    $article['title'] = ($article['title'] ?? '') ?: ($_prefill['title'] ?? '');
    $article['slug']  = ($article['slug']  ?? '') ?: ($_prefill['slug']  ?? '');
    if (!empty($_prefill['_pn_tags'] ?? [])) {
        $tags = array_values(array_unique(array_merge($tags ?? [], $_prefill['_pn_tags']), SORT_REGULAR));
    }
}
if ($isEdit) {
    $action = $isAdmin
        ? BASE_URL.'/public/admin/articles/edit/'.($article['id']??0)
        : BASE_URL.'/public/portal/all-articles/edit/'.($article['id']??0);
} else {
    $action = $isAdmin
        ? BASE_URL.'/public/admin/articles/create'
        : BASE_URL.'/public/portal/write';
}

$parentCats = []; $childMap = [];
foreach ($categories as $cat) {
    if (!$cat['parent_id']) $parentCats[] = $cat;
    else $childMap[$cat['parent_id']][] = $cat;
}
$currentCatId = (int)($article['category_id'] ?? 0);
$selectedParent = $selectedChild = 0;
foreach ($categories as $cat) {
    if ($cat['id'] === $currentCatId) {
        if ($cat['parent_id']) { $selectedParent = (int)$cat['parent_id']; $selectedChild = $currentCatId; }
        else { $selectedParent = $currentCatId; }
    }
}
?>

<div class="af-topbar">
  <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <div class="af-topbar-title">
    <?= $isEdit ? 'Edit Article' : 'New Article' ?>
    <?php if ($isEdit && !empty($article['updated_at'])): ?>
    <small class="af-topbar-meta">Saved <?= date('d M, H:i', strtotime($article['updated_at'])) ?></small>
    <?php endif; ?>
  </div>
</div>

<form method="POST" action="<?= $action ?>" id="articleForm" enctype="multipart/form-data">
<?= CSRF::field() ?>

<div class="af-grid">

  <!-- ════ LEFT — MAIN CONTENT ════ -->
  <div class="af-col-main">

    <!-- SECTION 1: Content -->
    <div class="af-card">
      <div class="af-card-head"><i class="bi bi-pencil-square me-2"></i>Content</div>
      <div class="af-card-body">

        <!-- Category + Subcategory -->
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="af-label">Category <span class="af-req">*</span></label>
            <select name="parent_category_id" id="parentCatSelect" class="af-select">
              <option value="">— Select —</option>
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
          <div class="col-6" id="subcatWrap" style="<?= empty($childMap[$selectedParent]) ? 'display:none' : '' ?>">
            <label class="af-label">Subcategory</label>
            <select id="subcatSelect" class="af-select">
              <option value="<?= $selectedParent ?>">— All —</option>
              <?php foreach ($childMap[$selectedParent] ?? [] as $sub): ?>
              <option value="<?= $sub['id'] ?>" <?= $selectedChild===(int)$sub['id'] ? 'selected' : '' ?>>
                <?= Helper::e($sub['name_tamil'] ?: $sub['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Content Type -->
        <div class="mb-3">
          <label class="af-label">Content Type</label>
          <select name="content_type" class="af-select">
            <?php foreach (['news'=>'News','video'=>'Video','short_news'=>'Short News','live_update'=>'Live Update','gallery'=>'Gallery','special'=>'Special','sponsored'=>'Sponsored'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($article['content_type']??'news')===$v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- District -->
        <div class="mb-3">
          <label class="af-label">District</label>
          <select name="district_id" class="af-select">
            <option value="">— All Districts —</option>
            <?php
            $selectedDistrict = $article['district_id'] ?? ($article['city_district_id'] ?? 0);
            foreach ($districts ?? [] as $dist): ?>
            <option value="<?= $dist['id'] ?>" <?= $selectedDistrict == $dist['id'] ? 'selected' : '' ?>>
              <?= Helper::e($dist['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- City / Area / Town — free text -->
        <div class="mb-3">
          <label class="af-label">City / Area / Town</label>
          <input type="text" name="city_name" class="af-input"
                 placeholder="e.g. Chennai, Madurai, Anna Nagar…"
                 value="<?= Helper::e($article['city_text'] ?? $article['city_name'] ?? '') ?>">
        </div>

        <!-- Title + Slug -->
        <div class="mb-3">
          <label class="af-label">Article Title <span class="af-req">*</span></label>
          <input type="text" name="title" id="titleInput" class="af-input af-input-title"
                 placeholder="Enter headline in Tamil or English…"
                 value="<?= Helper::e($article['title'] ?? '') ?>" required>
          <div class="af-slug-group mt-2">
            <span class="af-slug-prefix">/article/</span>
            <input type="text" name="slug" id="slugInput" class="af-slug-input"
                   value="<?= Helper::e($article['slug'] ?? '') ?>" placeholder="auto-generated">
            <button type="button" class="af-slug-regen" id="regenSlug" title="Regenerate">↺</button>
          </div>
        </div>

        <!-- Content Editor -->
        <div class="mb-3">
          <label class="af-label">Article Content <span class="af-req">*</span></label>
          <div class="af-editor-wrap">
            <div class="af-toolbar art-toolbar">
              <button type="button" data-fmt="bold" title="Bold"><b>B</b></button>
              <button type="button" data-fmt="italic" title="Italic"><i>I</i></button>
              <span class="af-toolbar-sep"></span>
              <button type="button" data-tag="h2" title="Heading 2">H2</button>
              <button type="button" data-tag="h3" title="Heading 3">H3</button>
              <button type="button" data-tag="p" title="Paragraph">¶</button>
              <span class="af-toolbar-sep"></span>
              <button type="button" id="insertLinkBtn" title="Insert link">🔗 Link</button>
            </div>
            <textarea id="content" name="content" class="af-textarea af-textarea-content"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
          </div>
        </div>

        <!-- Featured Image -->
        <div>
          <label class="af-label">Featured Image</label>
          <input type="hidden" name="media_id" id="mediaId" value="<?= $article['media_id'] ?? '' ?>">
          <div id="imagePreview" class="<?= empty($article['image_url']) ? 'd-none' : '' ?>">
            <div class="af-img-preview">
              <img src="<?= $article['image_url'] ? rtrim(ASSET_URL,'/'). '/public' . htmlspecialchars($article['image_url']) : '' ?>" id="previewImg" alt="">
              <button type="button" class="af-img-del" onclick="ArticleForm.clearImage()">✕</button>
            </div>
            <div class="af-img-replace mt-2">
              <button type="button" class="af-img-replace-btn" onclick="document.getElementById('directUpload').click()">↺ Replace</button>
              <button type="button" class="af-img-replace-btn" onclick="ArticleForm.openMediaLibrary()">☰ Library</button>
            </div>
          </div>
          <div id="uploadZone" class="af-dropzone <?= !empty($article['image_url']) ? 'd-none' : '' ?>">
            <div class="af-dropzone-icon">📷</div>
            <div class="af-dropzone-label">Add cover image</div>
            <div class="af-dropzone-hint">JPG · PNG · WebP · Max 5MB</div>
            <div class="af-dropzone-btns">
              <button type="button" class="af-dropzone-btn" onclick="document.getElementById('directUpload').click()">Browse</button>
              <button type="button" class="af-dropzone-btn" onclick="ArticleForm.openMediaLibrary()">Library</button>
            </div>
            <div id="uploadError" class="af-upload-error"></div>
          </div>
          <input type="file" id="directUpload" accept="image/*" class="d-none">
          <div id="uploadProgress" style="display:none" class="af-upload-progress">
            <div class="af-progress-track"><div id="uploadBar" class="af-progress-bar"></div></div>
          </div>
        </div>

      </div>
    </div>

  </div><!-- /main -->

  <!-- ════ RIGHT — SIDEBAR ════ -->
  <div class="af-col-side">

    <!-- SECTION 2: Details -->
    <div class="af-card">
      <div class="af-card-head"><i class="bi bi-list-check me-2"></i>Details</div>
      <div class="af-card-body">

        <!-- Excerpt -->
        <div class="mb-3">
          <label class="af-label">Excerpt <small class="af-hint">auto if blank</small></label>
          <textarea name="excerpt" id="excerptInput" class="af-textarea"
                    rows="3" placeholder="Short summary…"><?= Helper::e($article['excerpt'] ?? '') ?></textarea>
        </div>


        <!-- Tags -->
        <div class="mb-3">
          <label class="af-label">Tags</label>
          <div id="tagPicker" class="tn-tag-picker">
            <?php foreach ($tags as $tag): ?>
            <div class="tn-tag-item" data-id="<?= $tag['id'] ?>"><?= Helper::e($tag['name_tamil'] ?: $tag['name']) ?> <i class="bi bi-x"></i></div>
            <?php endforeach; ?>
          </div>
          <div id="selectedTagIds">
            <input type="hidden" name="tags_managed" value="1">
            <?php foreach ($tags as $tag): ?>
            <input type="hidden" name="tag_ids[]" value="<?= $tag['id'] ?>">
            <?php endforeach; ?>
          </div>
          <input type="text" id="tagSearch" class="af-input mt-2" placeholder="Search & add tags…">
          <div id="tagSuggestions" class="tn-tag-suggestions"></div>
        </div>

        <!-- YouTube -->
        <div class="mb-3">
          <label class="af-label"><span style="color:#6B7280">▶</span> YouTube <span class="text-muted" style="font-size:11px;font-weight:400">(optional)</span></label>
          <input type="url" name="youtube_url" class="af-input"
                 placeholder="https://youtube.com/watch?v=…"
                 value="<?= Helper::e($article['youtube_url'] ?? '') ?>">
          <input type="hidden" name="youtube_video_id" id="youtubeVideoId" value="<?= Helper::e($article['youtube_video_id'] ?? '') ?>">
        </div>

        <!-- SEO -->
        <div class="mb-3">
          <label class="af-label">Meta Title <small class="af-hint">blank = title</small></label>
          <input type="text" name="meta_title" id="metaTitleInput" class="af-input"
                 value="<?= Helper::e($article['meta_title'] ?? '') ?>"
                 maxlength="300"
                 <?= !empty($article['meta_title']) ? 'data-edited="1"' : '' ?>>
        </div>
        <div class="mb-3">
          <label class="af-label">Meta Description</label>
          <textarea name="meta_desc" id="metaDescInput" class="af-textarea" rows="2"
                    maxlength="500"><?= Helper::e($article['meta_desc'] ?? '') ?></textarea>
        </div>

        <!-- Flags -->
        <div>
          <label class="af-label">Flags</label>
          <div class="af-flags">
            <label class="af-flag">
              <input type="checkbox" name="is_breaking" value="1" <?= !empty($article['is_breaking']) ? 'checked' : '' ?>>
              <span>⚡ Breaking</span>
            </label>
            <label class="af-flag">
              <input type="checkbox" name="is_editors_pick" value="1" <?= !empty($article['is_editors_pick']) ? 'checked' : '' ?>>
              <span>⭐ Editor's Pick</span>
            </label>
            <label class="af-flag">
              <input type="checkbox" name="is_featured" value="1" <?= !empty($article['is_featured']) ? 'checked' : '' ?>>
              <span>📌 Featured</span>
            </label>
            <label class="af-flag">
              <input type="checkbox" name="is_premium" value="1" <?= !empty($article['is_premium']) ? 'checked' : '' ?>>
              <span>🔒 Premium</span>
            </label>
          </div>
        </div>

      </div>
    </div>

    <!-- SECTION 3: Status + Submit -->
    <div class="af-card">
      <div class="af-card-head"><i class="bi bi-send me-2"></i>Status &amp; Publish</div>
      <div class="af-card-body">
        <?php
        $canPublish     = \App\Core\Auth::can('publish_articles');
        // Default status: if user can publish and this is a new article → pre-select Published
        $currentStatus  = $article['status'] ?? ($canPublish && !$isEdit ? 'published' : 'draft');
        ?>
        <label class="af-label">Status</label>
        <select name="status" class="af-select af-status-select mb-2" id="statusSelect">
          <option value="draft"     <?= $currentStatus === 'draft'     ? 'selected' : '' ?>>📝 Draft</option>
          <option value="review"    <?= $currentStatus === 'review'    ? 'selected' : '' ?>>🔍 Submit for Review</option>
          <?php if ($canPublish): ?>
          <option value="published" <?= $currentStatus === 'published' ? 'selected' : '' ?>>✅ Publish Now</option>
          <option value="scheduled" <?= $currentStatus === 'scheduled' ? 'selected' : '' ?>>🗓 Scheduled</option>
          <?php endif; ?>
        </select>
        <div id="scheduledAtWrap" style="display:none" class="mb-2">
          <label class="af-label">Publish At</label>
          <input type="datetime-local" name="scheduled_at" class="af-input"
                 value="<?= !empty($article['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($article['scheduled_at'])) : '' ?>">
        </div>
        <!-- Push notification option -->
        <?php if (in_array(\App\Core\Auth::role(), ['admin','chief_editor'])): ?>
        <div class="mb-3 p-3 rounded" style="background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.25)">
          <label class="d-flex align-items-center gap-2" style="cursor:pointer">
            <input type="checkbox" name="send_push" value="1" class="form-check-input" id="sendPushChk">
            <span class="small fw-600">🔔 Send push notification when published</span>
          </label>
          <div class="form-text mt-1 ms-4" id="pushTargetWrap" style="display:none">
            Push to: <strong>all subscribers</strong>
            <?php if (!empty($districts)): ?>
            or select district →
            <select name="push_district_id" class="form-select form-select-sm mt-1">
              <option value="">All districts</option>
              <?php foreach ($districts as $d): ?>
              <option value="<?= $d['id'] ?>"><?= \App\Core\Helper::e($d['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php endif; ?>
          </div>
        </div>
        <script>
        document.getElementById('sendPushChk')?.addEventListener('change',function(){
          document.getElementById('pushTargetWrap').style.display = this.checked ? '' : 'none';
        });
        </script>
        <?php endif; ?>

        <button type="submit" class="af-submit">
          <i class="bi bi-<?= $isEdit ? 'save' : 'send' ?>"></i>
          <?= $isEdit ? 'Update Article' : 'Create Article' ?>
        </button>
      </div>
    </div>

  </div><!-- /sidebar -->

</div><!-- /af-grid -->
</form>

<!-- Media Library modal -->
<div id="artMediaModal" class="ad-media-modal-overlay">
  <div class="ad-media-modal">
    <div class="ad-media-modal-header">
      <span>Choose from Media Library</span>
      <button type="button" onclick="ArticleForm.closeMediaLibrary()">✕</button>
    </div>
    <div id="artMediaModalBody" class="ad-media-modal-body">Loading…</div>
  </div>
</div>

<script>
window.ArticleFormConfig = {
  uploadUrl:        '<?= BASE_URL ?>/public/admin/media/upload-ajax',
  mediaModalUrl:    '<?= BASE_URL ?>/public/admin/media/modal',
  tagSearchUrl:     '<?= BASE_URL ?>/public/admin/tags/suggest',
  tagQuickCreateUrl:'<?= BASE_URL ?>/public/admin/tags/quick-create',
  categories:       <?= json_encode($childMap) ?>,
  selectedParent:   <?= (int)$selectedParent ?>,
  isPublished:      <?= ($isEdit && ($article['status'] ?? '') === 'published') ? 'true' : 'false' ?>
};
</script>
<script src="<?= ASSET_URL ?>/public/assets/js/article-form.js"></script>
