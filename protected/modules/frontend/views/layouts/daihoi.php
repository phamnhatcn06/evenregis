<?php
/**
 * Layout cho trang Website công khai Đại hội (thiết kế mới DHMT_update).
 * Tự chứa, chỉ nạp CSS/JS tĩnh từ local (KHÔNG dùng CDN theo quy tắc dự án).
 *
 * Tailwind: dùng file CSS tĩnh build sẵn (public/vendor/tailwind/tailwind.min.css),
 * KHÔNG dùng Play CDN runtime trên production. Build lại bằng Tailwind CLI khi
 * thêm class mới (xem HƯỚNG DẪN build trong README/ghi chú dự án).
 *
 * Thứ tự JS:
 *   1) bootstrap.bundle.min.js
 *   2) daihoi-home.js   (slider + realtime)
 */
$base = Yii::app()->request->baseUrl;
$vendor = $base . '/public/vendor';
$dh = $base . '/public/daihoi';
?><!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon: logo Đại hội -->
  <link rel="icon" type="image/png" href="<?php echo $base; ?>/themes/hope-ui/logo_daihoi.png" />
  <link rel="shortcut icon" type="image/png" href="<?php echo $base; ?>/themes/hope-ui/logo_daihoi.png" />

  <title><?php echo isset($this->frontTitle) && $this->frontTitle ? CHtml::encode($this->frontTitle) : 'Đại hội Mường Thanh Ninh Bình 2026'; ?></title>

  <!-- Google Fonts: Inter (Full Vietnamese support) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Fonts (local fallback for offline environments) -->
  <link rel="stylesheet" href="<?php echo $vendor; ?>/fonts/inter/inter.css" />
  <link rel="stylesheet" href="<?php echo $vendor; ?>/fonts/material-symbols/ms.css" />

  <!-- Bootstrap 5 CSS (local) -->
  <link rel="stylesheet" href="<?php echo $vendor; ?>/bootstrap/css/bootstrap.min.css" />

  <!-- Tailwind CSS tĩnh (đã build sẵn, thay cho Play CDN runtime) -->
  <link rel="stylesheet" href="<?php echo $vendor; ?>/tailwind/tailwind.min.css?v=<?php echo @filemtime(Yii::getPathOfAlias('webroot') . '/public/vendor/tailwind/tailwind.min.css') ?: time(); ?>" />

  <!-- Style tùy biến trang chủ -->
  <link rel="stylesheet" href="<?php echo $dh; ?>/daihoi-home.css?v=20261009_v3_<?php echo @filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/daihoi-home.css') ?: time(); ?>" />
</head>
<body class="min-vh-100 d-flex flex-column bg-slate-50 text-slate-900">
  <?php echo $content; ?>

  <!-- Bootstrap 5 Bundle JS (local) -->
  <script src="<?php echo $vendor; ?>/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- JS trang chủ: slider + realtime -->
  <script src="<?php echo $dh; ?>/daihoi-home.js?v=<?php echo @filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/daihoi-home.js') ?: time(); ?>"></script>
</body>
</html>
