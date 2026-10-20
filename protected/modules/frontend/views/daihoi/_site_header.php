<?php

/**
 * Header điều hướng dùng chung cho các trang công khai của Đại hội
 * (trang chủ Đại hội, Cổng bình chọn Miss...).
 *
 * Partial tự tính các biến cần thiết (phiên thành viên, URL, menu, logo) nên có
 * thể render độc lập ở bất kỳ trang nào dùng layout `daihoi`. Trang gọi có thể
 * truyền thêm $event (mảng thông tin sự kiện) để hiển thị đúng tên/địa điểm.
 *
 * @var array|null $event   Thông tin sự kiện (tùy chọn).
 */
$base = Yii::app()->request->baseUrl;
$logo = $base . '/themes/hope-ui/logo_daihoi.png';

$e = function ($s) {
  return CHtml::encode($s !== null ? (string) $s : '');
};

// ----- Thông tin sự kiện (lấy từ $event nếu có, kèm giá trị mặc định) -----
$ev = isset($event) && is_array($event) ? $event : array();
$pick = function ($keys, $default) use ($ev) {
  foreach ((array) $keys as $k) {
    if (isset($ev[$k]) && $ev[$k] !== '' && $ev[$k] !== null && !is_array($ev[$k])) {
      return $ev[$k];
    }
  }
  return $default;
};
$eventName = $pick(array('name', 'title'), 'ĐẠI HỘI MƯỜNG THANH 2026');
$eventLocation = $pick(array('location', 'venue', 'place'), 'Ninh Bình 2026');

// ----- Trạng thái đăng nhập / quyền quản trị -----
$hasPortalToken = !empty(Yii::app()->session['sso_token']) || AuthHandler::isAuthenticated();
$adminUrl = $base . '/admin/default/index';
$portalUrl = $base . '/run';

// ----- Trạng thái đăng nhập thành viên (cổng cá nhân /portal) -----
$session = Yii::app()->session;
$memberLoggedIn = !empty($session['portal_attendee_id']);
$memberName = $memberLoggedIn ? trim((string) $session['portal_full_name']) : '';
if ($memberLoggedIn && $memberName === '') {
  $memberName = 'Thành viên';
}
$memberHomeUrl     = $base . '/portal/index';
$memberScheduleUrl = $base . '/lich-trinh-cua-toi';
$memberLogoutUrl   = $base . '/portal/logout';

// ----- Menu điều hướng -----
$navItems = array(
  // array($base . '/binh-chon', 'Bình chọn Miss'),
  array($base . '/#noi-dung', 'Chương trình'),
  array($base . '/agenda-tong-the', 'Lịch trình'),
  array($base . '/lich-thi-the-thao', 'Lịch thi đấu'),
  array($base . '/tin-tuc', 'Tin tức'),
  array($base . '/#thu-vien', 'Thư viện'),
  array($base . '/#so-tay', 'Sổ tay'),
);
?>
<!-- HEADER -->
<header class="sticky-top bg-white border-bottom shadow-sm z-3">
  <div class="container py-2 py-lg-3">
    <div class="row align-items-center g-2 g-lg-3">
      <!-- Logo & Brand -->
      <div class="col-8 col-sm-6 col-lg-3 d-flex align-items-center">
        <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="<?php echo $e($base); ?>/">
          <img alt="<?php echo $e($eventName); ?>" class="img-fluid" src="<?php echo $e($logo); ?>" style="height:44px;object-fit:contain;">
          <div class="d-flex flex-column">
            <span class="fw-bold text-dark lh-1" style="font-size:15.5px;letter-spacing:-0.02em;">ĐẠI HỘI MƯỜNG THANH</span>
            <span class="text-primary fw-bold text-uppercase" style="font-size:10.5px;letter-spacing:0.08em;"><?php echo $e($eventLocation); ?></span>
          </div>
        </a>
      </div>
      <!-- Navigation (Desktop) -->
      <div class="col-lg-6 d-none d-lg-flex justify-content-center">
        <nav class="d-flex align-items-center gap-1">
          <?php foreach ($navItems as $it): ?>
            <a class="nav-link px-3 py-2 fw-semibold text-secondary rounded hover:text-dark transition" href="<?php echo $e($it[0]); ?>"><?php echo $e($it[1]); ?></a>
          <?php endforeach; ?>
        </nav>
      </div>
      <!-- Header Actions -->
      <div class="col-4 col-sm-6 col-lg-3 d-flex align-items-center justify-content-end gap-2">
        <?php if ($memberLoggedIn): ?>
          <div class="dropdown <?php echo $hasPortalToken ? 'd-none d-md-block' : 'd-none d-sm-block'; ?>">
            <button class="btn btn-sm fw-bold px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm text-white btn-shimmer" type="button" id="memberMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" style="background:linear-gradient(135deg,#059669 0%,#0d9488 100%);border:none;font-size:13px;">
              <span class="material-symbols-outlined" style="font-size:17px;">account_circle</span>
              <span class="text-truncate" style="max-width:150px;"><?php echo $e($memberName); ?></span>
              <span class="material-symbols-outlined" style="font-size:16px;">expand_more</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="memberMenuBtn" style="min-width:220px;">
              <li>
                <h6 class="dropdown-header text-truncate"><?php echo $e($memberName); ?></h6>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?php echo $e($memberHomeUrl); ?>">
                  <span class="material-symbols-outlined text-success" style="font-size:19px;">person</span> Trang cá nhân
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?php echo $e($memberScheduleUrl); ?>">
                  <span class="material-symbols-outlined text-success" style="font-size:19px;">event</span> Lịch trình của tôi
                </a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="<?php echo $e($memberLogoutUrl); ?>">
                  <span class="material-symbols-outlined" style="font-size:19px;">logout</span> Đăng xuất
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <a class="btn btn-sm fw-bold px-3 py-2 rounded-pill <?php echo $hasPortalToken ? 'd-none d-md-inline-flex' : 'd-none d-sm-inline-flex'; ?> align-items-center gap-1 shadow-sm text-white btn-shimmer" href="<?php echo $e($portalUrl); ?>" style="background:linear-gradient(135deg,#059669 0%,#0d9488 100%);border:none;font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:17px;">how_to_reg</span> Đăng nhập thành viên
          </a>
        <?php endif; ?>
        <?php if ($hasPortalToken): ?>
          <a class="btn btn-primary btn-sm fw-bold px-3 py-2 rounded-pill d-none d-sm-inline-flex align-items-center gap-1 shadow-sm btn-shimmer" href="<?php echo $e($adminUrl); ?>" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:17px;">admin_panel_settings</span> Vào quản trị
          </a>
        <?php endif; ?>
        <button aria-label="Menu" class="btn btn-light d-lg-none p-1 border rounded" data-bs-target="#mobileNav" data-bs-toggle="offcanvas" aria-controls="mobileNav" type="button">
          <span class="material-symbols-outlined fs-4">menu</span>
        </button>
      </div>
    </div>
  </div>
  <div class="rainbow-divider w-100"></div>
