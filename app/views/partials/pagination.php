<?php
/**
 * Universal Pagination Component
 * Variables needed: $page, $total, $per_page
 * Optional: $queryExtra (e.g. '&status=review')
 */
if (!isset($total) || !isset($per_page) || (int)$per_page <= 0) return;
$_pTotal      = (int)$total;
$_pPerPage    = (int)$per_page;
$_pPage       = (int)($page ?? 1);
$_pTotalPages = (int)ceil($_pTotal / $_pPerPage);
$_pExtra      = $queryExtra ?? '';

// Always show info row; hide nav if only 1 page
?>
<div class="tn-pag-wrap">
  <div class="tn-pag-info">
    <?php if ($_pTotal > 0): ?>
    <?= number_format(($_pPage - 1) * $_pPerPage + 1) ?>–<?= number_format(min($_pPage * $_pPerPage, $_pTotal)) ?>
    <span>of</span> <?= number_format($_pTotal) ?>
    <?php else: ?>
    0 results
    <?php endif; ?>
  </div>

  <?php if ($_pTotalPages > 1): ?>
  <nav>
    <ul class="tn-pag">
      <!-- PREV -->
      <li>
        <?php if ($_pPage > 1): ?>
        <a href="?page=<?= $_pPage - 1 ?><?= $_pExtra ?>" class="tn-pag-btn">‹</a>
        <?php else: ?>
        <span class="tn-pag-btn disabled">‹</span>
        <?php endif; ?>
      </li>

      <!-- FIRST + ELLIPSIS -->
      <?php $_pStart = max(1, $_pPage - 2); $_pEnd = min($_pTotalPages, $_pPage + 2); ?>
      <?php if ($_pStart > 1): ?>
      <li><a href="?page=1<?= $_pExtra ?>" class="tn-pag-btn">1</a></li>
      <?php if ($_pStart > 2): ?><li><span class="tn-pag-btn tn-pag-dots">…</span></li><?php endif; ?>
      <?php endif; ?>

      <!-- PAGE RANGE -->
      <?php for ($i = $_pStart; $i <= $_pEnd; $i++): ?>
      <li>
        <a href="?page=<?= $i ?><?= $_pExtra ?>"
           class="tn-pag-btn <?= $i === $_pPage ? 'tn-pag-active' : '' ?>">
          <?= $i ?>
        </a>
      </li>
      <?php endfor; ?>

      <!-- LAST + ELLIPSIS -->
      <?php if ($_pEnd < $_pTotalPages): ?>
      <?php if ($_pEnd < $_pTotalPages - 1): ?><li><span class="tn-pag-btn tn-pag-dots">…</span></li><?php endif; ?>
      <li><a href="?page=<?= $_pTotalPages ?><?= $_pExtra ?>" class="tn-pag-btn"><?= $_pTotalPages ?></a></li>
      <?php endif; ?>

      <!-- NEXT -->
      <li>
        <?php if ($_pPage < $_pTotalPages): ?>
        <a href="?page=<?= $_pPage + 1 ?><?= $_pExtra ?>" class="tn-pag-btn">›</a>
        <?php else: ?>
        <span class="tn-pag-btn disabled">›</span>
        <?php endif; ?>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
</div>
