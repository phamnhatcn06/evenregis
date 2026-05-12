<?php

/**
 * Main Layout - Hope UI Dashboard
 * @var Controller $this
 * @var string $content
 */
$baseUrl = Yii::app()->theme->baseUrl;
$user = AuthHandler::getUser();
$appName = Yii::app()->name;
?>
<!DOCTYPE html>
<html lang="vi" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo CHtml::encode($this->title . ' - ' . $appName); ?></title>
    <meta name="description" content="<?php echo CHtml::encode($appName); ?>">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo $baseUrl; ?>/assets/images/favicon.ico" />

    <!-- Hope UI CSS -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/core/libs.min.css" />
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/hope-ui-thangvc.css?v=2.0.0" />
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/custom.min.css?v=2.0.0" />
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/dark.min.css" />
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/customizer.min.css" />
    <style>
        /* 

        */
        .sidebar.sidebar-mini~.main-content .iq-navbar.navbar-sticky {
            left: 4.8rem;
        }
    </style>
</head>

<body>
    <!-- Loader -->
    <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body"></div>
        </div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all">
        <div class="sidebar-header d-flex align-items-center justify-content-start">
            <a href="<?php echo Yii::app()->homeUrl; ?>" class="navbar-brand">
                <div class="logo-main">
                    <div class="logo-normal">
                        <svg class="icon-30 text-primary" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                        </svg>
                    </div>
                    <div class="logo-mini">
                        <svg class="icon-30 text-primary" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2" transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2" transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2" transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2" transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                        </svg>
                    </div>
                </div>
                <h4 class="logo-title">Sự kiện</h4>
            </a>
            <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                <i class="icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </i>
            </div>
        </div>
        <div class="sidebar-body pt-0 data-scrollbar">
            <div class="sidebar-list">
                <?php
                // Build menu tree from permissions
                $menuPermissions = PermissionHelper::getMenuPermissions();
                $menuTree = MenuHelper::buildMenuTree($menuPermissions);
                ?>
                <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                    <li class="nav-item static-item">
                        <a class="nav-link static-item disabled" href="#" tabindex="-1">
                            <span class="default-icon">Menu</span>
                            <span class="mini-icon">-</span>
                        </a>
                    </li>
                    <?php echo MenuHelper::renderMenu($menuTree); ?>
                </ul>
            </div>
        </div>
        <div class="sidebar-footer"></div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="position-relative iq-banner">
            <!-- Navbar -->
            <nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
                <div class="container-fluid navbar-inner">
                    <a href="<?php echo Yii::app()->homeUrl; ?>" class="navbar-brand">
                        <h4 class="logo-title"><?php echo CHtml::encode($appName); ?></h4>
                    </a>

                    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true">
                        <i class="icon">
                            <svg width="20px" class="icon-20" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
                            </svg>
                        </i>
                    </div>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon">
                            <span class="mt-2 navbar-toggler-bar bar1"></span>
                            <span class="navbar-toggler-bar bar2"></span>
                            <span class="navbar-toggler-bar bar3"></span>
                        </span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <?php if (!empty($this->breadcrumbs)): ?>
                            <nav aria-label="breadcrumb" class="me-auto">
                                <?php $this->widget('zii.widgets.CBreadcrumbs', array(
                                    'links' => $this->breadcrumbs,
                                    'htmlOptions' => array('class' => 'breadcrumb mb-0'),
                                    'tagName' => 'ol',
                                    'separator' => '',
                                    'activeLinkTemplate' => '<li class="breadcrumb-item"><a href="{url}">{label}</a></li>',
                                    'inactiveLinkTemplate' => '<li class="breadcrumb-item active" aria-current="page">{label}</li>',
                                    'homeLink' => '<li class="breadcrumb-item"><a href="' . Yii::app()->homeUrl . '">Trang chủ</a></li>',
                                )); ?>
                            </nav>
                        <?php endif; ?>
                        <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0">
                            <!-- User Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link py-0 d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo $baseUrl; ?>/assets/images/avatars/01.png" alt="User" class="img-fluid avatar avatar-50 avatar-rounded">
                                    <div class="caption ms-3 d-none d-md-block">
                                        <h6 class="mb-0 caption-title"><?php echo $user ? CHtml::encode($user['full_name']) : 'Guest'; ?></h6>
                                        <p class="mb-0 caption-sub-title"><?php echo $user ? CHtml::encode($user['email']) : ''; ?></p>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="<?php echo Yii::app()->createUrl('/admin/profile'); ?>">Hồ sơ</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="<?php echo Yii::app()->createUrl('/site/logout'); ?>">Đăng xuất</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Breadcrumb -->
            <?php if (!empty($this->breadcrumbs)): ?>
                <div class="iq-navbar-header" style="height: 80px;">
                    <div class="iq-header-img">
                        <img src="<?php echo $baseUrl; ?>/assets/images/dashboard/top-header.png" alt="header" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page Content -->
        <div class="conatiner-fluid content-inner mt-n5 py-0">
            <div class="row">
                <div class="col-12">
                    <?php echo $content; ?>
                    <?php
                    // Toast notifications for flash messages
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
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-body">
                <ul class="left-panel list-inline mb-0 p-0">
                    <li class="list-inline-item">&copy; <?php echo date('Y'); ?> <?php echo CHtml::encode($appName); ?></li>
                </ul>
            </div>
        </footer>
    </main>

    <!-- Scripts -->
    <script src="<?php echo $baseUrl; ?>/assets/js/core/libs.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/core/external.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/charts/widgetcharts.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/charts/vectore-chart.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/charts/dashboard.js" defer></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/plugins/fslightbox.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/plugins/setting.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/plugins/slider-tabs.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/plugins/form-wizard.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/hope-ui.js" defer></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/plugins/toast.js"></script>
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
    <script>
        (function() {
            var navbar = document.querySelector('.iq-navbar');
            var sidebar = document.querySelector('.sidebar');
            var navbarOffset = navbar ? navbar.offsetTop + 100 : 100;

            function updateNavbarPosition() {
                if (navbar && navbar.classList.contains('navbar-sticky')) {
                    var sidebarWidth = sidebar && !sidebar.classList.contains('sidebar-mini') ? '16.2rem' : '4.8rem';
                    navbar.style.left = sidebarWidth;
                }
            }

            window.addEventListener('scroll', function() {
                if (window.pageYOffset > navbarOffset) {
                    navbar.classList.add('navbar-sticky');
                    updateNavbarPosition();
                } else {
                    navbar.classList.remove('navbar-sticky');
                    navbar.style.left = '';
                }
            });

            // Watch for sidebar toggle
            var observer = new MutationObserver(updateNavbarPosition);
            if (sidebar) {
                observer.observe(sidebar, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
        })();
    </script>
</body>

</html>