</header>

<!-- MOBILE OFFCANVAS NAV (hiển thị dạng sidebar bên phải khi bấm 3 gạch) -->
<div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel" style="max-width:320px;width:85%;">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2" id="mobileNavLabel">
      <img alt="<?php echo $e($eventName); ?>" src="<?php echo $e($logo); ?>" style="height:38px;object-fit:contain;">
      <div class="d-flex flex-column">
        <span class="fw-bold text-dark lh-1" style="font-size:14px;letter-spacing:-0.02em;">ĐẠI HỘI MƯỜNG THANH</span>
        <span class="text-primary fw-bold text-uppercase" style="font-size:10px;letter-spacing:0.08em;"><?php echo $e($eventLocation); ?></span>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column p-0">
    <nav class="nav flex-column py-2">
      <?php foreach ($navItems as $it): ?>
        <a class="nav-link d-flex align-items-center justify-content-between px-4 py-3 fw-semibold text-dark border-bottom" href="<?php echo $e($it[0]); ?>" data-bs-dismiss="offcanvas">
          <span><?php echo $e($it[1]); ?></span>
          <span class="material-symbols-outlined text-muted fs-6">chevron_right</span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="d-flex flex-column gap-2 p-4 mt-auto border-top">
      <?php if ($memberLoggedIn): ?>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="material-symbols-outlined text-success" style="font-size:22px;">account_circle</span>
          <span class="fw-bold text-dark text-truncate" style="font-size:14px;"><?php echo $e($memberName); ?></span>
        </div>
        <a class="btn btn-outline-success fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1" href="<?php echo $e($memberHomeUrl); ?>" style="font-size:14px;">
          <span class="material-symbols-outlined" style="font-size:18px;">person</span> Trang cá nhân
        </a>
        <a class="btn btn-outline-success fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1" href="<?php echo $e($memberScheduleUrl); ?>" style="font-size:14px;">
          <span class="material-symbols-outlined" style="font-size:18px;">event</span> Lịch trình của tôi
        </a>
        <a class="btn btn-outline-danger fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1" href="<?php echo $e($memberLogoutUrl); ?>" style="font-size:14px;">
          <span class="material-symbols-outlined" style="font-size:18px;">logout</span> Đăng xuất
        </a>
      <?php else: ?>
        <a class="btn fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1 text-white btn-shimmer" href="<?php echo $e($portalUrl); ?>" style="background:linear-gradient(135deg,#059669 0%,#0d9488 100%);border:none;font-size:14px;">
          <span class="material-symbols-outlined" style="font-size:18px;">how_to_reg</span> Đăng nhập thành viên
        </a>
      <?php endif; ?>
      <?php if ($hasPortalToken): ?>
        <a class="btn btn-primary fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1 btn-shimmer" href="<?php echo $e($adminUrl); ?>" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;font-size:14px;">
          <span class="material-symbols-outlined" style="font-size:18px;">admin_panel_settings</span> Vào quản trị
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>