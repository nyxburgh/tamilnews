<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Assign Package</h2>
  <a href="<?= $r ?>/admin/business-ads/show/<?= $ad['id'] ?>" class="btn btn-outline-secondary btn-sm">← Back to Ad</a>
</div>

<!-- Current subscriptions -->
<?php if (!empty($subs)): ?>
<div class="tn-card mb-4">
  <div class="tn-card-header"><span class="fw-600">Existing Subscriptions</span></div>
  <div class="tn-card-body p-0">
    <table class="table table-sm mb-0">
      <thead><tr><th>Package</th><th>Status</th><th>Valid From</th><th>Valid Until</th><th>Owner</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($subs as $s): ?>
        <tr>
          <td><?= Helper::e($s['package_name']) ?></td>
          <td><span class="badge bg-<?php
              if ($s['status']==='active') echo 'success';
              elseif ($s['status']==='expired') echo 'secondary';
              elseif ($s['status']==='suspended') echo 'danger';
              else echo 'warning';
              ?>"><?= ucfirst($s['status']) ?></span></td>
          <td><?= $s['valid_from'] ?></td>
          <td><?= $s['valid_until'] ?></td>
          <td><?= Helper::e($s['owner_name'] ?? '—') ?></td>
          <td><a href="<?= $r ?>/admin/business-ads/subscription/<?= $s['id'] ?>" class="btn btn-xs btn-outline-primary">Manage</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Assign new package -->
<div class="tn-card">
  <div class="tn-card-header"><span class="fw-600">Assign New Package to: <?= Helper::e($ad['business_name']) ?></span></div>
  <div class="tn-card-body">
    <form method="POST" action="<?= $r ?>/admin/business-ads/<?= $ad['id'] ?>/assign">
      <?= CSRF::field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-600">Package <span class="text-danger">*</span></label>
          <select name="package_id" class="form-select" id="pkgSelect" required>
            <option value="">— Select Package —</option>
            <?php foreach ($packages as $p): ?>
            <option value="<?= $p['id'] ?>"
                    data-trial="<?= $p['is_trial'] ?>"
                    data-vertical="<?= $p['slot_type']==='vertical' ? 1 : 0 ?>"
                    data-min="<?= $p['min_days'] ?>"
                    data-max="<?= $p['max_days'] ?? 30 ?>"
                    data-rate="<?= $p['rate_per_day'] ?>"
                    data-amount="<?= $p['amount'] ?>">
              <?= Helper::e($p['name']) ?> — ₹<?= number_format($p['amount'] ?: $p['rate_per_day'],2) ?><?= $p['slot_type']==='vertical' ? '/day' : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-600">Valid From</label>
          <input type="date" name="valid_from" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-3" id="daysRow" style="display:none">
          <label class="form-label fw-600">Days (10–30)</label>
          <input type="number" name="selected_days" class="form-control" min="10" max="30" value="10">
          <div class="form-text" id="amountCalc"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-600">Amount Paid (₹)</label>
          <input type="number" name="amount_paid" id="amountPaid" class="form-control" step="0.01" min="0">
        </div>
        <div class="col-md-8">
          <label class="form-label fw-600">Notes</label>
          <input type="text" name="notes" class="form-control" placeholder="Payment reference, remarks…">
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-3">Assign Package</button>
    </form>
  </div>
</div>

<script>
document.getElementById('pkgSelect').addEventListener('change', function () {
  var opt = this.options[this.selectedIndex];
  var isVertical = opt.dataset.vertical === '1';
  var isTrial    = opt.dataset.trial === '1';
  document.getElementById('daysRow').style.display = isVertical ? 'block' : 'none';
  document.getElementById('amountPaid').value = isTrial ? '0' : (isVertical ? '' : opt.dataset.amount);
});
document.querySelector('[name=selected_days]')?.addEventListener('input', function () {
  var opt = document.getElementById('pkgSelect').options[document.getElementById('pkgSelect').selectedIndex];
  var rate = parseFloat(opt.dataset.rate || 0);
  var total = rate * parseInt(this.value || 0);
  document.getElementById('amountCalc').textContent = 'Total: ₹' + total.toFixed(2);
  document.getElementById('amountPaid').value = total.toFixed(2);
});
</script>
