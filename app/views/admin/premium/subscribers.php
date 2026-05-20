<?php use App\Core\Helper; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Premium Subscribers</h2>
  <a href="<?= $r ?>/admin/premium" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>
<div class="tn-card">
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead><tr><th>Reader</th><th>Email</th><th>Plan</th><th>Price</th><th>Starts</th><th>Expires</th><th>Status</th><th>Payment Ref</th></tr></thead>
      <tbody>
        <?php if (empty($subscribers)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted">No subscribers yet. Activate plans and integrate payment gateway.</td></tr>
        <?php endif; ?>
        <?php foreach ($subscribers as $s): ?>
        <tr>
          <td><strong><?= Helper::e($s['reader_name']) ?></strong></td>
          <td class="text-muted small"><?= Helper::e($s['reader_email']) ?></td>
          <td><?= Helper::e($s['plan_name']) ?></td>
          <td>₹<?= number_format($s['price_inr'], 0) ?></td>
          <td class="text-muted small"><?= Helper::formatDate($s['starts_at'], 'd M Y') ?></td>
          <td class="text-muted small"><?= Helper::formatDate($s['expires_at'], 'd M Y') ?></td>
          <td>
            <?php $sc = ['active'=>'success','expired'=>'secondary','cancelled'=>'danger'][$s['status']] ?? 'secondary'; ?>
            <span class="badge bg-<?= $sc ?>"><?= ucfirst($s['status']) ?></span>
          </td>
          <td class="text-muted small"><?= Helper::e($s['payment_ref'] ?: '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
