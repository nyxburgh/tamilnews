<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Locations</h2>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addStateModal">+ State</button>
    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addDistrictModal">+ District</button>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCityModal">+ City</button>
  </div>
</div>
<div class="row g-4">
  <div class="col-md-4">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-map me-2"></i>States (<?= count($states) ?>)</span></div>
      <div class="table-responsive">
        <table class="table tn-table mb-0">
          <thead><tr><th>Name</th><th>Slug</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($states as $s): ?>
          <tr>
            <td><?= Helper::e($s['name']) ?></td>
            <td><code><?= Helper::e($s['slug'] ?? '') ?></code></td>
            <td>
              <form action="<?= $r ?>/admin/locations/delete/state/<?= $s['id'] ?>" method="POST" class="d-inline"
                    onsubmit="return confirm('Delete state and all its districts/cities?')">
                <?= CSRF::field() ?>
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-geo me-2"></i>Districts (<?= count($districts) ?>)</span></div>
      <div class="table-responsive">
        <table class="table tn-table mb-0">
          <thead><tr><th>Name</th><th>State</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($districts as $d): ?>
          <tr>
            <td><?= Helper::e($d['name']) ?></td>
            <td class="text-muted small"><?= Helper::e($d['state_name']) ?></td>
            <td>
              <form action="<?= $r ?>/admin/locations/delete/district/<?= $d['id'] ?>" method="POST" class="d-inline"
                    onsubmit="return confirm('Delete district?')">
                <?= CSRF::field() ?>
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-pin-map me-2"></i>Cities (<?= count($cities) ?>)</span></div>
      <div class="table-responsive">
        <table class="table tn-table mb-0">
          <thead><tr><th>Name</th><th>District</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($cities as $c): ?>
          <tr>
            <td><?= Helper::e($c['name']) ?></td>
            <td class="text-muted small"><?= Helper::e($c['district_name']) ?></td>
            <td>
              <form action="<?= $r ?>/admin/locations/delete/city/<?= $c['id'] ?>" method="POST" class="d-inline"
                    onsubmit="return confirm('Delete city?')">
                <?= CSRF::field() ?>
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ADD STATE -->
<div class="modal fade" id="addStateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form action="<?= $r ?>/admin/locations/states/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add State</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><label class="form-label">State Name *</label><input type="text" name="name" class="form-control" required></div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Add</button></div>
  </form>
</div></div></div>

<!-- ADD DISTRICT -->
<div class="modal fade" id="addDistrictModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form action="<?= $r ?>/admin/locations/districts/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add District</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3">
      <label class="form-label">State</label>
      <select name="state_id" class="form-select">
        <?php foreach ($states as $s): ?><option value="<?= $s['id'] ?>"><?= Helper::e($s['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div><label class="form-label">District Name *</label><input type="text" name="name" class="form-control" required></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Add</button></div>
  </form>
</div></div></div>

<!-- ADD CITY -->
<div class="modal fade" id="addCityModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form action="<?= $r ?>/admin/locations/cities/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add City</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3">
      <label class="form-label">District</label>
      <select name="district_id" class="form-select">
        <?php foreach ($districts as $d): ?><option value="<?= $d['id'] ?>"><?= Helper::e($d['name']) ?> (<?= Helper::e($d['state_name']) ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div><label class="form-label">City Name *</label><input type="text" name="name" class="form-control" required></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Add</button></div>
  </form>
</div></div></div>
