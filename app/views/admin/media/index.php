<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Media Library</h2>
    <p class="tn-page-sub"><?= number_format($total) ?> files</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
    <i class="bi bi-cloud-upload me-2"></i>Upload
  </button>
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
      <img src="<?= Helper::e($m['thumb_path'] ?: $m['filepath']) ?>" alt="<?= Helper::e($m['alt_text'] ?? $m['filename']) ?>" loading="lazy">
      <?php else: ?>
      <div class="tn-media-icon"><i class="bi bi-file-earmark"></i></div>
      <?php endif; ?>
    </div>
    <div class="tn-media-info">
      <div class="tn-media-name" title="<?= Helper::e($m['filename']) ?>"><?= Helper::e(mb_substr($m['filename'], 0, 24)) ?></div>
      <div class="tn-media-meta"><?= Helper::formatBytes($m['size']) ?><?= $m['width'] ? ' · ' . $m['width'] . '×' . $m['height'] : '' ?></div>
    </div>
    <div class="tn-media-actions">
      <a href="<?= Helper::e($m['filepath']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary" title="View">
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

<!-- UPLOAD MODAL -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload Files</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="dropZone" class="tn-drop-zone">
          <i class="bi bi-cloud-upload fs-1 text-muted"></i>
          <p class="mt-2 mb-1">Drag & drop or click to select</p>
          <small class="text-muted">JPG, PNG, WebP, GIF — max 5MB</small>
          <input type="file" id="fileInput" accept="image/*" multiple class="tn-drop-input">
        </div>
        <div id="uploadProgress" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<form id="deleteMediaForm" method="POST" style="display:none">
  <?= CSRF::field() ?>
</form>

<script>
// Drop zone
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const csrf      = document.querySelector('meta[name="csrf-token"]').content;

dropZone?.addEventListener('click', () => fileInput.click());
dropZone?.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragging'); });
dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('dragging'));
dropZone?.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('dragging'); uploadFiles(e.dataTransfer.files); });
fileInput?.addEventListener('change', () => uploadFiles(fileInput.files));

async function uploadFiles(files) {
  const progress = document.getElementById('uploadProgress');
  for (const file of files) {
    const bar = document.createElement('div');
    bar.className = 'mb-2';
    bar.innerHTML = `<small>${file.name}</small><div class="progress mt-1" style="height:6px"><div class="progress-bar progress-bar-striped progress-bar-animated w-100"></div></div>`;
    progress.appendChild(bar);

    const fd = new FormData();
    fd.append('file', file);
    fd.append('_token', csrf);
    try {
      const res  = await fetch(r + '/admin/media/upload', { method: 'POST', body: fd });
      const data = await res.json();
      bar.querySelector('.progress-bar').className = data.success ? 'progress-bar bg-success w-100' : 'progress-bar bg-danger w-100';
      if (data.success) {
        // Prepend to grid
        const grid = document.getElementById('mediaGrid');
        const div  = document.createElement('div');
        div.className = 'tn-media-item';
        div.dataset.id = data.media.id;
        div.innerHTML = `<div class="tn-media-thumb"><img src="${data.media.thumb_path || data.media.filepath}" loading="lazy"></div><div class="tn-media-info"><div class="tn-media-name">${data.media.filename.substring(0,24)}</div></div>`;
        grid.prepend(div);
      }
    } catch(e) {
      bar.querySelector('.progress-bar').className = 'progress-bar bg-danger w-100';
    }
  }
}

function deleteMedia(id) {
  if (!confirm('Delete this file permanently?')) return;
  const form = document.getElementById('deleteMediaForm');
  form.action = r + '/admin/media/delete/' + id;
  form.submit();
}
</script>
