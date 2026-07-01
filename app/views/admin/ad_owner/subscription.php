<?php use App\Core\{Helper, CSRF};
$quota    = (new \App\Models\AdPackageModel())->canPostSponsoredNews((int)$ad['id']);
$newsList = (new \App\Models\AdPackageModel())->sponsoredNewsByAd((int)$ad['id']);
$news     = $newsList;
$canPostNews    = !empty($ad['allow_news']) && $quota['allowed'];
$newsBlockReason = $canPostNews ? null : ($quota['reason'] ?? null);
$canChangeImage  = false;
if (!empty($ad['allow_images'])) {
    if (empty($ad['image_last_changed'])) {
        $canChangeImage = true;
    } else {
        $daysSince = (int)floor((time() - strtotime($ad['image_last_changed'])) / 86400);
        $canChangeImage = $daysSince >= (int)($ad['image_change_days'] ?? 30);
    }
}
?>
<div class="portal-page-header">
  <div>
    <h2 class="portal-page-title"><?= Helper::e($ad['business_name']) ?></h2>
    <p class="portal-page-sub"><?= Helper::e($ad['package_name'] ?? '—') ?> · Until <?= $ad['valid_until'] ?? '—' ?></p>
  </div>
  <a href="<?= $r ?>/portal/my-ads" class="portal-back-btn">← Dashboard</a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= number_format($ad['impression_count'] ?? 0) ?></div>
      <div class="portal-stat-label">Impressions</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= number_format($ad['click_count'] ?? 0) ?></div>
      <div class="portal-stat-label">Clicks</div>
    </div>
  </div>
  <?php if (!empty($ad['allow_news'])): ?>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <?php $quota = (int)($ad['news_quota'] ?? 0); $used = (int)($ad['news_used'] ?? 0); ?>
      <div class="portal-stat-num"><?= $used ?>/<?= $quota ?></div>
      <div class="portal-stat-label">News Used</div>
    </div>
  </div>
  <?php endif; ?>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <?php $daysLeft = $ad['valid_until'] ? max(0,(int)floor((strtotime($ad['valid_until'])-time())/86400)) : 0; ?>
      <div class="portal-stat-num"><?= $daysLeft ?>d</div>
      <div class="portal-stat-label">Days Left</div>
    </div>
  </div>
</div>

<!-- Ad Images -->
<div class="portal-card mb-4">
  <div class="portal-card-header">
    <span><i class="bi bi-images me-2"></i>Ad Images</span>
    <?php if (!empty($ad['allow_images'])): ?>
    <small class="portal-card-header-note">
      <?php if ($canChangeImage): ?>
        Image update available.
      <?php elseif (!empty($ad['image_last_changed'])): ?>
        Next update: <?= date('d M Y', strtotime($ad['image_last_changed'].' +'.(int)($ad['image_change_days'] ?? 30).' days')) ?>
      <?php endif; ?>
    </small>
    <?php endif; ?>
  </div>
  <div class="portal-card-body">
    <?php if (!empty($ad['images'])): ?>
    <div class="row g-2 mb-3">
      <?php foreach ($ad['images'] as $img): ?>
      <div class="col-6 col-md-3">
        <img src="<?= rtrim(ASSET_URL,'/') . '/public' . Helper::e($img['filepath']) ?>" class="ad-owner-img" loading="lazy">
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($ad['allow_images']) && $canChangeImage): ?>
    <form method="POST" action="<?= $r ?>/portal/my-ads/<?= $ad['id'] ?>/upload-image"
          enctype="multipart/form-data">
      <?= CSRF::field() ?>
      <div class="d-flex gap-2">
        <input type="file" name="image" accept="image/*" class="form-control form-control-sm" required>
        <button class="btn btn-primary btn-sm text-nowrap">Upload</button>
      </div>
      <div class="form-text">JPG/PNG/WebP max 5MB. Goes live after approval.</div>
    </form>
    <?php elseif (empty($ad['allow_images'])): ?>
    <p class="text-muted small mb-0">Image upload not included in your package.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Sponsored News -->
