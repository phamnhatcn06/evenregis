<?php

/**
 * Trang "Sổ tay Đại hội" - slug /so-tay-dai-hoi.
 *
 * Tổng hợp toàn bộ thông tin hướng dẫn dành cho Quý Đại biểu theo cuốn
 * Sổ tay Đại hội Mường Thanh 2026. Nội dung tĩnh, biên soạn từ tài liệu gốc,
 * kèm liên kết tới các trang chi tiết đã có (Agenda, Lịch thi đấu, Hỗ trợ...).
 *
 * Icon dùng emoji màu (Unicode) thay cho Material Symbols để minh hoạ sinh động
 * và không phụ thuộc file font icon.
 *
 * @var array $event
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) {
  return CHtml::encode($s !== null ? (string) $s : '');
};
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
// Tông màu theo theme Đại hội: brand-primary (xanh dương) -> brand-secondary (teal).
$accent = '#1d4ed8';
$accent2 = '#0d9488';
$heroGradient = 'linear-gradient(135deg,#0b1329 0%,#1d4ed8 55%,#0d9488 100%)';

// Helper hiển thị emoji (không dùng class material-symbols-outlined vì emoji không
// nằm trong font icon). $style cho phép chỉnh cỡ chữ khi cần.
$emo = function ($ch, $style = '') {
  return '<span class="handbook-emo" style="line-height:1;' . $style . '">' . $ch . '</span>';
};

// Tiêu đề từng mục với chip emoji.
$head = function ($icon, $title) {
  echo '<div class="d-flex align-items-center gap-3 mb-3">'
    . '<span class="handbook-ico" style="font-size:22px;line-height:1;">' . $icon . '</span>'
    . '<h2 class="fw-bold fs-5 mb-0">' . CHtml::encode($title) . '</h2>'
    . '</div>';
};

// Mục lục điều hướng nhanh trong trang.
$toc = array(
  array('id' => 'thong-tin', 'icon' => 'ℹ️', 'label' => 'Thông tin Đại hội'),
  array('id' => 'qua-tang', 'icon' => '🎁', 'label' => 'Bộ quà tặng'),
  array('id' => 'hanh-trang', 'icon' => '🧳', 'label' => 'Hành trang Đại biểu'),
  array('id' => 'lien-he', 'icon' => '📇', 'label' => 'Thông tin liên hệ'),
  array('id' => 'chuong-trinh', 'icon' => '🗓️', 'label' => 'Agenda & Chương trình'),
  array('id' => 'quy-dinh', 'icon' => '⚖️', 'label' => 'Quy định chung'),
  array('id' => 'an-uong', 'icon' => '🍽️', 'label' => 'Ăn uống & Tiệc'),
  array('id' => 'luu-tru', 'icon' => '🏨', 'label' => 'Lưu trú'),
  array('id' => 'y-te', 'icon' => '🏥', 'label' => 'Y tế'),
  array('id' => 'an-ninh', 'icon' => '🧯', 'label' => 'An ninh - PCCC'),
  array('id' => 'so-do', 'icon' => '🗺️', 'label' => 'Sơ đồ địa điểm'),
  array('id' => 'kenh-qr', 'icon' => '🔳', 'label' => 'QR & Kênh thông tin'),
);

// Hành trang cá nhân cần chuẩn bị.
$packing = array(
  array('icon' => '🪪', 'text' => 'Căn cước công dân hoặc giấy tờ tùy thân.'),
  array('icon' => '🥤', 'text' => 'Bình nước cá nhân.'),
  array('icon' => '☂️', 'text' => 'Ô hoặc áo mưa gấp gọn.'),
  array('icon' => '👚', 'text' => 'Quần áo thay đổi phù hợp thời tiết.'),
  array('icon' => '👟', 'text' => 'Giày thể thao hoặc giày đế thấp.'),
  array('icon' => '🩴', 'text' => 'Dép đi trong phòng.'),
  array('icon' => '🛏️', 'text' => 'Túi ngủ, chăn mỏng, gối.'),
  array('icon' => '🛁', 'text' => 'Khăn tắm.'),
  array('icon' => '🧼', 'text' => 'Đồ vệ sinh cá nhân.'),
);

// Bộ quà tặng.
$gifts = array(
  array('icon' => '🎒', 'text' => '01 balo rút'),
  array('icon' => '👕', 'text' => '01 áo phông'),
  array('icon' => '🧢', 'text' => '01 mũ'),
  array('icon' => '🪪', 'text' => '01 thẻ tham dự'),
);

// Agenda & các chương trình chính (liên kết tới trang chi tiết).
$programs = array(
  array('icon' => '📅', 'title' => 'Agenda tổng thể', 'desc' => 'Lịch trình chi tiết 3 ngày Đại hội.', 'url' => $base . '/agenda-tong-the'),
  array('icon' => '🎉', 'title' => 'Khai mạc, thi Miss & Văn nghệ', 'desc' => 'Chương trình Tiệc Welcome.', 'url' => $base . '/tiec-welcome'),
  array('icon' => '🏆', 'title' => 'Lịch thi đấu thể thao', 'desc' => 'Lịch và kết quả các môn thi đấu.', 'url' => $base . '/lich-thi-the-thao'),
  array('icon' => '🚌', 'title' => 'Lịch tham quan & Tour', 'desc' => 'Hành trình tham quan và Tour Itinerary.', 'url' => $base . '/lich-tham-quan'),
  array('icon' => '🥂', 'title' => 'Bế mạc Đại hội - Tiệc Gala', 'desc' => 'Chương trình Lễ bế mạc.', 'url' => $base . '/tiec-gala'),
);

// Quy định chung.
$generalRules = array(
  'Đeo thẻ đại biểu trong suốt thời gian diễn ra Đại hội.',
  'Chấp hành hướng dẫn của Ban Tổ chức và lực lượng điều phối.',
  'Giữ gìn hình ảnh, tác phong và văn hóa ứng xử của đơn vị.',
  'Bảo quản tài sản cá nhân và tài sản chung.',
  'Không tự ý tách đoàn khi chưa thông báo với Trưởng đoàn hoặc Ban Tổ chức.',
  'Chủ động hỗ trợ các đại biểu khác, góp phần xây dựng kỳ Đại hội văn minh, đoàn kết và chuyên nghiệp.',
);

// Lịch phục vụ ăn uống.
$meals = array(
  array('Bữa sáng', '05:30 – 08:30', 'Ngày 27, 28, 29, 30/10', 'Nhà hàng Tràng An - Tầng 2'),
  array('Bữa trưa', '10:30 – 13:00', 'Ngày 26, 27, 28, 29/10', 'Nhà hàng Tràng An - Tầng 2'),
  array('Bữa tối', '17:00 – 19:00', 'Ngày 26 và 28/10', 'Nhà hàng Tràng An - Tầng 2'),
  array('Bữa đêm', '21:00 – 00:00', 'Ngày 27, 28, 29/10', 'Nhà ăn nhân viên - Tầng hầm'),
  array('Tiệc Welcome', '17:00 – 19:00', 'Ngày 27/10', 'Nhà hàng Tràng An - Tầng 2'),
  array('Tiệc Gala', '17:00 – 19:00', 'Ngày 29/10', 'Nhà hàng Tràng An - Tầng 2'),
);

// Quy định ăn uống.
$diningRules = array(
  'Dùng bữa đúng thời gian quy định.',
  'Trường hợp không dùng bữa, Trưởng đoàn báo hủy suất ăn cho Ban Thư Ký Đoàn 24 giờ trước giờ ăn.',
  'Xếp hàng văn minh, không chen lấn.',
  'Chỉ lấy lượng thức ăn vừa đủ; có thể lấy thêm nếu còn nhu cầu.',
  'Không mang thức ăn ra khỏi khu vực phục vụ nếu chưa được Ban Tổ chức cho phép.',
  'Sau khi dùng bữa, vui lòng thu dọn khay, bát đĩa đúng nơi quy định.',
  'Giữ gìn vệ sinh chung.',
);

// Quy định lưu trú.
$lodgingRules = array(
  'Không tự ý đổi phòng hoặc đổi người ở cùng phòng khi chưa được Ban Tổ chức đồng ý. Không đưa người không có tên trong danh sách lưu trú vào phòng ở cùng.',
  'Trước khi trả phòng, sắp xếp đồ đạc, giường trong phòng như khi nhận phòng.',
  'Giữ gìn vệ sinh, tài sản và trang thiết bị của khách sạn.',
  'Không gây mất trật tự, ảnh hưởng đến các đại biểu khác.',
  'Không hút thuốc trong phòng và khu vực cấm hút thuốc.',
  'Không sử dụng các thiết bị điện có công suất lớn trong phòng nghỉ.',
  'Phát hiện hư hỏng tài sản hoặc sự cố, vui lòng thông báo ngay cho bộ phận Lễ tân.',
  'Giặt là: Ban Tổ chức bố trí khu vực nhận, trả đồ giặt là hàng ngày với số lượng và khung giờ theo quy định.',
);

// Đầu mối y tế.
$medical = array(
  array('role' => 'Phòng y tế - phụ trách', 'name' => 'Bác sĩ Hoàng Xuân Kỳ', 'phone' => '0982080035'),
  array('role' => 'Lái xe cấp cứu', 'name' => 'Nguyễn Văn Hưng', 'phone' => '0973588031'),
  array('role' => 'Bác sĩ', 'name' => 'Quang Tuấn Anh', 'phone' => '0394115345'),
  array('role' => 'Điều dưỡng', 'name' => 'Ngô Thị Tình', 'phone' => '0972254800'),
);

// Sơ cứu cơ bản trong lúc chờ tổ y tế.
$firstAid = array(
  array('title' => 'Ngừng tim, ngừng thở', 'text' => 'Ép tim và thổi ngạt ngay; ép ở ½ dưới xương ức, sâu 5–6 cm, tần số 100–120 lần/phút.'),
  array('title' => 'Chảy máu nhiều', 'text' => 'Dùng gạc hoặc khăn sạch băng ép lên vết thương.'),
  array('title' => 'Say nắng', 'text' => 'Đưa đến nơi mát, thoáng; nới lỏng quần áo; chườm mát cổ, nách, bẹn; nếu tỉnh táo cho uống thêm nước.'),
);

// An ninh - PCCC.
$securityRules = array(
  'Không để đồ đạc cá nhân cản trở lối đi chung, hành lang và đường dẫn đến cửa thoát hiểm.',
  'Khi có báo động khẩn cấp, cần bình tĩnh di chuyển theo hướng dẫn của đội An ninh.',
  'Thực hiện các quy định về an toàn, phòng cháy chữa cháy và bảo vệ môi trường.',
);
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'handbook')); ?>

<main class="flex-grow-1 bg-light">
  <!-- HERO -->
  <section class="position-relative overflow-hidden handbook-hero" style="background:<?php echo $heroGradient; ?>;">
    <div class="container py-5 text-white position-relative" style="z-index:1;">
      <span class="badge rounded-pill bg-white text-dark fw-bold mb-3 px-3 py-2">
        <?php echo $emo('📖', 'font-size:16px;'); ?>
        Sổ tay Đại hội
      </span>
      <h1 class="fw-black display-6 mb-2">Sổ tay Đại hội Mường Thanh 2026</h1>
      <p class="mb-0 fs-6 opacity-90" style="max-width:720px;">
        Chào mừng Quý Đại biểu đến dự Đại hội! Cuốn sổ tay cung cấp những thông tin cần thiết,
        giúp Quý Đại biểu chủ động theo dõi lịch trình và nắm bắt đầy đủ các hoạt động trong suốt thời gian tham dự.
      </p>
      <div class="d-flex flex-wrap gap-3 mt-4">
        <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-3 px-3 py-2">
          <?php echo $emo('📅', 'font-size:22px;'); ?>
          <div class="small lh-sm"><span class="opacity-75 d-block">Thời gian</span><strong>27 – 29/10/2026</strong></div>
        </div>
        <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-3 px-3 py-2">
          <?php echo $emo('📍', 'font-size:22px;'); ?>
          <div class="small lh-sm"><span class="opacity-75 d-block">Địa điểm</span><strong>Mường Thanh Luxury Ninh Bình</strong></div>
        </div>
      </div>
    </div>
  </section>

  <section class="container py-4">
    <div class="row g-4">
      <!-- MỤC LỤC (sticky) -->
      <div class="col-lg-3 d-none d-lg-block">
        <div class="position-sticky" style="top:76px;">
          <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-3">
              <h2 class="fw-bold text-uppercase small text-secondary mb-3 px-2">Mục lục</h2>
              <nav class="nav flex-column gap-1" id="handbookToc">
                <?php foreach ($toc as $t): ?>
                  <a class="nav-link d-flex align-items-center gap-2 rounded-3 px-2 py-2 text-secondary fw-semibold" href="#<?php echo $e($t['id']); ?>" style="font-size:13.5px;">
                    <?php echo $emo($t['icon'], 'font-size:16px;'); ?>
                    <span><?php echo $e($t['label']); ?></span>
                  </a>
                <?php endforeach; ?>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- NỘI DUNG -->
      <div class="col-lg-9">

        <!-- THÔNG TIN ĐẠI HỘI -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="thong-tin">
          <div class="card-body p-4">
            <?php $head('ℹ️', 'Thông tin Đại hội'); ?>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-3">
                  <?php echo $emo('📅', 'font-size:30px;'); ?>
                  <div><span class="text-secondary small d-block">Thời gian</span><strong class="text-dark">Từ ngày 27 đến ngày 29/10/2026</strong></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100 d-flex align-items-center gap-3">
                  <?php echo $emo('📍', 'font-size:30px;'); ?>
                  <div><span class="text-secondary small d-block">Địa điểm</span><strong class="text-dark">Khách sạn Mường Thanh Luxury Ninh Bình</strong></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- BỘ QUÀ TẶNG -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="qua-tang">
          <div class="card-body p-4">
            <?php $head('🎁', 'Bộ quà tặng Đại biểu'); ?>
            <p class="text-secondary small mb-3">Mỗi đại biểu tham dự được tặng:</p>
            <div class="row g-3">
              <?php foreach ($gifts as $g): ?>
                <div class="col-6 col-md-3">
                  <div class="border rounded-4 p-3 h-100 text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:52px;height:52px;background:<?php echo $e($accent); ?>1a;">
                      <?php echo $emo($g['icon'], 'font-size:26px;'); ?>
                    </div>
                    <div class="fw-semibold text-dark small"><?php echo $e($g['text']); ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- HÀNH TRANG -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="hanh-trang">
          <div class="card-body p-4">
            <?php $head('🧳', 'Hành trang Đại biểu'); ?>
            <p class="text-secondary small mb-3">Vật dụng cá nhân cần chuẩn bị mang theo trước khi tham dự Đại hội:</p>
            <div class="row g-3">
              <?php foreach ($packing as $p): ?>
                <div class="col-6 col-md-4 col-xl-3">
                  <div class="d-flex flex-column align-items-center text-center gap-2 border rounded-3 p-3 h-100">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;background:<?php echo $e($accent); ?>1a;">
                      <?php echo $emo($p['icon'], 'font-size:22px;'); ?>
                    </span>
                    <span class="text-dark small"><?php echo $e($p['text']); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- LIÊN HỆ -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="lien-he">
          <div class="card-body p-4">
            <?php $head('📇', 'Thông tin liên hệ'); ?>
            <a href="<?php echo $e($base); ?>/doan-thu-ky" class="d-flex align-items-center justify-content-between text-decoration-none border rounded-3 p-3 handbook-link">
              <span class="d-flex align-items-center gap-3">
                <?php echo $emo('👥', 'font-size:22px;'); ?>
                <span><strong class="text-dark d-block">Danh sách Ban Thư Ký Đoàn</strong><span class="text-secondary small">Đầu mối liên hệ hỗ trợ từng đoàn.</span></span>
              </span>
              <?php echo $emo('›', 'font-size:20px;color:#9ca3af;'); ?>
            </a>
          </div>
        </div>

        <!-- AGENDA & CHƯƠNG TRÌNH -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="chuong-trinh">
          <div class="card-body p-4">
            <?php $head('🗓️', 'Agenda & các chương trình chính'); ?>
            <div class="row g-3">
              <?php foreach ($programs as $pr): ?>
                <div class="col-md-6">
                  <a href="<?php echo $e($pr['url']); ?>" class="d-flex align-items-center gap-3 text-decoration-none border rounded-3 p-3 h-100 handbook-link">
                    <span class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:46px;height:46px;background:<?php echo $e($accent); ?>1a;">
                      <?php echo $emo($pr['icon'], 'font-size:24px;'); ?>
                    </span>
                    <span class="flex-grow-1">
                      <strong class="text-dark d-block small"><?php echo $e($pr['title']); ?></strong>
                      <span class="text-secondary" style="font-size:12.5px;"><?php echo $e($pr['desc']); ?></span>
                    </span>
                    <?php echo $emo('›', 'font-size:18px;color:#9ca3af;'); ?>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- QUY ĐỊNH CHUNG -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="quy-dinh">
          <div class="card-body p-4">
            <?php $head('⚖️', 'Quy định chung'); ?>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
              <?php foreach ($generalRules as $r): ?>
                <li class="d-flex align-items-start gap-2">
                  <?php echo $emo('✅', 'font-size:18px;'); ?>
                  <span class="text-dark small"><?php echo $e($r); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <!-- ĂN UỐNG & TIỆC -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="an-uong">
          <div class="card-body p-4">
            <?php $head('🍽️', 'Ăn uống & các sự kiện tiệc'); ?>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
              <?php foreach ($diningRules as $r): ?>
                <li class="d-flex align-items-start gap-2">
                  <?php echo $emo('✅', 'font-size:18px;'); ?>
                  <span class="text-dark small"><?php echo $e($r); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <!-- LƯU TRÚ -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="luu-tru">
          <div class="card-body p-4">
            <?php $head('🏨', 'Lưu trú'); ?>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
              <?php foreach ($lodgingRules as $r): ?>
                <li class="d-flex align-items-start gap-2">
                  <?php echo $emo('✅', 'font-size:18px;'); ?>
                  <span class="text-dark small"><?php echo $e($r); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <!-- Y TẾ -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="y-te">
          <div class="card-body p-4">
            <?php $head('🏥', 'Y tế'); ?>
            <p class="text-secondary small mb-3">Phòng y tế thường trực 24/24 giờ trong suốt thời gian diễn ra Đại hội.</p>
            <div class="row g-3 mb-4">
              <?php foreach ($medical as $md): ?>
                <div class="col-md-6">
                  <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between gap-3">
                    <div>
                      <span class="text-secondary d-block" style="font-size:12px;"><?php echo $e($md['role']); ?></span>
                      <strong class="text-dark small"><?php echo $e($md['name']); ?></strong>
                    </div>
                    <a href="tel:<?php echo $e(str_replace(' ', '', $md['phone'])); ?>" class="btn btn-sm rounded-pill fw-bold text-white d-inline-flex align-items-center gap-1 flex-shrink-0" style="background:<?php echo $e($accent); ?>;">
                      <?php echo $emo('📞', 'font-size:15px;'); ?><?php echo $e($md['phone']); ?>
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="alert d-flex align-items-start gap-2 mb-3" style="background:#fef2f2;border:1px solid #fecaca;">
              <?php echo $emo('🚨', 'font-size:22px;'); ?>
              <div class="small text-dark">
                <strong>Khi xảy ra sự cố y tế:</strong> Báo ngay BS Quang Tuấn Anh - <a href="tel:0394115345" class="fw-bold text-danger text-decoration-none">0394 115 345</a>.
                Cử người đón đội y tế/xe cứu thương, giữ thông thoáng lối đi và tạo khoảng trống quanh người bệnh.
                Không tự ý cho uống thuốc hoặc làm thủ thuật khi không có chuyên môn.
              </div>
            </div>
            <h3 class="fw-semibold text-secondary small text-uppercase mb-2">Sơ cứu cơ bản trong lúc chờ tổ y tế</h3>
            <div class="row g-3">
              <?php foreach ($firstAid as $fa): ?>
                <div class="col-md-4">
                  <div class="border rounded-3 p-3 h-100">
                    <strong class="text-dark small d-block mb-1"><?php echo $e($fa['title']); ?></strong>
                    <span class="text-secondary" style="font-size:12.5px;"><?php echo $e($fa['text']); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="text-secondary small mt-3 mb-0">
              Khi tổ y tế đến, cung cấp nhanh: thời điểm xảy ra, biểu hiện của người bệnh, đã xử trí gì và tình trạng hiện tại.
            </p>
          </div>
        </div>

        <!-- AN NINH - PCCC -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="an-ninh">
          <div class="card-body p-4">
            <?php $head('🧯', 'An ninh - PCCC'); ?>
            <ul class="list-unstyled mb-3 d-flex flex-column gap-2">
              <?php foreach ($securityRules as $r): ?>
                <li class="d-flex align-items-start gap-2">
                  <?php echo $emo('✅', 'font-size:18px;'); ?>
                  <span class="text-dark small"><?php echo $e($r); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="alert d-flex align-items-center gap-2 mb-0" style="background:#eff6ff;border:1px solid #bfdbfe;">
              <?php echo $emo('📞', 'font-size:22px;'); ?>
              <div class="small text-dark">
                Liên hệ tình huống khẩn cấp: <strong>Mr. Đoàn Thế Cương</strong> -
                <a href="tel:0912211929" class="fw-bold text-decoration-none" style="color:<?php echo $e($accent); ?>;">0912 211 929</a>
              </div>
            </div>
          </div>
        </div>

        <!-- SƠ ĐỒ ĐỊA ĐIỂM -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="so-do">
          <div class="card-body p-4">
            <?php $head('🗺️', 'Sơ đồ địa điểm Đại hội'); ?>
            <a href="<?php echo $e($base); ?>/so-do-dia-diem" class="btn rounded-pill fw-bold text-white d-inline-flex align-items-center gap-2" style="background:<?php echo $e($accent); ?>;">
              <?php echo $emo('🗺️', 'font-size:16px;'); ?> Xem sơ đồ chi tiết
            </a>
          </div>
        </div>

        <!-- QR & KÊNH THÔNG TIN -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 handbook-section" id="kenh-qr">
          <div class="card-body p-4">
            <?php $head('🔳', 'QR & kênh thông tin chính thức'); ?>
            <div class="row g-3">
              <div class="col-md-4">
                <a href="<?php echo $e($base); ?>/agenda-tong-the" class="d-flex align-items-center gap-3 text-decoration-none border rounded-3 p-3 h-100 handbook-link">
                  <?php echo $emo('🔄', 'font-size:22px;'); ?>
                  <span class="text-dark small fw-semibold">Lịch trình cập nhật</span>
                </a>
              </div>
              <div class="col-md-4">
                <a href="<?php echo $e($base); ?>/lich-thi-the-thao" class="d-flex align-items-center gap-3 text-decoration-none border rounded-3 p-3 h-100 handbook-link">
                  <?php echo $emo('📊', 'font-size:22px;'); ?>
                  <span class="text-dark small fw-semibold">Kết quả thi đấu</span>
                </a>
              </div>
              <div class="col-md-4">
                <a href="<?php echo $e($base); ?>/thu-vien-anh" class="d-flex align-items-center gap-3 text-decoration-none border rounded-3 p-3 h-100 handbook-link">
                  <?php echo $emo('🖼️', 'font-size:22px;'); ?>
                  <span class="text-dark small fw-semibold">Ảnh Đại hội hàng ngày</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- LỜI CHÚC -->
        <div class="card border-0 rounded-4 shadow-sm mb-2 text-center" style="background:linear-gradient(135deg,#eff6ff 0%,#ecfeff 100%);">
          <div class="card-body p-4">
            <div class="mb-2"><?php echo $emo('🤝', 'font-size:38px;'); ?></div>
            <p class="text-dark mb-1">Ban Tổ chức luôn sẵn sàng đồng hành và hỗ trợ Đại biểu trong suốt thời gian diễn ra Đại hội.</p>
            <p class="fw-bold text-dark mb-0">Kính chúc Quý Đại biểu một kỳ Đại hội sức khỏe, thành công với nhiều trải nghiệm đáng nhớ!</p>
          </div>
        </div>

      </div>
    </div>
  </section>
</main>

<?php
Yii::app()->clientScript->registerScriptFile(
  Yii::app()->theme->baseUrl . '/assets/js/pages/daihoi-so-tay.js',
  CClientScript::POS_END
);
?>