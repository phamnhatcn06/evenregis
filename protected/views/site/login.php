<?php
/**
 * @var SiteController $this
 */
$this->pageTitle = 'Đăng nhập - ' . Yii::app()->name;

$params = require Yii::getPathOfAlias('application.config') . '/params.php';
$portalUrl = $params['portal']['url'];
$returnUrl = Yii::app()->request->hostInfo . Yii::app()->request->baseUrl;
$loginUrl = $portalUrl . '/login?redirect=' . urlencode($returnUrl);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 420px;
            width: 90%;
        }
        .logo { margin-bottom: 24px; }
        .logo img { max-width: 180px; height: auto; }
        h1 { font-size: 24px; color: #333; margin-bottom: 8px; }
        .subtitle { color: #666; margin-bottom: 32px; font-size: 14px; }
        .btn-portal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-portal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .flash-message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .flash-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .flash-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .flash-info { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/images/logo.png"
                 alt="Logo" onerror="this.style.display='none'">
        </div>

        <h1><?php echo CHtml::encode(Yii::app()->name); ?></h1>
        <p class="subtitle">Vui lòng đăng nhập bằng tài khoản Portal</p>

        <?php if (Yii::app()->user->hasFlash('error')): ?>
            <div class="flash-message flash-error"><?php echo Yii::app()->user->getFlash('error'); ?></div>
        <?php endif; ?>

        <?php if (Yii::app()->user->hasFlash('info')): ?>
            <div class="flash-message flash-info"><?php echo Yii::app()->user->getFlash('info'); ?></div>
        <?php endif; ?>

        <a href="<?php echo CHtml::encode($loginUrl); ?>" class="btn-portal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10,17 15,12 10,7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Đăng nhập với Portal
        </a>
    </div>
</body>
</html>
