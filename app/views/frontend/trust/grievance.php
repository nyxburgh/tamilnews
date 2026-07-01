<?php
$trustIcon    = '🙋';
$trustTitle   = 'குறைதீர் அலுவலர் — Grievance Officer';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>குறைதீர் அதிகாரி</h2>
  <p><strong>S.A. Kannan</strong><br>Publisher &amp; Editor, தினத்துளிர்</p>
</section>
<section class="trust-section">
  <h2>தொடர்பு</h2>
  <div class="trust-contact-grid">
    <div class="trust-contact-item"><span class="trust-contact-icon">📞</span><div><strong>தொலைபேசி</strong><br>93639 58850</div></div>
    <div class="trust-contact-item"><span class="trust-contact-icon">✉️</span><div><strong>மின்னஞ்சல்</strong><br>grievance@thinathulir.com</div></div>
  </div>
  <p style="margin-top:12px">அலுவலகம்: மதுரை, தமிழ்நாடு.</p>
</section>
<section class="trust-section">
  <h2>குறைதீர் நடைமுறை</h2>
  <p>பெறப்படும் புகார்கள் மற்றும் குறைகள் நியாயமான காலத்திற்குள் ஆய்வு செய்யப்பட்டு தீர்வு வழங்கப்படும்.</p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
