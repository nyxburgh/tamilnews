<?php use App\Core\{Helper, CSRF}; ?>

<div class="breadcrumb">
  <a href="<?= $r ?>/">முகப்பு</a>
  <span>›</span>
  <span>குடிமக்கள் நிருபர்</span>
</div>

<div class="page-layout-wrap">
  <div class="main">
    <div class="sec-head sec-head-mt">
      <span class="sec-head-bar" style="--ac:#C0001A"></span>
      <span class="sec-head-title">குடிமக்கள் நிருபர்</span>
      <span class="sec-head-ta">Citizen Reporter</span>
    </div>

    <div class="citizen-intro">
      <p>செய்தி அனுப்புங்கள் — நம்பகமான தகவல்கள் ஆசிரியர் குழு ஆய்வுக்குப் பிறகு வெளியிடப்படும்.</p>
    </div>

    <form method="POST" action="<?= $r ?>/citizen-reporter" enctype="multipart/form-data" class="citizen-form">
      <?= CSRF::field() ?>

      <div class="citizen-form-row">
        <div class="citizen-form-group">
          <label class="citizen-label">பெயர் <span class="req">*</span></label>
          <input type="text" name="name" class="citizen-input" required placeholder="உங்கள் பெயர்">
        </div>
        <div class="citizen-form-group">
          <label class="citizen-label">தொலைபேசி <span class="req">*</span></label>
          <input type="tel" name="phone" class="citizen-input" required placeholder="9XXXXXXXXX">
        </div>
      </div>

      <div class="citizen-form-row">
        <div class="citizen-form-group">
          <label class="citizen-label">மின்னஞ்சல்</label>
          <input type="email" name="email" class="citizen-input" placeholder="விருப்பமான">
        </div>
        <div class="citizen-form-group">
          <label class="citizen-label">மாவட்டம்</label>
          <select name="district_id" class="citizen-input">
            <option value="">-- தேர்ந்தெடுக்கவும் --</option>
            <?php foreach ($districts as $d): ?>
            <option value="<?= $d['id'] ?>"><?= Helper::e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="citizen-form-group">
        <label class="citizen-label">செய்தி நடந்த இடம்</label>
        <input type="text" name="location" class="citizen-input" placeholder="நகரம் / கிராமம்">
      </div>

      <div class="citizen-form-group">
        <label class="citizen-label">செய்தி தலைப்பு <span class="req">*</span></label>
        <input type="text" name="title" class="citizen-input" required placeholder="சுருக்கமான தலைப்பு">
      </div>

      <div class="citizen-form-group">
        <label class="citizen-label">செய்தி விவரம் <span class="req">*</span></label>
        <textarea name="content" class="citizen-input citizen-textarea" required
                  placeholder="என்ன நடந்தது, எங்கே, எப்போது, யாருக்கு — விரிவாக எழுதுங்கள்"></textarea>
      </div>

      <div class="citizen-form-group">
        <label class="citizen-label">புகைப்படம் (விருப்பமான)</label>
        <input type="file" name="image" class="citizen-input" accept="image/*">
        <small class="citizen-hint">JPG / PNG / WebP — அதிகபட்சம் 5MB</small>
      </div>

      <button type="submit" class="citizen-submit">செய்தி அனுப்பு →</button>
    </form>
  </div>
</div>
