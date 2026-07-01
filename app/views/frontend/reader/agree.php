<?php use App\Core\Helper; ?>
<style>
.agree-wrap { max-width:560px;margin:40px auto;padding:0 16px 60px; }
.agree-card { background:#fff;border-radius:16px;box-shadow:0 4px 32px rgba(0,0,0,.10);padding:32px; }
.agree-logo { text-align:center;margin-bottom:24px; }
.agree-welcome { font-size:18px;font-weight:800;text-align:center;margin-bottom:4px; }
.agree-sub { font-size:13px;color:#6B6A64;text-align:center;margin-bottom:24px; }
.agree-section { background:#F9F8F5;border-radius:10px;padding:16px;margin-bottom:16px; }
.agree-section-title { font-size:12px;font-weight:800;color:#C0001A;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px; }
.agree-tc-box { max-height:160px;overflow-y:auto;font-size:12px;color:#4B5563;line-height:1.7;padding-right:4px; }
.agree-tc-box p { margin:0 0 8px; }
.agree-check { display:flex;align-items:flex-start;gap:10px;padding:14px;background:#EFF6FF;border-radius:8px;border:1.5px solid #BFDBFE;margin-bottom:20px;cursor:pointer; }
.agree-check input { margin-top:3px;flex-shrink:0; }
.agree-check label { font-size:13px;font-weight:600;color:#1D4ED8;cursor:pointer; }
.agree-btn { width:100%;padding:13px;background:#C0001A;color:#fff;border:none;border-radius:8px;font-family:'Noto Sans Tamil','Inter',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:opacity .15s; }
.agree-btn:disabled { opacity:.5;cursor:not-allowed; }
.agree-btn:not(:disabled):hover { opacity:.9; }
</style>

<div class="agree-wrap">
  <div class="agree-card">

    <div class="agree-logo">
      <span style="color:#C0001A;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;font-size:26px">தினத்</span><span style="color:#fff;background:#C0001A;padding:2px 10px;border-radius:5px;font-family:'Noto Sans Tamil',sans-serif;font-weight:900;font-size:26px;margin-left:3px">துளிர்</span>
    </div>

    <div class="agree-welcome">Welcome, <?= htmlspecialchars($reader['name']) ?>! 👋</div>
    <div class="agree-sub">Complete your profile to get started</div>

    <form method="POST" action="<?= $r ?>/reader/agree" id="agreeForm">
      <?= \App\Core\CSRF::field() ?>

      <!-- District -->
      <div class="agree-section">
        <div class="agree-section-title">📍 Your District</div>
        <p style="font-size:12px;color:#6B6A64;margin-bottom:10px">
          Used to show you local news, weather, and district-specific content.
        </p>
        <select name="district_id" id="districtSel" class="form-select form-select-sm" required>
          <option value="">— Select your district —</option>
          <?php foreach ($districts as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Terms -->
      <div class="agree-section">
        <div class="agree-section-title">📋 Terms of Use</div>
        <div class="agree-tc-box">
          <p><strong>1. Content Submission</strong><br>All articles and news submitted will be reviewed by our editorial team before publishing. We reserve the right to edit or decline submissions.</p>
          <p><strong>2. District-based Reporting</strong><br>As a contributor, you will initially write news relevant to your selected district. Expanding coverage requires approval to Reporter role.</p>
          <p><strong>3. Accuracy & Integrity</strong><br>You must ensure submitted content is factual, accurate, and not misleading. No fabricated, defamatory, or plagiarised content.</p>
          <p><strong>4. Copyright</strong><br>You retain rights to your original work. By submitting, you grant தினத்துளிர் a non-exclusive licence to publish and distribute your content.</p>
          <p><strong>5. Account Access</strong><br>Contributor access is granted by admin approval. Misuse of the portal will result in account suspension.</p>
          <p><strong>6. Privacy</strong><br>Your name, district and Google profile will be used within the platform. We do not share personal data with third parties.</p>
        </div>
      </div>

      <!-- Agree checkbox -->
      <div class="agree-check" id="agreeCheckWrap">
        <input type="checkbox" name="agreed" id="agreeChk" value="1">
        <label for="agreeChk">I have read and agree to the Terms of Use. I understand that all submitted content is subject to editorial review before publishing.</label>
      </div>

      <button type="submit" class="agree-btn" id="agreeBtn" disabled>
        Complete Setup →
      </button>
    </form>

  </div>
</div>

<script>
(function () {
  var chk = document.getElementById('agreeChk');
  var sel = document.getElementById('districtSel');
  var btn = document.getElementById('agreeBtn');
  function updateBtn() {
    btn.disabled = !(chk.checked && sel.value);
  }
  chk.addEventListener('change', updateBtn);
  sel.addEventListener('change', updateBtn);
}());
</script>
