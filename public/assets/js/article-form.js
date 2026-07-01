/**
 * article-form.js
 * Article write/edit page — all interactive behavior.
 * Reads config from window.ArticleFormConfig (set inline in form.php).
 */
(function () {
  'use strict';

  var cfg = window.ArticleFormConfig || {};

  /* ── Slug auto-generate ── */
  var titleInput = document.getElementById('titleInput');
  var slugInput  = document.getElementById('slugInput');

  function slugify(text) {
    return text
      .toLowerCase()
      .trim()
      // Keep: Tamil script (U+0B80–U+0BFF), English letters/digits, spaces, hyphens.
      // Everything else (punctuation, symbols) is stripped.
      .replace(/[^\u0B80-\u0BFFa-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-+|-+$/g, '')
      .substring(0, 120);
  }
  titleInput?.addEventListener('input', function () {
    if (!slugInput.dataset.manual) slugInput.value = slugify(titleInput.value);
  });
  slugInput?.addEventListener('input', function () { slugInput.dataset.manual = '1'; });
  document.getElementById('regenSlug')?.addEventListener('click', function () {
    var cfg = window.ArticleFormConfig || {};
    if (cfg.isPublished) {
      if (!confirm('⚠️ This article is published.\n\nChanging the slug will break the live URL and any links/shares pointing to it.\n\nContinue anyway?')) return;
    }
    delete slugInput.dataset.manual;
    titleInput.dispatchEvent(new Event('input'));
  });

  /* ── Auto-fill meta title + desc from title ── */
  var metaTitleInput = document.getElementById('metaTitleInput');
  var metaDescInput  = document.getElementById('metaDescInput');
  var excerptInput   = document.getElementById('excerptInput');

  titleInput?.addEventListener('input', function () {
    if (metaTitleInput && !metaTitleInput.dataset.edited) {
      metaTitleInput.value = titleInput.value;
    }
  });
  metaTitleInput?.addEventListener('input', function () {
    metaTitleInput.dataset.edited = '1';
  });
  excerptInput?.addEventListener('input', function () {
    if (metaDescInput && !metaDescInput.dataset.edited) {
      metaDescInput.value = excerptInput.value.substring(0, 160);
    }
  });
  metaDescInput?.addEventListener('input', function () {
    metaDescInput.dataset.edited = '1';
  });

  /* ── Subcategory loader ── */
  var parentCatSelect = document.getElementById('parentCatSelect');
  function loadSubcats(parentId) {
    var wrap = document.getElementById('subcatWrap');
    var sel  = document.getElementById('subcatSelect');
    var fin  = document.getElementById('finalCategoryId');
    var opt  = document.querySelector('#parentCatSelect option[value="' + parentId + '"]');
    var children = opt ? JSON.parse(opt.dataset.children || '[]') : [];
    fin.value = parentId;
    if (!children.length) { wrap.style.visibility = 'hidden'; return; }
    wrap.style.visibility = 'visible';
    sel.innerHTML = '<option value="' + parentId + '">-- All --</option>';
    children.forEach(function (c) {
      sel.innerHTML += '<option value="' + c.id + '">' + (c.name_tamil || c.name) + '</option>';
    });
    sel.onchange = function () { fin.value = sel.value; };
  }
  parentCatSelect?.addEventListener('change', function () { loadSubcats(this.value); });
  if (parentCatSelect?.value) loadSubcats(parentCatSelect.value);

  /* ── Scheduled date toggle ── */
  var statusSelect = document.getElementById('statusSelect');
  var schedWrap     = document.getElementById('scheduledAtWrap');
  statusSelect?.addEventListener('change', function () {
    schedWrap.style.display = this.value === 'scheduled' ? 'block' : 'none';
  });
  if (statusSelect?.value === 'scheduled') schedWrap.style.display = 'block';

  /* ── Content toolbar (bold/italic/underline/headings/list/link) ── */
  var contentArea = document.getElementById('content');
  document.querySelectorAll('.art-toolbar [data-fmt]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var cmd = btn.dataset.fmt;
      var s = contentArea.selectionStart, e = contentArea.selectionEnd;
      var sel = contentArea.value.substring(s, e);
      var open  = { bold: '<strong>', italic: '<em>', underline: '<u>' }[cmd];
      var close = { bold: '</strong>', italic: '</em>', underline: '</u>' }[cmd];
      contentArea.setRangeText(open + sel + close, s, e, 'end');
    });
  });
  document.querySelectorAll('.art-toolbar [data-tag]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var tag = btn.dataset.tag;
      var s = contentArea.selectionStart, e = contentArea.selectionEnd;
      var sel = contentArea.value.substring(s, e) || 'Text here';
      contentArea.setRangeText('<' + tag + '>' + sel + '</' + tag + '>', s, e, 'end');
    });
  });
  document.getElementById('insertUlBtn')?.addEventListener('click', function () {
    var s = contentArea.selectionStart;
    contentArea.setRangeText('\n<ul>\n  <li></li>\n  <li></li>\n</ul>\n', s, s, 'end');
    contentArea.focus();
  });
  document.getElementById('insertLinkBtn')?.addEventListener('click', function () {
    var url = prompt('Link URL:');
    if (!url) return;
    var s = contentArea.selectionStart, e = contentArea.selectionEnd;
    var txt = contentArea.value.substring(s, e) || 'link text';
    contentArea.setRangeText('<a href="' + url + '" target="_blank">' + txt + '</a>', s, e, 'end');
    contentArea.focus();
  });

  /* ── Featured image: upload, drag-drop, preview, media library ── */
  function csrfToken() {
    return document.querySelector('[name=_token]')?.value
        || document.querySelector('[name=csrf_token]')?.value || '';
  }

  function resetUploadZone() {
    document.getElementById('uploadProgress').style.display = 'none';
    document.getElementById('uploadBar').style.width = '0%';
    var err = document.getElementById('uploadError');
    if (err) { err.textContent = ''; err.style.display = 'none'; }
  }

  function showUploadedImage(url, mediaId) {
    document.getElementById('mediaId').value = mediaId || '';
    document.getElementById('previewImg').src = url;
    document.getElementById('imagePreview').classList.remove('d-none');
    document.getElementById('uploadZone').classList.add('d-none');
  }

  function uploadImage(file) {
    if (!file) return;
    var fd = new FormData();
    fd.append('image', file);
    fd.append('_token', csrfToken());

    document.getElementById('uploadProgress').style.display = 'block';
    var bar = document.getElementById('uploadBar');
    var p = 0;
    var timer = setInterval(function () { if (p < 80) { p += 10; bar.style.width = p + '%'; } }, 150);

    fetch(cfg.uploadUrl, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        clearInterval(timer);
        resetUploadZone();
        if (d.success) {
          bar.style.width = '100%';
          showUploadedImage(d.url, d.media_id);
        } else {
          var err = document.getElementById('uploadError');
          if (err) { err.textContent = d.error || 'Upload failed'; err.style.display = 'block'; }
        }
      })
      .catch(function () { clearInterval(timer); resetUploadZone(); });
  }

  var dropzone = document.getElementById('uploadZone');
  var fileInput = document.getElementById('directUpload');
  dropzone?.addEventListener('click', function (e) {
    if (!e.target.closest('button')) fileInput.click();
  });
  fileInput?.addEventListener('change', function () { uploadImage(this.files[0]); });
  dropzone?.addEventListener('dragover', function (e) { e.preventDefault(); dropzone.classList.add('dragover'); });
  dropzone?.addEventListener('dragleave', function () { dropzone.classList.remove('dragover'); });
  dropzone?.addEventListener('drop', function (e) {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    var file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) uploadImage(file);
  });

  function clearImage() {
    document.getElementById('mediaId').value = '';
    document.getElementById('imagePreview').classList.add('d-none');
    document.getElementById('uploadZone').classList.remove('d-none');
    resetUploadZone();
  }

  /* ── Media Library picker (single-select) ── */
  function openMediaLibrary() {
    var modal = document.getElementById('artMediaModal');
    if (!modal) return;
    modal.classList.add('open');
    loadMediaPage(1);
  }
  function closeMediaLibrary() {
    document.getElementById('artMediaModal')?.classList.remove('open');
  }
  document.getElementById('artMediaModal')?.addEventListener('click', function (e) {
    if (e.target.id === 'artMediaModal') closeMediaLibrary();
  });

  // Override global selectMedia so modal clicks wire to this form
  window.selectMedia = function (mediaId, filepath) {
    var base = (document.querySelector('meta[name="base-url"]') || {}).content || '';
    // filepath is relative like /uploads/2026/06/img.jpg
    // base is ASSET_URL (no /public) — must add /public/ explicitly
    var url  = filepath.startsWith('http') ? filepath : base + '/public' + filepath;
    showUploadedImage(url, mediaId);
    closeMediaLibrary();
  };

  function loadMediaPage(page) {
    var body = document.getElementById('artMediaModalBody');
    if (!body) return;
    body.innerHTML = '<div class="text-center py-4 text-muted">Loading…</div>';
    fetch(cfg.mediaModalUrl + '?page=' + page)
      .then(function (r) { return r.text(); })
      .then(function (html) { body.innerHTML = html; })
      .catch(function () { body.innerHTML = '<div class="text-center py-4 text-muted">Failed to load</div>'; });
  }

  /* ── Tag search ── */
  var tagSearch = document.getElementById('tagSearch');
  var tagSugBox = document.getElementById('tagSuggestions');
  tagSearch?.addEventListener('input', function () {
    var q = this.value.trim();
    if (!q) { tagSugBox.innerHTML = ''; return; }
    fetch(cfg.tagSearchUrl + '?q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(function (tags) {
        tagSugBox.innerHTML = '';
        tags.forEach(function (t) {
          var label = t.name_tamil || t.name;
          var div = document.createElement('div');
          div.className = 'tn-tag-suggest-item';
          div.textContent = label;
          div.addEventListener('click', function () { addTag(t.id, label); });
          tagSugBox.appendChild(div);
        });
        // No match — offer inline quick-create (English + Tamil)
        if (!tags.length) {
          var box = document.createElement('div');
          box.className = 'tn-tag-quickadd';
          box.innerHTML =
            '<input type="text" placeholder="English name" class="form-control form-control-sm mb-1" id="qcEn" value="' + q + '">' +
            '<input type="text" placeholder="தமிழ் பெயர் (Tamil)" class="form-control form-control-sm mb-1" id="qcTa">' +
            '<button type="button" class="btn btn-sm btn-primary w-100">+ Create Tag</button>';
          box.querySelector('button').addEventListener('click', function () {
            var en = document.getElementById('qcEn').value.trim();
            var ta = document.getElementById('qcTa').value.trim();
            if (!en && !ta) return;
            var fd = new FormData();
            fd.append('_token', csrfToken());
            fd.append('name', en);
            fd.append('name_tamil', ta);
            fetch(cfg.tagQuickCreateUrl, { method: 'POST', body: fd })
              .then(function (r) { return r.json(); })
              .then(function (d) {
                if (d.success) { addTag(d.id, d.name_tamil || d.name); tagSugBox.innerHTML = ''; }
              });
          });
          tagSugBox.appendChild(box);
        }
      });
  });

  function addTag(id, name) {
    if (document.querySelector('[data-id="' + id + '"]')) return;
    var picker = document.getElementById('tagPicker');
    var selIds = document.getElementById('selectedTagIds');
    var div = document.createElement('div');
    div.className = 'tn-tag-item';
    div.dataset.id = id;
    div.innerHTML = name + ' <i class="bi bi-x"></i>';
    div.querySelector('i').addEventListener('click', function () {
      div.remove();
      selIds.querySelector('[value="' + id + '"]')?.remove();
    });
    picker.appendChild(div);
    var inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'tag_ids[]'; inp.value = id;
    selIds.appendChild(inp);
    tagSearch.value = ''; tagSugBox.innerHTML = '';
  }

  document.getElementById('tagPicker')?.addEventListener('click', function (e) {
    var item = e.target.closest('.tn-tag-item');
    if (e.target.tagName === 'I' && item) {
      var id = item.dataset.id;
      document.querySelector('#selectedTagIds [value="' + id + '"]')?.remove();
      item.remove();
    }
  });

  /* ── Expose namespaced API for inline onclick references ── */
  window.ArticleForm = {
    clearImage: clearImage,
    openMediaLibrary: openMediaLibrary,
    closeMediaLibrary: closeMediaLibrary
  };
})();
