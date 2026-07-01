<?php
$trustIcon    = 'ℹ️';
$trustTitle   = 'தகவல் மையம் — Info Centre';
$trustUpdated = '';
$trustIsIndex = true;  // breadcrumb: Home › தகவல் மையம் (no third level)
ob_start();

$infoLinks = [
    ['url' => $r.'/about',              'icon' => '📰', 'label' => 'About Us',          'ta' => 'எங்களைப் பற்றி'],
    ['url' => $r.'/contact',            'icon' => '📬', 'label' => 'Contact',            'ta' => 'தொடர்பு கொள்ள'],
    ['url' => $r.'/ownership',          'icon' => '🏢', 'label' => 'Publisher',          'ta' => 'உரிமையாளர் விவரங்கள்'],
    ['url' => $r.'/editorial-policy',   'icon' => '⚖️', 'label' => 'Editorial Policy',  'ta' => 'ஆசிரியக் கொள்கை'],
    ['url' => $r.'/fact-checking',      'icon' => '✅', 'label' => 'Fact Checking',      'ta' => 'உண்மைச் சரிபார்ப்பு'],
    ['url' => $r.'/ethics-policy',      'icon' => '🎯', 'label' => 'Ethics Policy',      'ta' => 'நெறிமுறைக் கொள்கை'],
    ['url' => $r.'/corrections',        'icon' => '🔧', 'label' => 'Corrections',        'ta' => 'திருத்தக் கொள்கை'],
    ['url' => $r.'/privacy',            'icon' => '🔒', 'label' => 'Privacy Policy',     'ta' => 'தனியுரிமைக் கொள்கை'],
    ['url' => $r.'/terms',              'icon' => '📋', 'label' => 'Terms of Use',       'ta' => 'பயன்பாட்டு விதிமுறைகள்'],
    ['url' => $r.'/advertising-policy', 'icon' => '📢', 'label' => 'Advertising Policy', 'ta' => 'விளம்பரக் கொள்கை'],
    ['url' => $r.'/copyright-policy',   'icon' => '©️', 'label' => 'Copyright Policy',  'ta' => 'பதிப்புரிமைக் கொள்கை'],
    ['url' => $r.'/grievance',          'icon' => '🙋', 'label' => 'Grievance Officer',  'ta' => 'குறைதீர் அலுவலர்'],
    ['url' => $r.'/ai-content-policy',  'icon' => '🤖', 'label' => 'AI Content Policy', 'ta' => 'AI உள்ளடக்கக் கொள்கை'],
    ['url' => $r.'/disclaimer',         'icon' => '📌', 'label' => 'Disclaimer',         'ta' => 'பொறுப்புத்துறப்பு'],
];
?>

<div class="info-grid">
  <?php foreach ($infoLinks as $link): ?>
  <a href="<?= htmlspecialchars($link['url']) ?>" class="info-card">
    <div class="info-card-icon"><?= $link['icon'] ?></div>
    <div class="info-card-body">
      <div class="info-card-label"><?= htmlspecialchars($link['label']) ?></div>
      <div class="info-card-ta"><?= htmlspecialchars($link['ta']) ?></div>
    </div>
    <div class="info-card-arrow">›</div>
  </a>
  <?php endforeach; ?>
</div>

<?php $trustContent = ob_get_clean(); include __DIR__ . '/_layout.php'; ?>
