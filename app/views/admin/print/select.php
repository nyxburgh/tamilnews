<?php use App\Core\{Helper, CSRF}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📰 Select Articles</h2>
    <p class="tn-page-sub">
      <strong><?= Helper::e($edition['title']) ?></strong> ·
      <?= date('d M Y', strtotime($edition['edition_date'])) ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= $r ?>/admin/print/manage/<?= $edition['id'] ?>" class="btn btn-success">
      <i class="bi bi-list-ol me-1"></i> Manage Selected
      <span class="badge bg-white text-success ms-1" id="selCount"><?= count($selectedIds) ?></span>
    </a>
    <a href="<?= $r ?>/admin/print" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i>
    </a>
  </div>
</div>

<!-- FILTERS -->
<div class="tn-card mb-3">
  <div class="tn-card-body py-2">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
      <div>
        <label class="form-label fw-600 small mb-1">Category</label>
        <select name="category_id" class="form-select form-select-sm" style="width:160px">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= ($filters['category_id']??'') == $cat['id'] ? 'selected' : '' ?>>
            <?= Helper::e($cat['name_tamil'] ?: $cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label fw-600 small mb-1">From Date</label>
        <input type="date" name="from_date" class="form-control form-control-sm"
               value="<?= Helper::e($filters['from_date'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label fw-600 small mb-1">Search</label>
        <input type="text" name="q" class="form-control form-control-sm"
               placeholder="Search articles..." value="<?= Helper::e($_GET['q'] ?? '') ?>"
               style="width:180px">
      </div>
      <button class="btn btn-sm btn-outline-secondary">Filter</button>
      <a href="<?= $r ?>/admin/print/select/<?= $edition['id'] ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
    </form>
  </div>
</div>

<!-- ARTICLES LIST -->
<div class="tn-card">
  <?php if (empty($articles)): ?>
  <div class="tn-card-body text-center py-5 text-muted">No articles found.</div>
  <?php else: ?>
  <table class="table tn-table mb-0">
    <thead>
      <tr>
        <th style="width:36px"></th>
        <th>Article</th>
        <th>Category</th>
        <th>Date</th>
        <th>Words</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articles as $a):
        $inEdition = in_array($a['id'], $selectedIds);
      ?>
      <tr class="art-row <?= $inEdition ? 'table-success' : '' ?>" id="row-<?= $a['id'] ?>">
        <td class="text-center">
          <button type="button"
                  class="btn btn-sm <?= $inEdition ? 'btn-success' : 'btn-outline-secondary' ?> toggle-btn"
                  data-article="<?= $a['id'] ?>"
                  data-edition="<?= $edition['id'] ?>"
                  data-selected="<?= $inEdition ? '1' : '0' ?>"
                  title="<?= $inEdition ? 'Remove from edition' : 'Add to edition' ?>">
            <i class="bi bi-<?= $inEdition ? 'check-lg' : 'plus' ?>"></i>
          </button>
        </td>
        <td>
          <div style="font-weight:600;font-size:13px"><?= Helper::e(mb_substr($a['title'],0,70)) ?></div>
          <?php if ($a['excerpt']): ?>
          <div style="font-size:11px;color:var(--text-muted)"><?= Helper::e(mb_substr($a['excerpt'],0,80)) ?></div>
          <?php endif; ?>
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px">
            By <?= Helper::e($a['author_name'] ?? '—') ?>
          </div>
        </td>
        <td><span class="tn-cat-badge"><?= Helper::e($a['category_name'] ?? '—') ?></span></td>
        <td style="font-size:12px;white-space:nowrap"><?= Helper::timeAgo($a['published_at']) ?></td>
        <td style="font-size:12px"><?= $a['word_count'] ? number_format($a['word_count']) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php
  $qs = http_build_query(array_filter(['category_id'=>$filters['category_id']??'','from_date'=>$filters['from_date']??'','q'=>$_GET['q']??'']));
  $queryExtra = $qs ? '&'.$qs : '';
  include VIEW_PATH . '/partials/pagination.php';
  ?>
  <?php endif; ?>
</div>

<!-- CSRF for AJAX -->
<input type="hidden" id="csrfToken" value="<?= \App\Core\CSRF::token() ?>">

<script>
let selCount = <?= count($selectedIds) ?>;

document.querySelectorAll('.toggle-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const artId    = this.dataset.article;
    const edId     = this.dataset.edition;
    const selected = this.dataset.selected === '1';
    const action   = selected ? 'remove' : 'add';
    const row      = document.getElementById('row-' + artId);
    const csrf     = document.getElementById('csrfToken').value;

    // Optimistic UI update
    if (action === 'add') {
      this.classList.replace('btn-outline-secondary','btn-success');
      this.innerHTML = '<i class="bi bi-check-lg"></i>';
      this.dataset.selected = '1';
      row.classList.add('table-success');
      selCount++;
    } else {
      this.classList.replace('btn-success','btn-outline-secondary');
      this.innerHTML = '<i class="bi bi-plus"></i>';
      this.dataset.selected = '0';
      row.classList.remove('table-success');
      selCount--;
    }
    document.getElementById('selCount').textContent = selCount;

    fetch(`<?= $r ?>/admin/print/toggle-article/` + edId, {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: `article_id=${artId}&action=${action}&csrf_token=${csrf}`
    }).catch(() => {
      // Revert on error
      location.reload();
    });
  });
});
</script>
