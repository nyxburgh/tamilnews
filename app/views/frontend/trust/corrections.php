<?php
$trustIcon    = '🔧';
$trustTitle   = 'திருத்தக் கொள்கை — Corrections Policy';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>திருத்தம் கோருவது எப்படி?</h2>
  <p>செய்திகளில் உள்ள தவறுகள், பெயர் பிழைகள், தரவு பிழைகள் அல்லது தவறான தகவல்கள் குறித்து தெரிவிக்கலாம்.</p>
  <div class="trust-contact-grid">
    <div class="trust-contact-item"><span class="trust-contact-icon">✉️</span><div><strong>மின்னஞ்சல்</strong><br>corrections@thinathulir.com</div></div>
    <div class="trust-contact-item"><span class="trust-contact-icon">📞</span><div><strong>தொலைபேசி</strong><br>93639 58850</div></div>
  </div>
</section>
<section class="trust-section">
  <h2>செயல்முறை</h2>
  <ul>
    <li>24 மணி நேரத்திற்குள் கோரிக்கை பெறப்பட்டதற்கான உறுதிப்படுத்தல் வழங்கப்படும்.</li>
    <li>48 மணி நேரத்திற்குள் ஆய்வு மேற்கொள்ளப்படும்.</li>
    <li>தவறு உறுதிப்படுத்தப்பட்டால் உடனடியாக திருத்தம் செய்யப்படும்.</li>
    <li>முக்கிய திருத்தங்களுக்கு கட்டுரையின் இறுதியில் திருத்த குறிப்பு சேர்க்கப்படும்.</li>
  </ul>
</section>
<section class="trust-section">
  <h2>வெளிப்படைத்தன்மை</h2>
  <p>வெளியிடப்பட்ட செய்திகளில் செய்யப்பட்ட திருத்தங்கள் மறைக்கப்படாமல் தெளிவாக பதிவு செய்யப்படும்.</p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
