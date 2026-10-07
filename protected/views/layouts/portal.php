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

    <!-- Bootstrap 5, FontAwesome & Bootstrap Icons -->
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/css/font-awesome/font-awesome.min.css" rel="stylesheet">

    <!-- Portal Base Stylesheet -->
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/css/pages/portal-index.css" rel="stylesheet">
</head>

<body class="portal-body">
    <!-- Portal Main Wrapper -->
    <div class="portal-wrapper">
        <!-- Main Content Area -->
        <main class="portal-content">
            <?php echo $content; ?>
        </main>

        <!-- Fixed Bottom Footer (Cố định ở đáy màn hình) -->
        <footer class="portal-fixed-footer">
            <div class="container">
                <div class="portal-footer-inner">
                    <div class="portal-footer-left">
                        <span class="portal-footer-dot"></span>
                        <span>&copy; <?php echo date('Y'); ?> <strong>Đại hội Mường Thanh 2026</strong></span>
                        <span class="d-none d-sm-inline">•</span>
                        <span class="d-none d-sm-inline">Ninh Bình - Hành trình di sản</span>
                    </div>
                    <div class="portal-footer-right">
                        <span class="portal-footer-status">
                            <span class="pulse-dot-green"></span>
                            <span>Trực tuyến</span>
                        </span>
                        <span class="d-none d-md-inline">•</span>
                        <span class="portal-footer-support">
                            <i class="bi bi-headset text-primary"></i> Hỗ trợ: Ban CNTT
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Vendor Scripts -->
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/js/plugins/toast.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>

    <script>
        function confirmDelete(formId) {
            Swal.fire({
                title: 'Xác nhận xóa',
                text: 'Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>

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
