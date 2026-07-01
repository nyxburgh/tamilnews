<?php use App\Core\{Helper, CSRF}; ?>
<link rel="stylesheet" href="<?= ASSET_URL ?>/assets/css/article-form.css">
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
            <input type="text" name="title" id="titleInput" class="form-control form-control-lg"
                   placeholder="Enter your article headline…"
                   value="<?= Helper::e($article['title'] ?? '') ?>" required>
          </div>
          <div>
            <label class="form-label fw-600">URL Slug</label>
            <div class="input-group">
              <span class="input-group-text text-muted">/article/</span>
              <input type="text" name="slug" id="slugInput" class="form-control"
                     value="<?= Helper::e($article['slug'] ?? '') ?>" placeholder="auto-generated">
            </div>
          </div>
        </div>
      </div>

      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-pencil-square me-2"></i>Article Content <span class="text-danger">*</span></span></div>
        <div class="portal-card-body p-0">
          <div class="art-toolbar">
            <button type="button" data-fmt="bold" title="Bold"><b>B</b></button>
            <button type="button" data-fmt="italic" title="Italic"><i>I</i></button>
            <button type="button" data-fmt="underline" title="Underline"><u>U</u></button>
            <span class="sep"></span>
            <button type="button" data-tag="h2" title="Heading">H2</button>
            <button type="button" data-tag="h3" title="Sub-heading">H3</button>
            <button type="button" data-tag="p" title="Paragraph">P</button>
            <span class="sep"></span>
            <button type="button" data-tag="blockquote" title="Quote">&#10078;</button>
            <button type="button" id="insertUlBtn" title="Bullet List">&bull; List</button>
            <span class="sep"></span>
            <button type="button" id="insertLinkBtn" title="Link">&#128279;</button>
          </div>
          <textarea id="content" name="content" class="form-control art-content-area"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
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
          <div class="mb-3">
            <label class="form-label fw-600">Article Type</label>
            <select name="content_type" class="form-select">
              <?php $curType = $article['content_type'] ?? 'special'; ?>
              <option value="special" <?= $curType === 'special' ? 'selected' : '' ?>>Special Article</option>
              <option value="news"    <?= $curType === 'news'    ? 'selected' : '' ?>>News</option>
            </select>
            <div class="form-text">Special Articles appear in a dedicated section as well as their category page.</div>
          </div>
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
          <select id="cParentSel" class="form-select mb-2">
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
            <select id="cSubSel" class="form-select">
              <option value="<?= $curParent ?>">-- அனைத்தும் (subcat இல்லை) --</option>
              <?php foreach ($cChildMap[$curParent] ?? [] as $sub): ?>
              <option value="<?= $sub['id'] ?>" <?= $curChild === (int)$sub['id'] ? 'selected' : '' ?>>
                <?= Helper::e($sub['name_tamil'] ?: $sub['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <input type="hidden" name="category_id" id="cCatFallback" value="<?= $curCatId ?: $curParent ?>">
          <?php endif; ?>
        </div>
      </div>

      <!-- TAGS -->
      <div class="portal-card mb-4">
        <div class="portal-card-header"><span><i class="bi bi-tags me-2"></i>Tags</span></div>
        <div class="portal-card-body">
          <div id="tagPicker" class="tn-tag-picker mb-2">
            <?php foreach ($tags as $tag): ?>
            <div class="tn-tag-item" data-id="<?= $tag['id'] ?>"><?= Helper::e($tag['name_tamil'] ?: $tag['name']) ?> <i class="bi bi-x"></i></div>
            <?php endforeach; ?>
          </div>
          <div id="selectedTagIds">
            <?php foreach ($tags as $tag): ?>
            <input type="hidden" name="tag_ids[]" value="<?= $tag['id'] ?>">
            <?php endforeach; ?>
          </div>
          <input type="text" id="tagSearch" class="form-control form-control-sm" placeholder="Search and add tags…">
          <div id="tagSuggestions" class="tn-tag-suggestions"></div>
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

<script>
window.ContributeFormConfig = {
  tagSearchUrl: '<?= $r ?>/admin/tags/suggest',
  tagQuickCreateUrl: '<?= $r ?>/admin/tags/quick-create'
};
</script>
<script src="<?= ASSET_URL ?>/assets/js/contribute-article-form.js"></script>
