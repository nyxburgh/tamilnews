<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Start Live Blog</h2>
  <a href="<?= $r ?>/admin/live-blog" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
</div>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-broadcast text-danger me-2"></i>New Live Blog Session</span></div>
      <div class="tn-card-body">
        <form action="<?= $r ?>/admin/live-blog/create" method="POST">
          <?= CSRF::field() ?>
          <div class="mb-3">
            <label class="form-label fw-600">Live Blog Title *</label>
            <input type="text" name="title" class="form-control form-control-lg"
                   placeholder="e.g. TN Assembly Elections 2026 — Live Results" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Slug</label>
            <input type="text" name="slug" class="form-control" placeholder="auto-generated">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Type</label>
            <select name="type" class="form-select">
              <option value="general">📰 General / Breaking News</option>
              <option value="election">🗳️ Election Results</option>
              <option value="cricket">🏏 Cricket Match</option>
              <option value="football">⚽ Football Match</option>
              <option value="sports">🏆 Sports Event</option>
              <option value="disaster">⚠️ Disaster / Emergency</option>
              <option value="budget">💰 Budget</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Description</label>
            <textarea name="description" class="form-control" rows="2"
                      placeholder="Brief description shown on the live page..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Link to Article <small class="text-muted">(optional — slug of existing article)</small></label>
            <input type="text" name="article_id" class="form-control" placeholder="Article ID (optional)">
          </div>

          <!-- SPORTS SCORE TEAMS -->
          <div class="row g-3 mb-3" id="teamsRow" style="display:none">
            <div class="col-sm-6">
              <label class="form-label">Home Team / Party A</label>
              <input type="text" name="team_home" class="form-control" placeholder="CSK / DMK">
            </div>
            <div class="col-sm-6">
              <label class="form-label">Away Team / Party B</label>
              <input type="text" name="team_away" class="form-control" placeholder="MI / ADMK">
            </div>
          </div>

          <div class="alert alert-warning py-2 px-3 small">
            <i class="bi bi-info-circle me-2"></i>
            Live blog starts <strong>immediately</strong> when created. Readers will see a 🔴 LIVE badge.
          </div>

          <button type="submit" class="btn btn-danger w-100 fw-700 py-2">
            <i class="bi bi-broadcast me-2"></i>Start Live Blog Now
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.querySelector('select[name="type"]')?.addEventListener('change', function() {
  const showTeams = ['cricket','football','sports','election'].includes(this.value);
  document.getElementById('teamsRow').style.display = showTeams ? 'flex' : 'none';
});
// Auto slug from title
document.querySelector('input[name="title"]')?.addEventListener('input', function() {
  const slug = document.querySelector('input[name="slug"]');
  if (!slug.value) {
    slug.value = this.value.toLowerCase().replace(/\s+/g,'-').replace(/[^\w-]/g,'').replace(/-+/g,'-');
  }
});
</script>
