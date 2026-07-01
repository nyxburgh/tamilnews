<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Premium Plans</h2>
  <div class="d-flex gap-2">
    <a href="<?= $r ?>/admin/premium" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
      <i class="bi bi-plus-circle me-2"></i>Add Plan
    </button>
  </div>
</div>

<div class="row g-3 mb-4">
  <?php foreach ($plans as $plan): ?>
  <div class="col-md-4">
    <div class="tn-card" style="<?= $plan['is_active'] ? 'border-color:rgba(232,160,0,.4)' : '' ?>">
      <div class="tn-card-header" style="<?= $plan['is_active'] ? 'background:rgba(232,160,0,.08)' : '' ?>">
        <span>
          <?= $plan['is_active'] ? '⭐ ' : '' ?><strong><?= Helper::e($plan['name']) ?></strong>
          <?php if ($plan['name_tamil']): ?>
          <span class="text-muted small ms-1"><?= Helper::e($plan['name_tamil']) ?></span>
          <?php endif; ?>
        </span>
        <span class="badge <?= $plan['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
          <?= $plan['is_active'] ? 'Active' : 'Inactive' ?>
        </span>
      </div>
      <div class="tn-card-body text-center py-4">
        <div style="font-size:36px;font-weight:900;color:#E8A000">₹<?= number_format($plan['price_inr'], 0) ?></div>
        <div class="text-muted"><?= $plan['duration_days'] ?> Days</div>
        <div class="text-muted small mt-1">₹<?= number_format($plan['price_inr'] / $plan['duration_days'], 1) ?>/day</div>
        <button class="btn btn-sm btn-outline-secondary mt-3"
                onclick="editPlan(<?= htmlspecialchars(json_encode($plan)) ?>)">
          <i class="bi bi-pencil me-1"></i>Edit
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="alert alert-info">
  <i class="bi bi-info-circle me-2"></i>
  Plans are ready for future payment integration (Razorpay / Stripe). Currently, access can be granted manually via database.
  Payment gateway integration is the next milestone.
</div>

<!-- ADD PLAN MODAL -->
<div class="modal fade" id="addPlanModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form action="<?= $r ?>/admin/premium/plans/create" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Add Plan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Plan Name *</label><input type="text" name="name" class="form-control" required placeholder="Monthly"></div>
    <div class="mb-3"><label class="form-label">Tamil Name</label><input type="text" name="name_tamil" class="form-control" placeholder="மாதாந்திர"></div>
    <div class="mb-3"><label class="form-label">Price (₹) *</label><input type="number" name="price_inr" class="form-control" step="0.01" min="0" required placeholder="99"></div>
    <div class="mb-3"><label class="form-label">Duration (days) *</label><input type="number" name="duration_days" class="form-control" value="30" min="1" required></div>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="planActive">
      <label class="form-check-label" for="planActive">Activate plan (visible to users)</label>
    </div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Create Plan</button></div>
  </form>
</div></div></div>

<!-- EDIT PLAN MODAL -->
<div class="modal fade" id="editPlanModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form id="editPlanForm" method="POST"><?= CSRF::field() ?>
  <div class="modal-header"><h5 class="modal-title">Edit Plan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Plan Name</label><input type="text" name="name" id="epName" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Tamil Name</label><input type="text" name="name_tamil" id="epTamil" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Price (₹)</label><input type="number" name="price_inr" id="epPrice" class="form-control" step="0.01"></div>
    <div class="mb-3"><label class="form-label">Duration (days)</label><input type="number" name="duration_days" id="epDays" class="form-control"></div>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="epActive">
      <label class="form-check-label" for="epActive">Active</label>
    </div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
  </form>
</div></div></div>

<script>
function editPlan(p) {
  document.getElementById('editPlanForm').action = '<?= $r ?>/admin/premium/plans/update/' + p.id;
  document.getElementById('epName').value  = p.name;
  document.getElementById('epTamil').value = p.name_tamil || '';
  document.getElementById('epPrice').value = p.price_inr;
  document.getElementById('epDays').value  = p.duration_days;
  document.getElementById('epActive').checked = p.is_active == 1;
  new bootstrap.Modal(document.getElementById('editPlanModal')).show();
}
</script>
