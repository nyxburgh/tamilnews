<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📰 Newspaper Archive</h2>
    <p class="tn-page-sub">Upload daily/weekly PDF editions for public reading</p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
    <i class="bi bi-upload me-2"></i>Upload Edition
  </button>
</div>

<?php if (empty($papers)): ?>
<div class="tn-card">
  <div class="tn-card-body text-center py-5">
    <div style="font-size:48px;margin-bottom:12px">📰</div>
    <p class="text-muted">No editions uploaded yet. Upload your first newspaper PDF.</p>
  </div>
</div>
<?php else: ?>

<div class="table-responsive tn-card">
  <table class="table tn-table mb-0">
    <thead>
      <tr>
        <th>Edition Date</th>
        <th>Title</th>
        <th>Type</th>
        <th>Size</th>
        <th>Downloads</th>
        <th>Uploaded By</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($papers as $p): ?>
      <tr>
        <td>
          <strong><?= Helper::formatDate($p['edition_date'], 'd M Y') ?></strong>
          <div style="font-size:11px;color:var(--text-muted)"><?= date('l', strtotime($p['edition_date'])) ?></div>
        </td>
        <td>
          <span class="tn-article-link"><?= Helper::e($p['title']) ?></span>
          <?php if ($p['title_tamil']): ?>
          <div style="font-size:12px;color:var(--text-muted);font-family:'Noto Sans Tamil',sans-serif"><?= Helper::e($p['title_tamil']) ?></div>
          <?php endif; ?>
        </td>
        <td><span class="badge bg-secondary"><?= ucfirst($p['edition_type']) ?></span></td>
        <td style="font-size:12px;color:var(--text-muted)"><?= round($p['file_size'] / 1024 / 1024, 1) ?> MB</td>
        <td>
          <span style="font-size:13px">📥 <?= number_format($p['download_count']) ?></span>
        </td>
        <td style="font-size:12px;color:var(--text-muted)"><?= Helper::e($p['uploaded_by_name']) ?></td>
        <td>
          <span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
            <?= $p['is_active'] ? 'Public' : 'Hidden' ?>
          </span>
        </td>
        <td>
          <a href="<?= $r ?>/public<?= Helper::e($p['pdf_path']) ?>" target="_blank"
             class="btn btn-sm btn-outline-primary" title="Preview">
            <i class="bi bi-eye"></i>
          </a>
          <form action="<?= $r ?>/admin/newspaper/toggle/<?= $p['id'] ?>" method="POST" class="d-inline">
            <?= CSRF::field() ?>
            <button class="btn btn-sm <?= $p['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                    title="<?= $p['is_active'] ? 'Hide' : 'Publish' ?>">
              <i class="bi bi-<?= $p['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
            </button>
          </form>
          <a href="<?= $r ?>/newspaper/download/<?= $p['id'] ?>"
             class="btn btn-sm btn-outline-secondary" title="Download">
            <i class="bi bi-download"></i>
          </a>
          <form action="<?= $r ?>/admin/newspaper/delete/<?= $p['id'] ?>" method="POST" class="d-inline"
                onsubmit="return confirm('Delete this edition permanently?')">
            <?= CSRF::field() ?>
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php $queryExtra = ''; include VIEW_PATH . '/partials/pagination.php'; ?>
</div>
<?php endif; ?>

<!-- UPLOAD MODAL -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= $r ?>/admin/newspaper/upload" method="POST" enctype="multipart/form-data">
        <?= CSRF::field() ?>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Upload Newspaper Edition</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-600">Edition Date *</label>
            <input type="date" name="edition_date" class="form-control"
                   value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Edition Type</label>
            <select name="edition_type" class="form-select">
              <option value="daily">Daily Edition</option>
              <option value="weekly">Weekly Edition</option>
              <option value="special">Special Edition</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Title <small class="text-muted">(auto-filled if empty)</small></label>
            <input type="text" name="title" class="form-control"
                   placeholder="e.g. Tamil News — 28 April 2026">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Title in Tamil</label>
            <input type="text" name="title_tamil" class="form-control"
                   placeholder="தமிழ் செய்தி — 28 ஏப்ரல் 2026"
                   style="font-family:'Noto Sans Tamil',sans-serif">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">PDF File * <small class="text-muted">(max 50MB)</small></label>
            <div id="pdfDropZone"
                 style="border:2px dashed rgba(255,255,255,.2);border-radius:8px;padding:28px;text-align:center;cursor:pointer;transition:border-color .2s"
                 onclick="document.getElementById('pdfInput').click()"
                 ondragover="event.preventDefault();this.style.borderColor='#3b82f6'"
                 ondragleave="this.style.borderColor=''"
                 ondrop="handlePdfDrop(event)">
              <div id="pdfDropContent">
                <div style="font-size:32px;margin-bottom:8px">📄</div>
                <div style="font-weight:600;margin-bottom:4px">Click or drag & drop PDF</div>
                <div style="font-size:12px;color:var(--text-muted)">PDF only · Max 50MB</div>
              </div>
              <div id="pdfSelected" style="display:none;color:#10b981;font-weight:600"></div>
            </div>
            <input type="file" id="pdfInput" name="pdf" accept=".pdf,application/pdf"
                   style="display:none" onchange="showPdfName(this)">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-upload me-2"></i>Upload Edition
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showPdfName(input) {
  if (input.files[0]) {
    const name = input.files[0].name;
    const size = (input.files[0].size / 1024 / 1024).toFixed(1);
    document.getElementById('pdfDropContent').style.display = 'none';
    document.getElementById('pdfSelected').style.display = 'block';
    document.getElementById('pdfSelected').innerHTML = '✅ ' + name + ' (' + size + ' MB)';
  }
}
function handlePdfDrop(e) {
  e.preventDefault();
  const file = e.dataTransfer.files[0];
  if (file && file.type === 'application/pdf') {
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('pdfInput').files = dt.files;
    showPdfName(document.getElementById('pdfInput'));
  }
}
// Auto-fill title from date
document.querySelector('input[name="edition_date"]')?.addEventListener('change', function() {
  const titleEl = document.querySelector('input[name="title"]');
  if (!titleEl.value) {
    const d = new Date(this.value);
    titleEl.value = 'Tamil News — ' + d.toLocaleDateString('en-IN', {day:'numeric',month:'long',year:'numeric'});
  }
});
</script>
