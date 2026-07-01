<?php
$trustIcon    = '🏢';
$trustTitle   = 'உரிமையாளர் விவரங்கள் — Ownership';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>வெளியீட்டாளர்</h2>
  <p><strong>S.A. Kannan</strong><br>Publisher &amp; Editor, தினத்துளிர்</p>
</section>
<section class="trust-section">
  <h2>நிறுவனம்</h2>
  <p>தினத்துளிர் ஒரு சுயாதீன தமிழ் செய்தி மற்றும் ஊடக தளமாக செயல்படுகிறது.</p>
</section>
<section class="trust-section">
  <h2>ஆசிரியர் குழு</h2>
  <div class="trust-team-grid">
    <div class="trust-team-card"><div class="trust-team-name">S.A. Kannan</div><div class="trust-team-role">Publisher &amp; Editor</div></div>
    <div class="trust-team-card"><div class="trust-team-name">B. Santhana Karuppan</div><div class="trust-team-role">News Editor</div></div>
    <div class="trust-team-card"><div class="trust-team-name">S. Vetrivelan</div><div class="trust-team-role">Senior Reporter</div></div>
    <div class="trust-team-card"><div class="trust-team-name">N. Suresh Kumar</div><div class="trust-team-role">Technical Director</div></div>
  </div>
</section>
<section class="trust-section">
  <h2>செயல்பாட்டு மையம்</h2>
  <p>மதுரை, தமிழ்நாடு, இந்தியா.</p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
