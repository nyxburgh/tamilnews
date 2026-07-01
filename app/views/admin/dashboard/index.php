<?php use App\Core\{Helper, Auth}; ?>

<!-- STATS ROW -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#3b82f6">
      <div class="tn-stat-icon"><i class="bi bi-file-earmark-check"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['published']) ?></div>
      <div class="tn-stat-label">Published</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#f59e0b">
      <div class="tn-stat-icon"><i class="bi bi-hourglass-split"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['review']) ?></div>
      <div class="tn-stat-label">Pending Review</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#8b5cf6">
      <div class="tn-stat-icon"><i class="bi bi-eye"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['views_today']) ?></div>
      <div class="tn-stat-label">Views Today</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#10b981">
      <div class="tn-stat-icon"><i class="bi bi-people"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['total_users']) ?></div>
      <div class="tn-stat-label">Users</div>
    </div>
  </div>
</div>

<!-- SECONDARY STATS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#6b7280">
      <div class="tn-stat-icon"><i class="bi bi-pencil"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['draft']) ?></div>
      <div class="tn-stat-label">Drafts</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#f59e0b">
      <div class="tn-stat-icon"><i class="bi bi-calendar-check"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['scheduled']) ?></div>
      <div class="tn-stat-label">Scheduled</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#C0001A">
      <div class="tn-stat-icon"><i class="bi bi-youtube"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['yt_pending']) ?></div>
      <div class="tn-stat-label">YT Queue</div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="tn-stat-card" style="--accent:#f97316">
      <div class="tn-stat-icon"><i class="bi bi-rss"></i></div>
      <div class="tn-stat-num"><?= number_format($stats['rss_pending']) ?></div>
      <div class="tn-stat-label">RSS Queue</div>
    </div>
  </div>
</div>

