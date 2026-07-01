<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Create Ad Owner Login</h2>
  <a href="<?= $r ?>/admin/business-ads/subscription/<?= $sub['id'] ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="tn-card">
      <div class="tn-card-header">
        <div>
          <span class="fw-600">New Login for: <?= Helper::e($sub['business_name']) ?></span>
          <div class="text-muted small mt-1">Package: <?= Helper::e($sub['package_name']) ?> · Valid until: <?= $sub['valid_until'] ?></div>
        </div>
      </div>
      <div class="tn-card-body">
        <form method="POST" action="<?= $r ?>/admin/business-ads/subscription/<?= $sub['id'] ?>/create-login">
          <?= CSRF::field() ?>
          <div class="mb-3">
            <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Business contact name" required value="<?= Helper::e($sub['business_name']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="owner@example.com" required value="<?= Helper::e($sub['owner_email'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Password <span class="text-danger">*</span></label>
            <input type="text" name="password" class="form-control" placeholder="Min 8 characters" required minlength="8">
            <div class="form-text">Share this with the client — they can change it after login.</div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Create Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
