<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Media Library</h2>
    <p class="tn-page-sub"><?= number_format($total) ?> files</p>
  </div>
</div>

<!-- UPLOAD ZONE — inline on page, no modal -->
<div class="tn-card mb-4">
  <div class="tn-card-body">
    <div id="dropZone" class="tn-drop-zone">
      <i class="bi bi-cloud-upload fs-1 text-muted"></i>
      <p class="mt-2 mb-1">Drag & drop or <label for="fileInput" class="tn-browse-label">Browse Files</label></p>
      <small class="text-muted">JPG, PNG, WebP, GIF — max 5MB each</small>
      <input type="file" id="fileInput" accept="image/*" multiple class="tn-drop-input">
    </div>
    <div id="uploadProgress" class="mt-3"></div>
  </div>
</div>

<!-- SEARCH -->
<div class="tn-card mb-4">
  <div class="tn-card-body">
    <form method="GET" class="d-flex gap-2">
      <input type="text" name="search" class="form-control" placeholder="Search files…" value="<?= Helper::e($search) ?>">
      <button class="btn btn-primary"><i class="bi bi-search"></i></button>
      <?php if ($search): ?><a href="<?= $r ?>/admin/media" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a><?php endif; ?>
    </form>
  </div>
</div>

<!-- GRID -->
<div class="tn-media-grid" id="mediaGrid">
  <?php if (empty($media)): ?>
  <div class="col-12 text-center py-5 text-muted">
    <i class="bi bi-images fs-1 d-block mb-3"></i>No media files yet
  </div>
  <?php endif; ?>
  <?php foreach ($media as $m): ?>
  <div class="tn-media-item" data-id="<?= $m['id'] ?>">
    <div class="tn-media-thumb">
      <?php if (str_starts_with($m['mime_type'], 'image/')): ?>
      <img src="<?= rtrim(ASSET_URL,'/') . '/public' . Helper::e($m['thumb_path'] ?: $m['filepath']) ?>" alt="<?= Helper::e($m['alt_text'] ?? $m['filename']) ?>" loading="lazy">
      <?php else: ?>
      <div class="tn-media-icon"><i class="bi bi-file-earmark"></i></div>
      <?php endif; ?>
    </div>
    <div class="tn-media-info">
      <div class="tn-media-name" title="<?= Helper::e($m['filename']) ?>"><?= Helper::e(mb_substr($m['filename'], 0, 24)) ?></div>
      <div class="tn-media-meta"><?= Helper::formatBytes($m['size']) ?><?= $m['width'] ? ' · ' . $m['width'] . '×' . $m['height'] : '' ?></div>
    </div>
    <div class="tn-media-actions">
      <a href="<?= rtrim(ASSET_URL,'/') . '/public' . Helper::e($m['filepath']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary" title="View">
        <i class="bi bi-box-arrow-up-right"></i>
      </a>
      <button class="btn btn-xs btn-outline-danger" onclick="deleteMedia(<?= $m['id'] ?>)" title="Delete">
        <i class="bi bi-trash"></i>
      </button>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- PAGINATION -->
<?php
$queryExtra = '&search='.urlencode($search);
include VIEW_PATH . '/partials/pagination.php';
?>

<!-- DELETE FORM -->
<form id="deleteMediaForm" method="POST" class="d-none">
  <?= CSRF::field() ?>
</form>

