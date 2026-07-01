<?php
$trustIcon    = '🔒';
$trustTitle   = 'தனியுரிமைக் கொள்கை — Privacy Policy';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>தகவல் சேகரிப்பு</h2>
  <p>தினத்துளிர் தளத்தை பயன்படுத்தும் போது பின்வரும் தகவல்கள் தானாகவோ அல்லது பயனரால் வழங்கப்பட்டோ சேகரிக்கப்படலாம்:</p>
  <ul>
    <li>பெயர் மற்றும் மின்னஞ்சல் முகவரி</li>
    <li>IP முகவரி மற்றும் உலாவி தகவல்கள்</li>
    <li>குக்கீ தகவல்கள்</li>
    <li>பயனர் செயல்பாட்டு புள்ளிவிவரங்கள்</li>
  </ul>
</section>
<section class="trust-section">
  <h2>குக்கீகள்</h2>
  <p>பயனர் அனுபவத்தை மேம்படுத்தவும், புள்ளிவிவர ஆய்வுகளை மேற்கொள்ளவும் குக்கீகள் பயன்படுத்தப்படலாம்.</p>
</section>
<section class="trust-section">
  <h2>மூன்றாம் தரப்பு சேவைகள்</h2>
  <ul>
    <li>Google Analytics</li>
    <li>Google Translate</li>
    <li>YouTube Embed Services</li>
    <li>Social Media Sharing Tools</li>
  </ul>
</section>
<section class="trust-section">
  <h2>தரவு பாதுகாப்பு</h2>
  <p>பயனர்களின் தகவல்களை பாதுகாப்பாக பராமரிக்க தேவையான தொழில்நுட்ப மற்றும் நிர்வாக நடவடிக்கைகள் மேற்கொள்ளப்படுகின்றன.</p>
</section>
<section class="trust-section">
  <h2>தொடர்பு</h2>
  <p>தனியுரிமை தொடர்பான கேள்விகளுக்கு: <strong>privacy@thinathulir.com</strong></p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
