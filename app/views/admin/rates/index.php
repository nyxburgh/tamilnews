<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <h2 class="tn-page-title">📊 Live Rates</h2>
</div>

<div class="row g-4">
  <!-- Current rates display -->
  <div class="col-md-7">
    <div class="tn-card">
      <div class="tn-card-header">Current Rates</div>
      <div class="tn-card-body p-0">
        <table class="tn-table">
          <thead><tr><th>Type</th><th>City</th><th>Value</th><th>Change</th><th>Updated</th></tr></thead>
          <tbody>
          <?php if (empty($rates)): ?>
          <tr><td colspan="5" class="text-center py-4 text-muted">No rates added yet.</td></tr>
          <?php else: ?>
          <?php foreach ($rates as $rate): ?>
          <tr>
            <td><strong><?= $types[$rate['type']] ?? $rate['type'] ?></strong></td>
            <td><?= $rate['city'] ?? '—' ?></td>
            <td>₹<?= number_format($rate['value'], 2) ?></td>
            <td>
              <?php if ($rate['change_val'] !== null): ?>
              <span style="color:<?= $rate['change_val'] >= 0 ? '#10B981' : '#EF4444' ?>">
                <?= $rate['change_val'] >= 0 ? '+' : '' ?><?= number_format($rate['change_val'], 2) ?>
                (<?= number_format($rate['change_pct'] ?? 0, 2) ?>%)
              </span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td style="font-size:11px"><?= Helper::timeAgo($rate['updated_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add/Update rate form -->
  <div class="col-md-5">
    <div class="tn-card">
      <div class="tn-card-header">Update Rate</div>
      <div class="tn-card-body">
        <form method="POST" action="<?= $r ?>/admin/rates/update">
          <?= CSRF::field() ?>
          <div class="mb-3">
            <label class="form-label fw-600">Type</label>
            <select name="type" class="form-select">
              <?php foreach ($types as $slug => $label): ?>
              <option value="<?= $slug ?>"><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Value (₹)</label>
            <input type="number" name="value" class="form-control" step="0.01" required placeholder="0.00">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">City <small class="text-muted">(optional — blank = national)</small></label>
            <input type="text" name="city" class="form-control" placeholder="e.g. Chennai">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Change Amount <small class="text-muted">(optional)</small></label>
            <input type="number" name="change" class="form-control" step="0.01" placeholder="+/- amount">
          </div>
          <button class="btn btn-primary w-100">Update Rate</button>
        </form>
      </div>
    </div>

    <!-- Floating icons preview -->
    <div class="tn-card mt-3">
      <div class="tn-card-header">📱 Mobile Floating Icons Preview</div>
      <div class="tn-card-body">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <?php
          $icons = ['gold'=>'🥇','silver'=>'🥈','petrol'=>'⛽','diesel'=>'🚛','currency_usd'=>'💵'];
          foreach ($icons as $type => $icon): ?>
          <div style="width:52px;height:52px;border-radius:50%;background:#C0001A;color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:10px;box-shadow:0 2px 8px rgba(0,0,0,.3);cursor:pointer">
            <span style="font-size:18px"><?= $icon ?></span>
            <span style="font-size:8px;margin-top:-2px"><?= strtoupper(str_replace(['currency_','_usd','_gbp','_eur'],'',$type)) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <p class="small text-muted mt-2 mb-0">These appear on mobile as floating action buttons. Click shows popup with current rate.</p>
      </div>
    </div>
  </div>
</div>
