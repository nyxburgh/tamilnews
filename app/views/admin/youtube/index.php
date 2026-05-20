<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">YouTube Automation</h2>
  <div class="d-flex gap-2">
    <a href="<?= $r ?>/admin/youtube/imports" class="btn btn-outline-secondary"><i class="bi bi-inbox me-2"></i>Import Queue</a>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChannelModal">
      <i class="bi bi-plus-circle me-2"></i>Add Channel
    </button>
  </div>
</div>

<!-- CHANNELS -->
<?php foreach ($channels as $ch): ?>
<div class="tn-card mb-4">
  <div class="tn-card-header">
    <div class="d-flex align-items-center gap-3">
      <div class="tn-yt-icon"><i class="bi bi-youtube text-danger"></i></div>
      <div>
        <strong><?= Helper::e($ch['channel_name']) ?></strong>
        <div class="text-muted small"><code><?= Helper::e($ch['channel_id']) ?></code> · <?= Helper::e($ch['category_name']) ?></div>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge <?= $ch['auto_publish'] ? 'bg-success' : 'bg-secondary' ?>">
        <?= $ch['auto_publish'] ? 'Auto-publish ON' : 'Draft mode' ?>
      </span>
      <span class="badge <?= $ch['is_active'] ? 'bg-primary' : 'bg-secondary' ?>">
        <?= $ch['is_active'] ? $ch['fetch_interval'] : 'Inactive' ?>
      </span>
      <button class="btn btn-sm btn-outline-primary" onclick="editChannel(<?= htmlspecialchars(json_encode($ch)) ?>)">
        <i class="bi bi-pencil"></i>
      </button>
      <form action="<?= $r ?>/admin/youtube/channels/delete/<?= $ch['id'] ?>" method="POST" class="d-inline"
            onsubmit="return confirm('Delete channel config?')">
        <?= CSRF::field() ?>
        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
      </form>
    </div>
  </div>

  <!-- KEYWORD MAPPINGS -->
  <div class="tn-card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <span class="fw-600 small text-muted text-uppercase" style="letter-spacing:.5px">Keyword → Category Mappings</span>
      <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="collapse"
              data-bs-target="#kw<?= $ch['id'] ?>">
        <i class="bi bi-plus"></i> Add Mapping
      </button>
    </div>

    <!-- ADD KEYWORD FORM -->
    <div class="collapse mb-3" id="kw<?= $ch['id'] ?>">
      <form action="<?= $r ?>/admin/youtube/keywords/create" method="POST" class="row g-2">
        <?= CSRF::field() ?>
        <input type="hidden" name="channel_id" value="<?= $ch['id'] ?>">
        <div class="col-sm-5">
          <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Keyword (e.g. cricket)" required>
        </div>
        <div class="col-sm-5">
          <select name="category_id" class="form-select form-select-sm">
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-sm btn-primary">Add</button>
        </div>
      </form>
    </div>

    <?php $keywords = (new \App\Models\YoutubeModel())->keywords($ch['id']); ?>
    <?php if ($keywords): ?>
    <div class="d-flex flex-wrap gap-2">
      <?php foreach ($keywords as $kw): ?>
      <div class="tn-kw-badge">
        <i class="bi bi-key text-muted me-1"></i>
        <strong><?= Helper::e($kw['keyword']) ?></strong>
        <i class="bi bi-arrow-right text-muted mx-1"></i>
        <?= Helper::e($kw['category_name']) ?>
        <form action="<?= $r ?>/admin/youtube/keywords/delete/<?= $kw['id'] ?>" method="POST" class="d-inline">
          <?= CSRF::field() ?>
          <button class="btn btn-link p-0 ms-1 text-danger" style="font-size:12px"><i class="bi bi-x"></i></button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-muted small mb-0">No keyword mappings. Title-based category fallback is active.</p>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>

<?php if (empty($channels)): ?>
<div class="tn-card"><div class="tn-card-body text-center py-5 text-muted">
  <i class="bi bi-youtube fs-1 d-block mb-3"></i>
  No YouTube channels configured. Add one to start auto-importing videos.
</div></div>
<?php endif; ?>

<!-- ADD CHANNEL MODAL -->
<div class="modal fade" id="addChannelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form action="<?= $r ?>/admin/youtube/channels/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add YouTube Channel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Channel Name *</label><input type="text" name="channel_name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Channel ID *</label><input type="text" name="channel_id" class="form-control" placeholder="UCxxxxxxxxxxxxxxxx" required></div>
    <div class="mb-3"><label class="form-label">Playlist ID <small class="text-muted">(optional)</small></label><input type="text" name="playlist_id" class="form-control" placeholder="PLxxxxxxxxxxxxxxxx"></div>
    <div class="mb-3">
      <label class="form-label">Default Category *</label>
      <select name="category_id" class="form-select">
        <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Fetch Interval</label>
      <select name="fetch_interval" class="form-select">
        <option value="hourly">Hourly</option>
        <option value="daily">Daily</option>
      </select>
    </div>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="auto_publish" value="1" id="autoPublish">
      <label class="form-check-label" for="autoPublish">Auto-publish imported videos</label>
    </div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Add Channel</button></div>
  </form>
</div></div></div>

<!-- EDIT CHANNEL MODAL -->
<div class="modal fade" id="editChannelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form id="editChannelForm" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Edit Channel</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Channel Name</label><input type="text" name="channel_name" id="ecName" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Playlist ID</label><input type="text" name="playlist_id" id="ecPlaylist" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Category</label>
      <select name="category_id" id="ecCategory" class="form-select">
        <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= Helper::e($cat['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3"><label class="form-label">Fetch Interval</label>
      <select name="fetch_interval" id="ecInterval" class="form-select">
        <option value="hourly">Hourly</option><option value="daily">Daily</option>
      </select>
    </div>
    <div class="mb-3 form-check form-switch">
      <input class="form-check-input" type="checkbox" name="auto_publish" value="1" id="ecAutoPublish">
      <label class="form-check-label" for="ecAutoPublish">Auto-publish</label>
    </div>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="ecActive">
      <label class="form-check-label" for="ecActive">Active</label>
    </div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
  </form>
</div></div></div>

<script>
function editChannel(ch) {
  document.getElementById('editChannelForm').action = '/admin/youtube/channels/edit/' + ch.id;
  document.getElementById('ecName').value      = ch.channel_name;
  document.getElementById('ecPlaylist').value  = ch.playlist_id || '';
  document.getElementById('ecCategory').value  = ch.category_id;
  document.getElementById('ecInterval').value  = ch.fetch_interval;
  document.getElementById('ecAutoPublish').checked = ch.auto_publish == 1;
  document.getElementById('ecActive').checked      = ch.is_active == 1;
  new bootstrap.Modal(document.getElementById('editChannelModal')).show();
}
</script>
