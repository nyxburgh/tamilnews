<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">🗞️ Print Editions</h2>
    <p class="tn-page-sub">Select articles and prepare editions for printing</p>
  </div>
  <a href="<?= $r ?>/admin/print/create" class="btn btn-primary">
    <i class="bi bi-plus-circle me-2"></i>New Edition
  </a>
</div>

<?php if (empty($editions)): ?>
<div class="tn-card">
  <div class="tn-card-body text-center py-5">
    <div style="font-size:48px;margin-bottom:12px">🗞️</div>
    <p class="text-muted">No editions yet. Create your first print edition.</p>
    <a href="<?= $r ?>/admin/print/create" class="btn btn-primary mt-2">Create Edition</a>
  </div>
</div>
<?php else: ?>
<div class="table-responsive tn-card">
  <table class="table tn-table mb-0">
    <thead>
      <tr>
        <th>Edition</th>
        <th>Date</th>
        <th>Articles</th>
        <th>Status</th>
        <th>Created By</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($editions as $e): ?>
      <tr>
        <td>
          <div class="tn-article-link fw-600"><?= Helper::e($e['title']) ?></div>
          <?php if ($e['notes']): ?>
          <div style="font-size:11px;color:var(--text-muted)"><?= Helper::e($e['notes']) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <div style="font-weight:600"><?= date('d M Y', strtotime($e['edition_date'])) ?></div>
          <div style="font-size:11px;color:var(--text-muted)"><?= date('l', strtotime($e['edition_date'])) ?></div>
        </td>
        <td>
          <span style="font-size:20px;font-weight:700;color:var(--bs-primary)"><?= $e['article_count'] ?></span>
          <span style="font-size:11px;color:var(--text-muted)"> articles</span>
        </td>
        <td>
          <?php
          $badges = ['draft'=>'secondary','ready'=>'success','printed'=>'dark'];
          $labels = ['draft'=>'Draft','ready'=>'Ready','printed'=>'Printed'];
          ?>
          <span class="badge bg-<?= $badges[$e['status']] ?>">
            <?= $labels[$e['status']] ?>
          </span>
        </td>
        <td style="font-size:12px;color:var(--text-muted)"><?= Helper::e($e['created_by_name']) ?></td>
        <td>
          <a href="<?= $r ?>/admin/print/select/<?= $e['id'] ?>"
             class="btn btn-sm btn-outline-primary" title="Select Articles">
            <i class="bi bi-ui-checks"></i> Select
          </a>
          <a href="<?= $r ?>/admin/print/manage/<?= $e['id'] ?>"
             class="btn btn-sm btn-outline-secondary" title="Manage">
            <i class="bi bi-list-ol"></i> Manage
          </a>
          <form action="<?= $r ?>/admin/print/delete/<?= $e['id'] ?>" method="POST" class="d-inline"
                onsubmit="return confirm('Delete this edition?')">
            <?= CSRF::field() ?>
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