<?php if (!empty($ad['allow_news'])): ?>
<?php
// Load this ad's sponsored news
$_db = \App\Core\Database::getInstance();
$_sn = $_db->prepare(
    "SELECT sn.*, a.title, a.status AS art_status, a.published_at
     FROM tn_sponsored_news sn
     JOIN tn_articles a ON a.id = sn.article_id
     WHERE sn.ad_id = ?
     ORDER BY sn.created_at DESC"
);
$_sn->execute([$ad['id']]);
$sponsoredNews = $_sn->fetchAll(\PDO::FETCH_ASSOC);

$quota    = (int)($ad['news_quota'] ?? 0);
$used     = (int)($ad['news_used'] ?? 0);
$interval = (int)($ad['news_interval_days'] ?? 0);
$canWrite = $canPostNews;
?>
<div class="portal-card mb-4">
  <div class="portal-card-header">
    <span><i class="bi bi-newspaper me-2"></i>Sponsored Articles
      <span class="ms-2 badge bg-secondary small"><?= $used ?>/<?= $quota ?></span>
    </span>
    <?php if ($canWrite): ?>
    <a href="<?= $r ?>/portal/my-ads/<?= $ad['id'] ?>/write-news"
       class="btn btn-primary btn-sm">+ Write Article</a>
    <?php elseif ($newsBlockReason === 'quota_exhausted'): ?>
    <span class="portal-card-header-note text-danger">Quota exhausted</span>
    <?php elseif ($newsBlockReason === 'too_soon'): ?>
    <span class="portal-card-header-note">Wait <?= $interval ?> days between articles</span>
    <?php elseif ($newsBlockReason === 'not_allowed'): ?>
    <span class="portal-card-header-note">Not in your package</span>
    <?php endif; ?>
  </div>
  <div class="portal-card-body">
    <?php if (empty($sponsoredNews)): ?>
    <p class="text-muted small mb-0">No sponsored articles yet.
      <?= $canWrite ? 'Click "+ Write Article" to get started.' : '' ?></p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead>
          <tr><th>Title</th><th>Status</th><th>Published</th></tr>
        </thead>
        <tbody>
          <?php foreach ($sponsoredNews as $sn):
            $bs = 'secondary';
            if ($sn['status']==='published')        $bs='success';
            elseif ($sn['status']==='approved')     $bs='info';
            elseif ($sn['status']==='pending_approval') $bs='warning';
            elseif ($sn['status']==='rejected')     $bs='danger';
          ?>
          <tr>
            <td class="small"><?= Helper::e(mb_substr($sn['title'],0,50)) ?></td>
            <td><span class="badge bg-<?= $bs ?> small"><?= ucfirst(str_replace('_',' ',$sn['status'])) ?></span></td>
            <td class="small text-muted"><?= $sn['published_at'] ? substr($sn['published_at'],0,10) : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= number_format($ad['impression_count'] ?? 0) ?></div>
      <div class="portal-stat-label">Impressions</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <div class="portal-stat-num"><?= number_format($ad['click_count'] ?? 0) ?></div>
      <div class="portal-stat-label">Clicks</div>
    </div>
  </div>
  <?php if (!empty($sub['allow_news'])): ?>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <?php $quota = $sub['news_quota'] ?: ($sub['selected_days'] ?? 0); ?>
      <div class="portal-stat-num"><?= (int)$sub['news_used'] ?>/<?= $quota ?></div>
      <div class="portal-stat-label">News Used</div>
    </div>
  </div>
  <?php endif; ?>
  <div class="col-6 col-md-3">
    <div class="portal-stat-card">
      <?php $daysLeft = max(0, (int)floor((strtotime($sub['valid_until']) - time()) / 86400)); ?>
      <div class="portal-stat-num"><?= $daysLeft ?>d</div>
      <div class="portal-stat-label">Days Left</div>
    </div>
  </div>
</div>

