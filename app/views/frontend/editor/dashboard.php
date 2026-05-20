<?php
use App\Core\Helper;
$roleColors = ['admin'=>'#C0001A','editor'=>'#1877F2','reporter'=>'#1B6B2E','contributor'=>'#10b981'];
$roleColor  = $roleColors[$role] ?? '#C0001A';
$isContrib  = $isContributor ?? false;
$firstName  = explode(' ', $isContrib ? (\App\Core\Session::get('contributor')['name'] ?? 'User') : (\App\Core\Auth::user()['name'] ?? 'User'))[0];
$writeUrl   = $isContrib ? ($r . '/contribute/articles/create') : ($r . '/admin/articles/create');
?>

<!-- WELCOME BAR -->
<div class="portal-write-box" style="background:linear-gradient(135deg,<?= $roleColor ?> 0%, <?= $roleColor ?>cc 100%)">
  <div>
    <h3>வணக்கம், <?= htmlspecialchars($firstName) ?>! 👋</h3>
    <p style="opacity:.85;font-size:13px">
      <?php if ($role==='editor'): ?>Your articles + review queue are ready.
      <?php elseif ($role==='reporter'): ?>Ready to write your next story?
      <?php else: ?>Share your articles with Tamil Nadu.<?php endif; ?>
    </p>
    <?php if (!empty($userBadges)): ?>
    <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
      <?php foreach ($userBadges as $badge): ?>
      <span style="background:rgba(255,255,255,.2);color:white;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600">
        <?= htmlspecialchars($badge['icon'] ?? '🏅') ?> <?= htmlspecialchars($badge['name']) ?>
      </span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <a href="<?= $writeUrl ?>" class="portal-write-btn" style="color:<?= $roleColor ?>">
    ✏️ <?= $role === 'contributor' ? 'Submit Article' : 'Write Article' ?>
  </a>
</div>

