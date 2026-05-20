<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Login') ?> — Tamil News Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= ASSET_URL ?>/assets/css/portal.css" rel="stylesheet">
</head>
<body style="min-height:100vh;background:#F5F5F0;display:flex;align-items:center;justify-content:center;padding:20px">
<?php
$alertType = \App\Core\Session::getFlash('alert_type');
$alertMsg  = \App\Core\Session::getFlash('alert_msg');
?>
<?= $content ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>