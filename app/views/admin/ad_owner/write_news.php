<?php use App\Core\{Helper, CSRF}; ?>
<div class="portal-page-header">
  <div>
    <h2 class="portal-page-title">✍️ Sponsored Article</h2>
    <p class="portal-page-sub"><?= Helper::e($ad['business_name']) ?></p>
  </div>
  <a href="<?= $r ?>/portal/my-ads/<?= $ad['id'] ?>" class="portal-back-btn">← Back</a>
</div>

<?php if (!empty($quota['quota'])): ?>
<div class="portal-card mb-3">
  <div class="portal-card-body py-2">
    <small class="text-muted">Quota: <strong><?= (int)($quota['used']??0) ?>/<?= $quota['quota'] ?></strong> articles used</small>
  </div>
</div>
<?php endif; ?>

<div class="portal-card">
  <div class="portal-card-header">New Sponsored Article</div>
  <div class="portal-card-body">
    <form method="POST" action="<?= $r ?>/portal/my-ads/<?= $ad['id'] ?>/submit-news">
      <?= CSRF::field() ?>
      <div class="mb-3">
        <label class="form-label fw-600 small">Article Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" placeholder="Enter headline..." required minlength="5">
      </div>
      <div class="mb-3">
        <label class="form-label fw-600 small">Content <span class="text-danger">*</span></label>
        <textarea name="content" class="form-control" rows="10"
                  placeholder="Write your sponsored article content here..." required minlength="50"></textarea>
        <div class="form-text">Minimum 50 characters. Will be reviewed by our editorial team before publishing.</div>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary">Submit for Review</button>
        <a href="<?= $r ?>/portal/my-ads/<?= $ad['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
