<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CHtml::encode($this->pageTitle ?? 'Admin - ' . Yii::app()->name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; }
        .sidebar { width: 250px; min-height: 100vh; background: #212529; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #343a40; color: #fff; }
        .main-content { flex: 1; }
        .navbar-admin { background: #343a40; }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php if (!Yii::app()->user->isGuest): ?>
        <nav class="sidebar">
            <div class="p-3 text-white">
                <h5><i class="bi bi-gear-fill"></i> Admin Panel</h5>
            </div>
            <hr class="text-secondary">
            <a href="<?php echo $this->createUrl('/admin/site/index'); ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?php echo $this->createUrl('/admin/user/index'); ?>">
                <i class="bi bi-people"></i> Quản lý User
            </a>
            <a href="<?php echo $this->createUrl('/admin/event/index'); ?>">
                <i class="bi bi-calendar-event"></i> Quản lý Event
            </a>
            <hr class="text-secondary">
            <a href="<?php echo $this->createUrl('/admin/site/logout'); ?>">
                <i class="bi bi-box-arrow-right"></i> Đăng xuất
            </a>
        </nav>
        <?php endif; ?>

        <div class="main-content">
            <?php if (!Yii::app()->user->isGuest): ?>
            <nav class="navbar navbar-admin navbar-dark px-4">
                <span class="navbar-text text-white">
                    Xin chào, <?php echo CHtml::encode(Yii::app()->user->name); ?>
                </span>
            </nav>
            <?php endif; ?>

            <div class="p-4">
                <?php echo $content; ?>
            </div>
        </div>
    </div>

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
