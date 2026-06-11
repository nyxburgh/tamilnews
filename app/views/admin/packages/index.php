<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <h2 class="tn-page-title">📦 Ad Packages</h2>
</div>

<div class="row g-4">
  <!-- Package list -->
  <div class="col-md-8">
    <div class="tn-card">
      <div class="tn-card-body p-0">
        <table class="tn-table">
          <thead><tr>
            <th>Package</th><th>Type</th><th>Price</th><th>Days</th>
            <th>Images</th><th>News</th><th>Video</th><th>Ads</th><th>Status</th><th></th>
          </tr></thead>
          <tbody>
          <?php foreach ($packages as $p): ?>
          <tr>
            <td>
              <div style="font-weight:600"><?= Helper::e($p['name']) ?></div>
              <div style="font-size:11px;color:#9A9890"><?= Helper::e($p['name_tamil'] ?? '') ?></div>
            </td>
            <td><span class="badge bg-info text-dark"><?= $p['type'] ?></span></td>
            <td>₹<?= number_format($p['price_inr'],2) ?></td>
            <td><?= $p['duration_days'] ?></td>
            <td><?= $p['max_images'] ?></td>
            <td><?= $p['includes_news'] ? '✓' : '—' ?></td>
            <td><?= $p['includes_video'] ? '✓' : '—' ?></td>
            <td><?= $p['ad_count'] ?? 0 ?></td>
            <td>
              <span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-primary"
                onclick="editPackage(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add package form -->
  <div class="col-md-4">
    <div class="tn-card" id="packageFormCard">
      <div class="tn-card-header" id="packageFormTitle">New Package</div>
      <div class="tn-card-body">
        <form method="POST" id="packageForm" action="<?= $r ?>/admin/packages/store">
          <?= CSRF::field() ?>
          <input type="hidden" name="_method" id="pkgMethod" value="store">
          <input type="hidden" name="_pkg_id" id="pkgId" value="">
          <div class="mb-2">
            <label class="form-label small fw-600">Name</label>
            <input type="text" name="name" id="pkgName" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-600">Tamil Name</label>
            <input type="text" name="name_tamil" id="pkgNameTa" class="form-control form-control-sm">
          </div>
          <div class="mb-2">
            <label class="form-label small fw-600">Type</label>
            <select name="type" id="pkgType" class="form-select form-select-sm">
              <option value="free">Free (7 days)</option>
              <option value="paid_ad">Ad Only</option>
              <option value="paid_ad_news">Ad + News</option>
              <option value="paid_ad_news_video">Ad + News + Video</option>
            </select>
          </div>
          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label small fw-600">Price (₹)</label>
              <input type="number" name="price_inr" id="pkgPrice" class="form-control form-control-sm" value="0" step="0.01">
            </div>
            <div class="col">
              <label class="form-label small fw-600">Days</label>
              <input type="number" name="duration_days" id="pkgDays" class="form-control form-control-sm" value="30">
            </div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label small fw-600">Max Images</label>
              <input type="number" name="max_images" id="pkgImages" class="form-control form-control-sm" value="5">
            </div>
            <div class="col">
              <label class="form-label small fw-600">Sort Order</label>
              <input type="number" name="sort_order" id="pkgSort" class="form-control form-control-sm" value="99">
            </div>
          </div>
          <div class="mb-3 d-flex gap-3">
            <div class="form-check">
              <input type="checkbox" name="includes_news" id="pkgNews" value="1" class="form-check-input">
              <label class="form-check-label small" for="pkgNews">Includes News</label>
            </div>
            <div class="form-check">
              <input type="checkbox" name="includes_video" id="pkgVideo" value="1" class="form-check-input">
              <label class="form-check-label small" for="pkgVideo">Includes Video</label>
            </div>
          </div>
          <button class="btn btn-primary w-100 btn-sm" type="submit" id="pkgSubmitBtn">Create Package</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function editPackage(p) {
  document.getElementById('packageFormTitle').textContent = 'Edit Package';
  document.getElementById('pkgSubmitBtn').textContent = 'Update Package';
  document.getElementById('packageForm').action = '<?= $r ?>/admin/packages/update/' + p.id;
  document.getElementById('pkgId').value = p.id;
  document.getElementById('pkgName').value = p.name;
  document.getElementById('pkgNameTa').value = p.name_tamil || '';
  document.getElementById('pkgType').value = p.type;
  document.getElementById('pkgPrice').value = p.price_inr;
  document.getElementById('pkgDays').value = p.duration_days;
  document.getElementById('pkgImages').value = p.max_images;
  document.getElementById('pkgSort').value = p.sort_order;
  document.getElementById('pkgNews').checked = p.includes_news == 1;
  document.getElementById('pkgVideo').checked = p.includes_video == 1;
  document.getElementById('packageFormCard').scrollIntoView({behavior:'smooth'});
}
</script>
