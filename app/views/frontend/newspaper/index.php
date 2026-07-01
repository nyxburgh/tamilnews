<?php use App\Core\Helper; ?>

<div class="main">
  <!-- PAGE HEADER -->
  <div style="background:linear-gradient(135deg,#1A1A1A 0%,#2D1010 100%);border-radius:10px;padding:28px 24px;margin-bottom:24px;color:white">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
      <span style="font-size:32px">📰</span>
      <div>
        <h1 style="font-size:24px;font-weight:700;margin:0;font-family:'Noto Sans Tamil',sans-serif">இ-பேப்பர்</h1>
        <div style="font-size:13px;opacity:.7;margin-top:2px">Digital Newspaper Archive</div>
      </div>
    </div>
    <p style="font-size:13px;opacity:.75;margin:0">Read any past edition online or download as PDF</p>
  </div>

  <!-- YEAR FILTER -->
  <?php if (!empty($years)): ?>
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <span style="font-size:12px;color:var(--gray-4);font-weight:600">YEAR:</span>
    <a href="<?= $r ?>/newspaper"
       style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
              <?= !$selectedYear ? 'background:var(--red);color:white' : 'background:var(--gray-1);color:var(--gray-4)' ?>">
      All
    </a>
    <?php foreach ($years as $y): ?>
    <a href="<?= $r ?>/newspaper?year=<?= $y['year'] ?>"
       style="padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;
              <?= $selectedYear == $y['year'] ? 'background:var(--red);color:white' : 'background:var(--gray-1);color:var(--gray-4)' ?>">
      <?= $y['year'] ?> <span style="opacity:.7">(<?= $y['count'] ?>)</span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- PAPERS GRID -->
  <?php if (empty($papers)): ?>
  <div style="text-align:center;padding:60px 20px;color:var(--gray-4)">
    <div style="font-size:48px;margin-bottom:12px">📰</div>
    <p>No editions available yet. Check back soon.</p>
  </div>
  <?php else: ?>
  <div class="np-grid">
    <?php foreach ($papers as $p): ?>
    <div class="np-card">
      <!-- PDF THUMBNAIL / ICON -->
      <a href="<?= $r ?>/newspaper/read/<?= htmlspecialchars($p['edition_date']) ?>" class="np-card-cover">
        <?php if ($p['thumb_path']): ?>
        <img src="<?= $r ?><?= htmlspecialchars($p['thumb_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
        <?php else: ?>
        <div class="np-card-placeholder">
          <div style="font-size:48px">📰</div>
          <div class="np-card-date-big"><?= date('d', strtotime($p['edition_date'])) ?></div>
          <div class="np-card-month-big"><?= date('M Y', strtotime($p['edition_date'])) ?></div>
        </div>
        <?php endif; ?>
        <div class="np-card-overlay">
          <span>👁 Read</span>
        </div>
      </a>

      <!-- INFO -->
      <div class="np-card-body">
        <div class="np-card-edition-date">
          <?= date('l, d M Y', strtotime($p['edition_date'])) ?>
        </div>
        <?php if ($p['title_tamil']): ?>
        <div class="np-card-title-ta"><?= htmlspecialchars($p['title_tamil']) ?></div>
        <?php endif; ?>
        <div class="np-card-meta">
          <span><?= ucfirst($p['edition_type']) ?></span>
          <span>·</span>
          <span><?= round($p['file_size'] / 1024 / 1024, 1) ?> MB</span>
          <?php if ($p['download_count'] > 0): ?>
          <span>·</span>
          <span>📥 <?= number_format($p['download_count']) ?></span>
          <?php endif; ?>
        </div>
        <div class="np-card-actions">
          <a href="<?= $r ?>/newspaper/read/<?= htmlspecialchars($p['edition_date']) ?>"
             class="np-btn np-btn-read">Read Online</a>
          <a href="<?= $r ?>/newspaper/download/<?= $p['id'] ?>"
             class="np-btn np-btn-download">⬇ PDF</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php
  $queryExtra = $selectedYear ? '&year='.$selectedYear : '';
  include VIEW_PATH . '/partials/pagination.php';
  ?>
  <?php endif; ?>
</div>

