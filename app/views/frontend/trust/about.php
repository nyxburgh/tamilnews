<?php
$trustIcon    = '📰';
$trustTitle   = 'எங்களைப் பற்றி — About Us';
$trustUpdated = 'January 2025';
ob_start();
?>
<section class="trust-section">
  <h2>தினத்துளிர் பற்றி</h2>
  <p>தினத்துளிர் என்பது உண்மை, துல்லியம் மற்றும் பொறுப்புணர்வை அடிப்படையாகக் கொண்டு செயல்படும் தமிழ் செய்தி மற்றும் ஊடக தளமாகும். அரசியல், சமூகம், கல்வி, விவசாயம், வணிகம், தொழில்நுட்பம், விளையாட்டு, சினிமா மற்றும் உள்ளூர் செய்திகளை பொதுமக்களுக்கு விரைவாகவும் நம்பகத்தன்மையுடனும் வழங்குவது எங்களின் முக்கிய நோக்கமாகும்.</p>
  <p>இணைய ஊடக உலகில் வேகமாக பரவும் தவறான தகவல்களுக்கு மத்தியில், உறுதிப்படுத்தப்பட்ட தகவல்களை மட்டுமே வெளியிடும் பொறுப்பான செய்தி நிறுவனமாக உருவாக தினத்துளிர் உறுதியளிக்கிறது.</p>
</section>

<section class="trust-section">
  <h2>எங்கள் நோக்கம் — Mission</h2>
  <p>உண்மையான, துல்லியமான மற்றும் நடுநிலையான செய்திகளை மக்களிடம் கொண்டு சேர்த்து, சமூக பொறுப்புணர்வுடன் செயல்படும் செய்தி ஊடகமாக இருப்பதே எங்கள் நோக்கமாகும்.</p>
</section>

<section class="trust-section">
  <h2>எங்கள் பார்வை — Vision</h2>
  <p>தமிழ்நாட்டின் முதன்மை இணைய மற்றும் அச்சு ஊடக நிறுவனமாக வளர்ந்து, மக்கள் நம்பிக்கையைப் பெற்ற செய்தி நிறுவனமாக திகழ்வதே எங்கள் பார்வையாகும்.</p>
</section>

<section class="trust-section">
  <h2>ஆசிரியர் குழு — Editorial Team</h2>
  <div class="trust-team-grid">
    <div class="trust-team-card"><div class="trust-team-name">S.A. Kannan</div><div class="trust-team-role">Publisher &amp; Editor</div></div>
    <div class="trust-team-card"><div class="trust-team-name">B. Santhana Karuppan</div><div class="trust-team-role">News Editor</div></div>
    <div class="trust-team-card"><div class="trust-team-name">S. Vetrivelan</div><div class="trust-team-role">Senior Reporter</div></div>
    <div class="trust-team-card"><div class="trust-team-name">N. Suresh Kumar</div><div class="trust-team-role">Technical Director</div></div>
  </div>
</section>
<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
