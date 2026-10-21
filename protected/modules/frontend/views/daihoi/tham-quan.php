<?php

/**
 * Trang thông tin Tour tham quan Di sản Tràng An - slug /lich-tham-quan.
 * Nội dung tổng hợp từ tài liệu "Tour tham quan 1/2 ngày - Di sản Tràng An"
 * (Ban Lễ tân - Tour - Tâm linh, Đại hội Mường Thanh 2026).
 * Trang công khai giới thiệu chương trình, lộ trình và quy định tham quan.
 *
 * @var array $event
 */
$base = Yii::app()->request->baseUrl;
$img = $base . '/themes/hope-ui/assets/images';
$e = function ($s) {
  return CHtml::encode($s);
};
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$accent = '#0d9488';

// Ba khung thời gian (đợt) CBNV lựa chọn đăng ký.
$sessions = array(
  array('no' => 'Đợt 1', 'date' => '27/10', 'period' => 'Chiều', 'time' => '13h30 - 17h00'),
  array('no' => 'Đợt 2', 'date' => '29/10', 'period' => 'Sáng', 'time' => '08h00 - 11h30'),
  array('no' => 'Đợt 3', 'date' => '29/10', 'period' => 'Chiều', 'time' => '13h30 - 17h00'),
);

// Thông tin chương trình - KDL Sinh thái Tràng An.
$info = array(
  array('icon' => 'hotel', 'label' => 'Điểm xuất phát / kết thúc', 'value' => 'Mường Thanh Luxury Ninh Bình'),
  array('icon' => 'directions_bus', 'label' => 'Phương tiện', 'value' => 'Xe ô tô 45 chỗ'),
  array('icon' => 'sailing', 'label' => 'Hình thức tham quan', 'value' => 'Đi thuyền (tối đa 6 người/thuyền)'),
  array('icon' => 'schedule', 'label' => 'Thời lượng tham quan', 'value' => '3,5 tiếng (trong đó ~2,5 tiếng trên thuyền)'),
  array('icon' => 'near_me', 'label' => 'Khoảng cách', 'value' => '9 km từ khách sạn đến Tràng An'),
  array('icon' => 'confirmation_number', 'label' => 'Tour bao gồm', 'value' => 'Xe ô tô, vé thắng cảnh, thuyền tham quan, HDV, Bảo hiểm du lịch'),
);

// Nội dung chương trình theo trình tự.
$itinerary = array(
  array(
    'icon' => 'flag',
    'title' => 'Khởi hành',
    'text' => 'HDV và xe ô tô đón đoàn tại khách sạn Mường Thanh Luxury Ninh Bình, điểm danh và di chuyển đi Tràng An. Chuyến sáng khởi hành 08h00, chuyến chiều khởi hành 13h30.',
  ),
  array(
    'icon' => 'temple_buddhist',
    'title' => 'Tham quan Tràng An',
    'text' => 'Đến Tràng An - Di sản Thế giới được UNESCO công nhận năm 2014. Di chuyển khoảng 2,5 tiếng tham quan trên thuyền nan, thưởng ngoạn vẻ đẹp thiên nhiên với hệ thống hang động ngầm kỳ vĩ - nơi từng xuất hiện trong "Skull Island", phần phim mới của Kong năm 2016.',
  ),
  array(
    'icon' => 'home',
    'title' => 'Kết thúc',
    'text' => 'Trở lại bến thuyền, lên xe theo điều phối của BTC và trở về khách sạn. Kết thúc chuyến đi.',
  ),
);

// Lộ trình 9 điểm tham quan trên thuyền (theo sơ đồ lịch trình tour).
$route = array(
  array('no' => '1', 'name' => 'Bến thuyền', 'type' => 'start'),
  array('no' => '2', 'name' => 'Hang Lấm', 'type' => 'cave'),
  array('no' => '3', 'name' => 'Hang Vạng', 'type' => 'cave'),
  array('no' => '4', 'name' => 'Hang Thánh Trượt', 'type' => 'cave'),
  array('no' => '5', 'name' => 'Đền Thánh Cao Sơn', 'type' => 'temple'),
  array('no' => '6', 'name' => 'Đền Suối Tiên', 'type' => 'temple'),
  array('no' => '7', 'name' => 'Hang Đại', 'type' => 'cave'),
  array('no' => '8', 'name' => 'Hành cung Vũ Lâm', 'type' => 'temple'),
  array('no' => '9', 'name' => 'Bến thuyền', 'type' => 'end'),
);
$routeIcons = array('start' => 'directions_boat', 'end' => 'flag', 'cave' => 'landscape', 'temple' => 'temple_buddhist');

// Trang phục khi tham quan.
$dressCode = array(
  'Mặc áo polo đồng phục Đại hội Mường Thanh 2026, không mặc váy ngắn.',
  'Đeo thẻ Đại hội và nên đi giày thể thao.',
  'Nên mang theo ô / mũ / nón để che nắng.',
);

