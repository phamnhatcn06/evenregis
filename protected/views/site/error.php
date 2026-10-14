<?php
/**
 * @var SiteController $this
 * @var int $code
 * @var string $message
 */
$this->pageTitle = 'Lỗi ' . $code;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <!-- Google Fonts: Inter (Full Vietnamese support) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/public/vendor/fonts/inter/inter.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body, button, input, select, textarea {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 480px;
        }
        .error-code { font-size: 72px; font-weight: bold; color: #dc2626; }
        .error-message { color: #666; margin: 16px 0 24px; }
        .btn-home {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?php echo $code; ?></div>
        <p class="error-message"><?php echo CHtml::encode($message); ?></p>
        <a href="<?php echo Yii::app()->homeUrl; ?>" class="btn-home">Về trang chủ</a>
    </div>
</body>
</html>
