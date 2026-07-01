<?php use App\Core\{Helper, CSRF, Auth}; ?>

<div class="tn-page-header">
  <div>
    <h2 class="tn-page-title">📸 Photo News — பட செய்திகள்</h2>
    <p class="tn-page-sub">Upload AI/Canva-generated news card images for published articles</p>
  </div>
</div>

<div class="tn-card">
  <div class="tn-card-body p-0">
    <table class="tn-table">
      <thead>
        <tr>
          <th style="width:60px">Image</th>
          <th>Article</th>
          <th style="width:100px">Category</th>
          <th style="width:100px">Card</th>
          <th style="width:180px">Upload Card</th>
          <th style="width:60px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($articles as $art): ?>
        <tr>
          <!-- Article thumbnail -->
          <td>
            <?php if ($art['image_url']): ?>
            <img src="<?= ($art['image_url'] ? rtrim(ASSET_URL,'/').'/public/'.ltrim($art['image_url'],'/') : '') ?>"
                 style="width:52px;height:38px;object-fit:cover;border-radius:4px" alt="">
            <?php else: ?>
            <div style="width:52px;height:38px;background:#F5F5F0;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#C8C6BE;font-size:18px">🖼</div>
            <?php endif; ?>
          </td>

          <!-- Title -->
          <td>
            <div style="font-size:13px;font-weight:700;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
              <?= Helper::e($art['title']) ?>
            </div>
            <div style="font-size:11px;color:#9CA3AF;margin-top:2px"><?= substr($art['published_at'],0,10) ?></div>
          </td>

          <!-- Category -->
          <td><span class="badge bg-secondary"><?= Helper::e($art['cat'] ?? '—') ?></span></td>

          <!-- Current card -->
          <td>
            <?php if ($art['news_card_image']): ?>
            <img src="<?= ($art['news_card_image'] ? rtrim(ASSET_URL,'/').'/public/'.ltrim($art['news_card_image'],'/') : '') ?>"
                 style="width:52px;height:72px;object-fit:cover;border-radius:4px;border:2px solid #10B981" alt=""
                 title="Card image set">
            <?php else: ?>
            <span style="font-size:11px;color:#D1D5DB">No card</span>
            <?php endif; ?>
          </td>

          <!-- Upload -->
          <td>
            <form method="POST"
                  action="<?= $r . $base ?>/upload/<?= $art['id'] ?>"
                  enctype="multipart/form-data"
                  style="display:flex;gap:6px;align-items:center">
              <?= CSRF::field() ?>
              <input type="file" name="card_image" accept="image/*"
                     class="form-control form-control-sm" style="font-size:11px;max-width:130px">
              <button class="btn btn-sm btn-primary" title="Upload">⬆</button>
            </form>
          </td>

          <!-- Remove -->
          <td>
            <?php if ($art['news_card_image']): ?>
            <form method="POST" action="<?= $r . $base ?>/remove/<?= $art['id'] ?>">
              <?= CSRF::field() ?>
              <button class="btn btn-sm btn-outline-danger" title="Remove card"
                      onclick="return confirm('Remove card image?')">✕</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Pagination -->
<?php if ($total > $per): ?>
<div class="d-flex justify-content-center gap-3 mt-3">
  <?php if ($page > 1): ?>
  <a href="<?= $r . $base ?>?page=<?= $page-1 ?>" class="btn btn-sm btn-outline-secondary">← Prev</a>
  <?php endif; ?>
  <span class="small text-muted align-self-center">Page <?= $page ?> / <?= ceil($total/$per) ?></span>
  <?php if ($page * $per < $total): ?>
  <a href="<?= $r . $base ?>?page=<?= $page+1 ?>" class="btn btn-sm btn-outline-secondary">Next →</a>
  <?php endif; ?>
</div>
<?php endif; ?>