<!-- STATS -->
<div class="row g-3 mb-4">
  <?php
  $statCards = [
    ['total',     '📝', 'Submitted',           '#6b7280'],
    ['published', '✅', 'Published',            '#1B6B2E'],
    ['review',    '⏳', 'Under Review',         '#A06800'],
    ['draft',     '📄', 'Drafts',               '#4b5563'],
  ];
  ?>
  <?php foreach ($statCards as [$key, $icon, $label, $color]): ?>
  <div class="col-6 col-lg-3">
    <div class="portal-stat-card" style="--accent:<?= $color ?>">
      <div class="portal-stat-icon"><?= $icon ?></div>
      <div class="portal-stat-num"><?= number_format($stats[$key]) ?></div>
      <div class="portal-stat-label"><?= $label ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <!-- MAIN -->
  <div class="col-lg-8">

    <!-- REVIEW QUEUE — editors only -->
    <?php if (!empty($reviewQueue) && in_array($role, ['admin','editor'])): ?>
    <div class="portal-card mb-4" style="border-color:rgba(160,104,0,.3)">
      <div class="portal-card-header" style="background:rgba(254,244,224,.5)">
        <span style="color:#A06800">⏳ Pending Review <span style="background:#A06800;color:white;padding:2px 8px;border-radius:10px;font-size:11px;margin-left:6px"><?= count($reviewQueue) ?></span></span>
        <a href="<?= $r ?>/admin/articles?status=review" class="btn btn-sm btn-warning">Review All</a>
      </div>
      <div class="table-responsive">
        <table class="portal-table">
          <thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Submitted</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($reviewQueue as $a): ?>
            <tr>
              <td><a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="portal-article-link"><?= htmlspecialchars(mb_substr($a['title'],0,55)) ?></a></td>
              <td style="font-size:12px;color:var(--portal-muted)"><?= htmlspecialchars($a['author_name']) ?></td>
              <td><span style="font-size:11px;color:#C0001A;font-weight:600"><?= htmlspecialchars($a['category_name']) ?></span></td>
              <td style="font-size:12px;color:var(--portal-muted)"><?= Helper::timeAgo($a['created_at']) ?></td>
              <td><a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="btn btn-sm btn-warning fw-600">Review</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- MY RECENT ARTICLES -->
    <div class="portal-card">
      <div class="portal-card-header">
        <span>🕐 My Recent Articles</span>
        <a href="<?= $r ?>/portal/articles" class="btn btn-sm btn-outline-secondary">View all</a>
      </div>
      <div class="table-responsive">
        <table class="portal-table">
          <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($recent)): ?>
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--portal-muted)">
              No articles yet. <a href="<?= $writeUrl ?>" style="color:<?= $roleColor ?>">Write your first →</a>
            </td></tr>
            <?php endif; ?>
            <?php foreach ($recent as $a): ?>
            <?php
            $myArticle = true; // they can only see their own from this view
            $canEdit   = in_array($a['status'], ['draft','rejected']);
            $editUrl   = $isContrib
                ? ($r . '/contribute/articles/edit/' . $a['id'])
                : ($r . '/admin/articles/edit/' . $a['id']);
            $cls = ['published'=>'portal-badge-published','draft'=>'portal-badge-draft',
                    'review'=>'portal-badge-review','rejected'=>'portal-badge-rejected',
                    'scheduled'=>'portal-badge-scheduled'][$a['status']] ?? 'portal-badge-draft';
            ?>
            <tr>
              <td>
                <span class="portal-article-link"><?= htmlspecialchars(mb_substr($a['title'],0,50)) ?></span>
                <?php if (!empty($a['pending_edit'])): ?>
                <span style="font-size:10px;background:#FEF4E0;color:#A06800;padding:1px 6px;border-radius:3px;margin-left:4px">✏️ Edit Pending</span>
                <?php endif; ?>
                <?php if (!empty($a['is_premium'])): ?>
                <span style="font-size:10px;background:#E8A000;color:#1A1A1A;padding:1px 6px;border-radius:3px;margin-left:4px">🔒</span>
                <?php endif; ?>
              </td>
              <td style="font-size:11px;color:<?= $roleColor ?>;font-weight:600"><?= htmlspecialchars($a['category_name']) ?></td>
              <td><span class="portal-badge <?= $cls ?>"><?= ucfirst($a['status']) ?></span></td>
              <td style="font-size:12px;color:var(--portal-muted)"><?= Helper::timeAgo($a['created_at']) ?></td>
              <td>
                <?php if ($canEdit): ?>
                <a href="<?= $editUrl ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <?php elseif ($a['status'] === 'review'): ?>
                <span style="font-size:11px;color:var(--portal-muted)">In review…</span>
                <?php elseif ($a['status'] === 'published'): ?>
                <a href="<?= $r ?>/article/<?= htmlspecialchars($a['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-success">View</a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div class="col-lg-4">
    <div class="portal-card mb-3">
      <div class="portal-card-header">⚡ Quick Actions</div>
      <div class="portal-card-body d-grid gap-2">
        <a href="<?= $writeUrl ?>" class="btn fw-600" style="background:<?= $roleColor ?>;color:white">✏️ Write Article</a>
        <a href="<?= $r ?>/portal/articles?status=draft"     class="btn btn-outline-secondary">📄 My Drafts</a>
        <a href="<?= $r ?>/portal/articles?status=review"    class="btn btn-outline-secondary">⏳ Under Review</a>
        <a href="<?= $r ?>/portal/articles?status=published" class="btn btn-outline-secondary">✅ Published</a>
        <?php if ($role === 'editor'): ?>
        <a href="<?= $r ?>/admin/articles?status=review"     class="btn btn-outline-warning">📋 All Pending Reviews</a>
        <?php endif; ?>
        <a href="<?= $r ?>/portal/profile" class="btn btn-outline-secondary">👤 My Profile</a>
      </div>
    </div>

    <!-- CATEGORIES -->
    <?php if (!empty($categories)): ?>
    <div class="portal-card mb-3">
      <div class="portal-card-header">📂 Categories</div>
      <div class="portal-card-body" style="padding:12px">
        <div style="display:flex;flex-wrap:wrap;gap:6px">
          <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
          <a href="<?= $r ?>/admin/articles?category_id=<?= $cat['id'] ?>"
             style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;background:<?= $roleColor ?>1A;color:<?= $roleColor ?>;font-family:'Noto Sans Tamil',sans-serif">
            <?= htmlspecialchars($cat['name_tamil'] ?: $cat['name']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- STATUS INFO -->
    <div class="portal-card">
      <div class="portal-card-header">ℹ️ How It Works</div>
      <div class="portal-card-body">
        <ul style="list-style:none;padding:0;margin:0;font-size:13px;line-height:2.2;color:var(--portal-muted)">
          <li>✏️ Write → saved as draft</li>
          <li>📤 Submit → goes to review</li>
          <?php if (in_array($role,['editor','admin'])): ?>
          <li>✅ Review → you can publish</li>
          <?php else: ?>
          <li>✅ Editor reviews → publishes</li>
          <?php endif; ?>
          <li>❌ Rejected → edit and resubmit</li>
          <li>✏️ Edit published → needs approval</li>
          <li>🗑️ Delete → not allowed</li>
        </ul>
      </div>
    </div>
  </div>
</div>
