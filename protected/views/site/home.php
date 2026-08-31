<?php

/**
 * Trang chủ sau khi đăng nhập SSO.
 * @var SiteController $this
 * @var array $user
 * @var bool $hasAdminAccess
 */
$this->pageTitle = 'Trang chủ - ' . Yii::app()->name;

$fullName = isset($user['full_name']) ? $user['full_name'] : '';
$adminUrl = Yii::app()->createUrl('/admin/default/index');
$logoutUrl = Yii::app()->createUrl('/site/logout');
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .home-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 460px;
            width: 90%;
        }

        .logo {
            margin-bottom: 24px;
        }

        .logo img {
            max-width: 180px;
            height: auto;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }

        .welcome {
            color: #666;
            margin-bottom: 32px;
            font-size: 15px;
        }

        .welcome strong {
            color: #333;
        }

        .btn-admin {
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

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .no-access {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .btn-logout {
            display: inline-block;
            margin-top: 20px;
            color: #888;
            font-size: 13px;
            text-decoration: none;
        }

        .btn-logout:hover {
            color: #dc2626;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="home-container">
        <div class="logo">
            <img src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/images/logo.png"
                alt="Logo" onerror="this.style.display='none'">
        </div>

        <h1><?php echo CHtml::encode(Yii::app()->name); ?></h1>
        <p class="welcome">Xin chào<?php echo $fullName ? ', <strong>' . CHtml::encode($fullName) . '</strong>' : ''; ?></p>

        <?php if ($hasAdminAccess): ?>
            <a href="<?php echo CHtml::encode($adminUrl); ?>" class="btn-admin">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="14" y="14" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                </svg>
                Đăng nhập admin
            </a>
        <?php else: ?>
            <div class="no-access">
                Tài khoản của bạn chưa được phân quyền cho chức năng nào. Vui lòng liên hệ quản trị viên.
            </div>
        <?php endif; ?>

        <div>
            <a href="<?php echo CHtml::encode($logoutUrl); ?>" class="btn-logout">Đăng xuất</a>
        </div>
    </div>
</body>

</html>
