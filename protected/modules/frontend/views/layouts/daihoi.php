<?php
/**
 * Layout cho trang Website công khai Đại hội.
 * Tự chứa, chỉ nạp CSS/JS tĩnh (không dùng CDN theo quy tắc dự án).
 */
$base = Yii::app()->request->baseUrl;
?><!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($this->frontTitle) && $this->frontTitle ? CHtml::encode($this->frontTitle) : 'Đại hội Mường Thanh 2026'; ?></title>
  <link rel="stylesheet" href="<?php echo $base; ?>/public/daihoi/daihoi.css" />
</head>
<body>
  <?php echo $content; ?>
  <script src="<?php echo $base; ?>/public/daihoi/daihoi.js"></script>
</body>
</html>
