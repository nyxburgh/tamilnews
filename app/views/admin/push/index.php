<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Push Notifications</h2>
</div>
<div class="row g-4">
  <!-- SEND FORM -->
  <div class="col-lg-5">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-bell me-2"></i>Send Notification</span></div>
      <div class="tn-card-body">
        <form action="<?= $r ?>/admin/push/send" method="POST">
          <?= CSRF::field() ?>
          <div class="mb-3">
            <label class="form-label fw-600">Topic / Audience</label>
            <select name="topic_id" class="form-select">
              <?php foreach ($topics as $t): ?>
              <option value="<?= $t['id'] ?>"><?= Helper::e($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Notification Title *</label>
            <input type="text" name="title" class="form-control" placeholder="Breaking: …" required maxlength="255">
          </div>
          <div class="mb-4">
            <label class="form-label fw-600">Message Body *</label>
            <textarea name="body" class="form-control" rows="4" required maxlength="500"
                      placeholder="Short description of the news…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100 fw-600">
            <i class="bi bi-send me-2"></i>Send Notification
          </button>
        </form>
      </div>
    </div>

    <!-- FCM INFO -->
    <div class="tn-card mt-4">
      <div class="tn-card-header"><span><i class="bi bi-info-circle me-2"></i>FCM Topics</span></div>
      <div class="tn-card-body p-0">
        <?php foreach ($topics as $t): ?>
        <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom border-opacity-10">
          <span><?= Helper::e($t['name']) ?></span>
          <code class="small">/topics/<?= Helper::e($t['slug']) ?></code>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- HISTORY -->
  <div class="col-lg-7">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-clock-history me-2"></i>Send History</span></div>
      <?php if (empty($history)): ?>
      <div class="tn-card-body text-center py-5 text-muted">No notifications sent yet</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table tn-table mb-0">
          <thead><tr><th>Title</th><th>Topic</th><th>Sender</th><th>Status</th><th>Time</th></tr></thead>
          <tbody>
          <?php foreach ($history as $h): ?>
          <tr>
            <td>
              <strong><?= Helper::e(mb_substr($h['title'], 0, 40)) ?></strong>
              <div class="text-muted small"><?= Helper::e(mb_substr($h['body'], 0, 50)) ?>…</div>
            </td>
            <td><span class="tn-cat-badge"><?= Helper::e($h['topic_name'] ?? 'General') ?></span></td>
            <td class="text-muted small"><?= Helper::e($h['sender_name'] ?? '—') ?></td>
            <td>
              <?php $sc = ['sent'=>'success','pending'=>'warning','failed'=>'danger'][$h['status']] ?? 'secondary'; ?>
              <span class="badge bg-<?= $sc ?>"><?= ucfirst($h['status']) ?></span>
            </td>
            <td class="text-muted small"><?= Helper::timeAgo($h['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
