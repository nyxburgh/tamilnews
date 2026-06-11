<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <h2 class="tn-page-title">📊 <?= $isEdit ? 'Edit Poll' : 'New Poll' ?></h2>
  <a href="<?= $r ?>/admin/polls" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

<form method="POST" action="<?= $r ?>/admin/polls/<?= $isEdit ? 'update/'.$poll['id'] : 'store' ?>">
<?= CSRF::field() ?>
<div class="row g-4">
  <div class="col-md-8">
    <div class="tn-card">
      <div class="tn-card-header">Question</div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Question (Tamil) <span class="text-danger">*</span></label>
          <input type="text" name="question_ta" class="form-control"
                 value="<?= Helper::e($poll['question_ta'] ?? '') ?>" placeholder="தமிழில் கேள்வி...">
        </div>
        <div class="mb-0">
          <label class="form-label fw-600">Question (English)</label>
          <input type="text" name="question" class="form-control"
                 value="<?= Helper::e($poll['question'] ?? '') ?>" placeholder="Question in English...">
        </div>
      </div>
    </div>

    <!-- Options -->
    <div class="tn-card mt-3">
      <div class="tn-card-header">
        Options <small class="text-muted">(minimum 2)</small>
      </div>
      <div class="tn-card-body" id="optionsContainer">
        <?php $existingOptions = $options ?? [['text'=>'','text_ta'=>''],['text'=>'','text_ta'=>'']]; ?>
        <?php foreach ($existingOptions as $i => $opt): ?>
        <div class="option-row mb-2 d-flex gap-2">
          <input type="text" name="option_text_ta[]" class="form-control"
                 value="<?= Helper::e($opt['option_text_ta'] ?? $opt['text_ta'] ?? '') ?>"
                 placeholder="Option <?= $i+1 ?> (Tamil)">
          <input type="text" name="option_text[]" class="form-control"
                 value="<?= Helper::e($opt['option_text'] ?? $opt['text'] ?? '') ?>"
                 placeholder="Option <?= $i+1 ?> (English)">
          <button type="button" class="btn btn-outline-danger btn-sm"
                  onclick="this.parentElement.remove()">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="tn-card-body pt-0">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption()">
          + Add Option
        </button>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="tn-card">
      <div class="tn-card-header">Settings</div>
      <div class="tn-card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Expires At</label>
          <input type="datetime-local" name="expires_at" class="form-control"
                 value="<?= isset($poll['expires_at']) ? date('Y-m-d\TH:i', strtotime($poll['expires_at'])) : '' ?>">
          <div class="form-text">Leave blank for no expiry</div>
        </div>
        <div class="mb-0">
          <label class="form-label fw-600">Status</label>
          <select name="is_active" class="form-select">
            <option value="1" <?= ($poll['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= empty($poll['is_active']) ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
      </div>
    </div>
    <div class="d-grid mt-3">
      <button class="btn btn-primary btn-lg">
        <?= $isEdit ? 'Update Poll' : 'Create Poll' ?>
      </button>
    </div>
  </div>
</div>
</form>

<script>
let optCount = <?= count($existingOptions ?? []) ?>;
function addOption() {
  optCount++;
  const div = document.createElement('div');
  div.className = 'option-row mb-2 d-flex gap-2';
  div.innerHTML = `
    <input type="text" name="option_text_ta[]" class="form-control" placeholder="Option ${optCount} (Tamil)">
    <input type="text" name="option_text[]" class="form-control" placeholder="Option ${optCount} (English)">
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
  `;
  document.getElementById('optionsContainer').appendChild(div);
}
</script>