<!-- Ad Images -->
<div class="portal-card mb-4">
  <div class="portal-card-header">
    <span><i class="bi bi-images me-2"></i>Ad Images</span>
    <?php if (!empty($sub['allow_images'])): ?>
    <small class="portal-card-header-note">
      <?php if ($canChangeImage): ?>
        Image update available now.
      <?php else: ?>
        Next update: <?= !empty($sub['image_last_changed'])
          ? date('d M Y', strtotime($sub['image_last_changed'].' +'.$sub['image_change_days'].' days'))
          : '—' ?>
      <?php endif; ?>
    </small>
    <?php endif; ?>
  </div>
  <div class="portal-card-body">
    <?php if (!empty($ad['images'])): ?>
    <div class="row g-2 mb-3">
      <?php foreach ($ad['images'] as $img): ?>
      <div class="col-6 col-md-3">
        <img src="<?= rtrim(ASSET_URL,'/') . '/public' . Helper::e($img['filepath']) ?>"
             class="ad-owner-img" loading="lazy"
             alt="<?= Helper::e($img['alt_text'] ?? '') ?>">
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($sub['allow_images']) && $canChangeImage): ?>
    <form method="POST" action="<?= $r ?>/portal/my-ads/<?= $sub['id'] ?>/upload-image"
          enctype="multipart/form-data">
      <?= CSRF::field() ?>
      <div class="d-flex gap-2 align-items-center">
        <input type="file" name="image" accept="image/*" class="form-control form-control-sm" required>
        <button class="btn btn-primary btn-sm text-nowrap">Upload</button>
      </div>
      <div class="form-text">JPG/PNG/WebP max 5MB. Live after approval.</div>
    </form>
    <?php elseif (empty($sub['allow_images'])): ?>
    <p class="text-muted small mb-0">Image upload not included in your package.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Sponsored News -->
<?php if (!empty($sub['allow_news'])): ?>
<div class="portal-card mb-4">
  <div class="portal-card-header">
    <span><i class="bi bi-newspaper me-2"></i>Sponsored Articles</span>
    <?php
    $quota    = (int)($sub['news_quota'] ?? 0);
    $used     = (int)($sub['news_used'] ?? 0);
    $interval = (int)($sub['news_interval_days'] ?? 0);
    $canPost  = $canPostNews;
    ?>
    <?php if ($canPost): ?>
    <a href="<?= $r ?>/portal/my-ads/<?= $ad['id'] ?>/write-news"
       class="btn btn-primary btn-sm">+ Write Article</a>
    <?php else: ?>
    <span class="portal-card-header-note">
      <?php if ($newsBlockReason === 'quota_exhausted'): ?>
        Quota exhausted (<?= $used ?>/<?= $quota ?>)
      <?php elseif ($newsBlockReason === 'too_soon'): ?>
        Next article available in <?= $interval ?> days
      <?php else: ?>
        Not available
      <?php endif; ?>
    </span>
    <?php endif; ?>
  </div>
  <div class="portal-card-body">
    <div class="portal-info-row mb-3">
      <span>Quota used</span>
      <strong><?= $used ?>/<?= $quota ?></strong>
    </div>
    <?php if (empty($news)): ?>
    <p class="text-muted small mb-0">No articles submitted yet.</p>
    <?php else: ?>
    <table class="table table-sm mb-0">
      <thead><tr><th>Title</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($news as $n):
          $bs = 'secondary';
          if ($n['status'] === 'published')            $bs = 'success';
          elseif ($n['status'] === 'approved')         $bs = 'info';
          elseif ($n['status'] === 'pending_approval') $bs = 'warning';
          elseif ($n['status'] === 'rejected')         $bs = 'danger';
        ?>
        <tr>
          <td><?= Helper::e(mb_substr($n['title'],0,50)) ?></td>
          <td><span class="badge bg-<?= $bs ?>"><?= ucfirst(str_replace('_',' ',$n['status'])) ?></span></td>
          <td><?= substr($n['created_at'],0,10) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
