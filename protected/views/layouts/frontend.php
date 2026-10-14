<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($this->pageTitle ?? Yii::app()->name); ?></title>
    <!-- Google Fonts: Inter (Full Vietnamese support) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/public/vendor/fonts/inter/inter.css">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/css/font-awesome/font-awesome.min.css" rel="stylesheet">
    <style>
        body, button, input, select, textarea {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }

        .frontend-content {
            flex: 1 0 auto;
        }

        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <main class="frontend-content">
        <div class="container">
            <?php echo $content; ?>
        </div>
    </main>

    <footer class="bg-light py-4 mt-auto border-top">
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> <?php echo Yii::app()->name; ?>. All rights reserved.</p>
        </div>
    </footer>

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