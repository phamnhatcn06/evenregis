<?php
$menuPermissions = AuthHandler::getMenuPermissions();
$tokenHash = md5(Yii::app()->session['sso_token']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Redirecting...</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
        }
        .loader {
            text-align: center;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e0e0e0;
            border-top-color: #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Redirecting...</p>
    </div>
    <script>
        (function() {
            var userProfile = <?php echo $userProfile ? CJSON::encode($userProfile) : 'null'; ?>;
            var menuPermissions = <?php echo !empty($menuPermissions) ? CJSON::encode($menuPermissions) : '[]'; ?>;
            var tokenHash = <?php echo CJSON::encode($tokenHash); ?>;
            var redirectUrl = <?php echo CJSON::encode($redirectUrl); ?>;

            if (userProfile) {
                localStorage.setItem('sso_user_profile', JSON.stringify(userProfile));
            }

            if (menuPermissions && menuPermissions.length > 0) {
                localStorage.setItem('sso_menu_permissions', JSON.stringify(menuPermissions));
                localStorage.setItem('sso_token_hash', tokenHash);
            }

            window.location.href = redirectUrl;
        })();
    </script>
</body>
</html>