<script>
(function () {
  // r is defined AFTER $content in the layout — read from meta tag instead
  var baseUrl   = document.querySelector('meta[name="base-url"]')?.content || '';
  var dropZone  = document.getElementById('dropZone');
  var fileInput = document.getElementById('fileInput');
  var progress  = document.getElementById('uploadProgress');
  var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // Click anywhere on dropzone opens file picker (except the label — it already does)
  dropZone.addEventListener('click', function (e) {
    if (e.target.tagName !== 'LABEL' && e.target !== fileInput) fileInput.click();
  });

  dropZone.addEventListener('dragover', function (e) {
    e.preventDefault(); dropZone.classList.add('dragging');
  });
  dropZone.addEventListener('dragleave', function () {
    dropZone.classList.remove('dragging');
  });
  dropZone.addEventListener('drop', function (e) {
    e.preventDefault(); dropZone.classList.remove('dragging');
    uploadFiles(e.dataTransfer.files);
  });
  fileInput.addEventListener('change', function () {
    if (this.files.length) uploadFiles(this.files);
  });

  function uploadFiles(files) {
    Array.from(files).forEach(function (file) {
      var row = document.createElement('div');
      row.className = 'upload-row mb-2';
      row.innerHTML =
        '<div class="d-flex align-items-center gap-2 mb-1">' +
        '<span class="upload-filename text-truncate small">' + file.name + '</span>' +
        '<span class="upload-status small ms-auto">Uploading…</span>' +
        '</div>' +
        '<div class="progress" style="height:4px">' +
        '<div class="progress-bar bg-primary" style="width:0%"></div>' +
        '</div>';
      progress.appendChild(row);

      var bar    = row.querySelector('.progress-bar');
      var status = row.querySelector('.upload-status');

      // Animate progress bar to 90% while uploading
      var pct = 0;
      var tick = setInterval(function () {
        if (pct < 88) { pct += 4; bar.style.width = pct + '%'; }
      }, 80);

      var fd = new FormData();
      fd.append('file', file);
      fd.append('_token', csrfToken);

      fetch(baseUrl + '/admin/media/upload', { method: 'POST', body: fd })
        .then(function (res) {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          return res.json();
        })
        .then(function (data) {
          clearInterval(tick);
          if (data.success) {
            bar.style.width = '100%';
            bar.className = 'progress-bar bg-success';
            status.textContent = '✓ Done';
            status.className = 'upload-status small ms-auto text-success';
            prependToGrid(data.media);
          } else {
            bar.style.width = '100%';
            bar.className = 'progress-bar bg-danger';
            status.textContent = '✗ ' + (data.error || 'Failed');
            status.className = 'upload-status small ms-auto text-danger';
          }
        })
        .catch(function (err) {
          clearInterval(tick);
          bar.style.width = '100%';
          bar.className = 'progress-bar bg-danger';
          status.textContent = '✗ Error: ' + err.message;
          status.className = 'upload-status small ms-auto text-danger';
        });
    });
    // Reset input so same file can be re-selected
    fileInput.value = '';
  }

  function prependToGrid(m) {
    var grid = document.getElementById('mediaGrid');
    // Remove empty-state if present
    var empty = grid.querySelector('.text-center.py-5');
    if (empty) empty.closest('.col-12') ? empty.closest('.col-12').remove() : empty.remove();

    var div = document.createElement('div');
    div.className = 'tn-media-item';
    div.dataset.id = m.id;
    var imgSrc = baseUrl + '/public' + (m.thumb_path || m.filepath);
    var viewHref = baseUrl + '/public' + m.filepath;
    div.innerHTML =
      '<div class="tn-media-thumb">' +
        '<img src="' + imgSrc + '" loading="lazy">' +
      '</div>' +
      '<div class="tn-media-info">' +
        '<div class="tn-media-name">' + m.filename.substring(0, 24) + '</div>' +
        '<div class="tn-media-meta">' + (m.size ? Math.round(m.size/1024) + ' KB' : '') + '</div>' +
      '</div>' +
      '<div class="tn-media-actions">' +
        '<a href="' + viewHref + '" target="_blank" class="btn btn-xs btn-outline-secondary" title="View"><i class="bi bi-box-arrow-up-right"></i></a>' +
        '<button class="btn btn-xs btn-outline-danger" onclick="deleteMedia(' + m.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' +
      '</div>';
    grid.prepend(div);
  }

  window.deleteMedia = function (id) {
    if (!confirm('Delete this file permanently?')) return;
    var form = document.getElementById('deleteMediaForm');
    form.action = baseUrl + '/admin/media/delete/' + id;
    form.submit();
  };
})();
</script>
