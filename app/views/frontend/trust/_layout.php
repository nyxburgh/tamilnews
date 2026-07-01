<?php
/**
 * _layout.php — Shared wrapper for all trust/policy pages.
 * Includes breadcrumb: Home / தகவல் மையம் / Page Title
 */
$_infoUrl     = $r . '/info';
$_isIndex     = $trustIsIndex ?? false;
$_trustBcList = $_isIndex
    ? [
        ['name' => 'முகப்பு', 'url' => $r . '/'],
        ['name' => 'தகவல் மையம்'],
      ]
    : [
        ['name' => 'முகப்பு', 'url' => $r . '/'],
        ['name' => 'தகவல் மையம்', 'url' => $_infoUrl],
        ['name' => $trustTitle ?? 'Page'],
      ];
?>
<div class="trust-wrap">

  <!-- Breadcrumb -->
  <nav class="trust-breadcrumb notranslate" translate="no" aria-label="breadcrumb">
    <?php foreach ($_trustBcList as $i => $bc): ?>
      <?php if ($i > 0): ?><span class="trust-bc-sep">›</span><?php endif; ?>
      <?php if (isset($bc['url'])): ?>
        <a href="<?= htmlspecialchars($bc['url']) ?>" class="trust-bc-link"><?= htmlspecialchars($bc['name']) ?></a>
      <?php else: ?>
        <span class="trust-bc-current"><?= htmlspecialchars($bc['name']) ?></span>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="trust-page">
    <div class="trust-page-header">
      <div class="trust-page-icon"><?= $trustIcon ?? '📄' ?></div>
      <h1><?= htmlspecialchars($trustTitle ?? '') ?></h1>
      <?php if (!empty($trustUpdated)): ?>
      <p class="trust-page-updated">Last updated: <strong><?= htmlspecialchars($trustUpdated) ?></strong></p>
      <?php endif; ?>
    </div>
    <div class="trust-page-body">
      <?= $trustContent ?? '' ?>
    </div>
    <div class="trust-back-row">
      <a href="<?= $_infoUrl ?>" class="trust-back-btn">← தகவல் மையம்</a>
    </div>
  </div>

</div>
