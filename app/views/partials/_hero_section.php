<?php
/**
 * _hero_section.php — Shared listing-page hero section.
 * Exactly mirrors the home page featured section (.hero4-big-v2 + .hero4-right-v2).
 *
 * Required variables from parent view (set before including):
 *   $h1        — first article array (big left card), or null
 *   $h2–$h5    — next 4 article arrays (2×2 right grid), or null
 *   $accentColor  — e.g. '#C0001A' (CSS colour for ctag highlight)
 *   $labelText    — e.g. 'தமிழ்நாடு' (category/tag label shown on cards)
 *   $heE(string)  — callable escaping helper, e.g. 'htmlspecialchars' or a local fn
 *
 * No-image behaviour: card maintains its exact height; background fills with
 * a dark gradient, and the title is displayed larger — no default placeholder
 * image is ever shown.
 */
if (!$h1) return; // nothing to render
?>
<div class="hero4-grid">

  <!-- LEFT: big featured card -->
  <a href="<?= $r ?>/article/<?= $heE($h1['slug']) ?>"
     class="hero4-big-v2 <?= empty($h1['image_url']) ? 'hero4-no-img' : '' ?>">
    <?php if (!empty($h1['image_url'])): ?>
    <img src="<?= $heE($h1['image_url']) ?>" alt="<?= $heE($h1['title']) ?>" loading="eager">
    <?php endif; ?>
    <div class="hero4-big-v2-body">
      <?php if (!empty($h1['is_breaking'])): ?>
      <div class="breaking-badge breaking-badge-sm"><span class="ticker-dot"></span>BREAKING</div>
      <?php endif; ?>
      <span class="ctag ctag-accent" style="--ac:<?= $accentColor ?>"><?= $heE($labelText) ?></span>
      <div class="hero4-card-title <?= empty($h1['image_url']) ? 'hero4-title-xl' : '' ?>"><?= $heE($h1['title']) ?></div>
      <?php if (!empty($h1['image_url']) && !empty($h1['excerpt'])): ?>
      <div class="hero4-card-excerpt"><?= $heE(mb_substr(strip_tags($h1['excerpt']), 0, 110)) ?></div>
      <?php elseif (empty($h1['image_url']) && !empty($h1['excerpt'])): ?>
      <div class="hero4-no-img-excerpt"><?= $heE(mb_substr(strip_tags($h1['excerpt']), 0, 300)) ?></div>
      <?php endif; ?>
      <div class="hero4-meta notranslate" translate="no"><?= \App\Core\Helper::timeAgo($h1['published_at']) ?></div>
    </div>
  </a>

  <!-- RIGHT: 2×2 grid of .nc news cards -->
  <div class="hero4-right-v2">
    <?php foreach ([$h2, $h3, $h4, $h5] as $a):
      if (!$a) continue; ?>
    <a href="<?= $r ?>/article/<?= $heE($a['slug']) ?>"
       class="nc <?= empty($a['image_url']) ? 'nc-no-img' : '' ?>">
      <?php if (!empty($a['image_url'])): ?>
      <img src="<?= $heE($a['thumb_url'] ?: $a['image_url']) ?>" alt="<?= $heE($a['title']) ?>" loading="lazy">
      <?php endif; ?>
      <div class="nc-body">
        <span class="ctag ctag-accent" style="--ac:<?= $accentColor ?>"><?= $heE($labelText) ?></span>
        <div class="nc-title <?= empty($a['image_url']) ? 'nc-title-lg' : '' ?>"><?= $heE($a['title']) ?></div>
      <?php if (empty($a['image_url']) && !empty($a['excerpt'])): ?>
      <div class="nc-no-img-excerpt"><?= $heE(mb_substr(strip_tags($a['excerpt']), 0, 140)) ?></div>
      <?php endif; ?>
        <div class="hero4-meta notranslate" translate="no"><?= \App\Core\Helper::timeAgo($a['published_at']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

</div>
