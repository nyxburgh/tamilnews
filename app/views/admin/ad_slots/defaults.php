<?php use App\Core\{Helper, CSRF}; ?>
<div class="tn-page-header">
  <h2 class="tn-page-title">🖼️ Ad Default Images</h2>
</div>
<div class="row g-4">
  <?php foreach ([
    ['type'=>'square',    'label'=>'Square Ad (300×150)',    'current'=>$squareDefault],
    ['type'=>'horizontal','label'=>'Horizontal Ad (728×100)','current'=>$horizontalDefault],
  ] as $slot): ?>
  <div class="col-md-6">
    <div class="tn-card">
      <div class="tn-card-header"><?= $slot['label'] ?></div>
      <div class="tn-card-body">
        <?php if (!empty($slot['current'])): ?>
        <img src="<?= ASSET_URL . Helper::e($slot['current']) ?>" alt="Default"
             style="max-width:100%;max-height:120px;object-fit:contain;border:1px solid #E8E6E0;border-radius:4px;display:block;margin-bottom:12px">
        <?php endif; ?>
        <form method="POST" action="<?= $r ?>/admin/ad-defaults/upload" enctype="multipart/form-data">
          <?= CSRF::field() ?>
          <input type="hidden" name="slot_type" value="<?= $slot['type'] ?>">
          <div class="mb-2">
            <input type="file" name="default_image" class="form-control" accept="image/*" required>
          </div>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Upload</button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
