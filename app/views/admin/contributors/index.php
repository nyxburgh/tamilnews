<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">Contributors</h2>
    <p class="tn-page-sub"><?= number_format($total) ?> registered<?= $pending ? " · <span class='text-warning'>{$pending} pending approval</span>" : '' ?></p>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContribModal">
    <i class="bi bi-person-plus me-2"></i>Add Contributor
  </button>
</div>

<?php if ($pending > 0): ?>
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
  <i class="bi bi-hourglass-split fs-5"></i>
  <div><?= $pending ?> contributor(s) have signed in with Google and are awaiting your approval.</div>
</div>
<?php endif; ?>

<div class="tn-card">
  <div class="table-responsive">
    <table class="table tn-table mb-0">
      <thead>
        <tr>
          <th>Contributor</th>
          <th>Email</th>
          <th>Google</th>
          <th>Articles</th>
          <th>Published</th>
          <th>Status</th>
          <th>Last Login</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($contributors)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted">No contributors yet</td></tr>
        <?php endif; ?>
        <?php foreach ($contributors as $c): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <?php if ($c['avatar']): ?>
              <img src="<?= Helper::e($c['avatar']) ?>" class="tn-google-avatar" alt="">
              <?php else: ?>
              <div class="tn-user-avatar-sm"><?= strtoupper(substr($c['name'], 0, 1)) ?></div>
              <?php endif; ?>
              <div>
                <strong><?= Helper::e($c['name']) ?></strong>
                <?php if ($c['bio']): ?>
                <div class="text-muted small"><?= Helper::e(mb_substr($c['bio'], 0, 40)) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td class="text-muted small"><?= Helper::e($c['email']) ?></td>
          <td>
            <?php if ($c['google_id']): ?>
            <span class="badge bg-success"><i class="bi bi-google me-1"></i>Linked</span>
            <?php else: ?>
            <span class="badge bg-secondary">Not linked</span>
            <?php endif; ?>
          </td>
          <td><span class="badge bg-secondary"><?= $c['total_articles'] ?></span></td>
          <td><span class="badge bg-success"><?= $c['published_count'] ?></span></td>
          <td>
            <span class="badge <?= $c['is_active'] ? 'bg-success' : 'bg-warning text-dark' ?>">
              <?= $c['is_active'] ? 'Active' : 'Pending' ?>
            </span>
          </td>
          <td class="text-muted small"><?= $c['last_login'] ? Helper::timeAgo($c['last_login']) : 'Never' ?></td>
          <td>
            <a href="<?= $r ?>/admin/contributors/show/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="View articles">
              <i class="bi bi-eye"></i>
            </a>
            <?php if (!$c['is_active']): ?>
            <form action="<?= $r ?>/admin/contributors/approve/<?= $c['id'] ?>" method="POST" class="d-inline">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check2"></i></button>
            </form>
            <?php else: ?>
            <form action="<?= $r ?>/admin/contributors/reject/<?= $c['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Deactivate this contributor?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-warning" title="Deactivate"><i class="bi bi-pause"></i></button>
            </form>
            <?php endif; ?>
            <form action="<?= $r ?>/admin/contributors/delete/<?= $c['id'] ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Delete contributor and all their articles?')">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div><!-- close tn-card -->
  <?php
$queryExtra = '';
include VIEW_PATH . '/partials/pagination.php';
?>