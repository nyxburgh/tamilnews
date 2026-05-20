<?php use App\Core\Helper; ?>
<div class="main">
  <!-- AUTHOR HEADER -->
  <div style="background:#fff;border-radius:12px;padding:24px;margin-bottom:24px;display:flex;align-items:center;gap:20px;border:1px solid #D8D6CE">
    <?php if ($author['avatar']): ?>
    <img src="<?= htmlspecialchars($author['avatar']) ?>" style="width:72px;height:72px;border-radius:50%;object-fit:cover;flex-shrink:0" alt="">
    <?php else: ?>
    <div style="width:72px;height:72px;border-radius:50%;background:#C0001A;color:white;font-size:28px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <?= strtoupper(substr($author['name'],0,1)) ?>
    </div>
    <?php endif; ?>
    <div>
      <h1 style="font-size:22px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($author['name']) ?></h1>
      <div style="font-size:13px;color:#6B6A64">
        <span style="background:#FDECEA;color:#C0001A;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600">
          <?= htmlspecialchars($author['role_name']) ?>
        </span>
        <span style="margin-left:10px"><?= number_format($author['article_count']) ?> articles published</span>
      </div>
    </div>
  </div>

  <div class="section-head" style="margin-bottom:16px">
    <div class="section-head-bar"></div>
    <div class="section-head-title">Articles</div>
    <div class="section-head-line"></div>
    <span style="font-size:12px;color:#6B6A64"><?= number_format($total) ?> total</span>
  </div>

  <?php if (empty($articles)): ?>
  <div class="empty-state"><div style="font-size:48px">📝</div><p>No articles published yet</p></div>
  <?php else: ?>
  <div class="top-stories-grid">
    <?php foreach ($articles as $a): ?>
    <a href="<?= $r ?>/article/<?= htmlspecialchars($a['slug']) ?>" class="story-card">
      <img src="<?= $a['thumb_url'] ?? $a['image_url'] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&q=80' ?>"
           alt="<?= htmlspecialchars($a['title']) ?>" loading="lazy">
      <div class="story-card-body">
        <span class="cat-tag cat-red"><?= htmlspecialchars($a['category_name']) ?></span>
        <div class="story-card-title" style="margin-top:6px"><?= htmlspecialchars($a['title']) ?></div>
        <div class="card-meta">
          <span><?= Helper::timeAgo($a['published_at']) ?></span>
          <?php if ($a['view_count'] > 0): ?><span>👁 <?= number_format($a['view_count']) ?></span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php
$queryExtra = '';
include VIEW_PATH . '/partials/pagination.php';
?>
</div>