<!-- ALERT PILLS -->
<?php if ($stats['review'] > 0 || $stats['yt_pending'] > 0 || $stats['rss_pending'] > 0): ?>
<div class="d-flex flex-wrap gap-2 mb-4">
  <?php if ($stats['review'] > 0): ?>
  <a href="<?= $r ?>/admin/articles?status=review" class="tn-alert-pill warn">
    <i class="bi bi-hourglass-split"></i> <?= $stats['review'] ?> article<?= $stats['review']>1?'s':'' ?> pending review
  </a>
  <?php endif; ?>
  <?php if ($stats['yt_pending'] > 0): ?>
  <a href="<?= $r ?>/admin/youtube/imports" class="tn-alert-pill info">
    <i class="bi bi-youtube"></i> <?= $stats['yt_pending'] ?> YouTube imports waiting
  </a>
  <?php endif; ?>
  <?php if ($stats['rss_pending'] > 0): ?>
  <a href="<?= $r ?>/admin/rss/imports" class="tn-alert-pill info">
    <i class="bi bi-rss"></i> <?= $stats['rss_pending'] ?> RSS items in queue
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-xl-8">

    <!-- REVIEW QUEUE -->
    <?php if (!empty($reviewQueue)): ?>
    <div class="tn-card mb-4" style="border-color:rgba(245,158,11,.3)">
      <div class="tn-card-header" style="background:rgba(245,158,11,.06)">
        <span><i class="bi bi-hourglass-split text-warning me-2"></i>Pending Review <span class="badge bg-warning text-dark ms-1"><?= count($reviewQueue) ?></span></span>
        <a href="<?= $r ?>/admin/articles?status=review" class="btn btn-sm btn-warning">Review All</a>
      </div>
      <div class="table-responsive">
        <table class="table tn-table mb-0">
          <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Submitted</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($reviewQueue as $a): ?>
            <tr>
              <td><a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="tn-article-link"><?= htmlspecialchars(mb_substr($a['title'],0,55)) ?></a></td>
              <td><span class="tn-cat-badge"><?= htmlspecialchars($a['category_name']) ?></span></td>
              <td class="text-muted small"><?= htmlspecialchars($a['author_name']) ?></td>
              <td class="text-muted small"><?= Helper::timeAgo($a['created_at']) ?></td>
              <td>
                <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="btn btn-sm btn-warning fw-600">
                  <i class="bi bi-eye me-1"></i>Review
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- RECENT ARTICLES -->
    <div class="tn-card">
      <div class="tn-card-header">
        <span><i class="bi bi-clock-history me-2"></i>Recent Articles</span>
        <a href="<?= $r ?>/admin/articles" class="btn btn-sm btn-outline-secondary">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table tn-table mb-0">
          <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
            <?php if (empty($recentArticles)): ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">No articles yet. <a href="<?= $r ?>/admin/articles/create">Create one →</a></td></tr>
            <?php endif; ?>
            <?php foreach ($recentArticles as $a): ?>
            <tr>
              <td>
                <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="tn-article-link">
                  <?= htmlspecialchars(mb_substr($a['title'],0,50)) ?>
                </a>
                <?php if ($a['is_breaking']): ?><span class="badge bg-danger ms-1" style="font-size:10px">BREAKING</span><?php endif; ?>
                <?php if (!empty($a['is_premium'])): ?><span class="badge ms-1" style="background:#E8A000;color:#1A1A1A;font-size:10px">🔒</span><?php endif; ?>
              </td>
              <td><span class="tn-cat-badge"><?= htmlspecialchars($a['category_name']) ?></span></td>
              <td class="text-muted small"><?= htmlspecialchars($a['author_name']) ?></td>
              <td>
                <?php $sc=['published'=>'success','draft'=>'secondary','review'=>'warning','scheduled'=>'info','rejected'=>'danger'][$a['status']]??'secondary'; ?>
                <span class="badge bg-<?= $sc ?>"><?= ucfirst($a['status']) ?></span>
              </td>
              <td class="text-muted small"><?= Helper::timeAgo($a['published_at']??$a['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- VIEW TREND CHART -->
    <div class="tn-card mt-4">
      <div class="tn-card-header"><span><i class="bi bi-graph-up me-2"></i>Views — Last 7 Days</span></div>
      <div class="tn-card-body"><canvas id="viewChart" height="80"></canvas></div>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div class="col-xl-4">

    <!-- QUICK ACTIONS -->
    <div class="tn-card mb-4">
      <div class="tn-card-header"><span><i class="bi bi-lightning me-2"></i>Quick Actions</span></div>
      <div class="tn-card-body d-grid gap-2">
        <a href="<?= $r ?>/admin/articles/create" class="btn btn-primary fw-600">
          <i class="bi bi-plus-circle me-2"></i>New Article
        </a>
        <?php if (Auth::can('send_push')): ?>
        <a href="<?= $r ?>/admin/push" class="btn btn-outline-secondary">
          <i class="bi bi-bell me-2"></i>Push Notification
        </a>
        <?php endif; ?>
        <a href="<?= $r ?>/admin/live-blog/create" class="btn btn-outline-danger">
          <i class="bi bi-broadcast me-2"></i>Start Live Blog
        </a>
        <a href="<?= $r ?>/admin/media" class="btn btn-outline-secondary">
          <i class="bi bi-upload me-2"></i>Upload Media
        </a>
        <a href="<?= $r ?>/admin/contributors" class="btn btn-outline-secondary">
          <i class="bi bi-person-badge me-2"></i>Contributors
          <?php if ($stats['pending_contributors'] > 0): ?>
          <span class="badge bg-warning text-dark ms-1"><?= $stats['pending_contributors'] ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= $baseUrl ?>/public/" target="_blank" class="btn btn-outline-secondary">
          <i class="bi bi-box-arrow-up-right me-2"></i>View Site
        </a>
      </div>
    </div>

    <!-- TRENDING -->
    <div class="tn-card mb-4">
      <div class="tn-card-header">
        <span><i class="bi bi-fire me-2"></i>Trending Today</span>
        <a href="<?= $r ?>/admin/analytics" class="btn btn-sm btn-outline-secondary">Analytics</a>
      </div>
      <div class="tn-card-body p-0">
        <?php if (empty($topArticles)): ?>
        <div class="p-3 text-muted text-center small">No data yet today</div>
        <?php endif; ?>
        <?php foreach ($topArticles as $i => $a): ?>
        <div class="tn-top-item">
          <span class="tn-rank"><?= $i+1 ?></span>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="tn-article-link d-block text-truncate">
              <?= htmlspecialchars($a['title']) ?>
            </a>
            <small class="text-muted"><?= number_format($a['period_views']) ?> views</small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- SCHEDULED -->
    <?php if (!empty($scheduledPosts)): ?>
    <div class="tn-card mb-4">
      <div class="tn-card-header">
        <span><i class="bi bi-calendar-check me-2"></i>Scheduled</span>
        <span class="badge bg-primary"><?= count($scheduledPosts) ?></span>
      </div>
      <div class="tn-card-body p-0">
        <?php foreach ($scheduledPosts as $s): ?>
        <div class="tn-top-item">
          <i class="bi bi-clock text-muted"></i>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= $r ?>/admin/articles/edit/<?= $s['id'] ?>" class="tn-article-link d-block text-truncate">
              <?= htmlspecialchars($s['title']) ?>
            </a>
            <small class="text-muted"><?= Helper::formatDate($s['scheduled_at'],'d M, h:i A') ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- LIVE BLOGS -->
    <?php if (!empty($liveBlogs)): ?>
    <div class="tn-card">
      <div class="tn-card-header">
        <span><span style="animation:blink 1s infinite;display:inline-block;width:8px;height:8px;border-radius:50%;background:#C0001A;margin-right:8px"></span>Live Now</span>
      </div>
      <div class="tn-card-body p-0">
        <?php foreach ($liveBlogs as $lb): ?>
        <div class="tn-top-item">
          <i class="bi bi-broadcast text-danger"></i>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= $r ?>/admin/live-blog/manage/<?= $lb['id'] ?>" class="tn-article-link d-block text-truncate">
              <?= htmlspecialchars($lb['title']) ?>
            </a>
            <small class="text-muted"><?= $lb['entry_count'] ?> updates</small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<style>@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const trend  = <?= json_encode(array_values($viewTrend)) ?>;
const labels = trend.map(r => r.date);
const values = trend.map(r => parseInt(r.views||0));
const ctx    = document.getElementById('viewChart');
if (ctx && labels.length) {
  new Chart(ctx, {
    type:'line',
    data:{ labels, datasets:[{ label:'Views', data:values, borderColor:'#3b82f6',
      backgroundColor:'rgba(59,130,246,0.1)', fill:true, tension:0.4, pointRadius:4 }] },
    options:{ responsive:true, plugins:{legend:{display:false}},
      scales:{ x:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#6b7280'}},
               y:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#6b7280'},beginAtZero:true} } }
  });
}
</script>
