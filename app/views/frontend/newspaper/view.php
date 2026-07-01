<?php use App\Core\Helper; ?>

<div class="main" style="padding:0">
  <!-- TOP BAR -->
  <div style="background:var(--white);border-bottom:1px solid var(--gray-2);padding:12px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;position:sticky;top:0;z-index:10">
    <div style="display:flex;align-items:center;gap:12px;min-width:0">
      <a href="<?= $r ?>/newspaper" style="color:var(--gray-4);font-size:20px;text-decoration:none;flex-shrink:0">←</a>
      <div style="min-width:0">
        <div style="font-size:13px;font-weight:700;color:var(--red)">
          <?= date('l, d M Y', strtotime($paper['edition_date'])) ?>
        </div>
        <?php if ($paper['title_tamil']): ?>
        <div style="font-size:12px;color:var(--gray-4);font-family:'Noto Sans Tamil',sans-serif;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          <?= htmlspecialchars($paper['title_tamil']) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0">
      <a href="<?= $r ?>/newspaper/download/<?= $paper['id'] ?>"
         style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:6px;background:var(--red);color:white;font-weight:600;font-size:13px;text-decoration:none">
        ⬇ Download PDF
      </a>
      <a href="<?= $r ?>/newspaper" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:6px;background:var(--gray-1);color:var(--text);font-size:13px;text-decoration:none;border:1px solid var(--gray-2)">
        All Editions
      </a>
    </div>
  </div>

  <!-- PDF VIEWER -->
  <div id="pdfViewer" style="background:#404040;min-height:80vh">
    <!-- Full-width embedded PDF reader -->
    <iframe
      src="<?= $r ?><?= htmlspecialchars($paper['pdf_path']) ?>#toolbar=1&navpanes=1&scrollbar=1"
      style="width:100%;height:85vh;border:none;display:block"
      title="<?= htmlspecialchars($paper['title']) ?>">
      <p style="text-align:center;padding:40px;color:white">
        Your browser cannot display PDFs.
        <a href="<?= $r ?>/newspaper/download/<?= $paper['id'] ?>" style="color:#C0001A">Download the PDF</a> instead.
      </p>
    </iframe>
  </div>

  <!-- DOWNLOAD CTA -->
  <div style="background:var(--gray-1);border-top:1px solid var(--gray-2);padding:20px;text-align:center">
    <p style="font-size:13px;color:var(--gray-4);margin-bottom:12px">Reading on mobile? Download for a better experience.</p>
    <a href="<?= $r ?>/newspaper/download/<?= $paper['id'] ?>"
       style="display:inline-flex;align-items:center;gap:8px;padding:10px 28px;border-radius:8px;background:var(--red);color:white;font-weight:700;font-size:14px;text-decoration:none">
      ⬇ Download This Edition (<?= round($paper['file_size']/1024/1024, 1) ?> MB)
    </a>
    <div style="margin-top:8px;font-size:11px;color:var(--gray-4)">
      Downloaded <?= number_format($paper['download_count']) ?> times
    </div>
  </div>

  <!-- MORE EDITIONS -->
  <?php if (!empty($nearby)): ?>
  <div style="padding:20px">
    <div class="section-head" style="margin-bottom:16px">
      <div class="section-head-bar"></div>
      <div class="section-head-title">More Editions</div>
      <div class="section-head-line"></div>
      <a href="<?= $r ?>/newspaper" style="font-size:12px;color:var(--red);font-weight:600;text-decoration:none">View all →</a>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <?php foreach ($nearby as $near):
        if ($near['edition_date'] === $paper['edition_date']) continue; ?>
      <a href="<?= $r ?>/newspaper/read/<?= htmlspecialchars($near['edition_date']) ?>"
         style="display:flex;flex-direction:column;align-items:center;padding:12px 16px;border-radius:8px;background:var(--white);border:1.5px solid var(--gray-2);text-decoration:none;transition:border-color .15s;min-width:80px;text-align:center">
        <div style="font-size:20px;font-weight:900;color:var(--red);line-height:1"><?= date('d', strtotime($near['edition_date'])) ?></div>
        <div style="font-size:11px;color:var(--gray-4);font-weight:600"><?= date('M', strtotime($near['edition_date'])) ?></div>
        <div style="font-size:10px;color:var(--gray-4)"><?= date('Y', strtotime($near['edition_date'])) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
