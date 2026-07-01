<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">📰 Sponsored News Queue</h2>
</div>

<?php if (empty($queue)): ?>
<div class="tn-card text-center py-5">
  <p class="text-muted">No sponsored articles pending review.</p>
</div>
<?php else: ?>
<div class="tn-card">
  <div class="tn-card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Business</th><th>Package</th><th>Title</th><th>Submitted</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($queue as $item): ?>
        <tr>
          <td><?= Helper::e($item['business_name']) ?></td>
          <td><span class="badge bg-secondary"><?= Helper::e($item['package_name'] ?? '—') ?></span></td>
          <td>
            <a href="<?= $r ?>/admin/articles/edit/<?= $item['article_id'] ?>" target="_blank">
              <?= Helper::e(mb_substr($item['title'] ?? '—', 0, 60)) ?>
            </a>
          </td>
          <td class="small text-muted"><?= substr($item['created_at'] ?? '', 0, 10) ?></td>
          <td>
            <div class="d-flex gap-1">
              <form method="POST" action="<?= $r ?><?= $adsBase ?>/sponsored-news/<?= $item['id'] ?>/approve">
                <?= CSRF::field() ?>
                <button class="btn btn-success btn-sm">✓ Approve</button>
              </form>
              <form method="POST" action="<?= $r ?><?= $adsBase ?>/sponsored-news/<?= $item['id'] ?>/reject">
                <?= CSRF::field() ?>
                <button class="btn btn-outline-danger btn-sm">✗ Reject</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
