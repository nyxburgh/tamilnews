<?php
$trustIcon    = '📬';
$trustTitle   = 'தொடர்பு கொள்ள — Contact Us';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>தொடர்பு விவரங்கள்</h2>
  <div class="trust-contact-grid">
    <div class="trust-contact-item"><span class="trust-contact-icon">✉️</span><div><strong>பொதுவான தகவல்கள்</strong><br>info@thinathulir.com</div></div>
    <div class="trust-contact-item"><span class="trust-contact-icon">✍️</span><div><strong>ஆசிரியர் குழு</strong><br>editor@thinathulir.com</div></div>
    <div class="trust-contact-item"><span class="trust-contact-icon">⚠️</span><div><strong>திருத்த கோரிக்கைகள்</strong><br>corrections@thinathulir.com</div></div>
    <div class="trust-contact-item"><span class="trust-contact-icon">📣</span><div><strong>விளம்பரங்கள்</strong><br>ads@thinathulir.com</div></div>
  </div>
</section>
<section class="trust-section">
  <h2>தொலைபேசி</h2>
  <p style="font-size:20px;font-weight:700;color:#1E293B">93639 58850</p>
</section>
<section class="trust-section">
  <h2>முகவரி</h2>
  <p>தினத்துளிர்<br>மதுரை, தமிழ்நாடு, இந்தியா.</p>
</section>
<section class="trust-section">
  <h2>கட்டுரை சமர்ப்பிப்பு</h2>
  <p>செய்திகள், சிறப்பு கட்டுரைகள் மற்றும் மக்கள் நலன் சார்ந்த தகவல்களை எங்கள் ஆசிரியர் குழுவிற்கு அனுப்பலாம். ஆய்வு செய்யப்பட்ட பின்னர் பொருத்தமான உள்ளடக்கங்கள் வெளியிடப்படும்.</p>
  <p><a href="<?= $r ?>/contribute/login">இங்கே பதிவு செய்யவும் →</a></p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
