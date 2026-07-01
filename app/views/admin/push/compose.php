<?php use App\Core\{Helper, Auth, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">🔔 Push Notifications</h2>
    <p class="tn-page-sub"><?= number_format($totalSubscribers) ?> active subscribers</p>
  </div>
</div>

<?php if ($totalSubscribers === 0): ?>
<div class="alert alert-warning">
  <strong>FCM not configured or no subscribers yet.</strong>
  Add Firebase keys to <code>.env</code> and replace placeholders in
  <code>public/firebase-messaging-sw.js</code> and the layout.
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- Compose -->
  <div class="col-lg-5">
    <div class="tn-card">
      <div class="tn-card-header"><i class="bi bi-send me-2"></i>Compose Push</div>
      <div class="tn-card-body">
        <form method="POST" action="<?= $r ?>/admin/push/send">
          <?= CSRF::field() ?>

          <div class="mb-3">
            <label class="form-label fw-600">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" maxlength="80"
                   placeholder="Breaking: ..." required>
            <div class="form-text">Keep under 60 characters for best display</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-600">Body <span class="text-danger">*</span></label>
            <textarea name="body" class="form-control" rows="3"
                      maxlength="200" placeholder="Short message..." required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-600">Link (optional)</label>
            <input type="url" name="click_url" class="form-control"
                   placeholder="https://thinathulir.com/article/...">
            <div class="form-text">Blank = home page</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-600">Send To</label>
            <div class="mb-2">
              <label class="d-flex align-items-center gap-2">
                <input type="radio" name="target" value="all" checked
                       onchange="document.getElementById('districtPicker').classList.add('d-none')">
                <span>🌐 All subscribers</span>
              </label>
              <label class="d-flex align-items-center gap-2 mt-1">
                <input type="radio" name="target" value="district"
                       onchange="document.getElementById('districtPicker').classList.remove('d-none')">
                <span>📍 Specific districts</span>
              </label>
            </div>
            <div id="districtPicker" class="d-none">
              <select name="district_ids[]" class="form-select" multiple size="6">
                <?php foreach ($districts as $d): ?>
                <option value="<?= $d['id'] ?>"><?= Helper::e($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
            </div>
          </div>

          <button type="submit" class="btn btn-danger w-100">
            <i class="bi bi-send-fill me-2"></i>Send Push Notification
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Log -->
  <div class="col-lg-7">
    <div class="tn-card">
      <div class="tn-card-header"><i class="bi bi-clock-history me-2"></i>Recent Pushes</div>
      <div class="tn-card-body p-0">
        <?php if (empty($logs)): ?>
        <div class="text-center text-muted py-5">No push notifications sent yet.</div>
        <?php else: ?>
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr><th>Title</th><th>Type</th><th>Sent</th><th>Failed</th><th>When</th></tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
              <td class="small fw-600"><?= Helper::e(mb_substr($log['title'],0,45)) ?></td>
              <td><span class="badge bg-secondary"><?= $log['type'] ?></span></td>
              <td class="text-success fw-600"><?= $log['sent_count'] ?></td>
              <td class="<?= $log['fail_count'] > 0 ? 'text-danger' : 'text-muted' ?>"><?= $log['fail_count'] ?></td>
              <td class="small text-muted"><?= substr($log['created_at'],0,16) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
