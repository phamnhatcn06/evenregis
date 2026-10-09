<?php
/**
 * Layout cho trang Website công khai Đại hội (thiết kế mới DHMT_update).
 * Tự chứa, chỉ nạp CSS/JS tĩnh từ local (KHÔNG dùng CDN theo quy tắc dự án).
 *
 * Thứ tự JS quan trọng:
 *   1) tailwind.min.js  -> 2) daihoi-config.js (cấu hình Tailwind)
 *   3) bootstrap.bundle.min.js
 *   4) daihoi-home.js   (slider + realtime)
 */
$base = Yii::app()->request->baseUrl;
$vendor = $base . '/public/vendor';
$dh = $base . '/public/daihoi';
?><!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($this->frontTitle) && $this->frontTitle ? CHtml::encode($this->frontTitle) : 'Đại hội Mường Thanh Ninh Bình 2026'; ?></title>

  <!-- Fonts (local) -->
  <link rel="stylesheet" href="<?php echo $vendor; ?>/fonts/lato/lato.css" />
  <link rel="stylesheet" href="<?php echo $vendor; ?>/fonts/material-symbols/ms.css" />

  <!-- Bootstrap 5 CSS (local) -->
  <link rel="stylesheet" href="<?php echo $vendor; ?>/bootstrap/css/bootstrap.min.css" />

  <!-- Tailwind Play CDN (local) + cấu hình -->
  <script src="<?php echo $vendor; ?>/tailwind/tailwind.min.js"></script>
  <script src="<?php echo $dh; ?>/daihoi-config.js"></script>

  <!-- Style tùy biến trang chủ -->
  <link rel="stylesheet" href="<?php echo $dh; ?>/daihoi-home.css?v=<?php echo @filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/daihoi-home.css') ?: time(); ?>" />
</head>
<body class="min-vh-100 d-flex flex-column bg-slate-50 text-slate-900">
  <?php echo $content; ?>

  <!-- Bootstrap 5 Bundle JS (local) -->
  <script src="<?php echo $vendor; ?>/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- JS trang chủ: slider + realtime -->
  <script src="<?php echo $dh; ?>/daihoi-home.js?v=<?php echo @filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/daihoi-home.js') ?: time(); ?>"></script>
</body>
</html>
