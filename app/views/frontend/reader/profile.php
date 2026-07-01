<?php use App\Core\Helper; ?>

<div class="breadcrumb">
  <a href="<?= $r ?>/">முகப்பு</a>
  <span>›</span>
  <span>My Profile</span>
</div>

<div class="citizen-profile-wrap">

  <!-- LEFT / MAIN -->
  <div class="citizen-profile-main">

    <!-- User card -->
    <div class="cp-card cp-user-card">
      <div class="cp-avatar">
        <?php if (!empty($reader['avatar'])): ?>
        <img src="<?= htmlspecialchars($reader['avatar']) ?>?sz=128"
             referrerpolicy="no-referrer" alt="" class="cp-avatar-img">
        <?php else: ?>
        <div class="cp-avatar-init"><?= strtoupper(substr($reader['name'],0,1)) ?></div>
        <?php endif; ?>
      </div>
      <div class="cp-user-info">
        <div class="cp-user-name"><?= htmlspecialchars($reader['name']) ?></div>
        <div class="cp-user-email"><?= htmlspecialchars($reader['email'] ?? '') ?></div>
        <div class="cp-badges">
          <span class="cp-badge cp-badge-reader">Reader</span>
          <?php if (!empty($reader['google_id'])): ?>
          <span class="cp-badge cp-badge-google">✓ Google</span>
          <?php endif; ?>
          <?php if (!empty($reader['district_id'])): ?>
          <span class="cp-badge cp-badge-district">📍 District set</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Quick action cards -->
    <div class="cp-card-grid">
      <a href="<?= $r ?>/citizen-reporter" class="cp-action-card cp-action-red">
        <div class="cp-action-icon">📢</div>
        <div class="cp-action-label">Report News</div>
        <div class="cp-action-sub">Submit a story from your area</div>
      </a>
      <a href="<?= $r ?>/search" class="cp-action-card">
        <div class="cp-action-icon">🔍</div>
        <div class="cp-action-label">Search</div>
        <div class="cp-action-sub">Find news articles</div>
      </a>
      <a href="<?= $r ?>/reader/agree" class="cp-action-card">
        <div class="cp-action-icon">📍</div>
        <div class="cp-action-label">My District</div>
        <div class="cp-action-sub">Change district settings</div>
      </a>
      <a href="<?= $r ?>/auth/reader/logout" class="cp-action-card cp-action-logout">
        <div class="cp-action-icon">🚪</div>
        <div class="cp-action-label">Logout</div>
        <div class="cp-action-sub">Sign out of your account</div>
      </a>
    </div>

    <!-- My submitted reports -->
    <?php if (!empty($ratings)): ?>
    <div class="cp-card">
      <div class="cp-card-head">⭐ My Ratings</div>
      <?php foreach ($ratings as $rat): ?>
      <a href="<?= $r ?>/article/<?= htmlspecialchars($rat['slug']) ?>" class="cp-list-item">
        <?php if (!empty($rat['image_url'])): ?>
        <img src="<?= htmlspecialchars(rtrim(ASSET_URL,'/').'/public'.$rat['image_url']) ?>" class="cp-list-thumb" alt="">
        <?php endif; ?>
        <div class="cp-list-body">
          <div class="cp-list-title"><?= htmlspecialchars($rat['title']) ?></div>
          <div class="cp-list-meta">
            <span style="color:#F59E0B"><?= str_repeat('★',(int)$rat['rating']) ?><?= str_repeat('☆',5-(int)$rat['rating']) ?></span>
            <span><?= substr($rat['created_at'],0,10) ?></span>
          </div>
        </div>
        <i class="bi bi-chevron-right cp-list-arrow"></i>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="cp-card" style="text-align:center;padding:28px;color:#9CA3AF;font-size:13px">
      No ratings yet — start reading and rating articles!
    </div>
    <?php endif; ?>

  </div>

  <!-- RIGHT / SIDEBAR — desktop only -->
  <aside class="citizen-profile-side">
    <div class="sb-vertical-ad notranslate" translate="no">
      <div class="ad-rotator" data-slot="vertical" data-cat="0"></div>
      <div class="sb-ad-label">Advertisement</div>
    </div>
  </aside>

</div>

<style>
.citizen-profile-wrap{display:flex;gap:24px;max-width:1100px;margin:20px auto;padding:0 16px 60px;align-items:flex-start;}
.citizen-profile-main{flex:1;min-width:0;}
.citizen-profile-side{width:280px;flex-shrink:0;position:sticky;top:80px;}
@media(max-width:900px){.citizen-profile-side{display:none;}}

.cp-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:16px;overflow:hidden;}
.cp-card-head{padding:12px 16px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#6B6A64;border-bottom:1px solid #F5F5F0;}
.cp-user-card{display:flex;align-items:center;gap:16px;padding:20px;}
.cp-avatar{flex-shrink:0;}
.cp-avatar-img{width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #C0001A;display:block;}
.cp-avatar-init{width:72px;height:72px;border-radius:50%;background:#C0001A;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;color:#fff;}
.cp-user-name{font-size:17px;font-weight:800;color:#1A1A1A;}
.cp-user-email{font-size:12px;color:#6B6A64;margin-top:2px;}
.cp-badges{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;}
.cp-badge{padding:2px 9px;border-radius:12px;font-size:11px;font-weight:700;}
.cp-badge-reader{background:#FEF3C7;color:#92400E;}
.cp-badge-google{background:#EFF6FF;color:#1D4ED8;}
.cp-badge-district{background:#F0FDF4;color:#065F46;}

.cp-card-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;}
.cp-action-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:16px;text-decoration:none;color:#1A1A1A;transition:transform .15s,box-shadow .15s;display:block;}
.cp-action-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.12);}
.cp-action-icon{font-size:24px;margin-bottom:8px;}
.cp-action-label{font-size:14px;font-weight:800;color:#1A1A1A;}
.cp-action-sub{font-size:11px;color:#6B6A64;margin-top:3px;}
.cp-action-red .cp-action-label{color:#C0001A;}
.cp-action-red{border-left:3px solid #C0001A;}
.cp-action-logout .cp-action-label{color:#C0001A;}

.cp-list-item{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #F5F5F0;text-decoration:none;color:#1A1A1A;transition:background .12s;}
.cp-list-item:last-child{border-bottom:none;}
.cp-list-item:hover{background:#F9F8F5;}
.cp-list-thumb{width:52px;height:38px;object-fit:cover;border-radius:4px;flex-shrink:0;}
.cp-list-body{flex:1;min-width:0;}
.cp-list-title{font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.cp-list-meta{font-size:11px;color:#9CA3AF;margin-top:3px;display:flex;gap:8px;}
.cp-list-arrow{color:#C8C6BE;font-size:12px;flex-shrink:0;}
</style>
