<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($this->pageTitle ?? Yii::app()->name); ?></title>
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 70px;
        }

        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php echo $content; ?>
    </div>

    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo Yii::app()->name; ?>. All rights reserved.</p>
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