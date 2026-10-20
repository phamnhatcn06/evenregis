<?php
/**
 * Header dùng chung cho các trang con của Đại hội (Lịch trình, Lịch thi đấu).
 *
 * @var string $eventName  Tên sự kiện hiển thị cạnh logo
 * @var string $active     Trang đang mở: 'agenda' | 'welcome' | 'schedule' | 'tour' | 'funrun' | 'gala' | 'handbook' | 'map' | 'support' | 'secretary'
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$logo = $base . '/themes/hope-ui/logo_daihoi.png';
$tabs = array(
    'agenda' => array('url' => $base . '/agenda-tong-the', 'label' => 'Agenda', 'icon' => 'calendar_month'),
    'welcome' => array('url' => $base . '/tiec-welcome', 'label' => 'Khai mạc & Miss', 'icon' => 'celebration'),
    'schedule' => array('url' => $base . '/lich-thi-the-thao', 'label' => 'Thi đấu', 'icon' => 'emoji_events'),
    'tour' => array('url' => $base . '/lich-tham-quan', 'label' => 'Tham quan', 'icon' => 'tour'),
    'funrun' => array('url' => $base . '/fun-run', 'label' => 'Fun Run', 'icon' => 'directions_run'),
    'gala' => array('url' => $base . '/tiec-gala', 'label' => 'Bế mạc', 'icon' => 'nightlife'),
    'news' => array('url' => $base . '/tin-tuc', 'label' => 'Tin tức', 'icon' => 'newspaper'),
    'handbook' => array('url' => $base . '/so-tay-dai-hoi', 'label' => 'Sổ tay', 'icon' => 'menu_book'),
    'map' => array('url' => $base . '/so-do-dia-diem', 'label' => 'Sơ đồ', 'icon' => 'map'),
    'support' => array('url' => $base . '/ho-tro', 'label' => 'Hỗ trợ', 'icon' => 'support_agent'),
    'secretary' => array('url' => $base . '/doan-thu-ky', 'label' => 'Thư ký đoàn', 'icon' => 'groups'),
);
$active = isset($active) ? $active : '';
$eventName = isset($eventName) && $eventName !== '' ? $eventName : 'Đại hội Mường Thanh 2026';
?>
<?php
$isLogged = (!empty(Yii::app()->session['sso_token']) || AuthHandler::isAuthenticated());
?>
<header class="bg-white border-bottom shadow-sm sticky-top">
  <div class="container py-2 d-flex align-items-center justify-content-between gap-2">
    <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="<?php echo $e($base); ?>/">
      <img src="<?php echo $e($logo); ?>" alt="Logo" style="height:38px;width:auto;" onerror="this.style.display='none'">
      <span class="fw-bold d-none d-sm-inline" style="font-size:15px;"><?php echo $e($eventName); ?></span>
    </a>

    <!-- Nav inline trên desktop -->
    <nav class="d-none d-lg-flex align-items-center flex-wrap justify-content-end gap-2">
      <?php foreach ($tabs as $key => $t):
        $isActive = $key === $active;
        $cls = $isActive
            ? 'btn-primary text-white'
            : 'btn-light text-secondary border';
      ?>
        <a class="btn btn-sm <?php echo $cls; ?> fw-semibold rounded-pill px-3 d-inline-flex align-items-center gap-1" href="<?php echo $e($t['url']); ?>">
          <span class="material-symbols-outlined fs-6"><?php echo $e($t['icon']); ?></span>
          <span><?php echo $e($t['label']); ?></span>
        </a>
      <?php endforeach; ?>
      <a class="btn btn-sm btn-dark fw-semibold rounded-pill px-3 d-inline-flex align-items-center gap-1" href="<?php echo $e($base); ?>/">
        <span class="material-symbols-outlined fs-6">home</span>
        <span>Trang chủ</span>
      </a>
      <?php if ($isLogged): ?>
        <a class="btn btn-sm btn-primary fw-semibold rounded-pill px-3 d-inline-flex align-items-center gap-1" href="<?php echo $e($base); ?>/admin/default/index" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;">
          <span class="material-symbols-outlined fs-6">admin_panel_settings</span>
          <span>Vào quản trị</span>
        </a>
      <?php endif; ?>
    </nav>

    <!-- Nút menu chỉ hiện trên mobile/tablet -->
    <button aria-label="Menu" class="btn btn-light d-lg-none p-1 border rounded" data-bs-target="#subheaderNav" data-bs-toggle="offcanvas" aria-controls="subheaderNav" type="button">
      <span class="material-symbols-outlined fs-4">menu</span>
    </button>
  </div>
</header>

<!-- MENU OFFCANVAS (sidebar bên phải khi bấm 3 gạch trên mobile) -->
<div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="subheaderNav" aria-labelledby="subheaderNavLabel" style="max-width:320px;width:85%;">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2" id="subheaderNavLabel">
      <img src="<?php echo $e($logo); ?>" alt="Logo" style="height:36px;width:auto;" onerror="this.style.display='none'">
      <span class="fw-bold text-dark lh-1" style="font-size:14px;"><?php echo $e($eventName); ?></span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column p-0">
    <nav class="nav flex-column py-2">
      <?php foreach ($tabs as $key => $t):
        $isActive = $key === $active;
      ?>
        <a class="nav-link d-flex align-items-center gap-2 px-4 py-3 fw-semibold border-bottom <?php echo $isActive ? 'text-primary bg-light' : 'text-dark'; ?>" href="<?php echo $e($t['url']); ?>" data-bs-dismiss="offcanvas">
          <span class="material-symbols-outlined fs-6"><?php echo $e($t['icon']); ?></span>
          <span class="flex-grow-1"><?php echo $e($t['label']); ?></span>
          <span class="material-symbols-outlined text-muted fs-6">chevron_right</span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="d-flex flex-column gap-2 p-4 mt-auto border-top">
      <a class="btn btn-dark fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1" href="<?php echo $e($base); ?>/">
        <span class="material-symbols-outlined" style="font-size:18px;">home</span> Trang chủ
      </a>
      <?php if ($isLogged): ?>
        <a class="btn btn-primary fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1" href="<?php echo $e($base); ?>/admin/default/index" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;">
          <span class="material-symbols-outlined" style="font-size:18px;">admin_panel_settings</span> Vào quản trị
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>
