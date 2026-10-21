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

    <!-- Nav inline trên desktop: 1 hàng, kiểu link gạch chân (không dùng pill) -->
    <nav class="dh-subnav d-none d-lg-flex align-items-center justify-content-end">
      <?php foreach ($tabs as $key => $t):
        $isActive = $key === $active;
      ?>
        <a class="dh-subnav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $e($t['url']); ?>"><?php echo $e($t['label']); ?></a>
      <?php endforeach; ?>
      <span class="dh-subnav-sep"></span>
      <a class="dh-subnav-link d-inline-flex align-items-center gap-1" href="<?php echo $e($base); ?>/">
        <span class="material-symbols-outlined" style="font-size:16px;">home</span>
        <span>Trang chủ</span>
      </a>
      <?php if ($isLogged): ?>
        <a class="dh-subnav-admin d-inline-flex align-items-center gap-1" href="<?php echo $e($base); ?>/admin/default/index">
          <span class="material-symbols-outlined" style="font-size:16px;">admin_panel_settings</span>
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

<style>
/* Menu desktop Đại hội: 1 hàng, link gạch chân (thay cho pill) */
.dh-subnav { flex-wrap: nowrap; column-gap: 2px; }
.dh-subnav-link {
  position: relative;
  padding: 6px 9px;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.2;
  color: #64748b;
  text-decoration: none;
  white-space: nowrap;
  transition: color .15s ease;
}
.dh-subnav-link::after {
  content: "";
  position: absolute;
  left: 9px; right: 9px; bottom: 1px;
  height: 2px;
  background: #2563eb;
  border-radius: 2px;
  transform: scaleX(0);
  transform-origin: center;
  transition: transform .2s ease;
}
.dh-subnav-link:hover { color: #1e293b; }
.dh-subnav-link:hover::after,
.dh-subnav-link.active::after { transform: scaleX(1); }
.dh-subnav-link.active { color: #2563eb; }
.dh-subnav-sep {
  width: 1px;
  height: 20px;
  background: #e2e8f0;
  margin: 0 6px;
}
.dh-subnav-admin {
  padding: 6px 12px;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  white-space: nowrap;
  border-radius: 8px;
  background: linear-gradient(135deg, #1d4ed8 0%, #312e81 100%);
  transition: filter .15s ease;
}
.dh-subnav-admin:hover { color: #fff; filter: brightness(1.08); }
/* Thu gọn thêm khi màn hình desktop nhỏ để giữ 1 hàng */
@media (min-width: 992px) and (max-width: 1399px) {
  .dh-subnav-link { padding: 6px 6px; font-size: 12.5px; }
  .dh-subnav-link::after { left: 6px; right: 6px; }
}
</style>
