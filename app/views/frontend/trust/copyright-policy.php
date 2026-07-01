<?php
$trustIcon    = '©️';
$trustTitle   = 'பதிப்புரிமைக் கொள்கை — Copyright Policy';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>பதிப்புரிமை</h2>
  <p>தினத்துளிர் இணையதளத்தில் வெளியிடப்படும் செய்திகள், கட்டுரைகள், படங்கள், வீடியோக்கள் மற்றும் வடிவமைப்புகள் அனைத்தும் பதிப்புரிமை பாதுகாப்பிற்கு உட்பட்டவை.</p>
</section>
<section class="trust-section">
  <h2>அனுமதி</h2>
  <p>முழு உள்ளடக்கத்தை நகலெடுத்து மீண்டும் வெளியிட முன் எழுத்து மூல அனுமதி பெற வேண்டும். தொடர்பு: <strong>editor@thinathulir.com</strong></p>
</section>
<section class="trust-section">
  <h2>மேற்கோள்</h2>
  <p>செய்தி மேற்கோள்களை பயன்படுத்தும் போது தினத்துளிர் இணையதளத்தை ஆதாரமாக குறிப்பிட வேண்டும்.</p>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
