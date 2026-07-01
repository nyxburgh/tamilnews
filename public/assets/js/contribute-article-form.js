/**
 * contribute-article-form.js
 * Contributor "Submit Article" / "Edit Article" page behavior.
 */
(function () {
  'use strict';

  var cfg = window.ContributeFormConfig || {};

  /* ── Slug auto-generate (Tamil + English aware) ── */
  var titleInput = document.getElementById('titleInput');
  var slugInput  = document.getElementById('slugInput');

  function slugify(text) {
    return text
      .toLowerCase()
      .trim()
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

  /* ── Content toolbar ── */
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
    contentArea.setRangeText('\n<ul>\n  <li>Item 1</li>\n  <li>Item 2</li>\n</ul>\n', s, s, 'end');
  });
  document.getElementById('insertLinkBtn')?.addEventListener('click', function () {
    var url = prompt('Enter URL:');
    if (!url) return;
    var s = contentArea.selectionStart, e = contentArea.selectionEnd;
    var txt = contentArea.value.substring(s, e) || 'Link text';
    contentArea.setRangeText('<a href="' + url + '">' + txt + '</a>', s, e, 'end');
  });

  /* ── Category cascade: parent select → subcat select → hidden fallback ──
     Same behavior as before: whichever select actually has subcats becomes
     the one submitted as "category_id"; the other is renamed so it's
     ignored by the form post. */
  var pSel = document.getElementById('cParentSel');
  var subWrap = document.getElementById('cSubcatWrap');
  var subSel  = document.getElementById('cSubSel');
  var fallback = document.getElementById('cCatFallback');

  function loadSubs(parentId) {
    if (!pSel) return;
    var opt = pSel.options[pSel.selectedIndex];
    var children = opt ? JSON.parse(opt.dataset.children || '[]') : [];
    fallback.value = parentId;

    if (children.length > 0) {
      subWrap.style.display = '';
      subSel.innerHTML = '<option value="' + parentId + '">-- அனைத்தும் (subcat இல்லை) --</option>';
      children.forEach(function (s) {
        subSel.innerHTML += '<option value="' + s.id + '">' + (s.name_tamil || s.name) + '</option>';
      });
      subSel.name = 'category_id';
      fallback.name = 'cat_fb_ignore';
    } else {
      subWrap.style.display = 'none';
      subSel.name = 'cat_sub_ignore';
      fallback.name = 'category_id';
    }
  }

  pSel?.addEventListener('change', function () { loadSubs(this.value); });

  document.addEventListener('DOMContentLoaded', function () {
    if (pSel && pSel.value) {
      loadSubs(pSel.value);
    } else if (subSel && fallback) {
      subSel.name = 'cat_sub_ignore';
      fallback.name = 'category_id';
    }
  });

  /* ── Tag search + quick-create ── */
  var tagSearch = document.getElementById('tagSearch');
  var tagSugBox = document.getElementById('tagSuggestions');
  var tagDebounce;

  tagSearch?.addEventListener('input', function () {
    clearTimeout(tagDebounce);
    var q = this.value.trim();
    if (!q) { tagSugBox.innerHTML = ''; return; }
    tagDebounce = setTimeout(function () {
      fetch(cfg.tagSearchUrl + '?q=' + encodeURIComponent(q))
        .then(function (r) { return r.json(); })
        .then(function (tags) {
          tagSugBox.innerHTML = '';
          tags.forEach(function (t) {
            var label = t.name_tamil || t.name;
            var div = document.createElement('div');
            div.className = 'tn-tag-suggestion';
            div.textContent = label;
            div.addEventListener('click', function () { addTag(t.id, label); });
            tagSugBox.appendChild(div);
          });
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
              fd.append('_token', document.querySelector('[name=_token]')?.value || '');
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
    }, 300);
  });

  function addTag(id, name) {
    if (document.querySelector('#tagPicker [data-id="' + id + '"]')) return;
    var picker = document.getElementById('tagPicker');
    var hidden = document.getElementById('selectedTagIds');
    var div = document.createElement('div');
    div.className = 'tn-tag-item';
    div.dataset.id = id;
    div.innerHTML = name + ' <i class="bi bi-x"></i>';
    div.querySelector('i').addEventListener('click', function () {
      div.remove();
      hidden.querySelector('[value="' + id + '"]')?.remove();
    });
    picker.appendChild(div);
    var inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'tag_ids[]'; inp.value = id;
    hidden.appendChild(inp);
    tagSearch.value = '';
    tagSugBox.innerHTML = '';
  }

  document.getElementById('tagPicker')?.addEventListener('click', function (e) {
    var item = e.target.closest('.tn-tag-item');
    if (e.target.tagName === 'I' && item) {
      document.querySelector('#selectedTagIds [value="' + item.dataset.id + '"]')?.remove();
      item.remove();
    }
  });
})();