// Quy định chung và những lưu ý.
$rules = array(
  'Có mặt đúng giờ và đúng chuyến đã đăng ký; không tự ý đổi xe, đổi chuyến hoặc chuyển sang nhóm khác khi chưa có BTC/HDV xác nhận. Không tự ý xuống thuyền, lên thuyền khác hoặc thay đổi vị trí khi chưa được phép.',
  'Mặc đồng phục Đại hội Mường Thanh 2026 khi đi tham quan.',
  'Giữ điện thoại liên lạc và theo dõi thông báo của BTC/HDV trong suốt chương trình.',
  'Không tự ý tách đoàn hoặc bỏ tour. Trường hợp cần rời đoàn vì lý do sức khỏe, cá nhân, phải thông báo ngay cho BTC/HDV.',
  'Tuân thủ hướng dẫn của BTC, HDV và người lái thuyền trong quá trình di chuyển.',
  'Tuân thủ đầy đủ các quy định an toàn khi tham quan bằng thuyền và sử dụng áo phao theo hướng dẫn.',
  'Tự bảo quản điện thoại, ví, giấy tờ và các tư trang cá nhân trong suốt chương trình.',
  'Giữ gìn vệ sinh môi trường và cảnh quan khu du lịch; không xả rác xuống sông, hồ hoặc tại khu vực tham quan. Thể hiện thái độ văn minh, lịch sự của người Mường Thanh khi đi tham quan du lịch.',
);

// Thông tin liên hệ hỗ trợ.
$contacts = array(
  array('name' => 'Mr. Tiệp', 'phone' => '0988 016 066'),
  array('name' => 'Ms. Ngọc', 'phone' => '0898 600 222'),
);

?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'tour')); ?>

