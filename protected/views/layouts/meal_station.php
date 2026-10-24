<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo CHtml::encode($this->pageTitle ? $this->pageTitle : 'Trạm chấm ăn - Đại Hội Mường Thanh 2026'); ?></title>
    <link rel="shortcut icon" href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/images/favicon.ico">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        html, body { height: 100%; }
        body {
            margin: 0; background: #0f172a; color: #e2e8f0;
            font-family: -apple-system, "Segoe UI", Roboto, system-ui, sans-serif;
            overflow: hidden;
        }
        .ms-wrap { display: flex; flex-direction: column; height: 100vh; }
        .ms-topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: .6rem 1rem; background: #1e293b; border-bottom: 1px solid #334155;
            flex-wrap: wrap; gap: .5rem;
        }
        .ms-topbar .ms-station { font-weight: 700; font-size: 1.05rem; }
        .ms-meal {
            font-size: 1rem; padding: .25rem .75rem; border-radius: 999px;
            background: #0d6efd; color: #fff; font-weight: 600;
        }
        .ms-meal.ms-meal-none { background: #64748b; }
        .ms-conn { font-size: .85rem; display: flex; align-items: center; gap: .35rem; }
        .ms-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .ms-dot.on { background: #22c55e; } .ms-dot.off { background: #ef4444; }
        .ms-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .ms-card {
            width: 100%; max-width: 680px; border-radius: 1.5rem; padding: 2.5rem 2rem;
            text-align: center; background: #1e293b; transition: background .15s;
        }
        .ms-card.state-success { background: #15803d; }
        .ms-card.state-already { background: #a16207; }
        .ms-card.state-error   { background: #b91c1c; }
        .ms-card.state-offline { background: #1d4ed8; }
        .ms-icon { font-size: 4.5rem; line-height: 1; margin-bottom: .5rem; }
        .ms-status { font-size: 1.9rem; font-weight: 800; margin-bottom: .75rem; }
        .ms-person { font-size: 2.4rem; font-weight: 800; }
        .ms-org { font-size: 1.3rem; opacity: .9; margin-top: .25rem; }
        .ms-sub { font-size: 1rem; opacity: .85; margin-top: .75rem; }
        .ms-footer {
            padding: .6rem 1rem; background: #1e293b; border-top: 1px solid #334155;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem;
        }
        .ms-counter { font-size: 1rem; }
        .ms-counter strong { font-size: 1.4rem; color: #fff; }
        .ms-hint { font-size: .85rem; opacity: .7; }
        .ms-scan-input { position: absolute; left: -9999px; opacity: 0; }
    </style>
</head>
<body>
    <?php echo $content; ?>

    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/js/plugins/toast.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/js/pages/meal-scan.js?v=20261024"></script>
</body>
</html>
