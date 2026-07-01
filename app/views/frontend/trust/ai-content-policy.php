<?php
$trustIcon    = '🤖';
$trustTitle   = 'AI உள்ளடக்கக் கொள்கை — AI Content Policy';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>AI பயன்பாடு</h2>
  <p>தினத்துளிர் சில உள்ளடக்கங்களை உருவாக்க, மொழிபெயர்க்க அல்லது தொகுக்க செயற்கை நுண்ணறிவு தொழில்நுட்பங்களை பயன்படுத்தலாம்.</p>
</section>
<section class="trust-section">
  <h2>மனித ஆய்வு</h2>
  <p>AI மூலம் உருவாக்கப்படும் அல்லது உதவியுடன் தயாரிக்கப்படும் உள்ளடக்கங்கள் வெளியிடப்படுவதற்கு முன் ஆசிரியர் குழுவினரால் ஆய்வு செய்யப்படும்.</p>
</section>
<section class="trust-section">
  <h2>பொறுப்பு</h2>
  <p>இறுதியாக வெளியிடப்படும் அனைத்து உள்ளடக்கங்களுக்கும் தினத்துளிர் ஆசிரியர் குழுவே பொறுப்பாகும்.</p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
