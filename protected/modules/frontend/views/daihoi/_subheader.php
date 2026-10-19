<?php
/**
 * Header dùng chung cho các trang con của Đại hội (Lịch trình, Lịch thi đấu).
 *
 * @var string $eventName  Tên sự kiện hiển thị cạnh logo
 * @var string $active     Trang đang mở: 'agenda' | 'welcome' | 'schedule' | 'tour' | 'funrun' | 'gala' | 'map' | 'support' | 'secretary'
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
    'map' => array('url' => $base . '/so-do-dia-diem', 'label' => 'Sơ đồ', 'icon' => 'map'),
    'support' => array('url' => $base . '/ho-tro', 'label' => 'Hỗ trợ', 'icon' => 'support_agent'),
    'secretary' => array('url' => $base . '/doan-thu-ky', 'label' => 'Thư ký đoàn', 'icon' => 'groups'),
);
$active = isset($active) ? $active : '';
$eventName = isset($eventName) && $eventName !== '' ? $eventName : 'Đại hội Mường Thanh 2026';
?>
<header class="bg-white border-bottom shadow-sm sticky-top">
  <div class="container py-2 d-flex align-items-center justify-content-between gap-2 flex-wrap">
    <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="<?php echo $e($base); ?>/">
      <img src="<?php echo $e($logo); ?>" alt="Logo" style="height:38px;width:auto;" onerror="this.style.display='none'">
      <span class="fw-bold d-none d-sm-inline" style="font-size:15px;"><?php echo $e($eventName); ?></span>
    </a>
    <nav class="d-flex align-items-center gap-2">
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
      <a class="btn btn-sm btn-dark fw-semibold rounded-pill px-3 d-none d-md-inline-flex align-items-center gap-1" href="<?php echo $e($base); ?>/">
        <span class="material-symbols-outlined fs-6">home</span>
        <span>Trang chủ</span>
      </a>
      <?php if (!empty(Yii::app()->session['sso_token']) || AuthHandler::isAuthenticated()): ?>
        <a class="btn btn-sm btn-primary fw-semibold rounded-pill px-3 d-none d-md-inline-flex align-items-center gap-1" href="<?php echo $e($base); ?>/admin/default/index" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;">
          <span class="material-symbols-outlined fs-6">admin_panel_settings</span>
          <span>Vào quản trị</span>
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>
