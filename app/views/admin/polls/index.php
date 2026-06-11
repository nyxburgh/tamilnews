<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📊 Polls</h2>
    <p class="tn-page-sub"><?= number_format($total) ?> polls</p>
  </div>
  <a href="<?= $r ?>/admin/polls/create" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i> New Poll
  </a>
</div>

<div class="tn-card">
  <div class="tn-card-body p-0">
    <?php if (empty($polls)): ?>
    <div class="text-center py-5 text-muted">No polls yet. Create your first poll!</div>
    <?php else: ?>
    <table class="tn-table">
      <thead><tr>
        <th>Question</th>
        <th>Votes</th>
        <th>Expires</th>
        <th>Status</th>
        <th>By</th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($polls as $p): ?>
      <tr>
        <td>
          <div style="font-weight:600"><?= Helper::e($p['question']) ?></div>
          <?php if ($p['question_ta']): ?><div style="font-size:11px;color:#9A9890"><?= Helper::e($p['question_ta']) ?></div><?php endif; ?>
        </td>
        <td><?= number_format($p['vote_count']) ?></td>
        <td style="font-size:11px"><?= $p['expires_at'] ? date('d M Y', strtotime($p['expires_at'])) : 'Never' ?></td>
        <td>
          <span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
            <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
          </span>
        </td>
        <td style="font-size:11px"><?= Helper::e($p['created_by_name'] ?? '—') ?></td>
        <td>
          <div class="d-flex gap-1">
            <form method="POST" action="<?= $r ?>/admin/polls/toggle/<?= $p['id'] ?>">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-secondary"><?= $p['is_active'] ? 'Pause' : 'Activate' ?></button>
            </form>
            <form method="POST" action="<?= $r ?>/admin/polls/delete/<?= $p['id'] ?>"
                  onsubmit="return confirm('Delete this poll and all votes?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php include VIEW_PATH . '/partials/pagination.php'; ?>
