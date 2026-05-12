<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($this->pageTitle ?? Yii::app()->name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 70px; }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo Yii::app()->homeUrl; ?>">
                <?php echo CHtml::encode(Yii::app()->name); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $this->createUrl('/frontend/site/index'); ?>">Trang chủ</a>
                    </li>
                    <?php if (Yii::app()->user->isGuest): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->createUrl('/frontend/site/login'); ?>">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->createUrl('/frontend/site/register'); ?>">Đăng ký</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $this->createUrl('/frontend/site/logout'); ?>">
                                Đăng xuất (<?php echo Yii::app()->user->name; ?>)
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php echo $content; ?>
    </div>

    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo Yii::app()->name; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/js/plugins/toast.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
