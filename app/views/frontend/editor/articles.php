<?php
use App\Core\Helper;
$isContrib = $isContributor ?? false;
$roleColors = ['admin'=>'#C0001A','editor'=>'#1877F2','reporter'=>'#1B6B2E','contributor'=>'#10b981'];
$roleColor  = $roleColors[$role] ?? '#C0001A';
$writeUrl   = $isContrib ? ($r.'/contribute/articles/create') : ($r.'/admin/articles/create');
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
  <h2 style="font-size:22px;font-weight:700;margin:0">My Articles</h2>
  <a href="<?= $writeUrl ?>" class="btn fw-600" style="background:<?= $roleColor ?>;color:white">✏️ Write New</a>
</div>

<!-- STATUS TABS -->
<div class="portal-status-tabs" style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
  <?php foreach ([''=> 'All','published'=>'✅ Published','review'=>'⏳ Review','draft'=>'📄 Drafts','rejected'=>'❌ Rejected'] as $val=>$label): ?>
  <a href="<?= $r ?>/portal/articles<?= $val ? '?status='.$val : '' ?>"
     style="padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
            <?= $status===$val ? "background:{$roleColor};color:white" : 'background:#F0EFE9;color:#6B6A64' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="portal-card">
  <div class="table-responsive"><table class="portal-table">
      <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Views</th><th>Words</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (empty($articles)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--portal-muted)">
          No articles found. <a href="<?= $writeUrl ?>" style="color:<?= $roleColor ?>">Start writing →</a>
        </td></tr>
        <?php endif; ?>
        <?php foreach ($articles as $a): ?>
        <?php
        $canEdit  = in_array($a['status'], ['draft','rejected']);
        $editUrl  = $isContrib
            ? ($r.'/contribute/articles/edit/'.$a['id'])
            : ($r.'/admin/articles/edit/'.$a['id']);
        $cls = ['published'=>'portal-badge-published','draft'=>'portal-badge-draft',
                'review'=>'portal-badge-review','rejected'=>'portal-badge-rejected',
                'scheduled'=>'portal-badge-scheduled'][$a['status']] ?? 'portal-badge-draft';
        ?>
        <tr>
          <td>
            <span class="portal-article-link" style="font-weight:500"><?= htmlspecialchars(mb_substr($a['title'],0,60)) ?></span>

            <?php if (!empty($a['is_premium'])): ?>
            <span style="font-size:10px;background:#E8A000;color:#1A1A1A;padding:1px 6px;border-radius:3px;margin-left:4px">🔒 Premium</span>
            <?php endif; ?>
          </td>
          <td style="font-size:11px;color:<?= $roleColor ?>;font-weight:600"><?= htmlspecialchars($a['category_name']) ?></td>
          <td><span class="portal-badge <?= $cls ?>"><?= ucfirst($a['status']) ?></span></td>
          <td style="font-size:13px;color:var(--portal-muted)"><?= number_format($a['view_count']) ?></td>
          <td style="font-size:12px;color:var(--portal-muted)"><?= !empty($a['word_count']) ? number_format($a['word_count']).' w' : '—' ?></td>
          <td style="font-size:12px;color:var(--portal-muted)"><?= Helper::timeAgo($a['created_at']) ?></td>
          <td>
            <?php if ($canEdit): ?>
            <a href="<?= $editUrl ?>" class="btn btn-sm btn-outline-secondary">✏️ Edit</a>
            <?php elseif ($a['status'] === 'published'): ?>
            <a href="<?= $r ?>/article/<?= htmlspecialchars($a['slug']) ?>" target="_blank"
               class="btn btn-sm btn-outline-success">👁️ View</a>
            <?php elseif ($a['status'] === 'review'): ?>
            <span style="font-size:11px;color:var(--portal-muted)">⏳ In review</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php
$queryExtra = $status ? '&status='.$status : '';
include VIEW_PATH . '/partials/pagination.php';
?>
</div>
