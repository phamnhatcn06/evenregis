<?php

/**
 * Created by PhpStorm.
 * User: Nhat.IT
 * Date: 02/01/2018
 * Time: 3:17 CH
 */
$this->beginContent('//layouts/mainMod'); ?>

<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">

    <div class="slimscroll-menu" id="remove-scroll">

        <!-- LOGO -->
        <div class="topbar-left">
            <img src="<?= Yii::app()->theme->baseUrl ?>/vertical/assets/images/logo-white.png" alt="user-img" title="<?= $this->user->name ?>">
        </div>
        <!-- User box -->
        <div class="user-box">
            <h5><a href="#"><?= $this->user->name ?></a></h5>
            <p class="text-muted"><?= $this->user->role->title; ?></p>
            <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated profile-dropdown ">
            </div>
            <div class="dropdown profile-element">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <span class="text-muted text-xs block profile-options">Quản lý tài khoản<b class="caret"></b></span> </span>
                </a>
                <ul class="dropdown-menu m-t-xs">
                    <li><a href="/admin/users/profile" class="dropdown-item notify-item">
                            <i class="fi-head"></i> <span>Thông tin</span>
                        </a></li>
                    <li><a href="/admin/users/changePassword" class="dropdown-item notify-item">
                            <i class="fi-repeat"></i> <span>Đổi mật khẩu</span>
                        </a></li>
                </ul>
            </div>
        </div>


        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <?php echo HelperBase::generateTree($this->dataTree(), array('class' => 'metismenu', 'id' => 'side-menu')); ?>
            <?php if ((int) $this->user->can_create_report == 1 || ($this->user->role_id == Params::$gmRole ||
                $this->user->role_id == Params::$dgmRole)) { ?>
                <div class="create_report">
                    <a href="<?= Yii::app()->createUrl('hotel/report/admin'); ?>" class="btn btn-info waves-effect waves-light">
                        <i class="fa fa-plus
          m-r-5"></i>
                        <span>Quản lý báo cáo</span> </a>
                </div>

                <div class="create_report">
                    <a href="<?= Yii::app()->createUrl('hotel/report/index'); ?>" class="btn btn-info waves-effect waves-light">
                        <i class="fa fa-search
          m-r-5"></i>
                        <span>Quản lý vấn đề DV ghi nhận</span> </a>
                </div>
            <?php } ?>

        </div>
        <!-- Sidebar -->
        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>
<!-- Left Sidebar End -->
<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->

<div class="content-page">
    <!-- Top Bar Start -->
    <div class="topbar">
        <nav class="navbar-custom">
            <ul class="list-unstyled topbar-right-menu float-right mb-0">

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle arrow-none" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="fi-bell noti-icon"></i>
                        <span class="badge badge-danger badge-pill noti-icon-badge">4</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-lg">

                        <!-- item-->
                        <div class="dropdown-item noti-title">
                            <h5 class="m-0"><span class="float-right"><a href="" class="text-dark"><small>Clear All</small></a> </span>Notification
                            </h5>
                        </div>

                        <div class="slimscroll" style="max-height: 230px;">
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-success"><i class="mdi mdi-comment-account-outline"></i>
                                </div>
                                <p class="notify-details">Caleb Flakelar commented on Admin
                                    <small class="text-muted">1 min ago</small>
                                </p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-info"><i class="mdi mdi-account-plus"></i></div>
                                <p class="notify-details">New user registered.
                                    <small class="text-muted">5 hours ago</small>
                                </p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-danger"><i class="mdi mdi-heart"></i></div>
                                <p class="notify-details">Carlos Crouch liked <b>Admin</b>
                                    <small class="text-muted">3 days ago</small>
                                </p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-warning"><i class="mdi mdi-comment-account-outline"></i>
                                </div>
                                <p class="notify-details">Caleb Flakelar commented on Admin
                                    <small class="text-muted">4 days ago</small>
                                </p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-purple"><i class="mdi mdi-account-plus"></i></div>
                                <p class="notify-details">New user registered.
                                    <small class="text-muted">7 days ago</small>
                                </p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-custom"><i class="mdi mdi-heart"></i></div>
                                <p class="notify-details">Carlos Crouch liked <b>Admin</b>
                                    <small class="text-muted">13 days ago</small>
                                </p>
                            </a>
                        </div>

                        <!-- All-->
                        <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">
                            View all <i class="fi-arrow-right"></i>
                        </a>

                    </div>
                </li>
                <li class="dropdown notification-list">
                    <a href="/admin/users/logout">
                        <i class="fa fa-sign-out"></i> Đăng xuất
                    </a>
                </li>

            </ul>
            <ul class="float-left list-inline menu-left mb-0">

                <li class="float-left">
                    <button class="button-menu-mobile open-left btn btn-success  minimalize-styl-2">
                        <i class="dripicons-menu"></i>
                    </button>
                </li>
                <li>
                    <span class="text-welcome">Chào mừng đến với website Đại hội cổ đông 2021</span>
                </li>
            </ul>
        </nav>
    </div>
    <div class="breadcrumb-top">
        <?php if (isset($this->breadcrumbs) && $this->breadcrumbs != null) { ?>
            <?= MyHelper::renderBreadCum($this->breadcrumbs); ?>
        <?php } ?>
    </div>
    <!-- Top Bar End -->
    <!-- Start Page content -->
    <div class="content">
        <div class="container-fluid">
            <?php if (isset($this->menu) && count($this->menu) > 0) { ?>
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5><i class="fi-paper "></i> &nbsp;Chức năng:</h5>
                        <div class="pull-left action-button" style="margin-left: 20px;">
                            <?php if (isset($this->menu) && count($this->menu) > 0) { ?>
                                <?php
                                echo HelperBase::generatButton($this->menu);
                                ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?= $content; ?>
        </div>
    </div>

    <footer class="footer text-right">
        2018 © Phát triển bởi nhóm phần mềm - Phòng công nghệ thông tin
    </footer>
</div>
<!-- ============================================================== -->
<!-- End Right content here -->
<!-- ============================================================== -->
<?php $this->endContent(); ?>