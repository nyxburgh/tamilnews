<?php use App\Core\CSRF; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">🗞️ New Print Edition</h2>
    <p class="tn-page-sub">Create an edition then select articles</p>
  </div>
  <a href="<?= $r ?>/admin/print" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back
  </a>
</div>

<div class="tn-card" style="max-width:520px">
  <div class="tn-card-body">
    <form action="<?= $r ?>/admin/print/store" method="POST">
      <?= CSRF::field() ?>
      <div class="mb-3">
        <label class="form-label fw-600">Edition Date <span class="text-danger">*</span></label>
        <input type="date" name="edition_date" class="form-control"
               value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-600">Title <small class="text-muted">(auto-filled if empty)</small></label>
        <input type="text" name="title" class="form-control"
               placeholder="வேள் சுடர் — 04 May 2026"
               style="font-family:'Noto Sans Tamil',sans-serif">
      </div>
      <div class="mb-4">
        <label class="form-label fw-600">Notes <small class="text-muted">(optional)</small></label>
        <textarea name="notes" class="form-control" rows="2"
                  placeholder="e.g. Weekly special, Election edition..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-arrow-right me-2"></i>Create & Select Articles
      </button>
    </form>
  </div>
</div>

<script>
document.querySelector('input[name="edition_date"]').addEventListener('change', function() {
  const t = document.querySelector('input[name="title"]');
  if (!t.value) {
    const d = new Date(this.value);
    t.value = 'வேள் சுடர் — ' + d.toLocaleDateString('en-IN', {day:'numeric',month:'long',year:'numeric'});
  }
});
</script>
