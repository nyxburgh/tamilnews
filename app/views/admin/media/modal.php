<?php use App\Core\Helper; ?>
<div class="d-flex gap-2 mb-3">
  <input type="text" id="modalSearch" class="form-control form-control-sm" placeholder="Search…" value="<?= Helper::e($search) ?>">
  <button class="btn btn-sm btn-primary" onclick="modalDoSearch()"><i class="bi bi-search"></i></button>
</div>
<div class="tn-media-grid" id="modalGrid">
  <?php foreach ($media as $m): ?>
  <?php if (!str_starts_with($m['mime_type'], 'image/')): continue; endif; ?>
  <div class="tn-media-item tn-media-selectable" onclick="selectMedia(<?= $m['id'] ?>, '<?= Helper::e($m['filepath']) ?>')">
    <div class="tn-media-thumb">
      <img src="<?= Helper::e($m['thumb_path'] ?: $m['filepath']) ?>" alt="" loading="lazy">
      <div class="tn-media-select-overlay"><i class="bi bi-check-circle-fill"></i></div>
    </div>
    <div class="tn-media-info">
      <div class="tn-media-name"><?= Helper::e(mb_substr($m['filename'], 0, 20)) ?></div>
      <div class="tn-media-meta"><?= Helper::formatBytes($m['size']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($media)): ?>
  <div class="col-12 text-center py-4 text-muted">No images found</div>
  <?php endif; ?>
</div>
<?php if ($total > $per_page): ?>
<?php $_tp=(int)ceil($total/$per_page);$_s=max(1,$page-2);$_e=min($_tp,$page+2); ?>
<div class="tn-pag-wrap">
  <small style="font-size:11px;color:#6b7280"><?= number_format(($page-1)*$per_page+1) ?>–<?= number_format(min($page*$per_page,$total)) ?> of <?= number_format($total) ?></small>
  <nav><ul class="tn-pag">
    <?php if($page>1):?><li><a href="#" class="tn-pag-btn" onclick="loadModal(<?=$page-1?>)">‹</a></li><?php endif;?>
    <?php if($_s>1):?><li><a href="#" class="tn-pag-btn" onclick="loadModal(1)">1</a></li><?php if($_s>2):?><li><span class="tn-pag-btn tn-pag-dots">…</span></li><?php endif;endif;?>
    <?php for($i=$_s;$i<=$_e;$i++):?><li><a href="#" class="tn-pag-btn <?=$i===$page?'tn-pag-active':''?>" onclick="loadModal(<?=$i?>)"><?=$i?></a></li><?php endfor;?>
    <?php if($_e<$_tp):?><?php if($_e<$_tp-1):?><li><span class="tn-pag-btn tn-pag-dots">…</span></li><?php endif;?><li><a href="#" class="tn-pag-btn" onclick="loadModal(<?=$_tp?>)"><?=$_tp?></a></li><?php endif;?>
    <?php if($page<$_tp):?><li><a href="#" class="tn-pag-btn" onclick="loadModal(<?=$page+1?>)">›</a></li><?php endif;?>
  </ul></nav>
</div>
<?php endif; ?>
<script>
function modalDoSearch() {
  const q = document.getElementById('modalSearch').value;
  fetch(r + '/admin/media/modal?search=' + encodeURIComponent(q))
    .then(r => r.text()).then(html => document.getElementById('mediaModalBody').innerHTML = html);
}
function loadModal(page) {
  const q = document.getElementById('modalSearch')?.value || '';
  fetch(r + '/admin/media/modal?page=' + page + '&search=' + encodeURIComponent(q))
    .then(r => r.text()).then(html => document.getElementById('mediaModalBody').innerHTML = html);
}
</script>
