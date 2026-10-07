<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo CHtml::encode($this->pageTitle ? $this->pageTitle . ' - Đại Hội Mường Thanh 2026' : 'Cổng Cá Nhân Đại Biểu - Đại Hội Mường Thanh 2026'); ?></title>
    <link rel="shortcut icon" href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/images/favicon.ico">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <!-- Portal Authentication CSS -->
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/css/pages/portal-auth.css" rel="stylesheet">
</head>
<body class="portal-auth-body">
    <!-- Ambient glowing backdrop orbs -->
    <div class="auth-ambient-orb orb-1"></div>
    <div class="auth-ambient-orb orb-2"></div>
    <div class="auth-ambient-orb orb-3"></div>
    <div class="auth-grid-overlay"></div>

    <div class="portal-auth-wrapper min-vh-100 d-flex flex-column justify-content-between">
        <!-- Main Content Area -->
        <main class="flex-grow-1 d-flex align-items-center py-2 py-md-4 py-lg-5">
            <div class="container">
                <?php echo $content; ?>
            </div>
        </main>

        <!-- Modern Footer -->
        <footer class="portal-auth-footer py-3 py-md-4 text-center">
            <div class="container">
                <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2 gap-sm-3 text-white-50 small">
                    <span>&copy; <?php echo date('Y'); ?> <strong>Đại hội Mường Thanh 2026</strong></span>
                    <span class="d-none d-sm-inline">•</span>
                    <span>Ninh Bình - Hành trình di sản</span>
                    <span class="d-none d-sm-inline">•</span>
                    <span>Hỗ trợ kỹ thuật: Ban Công nghệ Thông tin</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Vendor Scripts -->
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/js/plugins/toast.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>

    <!-- Flash Toast Notifications -->
    <?php
    $flashSuccess = Yii::app()->user->getFlash('success');
    $flashError = Yii::app()->user->getFlash('error');
    $flashWarning = Yii::app()->user->getFlash('warning');
    $flashInfo = Yii::app()->user->getFlash('info');
    if ($flashSuccess || $flashError || $flashWarning || $flashInfo):
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($flashSuccess): ?>
                    Toast.success('<?php echo addslashes($flashSuccess); ?>');
                <?php endif; ?>
                <?php if ($flashError): ?>
                    Toast.error('<?php echo addslashes($flashError); ?>');
                <?php endif; ?>
                <?php if ($flashWarning): ?>
                    Toast.warning('<?php echo addslashes($flashWarning); ?>');
                <?php endif; ?>
                <?php if ($flashInfo): ?>
                    Toast.info('<?php echo addslashes($flashInfo); ?>');
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>