<main class="flex-grow-1">
  <!-- Hero -->
  <section class="py-5" style="background:linear-gradient(135deg,#0f766e 0%,#0d9488 55%,#0891b2 100%);">
    <div class="container text-white">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <span class="fw-bold text-uppercase small d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill mb-3" style="background:rgba(255,255,255,.18);">
            <span class="material-symbols-outlined fs-6">tour</span> Trải nghiệm Đại hội
          </span>
          <h1 class="fw-bold display-6 mb-2">Tour tham quan Di sản Tràng An</h1>
          <p class="fs-5 mb-4 opacity-75">Tour tham quan 1/2 ngày <strong>miễn phí</strong> dành cho CBNV trong khuôn khổ Đại hội Mường Thanh 2026 - khám phá vẻ đẹp non nước Tràng An, Di sản Thế giới được UNESCO công nhận.</p>
          <div class="d-flex flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2"><span class="material-symbols-outlined">calendar_month</span><span class="fw-semibold">27/10 &amp; 29/10/2026</span></div>
            <div class="d-flex align-items-center gap-2"><span class="material-symbols-outlined">location_on</span><span class="fw-semibold">Khu du lịch sinh thái Tràng An</span></div>
          </div>
        </div>
        <div class="col-lg-5">
          <img src="<?php echo $img; ?>/tour-trang-an.jpg" alt="Tour tham quan Di sản Tràng An" class="img-fluid rounded-4 shadow-lg w-100" style="object-fit:cover;">
        </div>
      </div>
    </div>
  </section>

  <div class="container py-4">
    <!-- Khung thời gian đăng ký -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">event_available</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Các khung thời gian tham quan</h2>
      </div>
      <p class="text-secondary small">CBNV vui lòng lựa chọn đăng ký 01 trong 03 khung thời gian sau:</p>
      <div class="row g-3">
        <?php foreach ($sessions as $s): ?>
          <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
              <div class="card-body text-center p-4">
                <span class="badge rounded-pill mb-3" style="background:<?php echo $e($accent); ?>1a;color:<?php echo $e($accent); ?>;"><?php echo $e($s['no']); ?></span>
                <div class="fw-bold fs-5 text-dark mb-1"><?php echo $e($s['period']); ?> ngày <?php echo $e($s['date']); ?></div>
                <div class="d-inline-flex align-items-center gap-1 fw-semibold" style="color:<?php echo $e($accent); ?>;">
                  <span class="material-symbols-outlined fs-6">schedule</span><?php echo $e($s['time']); ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Thông tin chương trình -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">info</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Thông tin chương trình</h2>
      </div>
      <div class="row g-3">
        <?php foreach ($info as $it): ?>
          <div class="col-md-6 col-xl-4">
            <div class="card border rounded-4 shadow-sm h-100">
              <div class="card-body d-flex gap-3 py-3">
                <span class="material-symbols-outlined flex-shrink-0" style="color:<?php echo $e($accent); ?>;font-size:34px;"><?php echo $e($it['icon']); ?></span>
                <div>
                  <span class="small text-secondary d-block"><?php echo $e($it['label']); ?></span>
                  <strong class="text-dark"><?php echo $e($it['value']); ?></strong>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Nội dung tham quan -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">list_alt</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Nội dung tham quan</h2>
      </div>
      <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
          <?php foreach ($itinerary as $i => $it): ?>
            <div class="d-flex gap-3 <?php echo $i < count($itinerary) - 1 ? 'pb-3 mb-3 border-bottom' : ''; ?>">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white" style="width:44px;height:44px;background:<?php echo $e($accent); ?>;">
                <span class="material-symbols-outlined"><?php echo $e($it['icon']); ?></span>
              </div>
              <div>
                <strong class="text-dark d-block mb-1"><?php echo $e($it['title']); ?></strong>
                <span class="text-secondary small"><?php echo $e($it['text']); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Lộ trình trên thuyền -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">route</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Lộ trình tham quan trên thuyền</h2>
      </div>
      <div class="row g-3 align-items-start">
        <div class="col-lg-6">
          <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
            <img src="<?php echo $img; ?>/tour-trang-an-route.jpg" data-full="<?php echo $img; ?>/tour-trang-an-route.jpg" alt="Sơ đồ lịch trình tour Tràng An" class="funrun-map w-100" style="object-fit:contain;background:#eef7f6;cursor:zoom-in;">
            <div class="card-body py-2"><span class="small text-secondary">Sơ đồ lộ trình 9 điểm tham quan - nhấn vào ảnh để xem phóng to.</span></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 rounded-4 shadow-sm h-100">
            <div class="card-body p-4">
              <ul class="list-unstyled mb-0">
                <?php foreach ($route as $i => $r): ?>
                  <li class="d-flex align-items-center gap-3 <?php echo $i < count($route) - 1 ? 'pb-2 mb-2 border-bottom' : ''; ?>">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white position-relative" style="width:40px;height:40px;background:<?php echo $e($accent); ?>;">
                      <span class="material-symbols-outlined fs-6"><?php echo $e($routeIcons[$r['type']]); ?></span>
                      <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-dark" style="font-size:10px;"><?php echo $e($r['no']); ?></span>
                    </div>
                    <span class="fw-semibold text-dark"><?php echo $e($r['name']); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Trang phục -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">checkroom</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Trang phục &amp; vật dụng</h2>
      </div>
      <div class="row g-3">
        <?php foreach ($dressCode as $d): ?>
          <div class="col-md-4">
            <div class="card border rounded-4 shadow-sm h-100">
              <div class="card-body d-flex gap-2 py-3">
                <span class="material-symbols-outlined flex-shrink-0" style="color:<?php echo $e($accent); ?>;">check_circle</span>
                <span class="text-dark small"><?php echo $e($d); ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Quy định chung & lưu ý -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">gavel</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Quy định chung &amp; những lưu ý</h2>
      </div>
      <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
          <ul class="list-unstyled mb-0">
            <?php foreach ($rules as $r): ?>
              <li class="d-flex gap-2 py-2 border-bottom">
                <span class="material-symbols-outlined flex-shrink-0" style="color:<?php echo $e($accent); ?>;">check_circle</span>
                <span class="text-dark small"><?php echo $e($r); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="alert mt-3 mb-0 small" style="background:<?php echo $e($accent); ?>0d;color:#115e59;border:none;">
            <strong>An toàn trên thuyền:</strong> Thuyền chở tối đa 06 người; Quý đại biểu vui lòng mặc áo phao, ngồi ổn định và tuân thủ hướng dẫn của người lái thuyền trong suốt hành trình tham quan.
          </div>
        </div>
      </div>
    </section>

    <!-- Liên hệ hỗ trợ -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">support_agent</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Thông tin liên hệ hỗ trợ</h2>
      </div>
      <div class="row g-3">
        <?php foreach ($contacts as $c): ?>
          <div class="col-md-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
              <div class="card-body d-flex align-items-center gap-3 p-4">
                <span class="material-symbols-outlined flex-shrink-0" style="color:<?php echo $e($accent); ?>;font-size:40px;">call</span>
                <div>
                  <strong class="text-dark d-block"><?php echo $e($c['name']); ?></strong>
                  <a href="tel:<?php echo $e(str_replace(' ', '', $c['phone'])); ?>" class="fw-bold fs-5 text-decoration-none" style="color:<?php echo $e($accent); ?>;"><?php echo $e($c['phone']); ?></a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>

<!-- Lightbox xem ảnh sơ đồ phóng to (dùng chung asset với trang Fun Run) -->
<div id="funrunLightbox" class="funrun-lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
  <button type="button" class="funrun-lightbox__close" aria-label="Đóng">&times;</button>
  <img src="" alt="Ảnh sơ đồ phóng to" class="funrun-lightbox__img">
</div>
<?php
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/css/pages/daihoi-funrun.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/js/pages/daihoi-funrun.js', CClientScript::POS_END);
?>