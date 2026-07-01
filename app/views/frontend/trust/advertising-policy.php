<?php
$trustIcon    = '📢';
$trustTitle   = 'விளம்பரக் கொள்கை — Advertising Policy';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>விளம்பரங்கள்</h2>
  <p>தினத்துளிர் தளத்தில் வெளியிடப்படும் விளம்பரங்கள் மற்றும் ஆசிரியர் உள்ளடக்கங்கள் தெளிவாக வேறுபடுத்திக் காட்டப்படும்.</p>
</section>
<section class="trust-section">
  <h2>ஆசிரியர் சுதந்திரம்</h2>
  <p>விளம்பரதாரர்கள் செய்தி அல்லது ஆசிரியர் முடிவுகளில் தலையீடு செய்ய அனுமதிக்கப்படமாட்டார்கள்.</p>
</section>
<section class="trust-section">
  <h2>தடைசெய்யப்பட்ட விளம்பரங்கள்</h2>
  <p>பின்வரும் உள்ளடக்கங்கள் ஏற்கப்படமாட்டாது:</p>
  <ul>
    <li>சட்டவிரோத சேவைகள்</li>
    <li>தவறான முதலீட்டு திட்டங்கள்</li>
    <li>மோசடி திட்டங்கள்</li>
    <li>வெறுப்பு மற்றும் வன்முறை சார்ந்த உள்ளடக்கங்கள்</li>
  </ul>
</section>
<section class="trust-section">
  <h2>விளம்பர தொடர்பு</h2>
  <p><strong>ads@thinathulir.com</strong> | 93639 58850</p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
