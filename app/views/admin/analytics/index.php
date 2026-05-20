<?php use App\Core\Helper; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">Analytics</h2>
  <div class="d-flex gap-2">
    <?php foreach (['today'=>'Today', 'week'=>'This Week', 'month'=>'This Month'] as $val => $label): ?>
    <a href="<?= $r ?>/admin/analytics?period=<?= $val ?>"
       class="btn btn-sm <?= $period === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- STAT -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="tn-stat-card" style="--accent:#3b82f6">
      <div class="tn-stat-icon"><i class="bi bi-eye"></i></div>
      <div class="tn-stat-num"><?= number_format($viewsToday) ?></div>
      <div class="tn-stat-label">Views Today</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="tn-stat-card" style="--accent:#10b981">
      <div class="tn-stat-icon"><i class="bi bi-bar-chart"></i></div>
      <div class="tn-stat-num"><?= number_format(array_sum(array_column($topArticles, 'period_views'))) ?></div>
      <div class="tn-stat-label">Period Views</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="tn-stat-card" style="--accent:#f59e0b">
      <div class="tn-stat-icon"><i class="bi bi-file-earmark-text"></i></div>
      <div class="tn-stat-num"><?= count($topArticles) ?></div>
      <div class="tn-stat-label">Articles Tracked</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- TREND CHART -->
  <div class="col-xl-7">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-graph-up me-2"></i>Views — Last 30 Days</span></div>
      <div class="tn-card-body">
        <canvas id="analyticsChart" height="120"></canvas>
      </div>
    </div>
  </div>

  <!-- TOP ARTICLES -->
  <div class="col-xl-5">
    <div class="tn-card">
      <div class="tn-card-header"><span><i class="bi bi-trophy me-2"></i>Top Articles</span></div>
      <div class="tn-card-body p-0">
        <?php if (empty($topArticles)): ?>
        <div class="p-4 text-muted text-center">No data for this period</div>
        <?php endif; ?>
        <?php foreach ($topArticles as $i => $a): ?>
        <div class="tn-top-item">
          <span class="tn-rank"><?= $i + 1 ?></span>
          <div class="flex-grow-1 min-w-0">
            <a href="<?= $r ?>/admin/articles/edit/<?= $a['id'] ?>" class="tn-article-link d-block text-truncate">
              <?= Helper::e($a['title']) ?>
            </a>
            <small class="text-muted">
              <?= Helper::e($a['category_name']) ?> ·
              <strong><?= number_format($a['period_views']) ?></strong> views
            </small>
          </div>
          <div class="tn-view-bar" style="--pct:<?= $topArticles[0]['period_views'] > 0 ? round($a['period_views']/$topArticles[0]['period_views']*100) : 0 ?>%"></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const trend  = <?= json_encode(array_values($viewTrend)) ?>;
const labels = trend.map(r => r.date);
const values = trend.map(r => parseInt(r.views));
new Chart(document.getElementById('analyticsChart'), {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      label: 'Views',
      data: values,
      backgroundColor: 'rgba(59,130,246,0.7)',
      borderRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b7280', maxTicksLimit: 10 } },
      y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b7280' }, beginAtZero: true }
    }
  }
});
</script>
