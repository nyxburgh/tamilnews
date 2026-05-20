<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Settings</h2>
</div>
<div class="row g-4">
  <!-- GROUP NAV -->
  <div class="col-md-3">
    <div class="tn-card">
      <div class="tn-card-body p-2">
        <?php
        $groups = ['general'=>['bi-gear','General'],'breaking'=>['bi-lightning','Breaking News'],'youtube'=>['bi-youtube','YouTube'],'rss'=>['bi-rss','RSS'],'fcm'=>['bi-bell','Push (FCM)'],'social'=>['bi-share','Social'],'cache'=>['bi-speedometer','Cache'],'seo'=>['bi-search','SEO'],'admin'=>['bi-shield-lock','Admin']];
        foreach ($groups as $g => [$icon,$label]):
        ?>
        <a href="<?= $r ?>/admin/settings/<?= $g ?>"
           class="tn-nav-item <?= $group === $g ? 'active' : '' ?>">
          <i class="bi <?= $icon ?>"></i> <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- SETTINGS FORM -->
  <div class="col-md-9">
    <?php if (isset($settings[$group])): ?>
    <div class="tn-card">
      <div class="tn-card-header">
        <span><i class="bi bi-sliders me-2"></i><?= ucfirst($group) ?> Settings</span>
      </div>
      <div class="tn-card-body">
        <form action="<?= $r ?>/admin/settings/<?= $group ?>" method="POST">
          <?= CSRF::field() ?>
          <?php foreach ($settings[$group] as $key => $row): ?>
          <div class="mb-4">
            <label class="form-label fw-600"><?= Helper::e($row['label'] ?? $key) ?></label>
            <?php
            $val = $row['value'] ?? '';
            switch ($row['input_type'] ?? 'text'):
              case 'toggle':
            ?>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="<?= $key ?>" value="1" id="set_<?= $key ?>"
                     <?= $val ? 'checked' : '' ?>>
              <label class="form-check-label" for="set_<?= $key ?>">Enabled</label>
            </div>
            <?php break; case 'textarea': ?>
            <textarea name="<?= $key ?>" class="form-control font-monospace" rows="4"><?= Helper::e($val) ?></textarea>
            <?php break; case 'number': ?>
            <input type="number" name="<?= $key ?>" class="form-control" value="<?= Helper::e($val) ?>">
            <?php break; case 'select': ?>
            <select name="<?= $key ?>" class="form-select">
              <?php if ($key === 'fetch_interval'): ?>
              <option value="hourly" <?= $val === 'hourly' ? 'selected' : '' ?>>Hourly</option>
              <option value="daily" <?= $val === 'daily' ? 'selected' : '' ?>>Daily</option>
              <?php endif; ?>
            </select>
            <?php break; case 'email': ?>
            <input type="email" name="<?= $key ?>" class="form-control" value="<?= Helper::e($val) ?>">
            <?php break; case 'image': ?>
            <input type="text" name="<?= $key ?>" class="form-control" value="<?= Helper::e($val) ?>" placeholder="URL or upload path">
            <?php break; default: ?>
            <input type="text" name="<?= $key ?>" class="form-control" value="<?= Helper::e($val) ?>">
            <?php endswitch; ?>
          </div>
          <?php endforeach; ?>
          <div class="pt-2 border-top">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-2"></i>Save <?= ucfirst($group) ?> Settings
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php else: ?>
    <div class="tn-card"><div class="tn-card-body text-center py-5 text-muted">Select a settings group</div></div>
    <?php endif; ?>
  </div>
</div>
