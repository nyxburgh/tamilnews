<?php use App\Core\Helper; ?>

<!-- WRITE BOX -->
<div class="portal-write-box" style="background:linear-gradient(135deg,#10b981,#059669)">
  <div>
    <h3>வணக்கம், <?= htmlspecialchars(explode(' ', $contributor['name'] ?? 'Contributor')[0]) ?>! ✍️</h3>
    <p>Share your stories with Tamil Nadu. Submit an article for review.</p>
  </div>
  <a href="<?= $r ?>/contribute/articles/create" class="portal-write-btn" style="color:#10b981">
    ✏️ Submit Article
  </a>
</div>

<!-- STATS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="portal-stat-card" style="--accent:#10b981">
      <div class="portal-stat-icon">📝</div>
      <div class="portal-stat-num"><?= $stats['total'] ?></div>
      <div class="portal-stat-label">Total Submitted</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="portal-stat-card" style="--accent:#1B6B2E">
      <div class="portal-stat-icon">✅</div>
      <div class="portal-stat-num"><?= $stats['published'] ?></div>
      <div class="portal-stat-label">Published</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="portal-stat-card" style="--accent:#A06800">
      <div class="portal-stat-icon">⏳</div>
      <div class="portal-stat-num"><?= $stats['review'] ?></div>
      <div class="portal-stat-label">Under Review</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="portal-stat-card" style="--accent:#6B6A64">
      <div class="portal-stat-icon">📄</div>
      <div class="portal-stat-num"><?= $stats['draft'] ?></div>
      <div class="portal-stat-label">Drafts</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- RECENT ARTICLES -->
  <div class="col-lg-8">
    <div class="portal-card">
      <div class="portal-card-header">
        <span>🕐 Recent Submissions</span>
        <a href="<?= $r ?>/contribute/articles" class="btn btn-sm btn-outline-secondary">View all</a>
      </div>
      <div class="table-responsive">
        <table class="portal-table">
          <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th></th></tr></thead>
          <tbody>
            <?php if (empty($recent)): ?>
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--portal-muted)">
              No articles yet. <a href="<?= $r ?>/contribute/articles/create" style="color:#10b981">Submit your first →</a>
            </td></tr>
            <?php endif; ?>
            <?php foreach ($recent as $a): ?>
            <tr>
              <td>
                <span class="portal-article-link"><?= htmlspecialchars(mb_substr($a['title'], 0, 55)) ?></span>
              </td>
              <td style="font-size:11px;color:#10b981;font-weight:600"><?= htmlspecialchars($a['category_name']) ?></td>
              <td>
                <?php $cls = ['published'=>'portal-badge-published','draft'=>'portal-badge-draft','review'=>'portal-badge-review','rejected'=>'portal-badge-rejected'][$a['status']] ?? 'portal-badge-draft'; ?>
                <span class="portal-badge <?= $cls ?>"><?= ucfirst($a['status']) ?></span>
              </td>
              <td style="font-size:12px;color:var(--portal-muted)"><?= Helper::timeAgo($a['created_at']) ?></td>
              <td>
                <?php if (in_array($a['status'], ['draft'])): ?>
                <a href="<?= $r ?>/contribute/articles/edit/<?= $a['id'] ?>" style="color:var(--portal-muted)"><i class="bi bi-pencil"></i></a>
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
    <!-- QUICK ACTIONS -->
    <div class="portal-card mb-3">
      <div class="portal-card-header">⚡ Quick Actions</div>
      <div class="portal-card-body d-grid gap-2">
        <a href="<?= $r ?>/contribute/articles/create" class="btn fw-600" style="background:#10b981;color:white">
          ✏️ Submit New Article
        </a>
        <a href="<?= $r ?>/contribute/articles?status=draft" class="btn btn-outline-secondary">📄 My Drafts</a>
        <a href="<?= $r ?>/contribute/articles?status=review" class="btn btn-outline-secondary">⏳ Review Status</a>
        <a href="<?= $r ?>/contribute/articles?status=published" class="btn btn-outline-secondary">✅ Published</a>
      </div>
    </div>

    <!-- ASSIGNED CATEGORIES -->
    <?php if (!empty($categories)): ?>
    <div class="portal-card mb-3">
      <div class="portal-card-header">📂 Your Categories</div>
      <div class="portal-card-body" style="padding:12px">
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          <?php foreach ($categories as $cat): ?>
          <span style="display:inline-block;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;background:rgba(16,185,129,.1);color:#10b981;font-family:'Noto Sans Tamil',sans-serif">
            <?= htmlspecialchars($cat['name_tamil'] ?: $cat['name']) ?>
          </span>
          <?php endforeach; ?>
        </div>
        <?php if (empty($categories)): ?>
        <p style="color:var(--portal-muted);font-size:13px;margin:0">No categories assigned yet. Contact admin.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- GUIDELINES -->
    <div class="portal-card">
      <div class="portal-card-header">📋 Submission Guidelines</div>
      <div class="portal-card-body">
        <ul style="list-style:none;padding:0;margin:0;font-size:13px;line-height:2.2;color:var(--portal-muted)">
          <li>✅ Original content only</li>
          <li>✅ Minimum 200 words</li>
          <li>✅ Add relevant tags</li>
          <li>✅ Review within 48 hours</li>
          <li>❌ No duplicate content</li>
          <li>❌ No promotional material</li>
        </ul>
      </div>
    </div>
  </div>
</div>
