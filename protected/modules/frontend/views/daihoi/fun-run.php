<?php

/**
 * Trang thông tin Giải chạy Mường Thanh Fun Run - slug /fun-run.
 * Nội dung tổng hợp từ Điều lệ giải chạy và Sơ đồ đường chạy (Ban Thể thao).
 * Trang công khai, tách biệt với cổng đăng ký /run.
 *
 * @var array $event
 */
$base = Yii::app()->request->baseUrl;
$img = $base . '/themes/hope-ui/assets/images/funrun';
$e = function ($s) {
  return CHtml::encode($s);
};
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$accent = '#16a34a';

// Cự ly & chỉ tiêu suất đăng ký.
$distances = array(
  array('name' => '5 km', 'slots' => 320, 'laps' => 1, 'cutoff' => '75 phút', 'control' => 'Không yêu cầu'),
  array('name' => '10 km', 'slots' => 90, 'laps' => 2, 'cutoff' => '120 phút', 'control' => '1 dấu xác nhận (vòng 2)'),
  array('name' => '15 km', 'slots' => 40, 'laps' => 3, 'cutoff' => '150 phút', 'control' => '2 dấu xác nhận (vòng 2, 3)'),
);

// Nhóm thi đấu theo từng cự ly.
// 5 km phân theo giới tính & độ tuổi (4 nhóm); 10 km và 15 km chỉ phân theo
// giới tính, không phân biệt độ tuổi (2 nhóm mỗi cự ly).
$distanceGroups = array(
  array(
    'name' => '5 km',
    'note' => 'Phân theo giới tính và độ tuổi',
    'groups' => array(
      array('gender' => 'Nam', 'age' => 'Dưới 35 tuổi'),
      array('gender' => 'Nam', 'age' => 'Từ 35 tuổi trở lên'),
      array('gender' => 'Nữ', 'age' => 'Dưới 35 tuổi'),
      array('gender' => 'Nữ', 'age' => 'Từ 35 tuổi trở lên'),
    ),
  ),
  array(
    'name' => '10 km',
    'note' => 'Không phân biệt độ tuổi',
    'groups' => array(
      array('gender' => 'Nam', 'age' => 'Mọi độ tuổi'),
      array('gender' => 'Nữ', 'age' => 'Mọi độ tuổi'),
    ),
  ),
  array(
    'name' => '15 km',
    'note' => 'Không phân biệt độ tuổi',
    'groups' => array(
      array('gender' => 'Nam', 'age' => 'Mọi độ tuổi'),
      array('gender' => 'Nữ', 'age' => 'Mọi độ tuổi'),
    ),
  ),
);

// Lịch trình tổ chức ngày thi đấu (28/10/2026) - theo Agenda giải chạy.
$schedule = array(
  array('time' => '04:30 - 05:00', 'title' => 'Setup hiện trường sớm', 'detail' => 'Kiểm tra nguồn điện, âm thanh, ánh sáng, cổng hơi Start/Finish, huy chương, cờ lưu niệm, linh vật, nước, y tế, rào chắn; nhân sự tiếp quản vị trí phụ trách.', 'place' => 'Khu vực Start/Finish & các điểm trạm'),
  array('time' => '05:00 - 05:45', 'title' => 'Tập trung & di chuyển đoàn', 'detail' => 'Điểm danh CBNV các đoàn VĐV tại sảnh khách sạn, lên xe di chuyển đến Tràng An Golf & Resort. Khởi hành đúng 05:00.', 'place' => 'Sảnh khách sạn → Tràng An Golf'),
  array('time' => '05:45 - 06:00', 'title' => 'Ổn định tổ chức', 'detail' => 'Đoàn đến nơi, điểm danh và ổn định đội hình VĐV tại khu vực trước sân khấu.', 'place' => 'Khu vực trước sân khấu'),
  array('time' => '06:00 - 06:30', 'title' => 'Khai mạc & phổ biến thể lệ', 'detail' => 'Phát biểu khai mạc của TGĐ; công bố thể lệ, đường chạy, mốc cut-off, hotline; khởi động chung.', 'place' => 'Sân khấu chính / Cổng Start'),
  array('time' => '06:30', 'title' => 'Xuất phát cự ly 15 km & 10 km', 'detail' => 'Điều phối và lệnh xuất phát cự ly 15 km (3 vòng hồ Đồng Chương) và 10 km.', 'place' => 'Vạch Start'),
  array('time' => '06:35', 'title' => 'Xuất phát cự ly 5 km Nam', 'detail' => 'Điều phối và lệnh xuất phát cự ly 5 km Nam (1 vòng hồ Đồng Chương).', 'place' => 'Vạch Start'),
  array('time' => '06:40', 'title' => 'Xuất phát cự ly 5 km Nữ', 'detail' => 'Điều phối và lệnh xuất phát cự ly 5 km Nữ (1 vòng hồ Đồng Chương).', 'place' => 'Vạch Start'),
  array('time' => '06:30 - 09:30', 'title' => 'Điều hành giao thông & chốt nút giao', 'detail' => 'Giữ làn cho runner, đảm bảo VĐV chạy đúng hướng; tiếp nhận thông tin VĐV bỏ cuộc/cần hỗ trợ y tế về BTC.', 'place' => 'Dọc tuyến đường hồ Đồng Chương'),
  array('time' => '06:45 - 09:30', 'title' => 'Trạm kiểm soát vòng chạy', 'detail' => 'Xác nhận số vòng hoàn thành: 10 km (dấu vòng 2), 15 km (dấu vòng 2 & 3) trước khi về đích.', 'place' => 'Trạm kiểm soát vòng chạy'),
  array('time' => '06:45 - 09:30', 'title' => 'Trạm tiếp nước & y tế', 'detail' => 'Cấp nước/điện giải tại km 2,3 & 4,1 (tối đa 1 lần/vòng); trực y tế sẵn sàng sơ cứu, hỗ trợ VĐV.', 'place' => 'Các trạm trên đường chạy'),
  array('time' => '07:15 - 09:30', 'title' => 'Kiểm soát & trao huy chương về đích', 'detail' => 'Ghi nhận thời gian, kiểm tra điều kiện về đích theo từng cự ly & nhóm tuổi; trao huy chương Finisher ngay khi VĐV về đích.', 'place' => 'Vạch Finish'),
  array('time' => '09:30 - 09:45', 'title' => 'Lễ trao giải cá nhân', 'detail' => 'Công bố kết quả chính thức và trao 24 giải cá nhân (Nhất, Nhì, Ba cho 8 nhóm thi đấu).', 'place' => 'Sân khấu chính'),
  array('time' => '09:30 - 10:00', 'title' => 'Thu hồi thiết bị & mở lại giao thông', 'detail' => 'Thu hồi standee, biển báo, rác; xác nhận hết VĐV trên tuyến; phối hợp mở lại giao thông bình thường.', 'place' => 'Toàn bộ cung đường chạy'),
  array('time' => '10:00 - 11:00', 'title' => 'Di chuyển đoàn về khách sạn', 'detail' => 'Điểm danh CBNV lên xe di chuyển về khách sạn nghỉ ngơi, kết thúc giải chạy.', 'place' => 'Bãi đỗ xe Tràng An → Khách sạn'),
);

// Các trạm / vị trí trên đường chạy (theo sơ đồ tổng thể).
$stations = array(
  array('no' => '0', 'icon' => 'flag', 'name' => 'Xuất phát / Về đích', 'pos' => 'Quảng trường khu nhà điều hành'),
  array('no' => '1', 'icon' => 'how_to_reg', 'name' => 'Kiểm soát vòng chạy', 'pos' => 'Khu nhà homestay 5 - Km 1,1'),
  array('no' => '2', 'icon' => 'signpost', 'name' => 'Điều hướng, giám sát 1', 'pos' => 'Khu Bus Stop A'),
  array('no' => '3', 'icon' => 'signpost', 'name' => 'Điều hướng, giám sát 2', 'pos' => 'Khu vực trả khách C'),
  array('no' => '4', 'icon' => 'local_drink', 'name' => 'Tiếp nước & Y tế 1', 'pos' => 'Trạm nghỉ - Km 2,3'),
  array('no' => '5', 'icon' => 'photo_camera', 'name' => 'Khu vực chụp ảnh', 'pos' => 'Cửa sổ tình yêu - Km 3,2'),
  array('no' => '6', 'icon' => 'signpost', 'name' => 'Điều hướng, giám sát 3', 'pos' => 'Ngã ba trống'),
  array('no' => '7', 'icon' => 'medical_services', 'name' => 'Y tế 2', 'pos' => 'Trạm nghỉ khu trượt cỏ - Km 4,1'),
  array('no' => '8', 'icon' => 'signpost', 'name' => 'Điều hướng, giám sát 4', 'pos' => 'Khu vực lối xuống hố tập H'),
);

// Ảnh BIB nhận diện theo cự ly / giới tính.
$bibs = array('bib-20', 'bib-21', 'bib-22', 'bib-23', 'bib-24', 'bib-25', 'bib-26', 'bib-27');

// Quy định thi đấu chính (trích Điều lệ).
$rules = array(
  'Mỗi VĐV chỉ được đăng ký 01 cự ly và không được tự ý thay đổi sau khi Ban Tổ chức chốt danh sách.',
  'Chạy đúng lộ trình quy định, không đi đường tắt, không chạy hộ hoặc nhờ người chạy thay.',
  'Không sử dụng phương tiện hỗ trợ hoặc bất kỳ hình thức gian lận nào trong thi đấu.',
  'Bắt buộc mặc áo đồng phục do BTC cung cấp và đeo đúng số BIB ở trước ngực trong suốt quá trình thi đấu; không tẩy xóa, trao đổi hoặc chuyển BIB cho người khác.',
  'VĐV cự ly 10 km và 15 km phải có đủ dấu xác nhận tại trạm kiểm soát (km số 6) mới được công nhận hoàn thành.',
  'Sau thời gian cut-off, BTC có quyền kết thúc việc ghi nhận kết quả với VĐV chưa hoàn thành cự ly.',
  'Khi có dấu hiệu bất thường (đau ngực, khó thở, choáng, chuột rút nặng...), VĐV cần chủ động dừng chạy và báo ngay nhân viên y tế hoặc lực lượng hỗ trợ gần nhất.',
  'VĐV bỏ cuộc phải thông báo và nộp lại BIB cho thành viên BTC gần nhất, đi gọn vào lề phải, không cản trở đường chạy.',
);

?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'funrun')); ?>

<main class="flex-grow-1">
  <!-- Hero -->
  <section class="py-5" style="background:linear-gradient(135deg,#065f46 0%,#16a34a 55%,#65a30d 100%);">
    <div class="container text-white">
      <div class="row align-items-center g-4">
        <div class="col-lg-12">
          <span class="fw-bold text-uppercase small d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill mb-3" style="background:rgba(255,255,255,.18);">
            <span class="material-symbols-outlined fs-6">directions_run</span> Hoạt động thể thao Đại hội
          </span>
          <h1 class="fw-bold display-6 mb-2">Giải chạy Mường Thanh Fun Run</h1>
          <p class="fs-5 mb-4 opacity-75">Khuyến khích phong trào rèn luyện sức khỏe, tăng cường giao lưu, gắn kết giữa các đơn vị trong khuôn khổ Đại hội Mường Thanh 2026.</p>
          <div class="d-flex flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2"><span class="material-symbols-outlined">calendar_month</span><span class="fw-semibold">Sáng 28/10/2026</span></div>
            <div class="d-flex align-items-center gap-2"><span class="material-symbols-outlined">location_on</span><span class="fw-semibold">Tràng An Golf &amp; Resort</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="container py-4">
    <!-- Cự ly & chỉ tiêu -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">route</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Các cự ly thi đấu</h2>
      </div>
      <div class="row g-3">
        <?php foreach ($distances as $d): ?>
          <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
              <div class="card-body text-center p-4">
                <div class="fw-bold display-6 mb-3" style="color:<?php echo $e($accent); ?>;"><?php echo $e($d['name']); ?></div>
                <ul class="list-unstyled text-start small text-secondary mb-0">
                  <li class="d-flex justify-content-between py-1 border-bottom"><span>Số vòng chạy</span><strong class="text-dark"><?php echo (int) $d['laps']; ?> vòng</strong></li>
                  <li class="d-flex justify-content-between py-1 border-bottom"><span>Thời gian cut-off</span><strong class="text-dark"><?php echo $e($d['cutoff']); ?></strong></li>
                  <li class="d-flex justify-content-between py-1"><span>Kiểm soát</span><strong class="text-dark text-end" style="max-width:55%;"><?php echo $e($d['control']); ?></strong></li>
                </ul>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="small text-secondary mt-3 mb-0"><span class="material-symbols-outlined align-middle fs-6">info</span> Mỗi vòng chạy quanh hồ Đồng Chương dài 5 km. Đường chạy xuất phát và về đích tại Quảng trường khu nhà điều hành.</p>
    </section>

    <!-- Nhóm thi đấu -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">emoji_events</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Nhóm thi đấu &amp; giải thưởng</h2>
      </div>
      <div class="row g-3 mb-3">
        <?php foreach ($distanceGroups as $dg): ?>
          <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden">
              <div class="px-4 py-3 d-flex align-items-center justify-content-between" style="background:<?php echo $e($accent); ?>0d;">
                <span class="fw-bold fs-5" style="color:<?php echo $e($accent); ?>;"><?php echo $e($dg['name']); ?></span>
                <span class="badge rounded-pill text-dark" style="background:<?php echo $e($accent); ?>1a;"><?php echo count($dg['groups']); ?> nhóm</span>
              </div>
              <div class="card-body p-0">
                <div class="small text-secondary px-4 pt-3 pb-2"><span class="material-symbols-outlined align-middle fs-6"><?php echo count($dg['groups']) > 2 ? 'groups' : 'wc'; ?></span> <?php echo $e($dg['note']); ?></div>
                <ul class="list-unstyled mb-0">
                  <?php foreach ($dg['groups'] as $g): ?>
                    <li class="d-flex align-items-center justify-content-between px-4 py-2 border-top">
                      <span class="fw-semibold text-dark"><?php echo $e($g['gender']); ?></span>
                      <span class="small text-secondary"><?php echo $e($g['age']); ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="small text-secondary mb-3"><span class="material-symbols-outlined align-middle fs-6">info</span> Tổng cộng 8 nhóm thi đấu: 5 km có 4 nhóm (theo giới tính và độ tuổi); 10 km và 15 km mỗi cự ly 2 nhóm (chỉ theo giới tính, không phân biệt độ tuổi).</p>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card border rounded-4 h-100">
            <div class="card-body d-flex gap-3">
              <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;font-size:36px;">workspace_premium</span>
              <div><strong class="text-dark d-block">24 giải cá nhân</strong><span class="small text-secondary">Mỗi nhóm thi đấu trao 01 giải Nhất, 01 giải Nhì và 01 giải Ba (8 nhóm). Xếp hạng theo thời gian hoàn thành cự ly.</span></div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border rounded-4 h-100">
            <div class="card-body d-flex gap-3">
              <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;font-size:36px;">military_tech</span>
              <div><strong class="text-dark d-block">Huy chương Finisher</strong><span class="small text-secondary">Trao cho các VĐV hoàn thành cự ly đăng ký trong thời gian cut-off và đáp ứng đầy đủ các điều kiện.</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lịch trình -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">schedule</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Lịch trình tổ chức</h2>
      </div>
      <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
          <div class="funrun-timeline">
            <?php foreach ($schedule as $i => $s): ?>
              <div class="funrun-timeline__item <?php echo $i < count($schedule) - 1 ? 'pb-4' : ''; ?>">
                <span class="funrun-timeline__dot" style="background:<?php echo $e($accent); ?>;"></span>
                <div class="fw-bold text-nowrap mb-1" style="color:<?php echo $e($accent); ?>;"><?php echo $e($s['time']); ?></div>
                <div class="fw-semibold text-dark mb-1"><?php echo $e($s['title']); ?></div>
                <?php if (!empty($s['detail'])): ?>
                  <div class="small text-secondary"><?php echo $e($s['detail']); ?></div>
                <?php endif; ?>
                <?php if (!empty($s['place'])): ?>
                  <div class="small text-secondary mt-1 d-inline-flex align-items-center gap-1">
                    <span class="material-symbols-outlined fs-6" style="color:<?php echo $e($accent); ?>;">location_on</span><?php echo $e($s['place']); ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Sơ đồ đường chạy -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">map</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Sơ đồ đường chạy &amp; các trạm</h2>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-lg-6">
          <div class="card border-0 rounded-4 shadow-sm overflow-hidden h-100">
            <img src="<?php echo $img; ?>/map-illustrated.jpg" data-full="<?php echo $img; ?>/map-illustrated.jpg" alt="Sơ đồ tổng thể cung đường chạy" class="funrun-map w-100" style="height:340px;object-fit:cover;background:#eef6ef;cursor:zoom-in;">
            <div class="card-body py-2"><span class="small text-secondary">Sơ đồ tổng thể cung đường chạy quanh hồ Đồng Chương (1 vòng = 5 km).</span></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card border-0 rounded-4 shadow-sm overflow-hidden h-100">
            <img src="<?php echo $img; ?>/map-route.jpg" data-full="<?php echo $img; ?>/map-route.jpg" alt="Bản đồ lộ trình đường chạy" class="funrun-map w-100" style="height:340px;object-fit:cover;background:#eef6ef;cursor:zoom-in;">
            <div class="card-body py-2"><span class="small text-secondary">Lộ trình thực tế trên bản đồ (Tràng An Golf &amp; Resort - hồ Đồng Chương).</span></div>
          </div>
        </div>
      </div>
      <div class="row g-3">
        <?php foreach ($stations as $st): ?>
          <div class="col-md-6 col-xl-4">
            <div class="card border rounded-4 shadow-sm h-100">
              <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white position-relative" style="width:44px;height:44px;background:<?php echo $e($accent); ?>;">
                  <span class="material-symbols-outlined"><?php echo $e($st['icon']); ?></span>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-dark" style="font-size:11px;"><?php echo $e($st['no']); ?></span>
                </div>
                <div><strong class="text-dark d-block small"><?php echo $e($st['name']); ?></strong><span class="small text-secondary"><?php echo $e($st['pos']); ?></span></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="small text-secondary mt-3 mb-0"><span class="material-symbols-outlined align-middle fs-6">local_drink</span> Trạm tiếp nước tại km số 7: VĐV cự ly 10 km và 15 km được vào tối đa 01 lần/vòng. 02 trạm y tế trực suốt giải, cùng lực lượng hỗ trợ lưu động trên đường chạy.</p>
    </section>

    <!-- Nhận diện BIB -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">badge</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Nhận diện số BIB</h2>
      </div>
      <p class="text-secondary small">Mỗi cự ly và giới tính có mẫu BIB riêng. VĐV đeo đúng BIB được BTC cấp ở trước ngực trong suốt quá trình thi đấu.</p>
      <div class="row g-3">
        <?php foreach ($bibs as $b): ?>
          <div class="col-6 col-md-4 col-xl-3">
            <div class="card border rounded-4 shadow-sm overflow-hidden h-100">
              <img src="<?php echo $img; ?>/<?php echo $e($b); ?>.png" alt="Mẫu BIB Fun Run" class="w-100" style="object-fit:contain;">
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Quy định thi đấu -->
    <section class="mb-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">gavel</span>
        <h2 class="fw-bold text-dark fs-4 mb-0">Quy định &amp; điều lệ thi đấu</h2>
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
          <div class="alert mt-3 mb-0 small" style="background:<?php echo $e($accent); ?>0d;color:#166534;border:none;">
            <strong>Điều kiện sức khỏe:</strong> Việc đăng ký đồng nghĩa VĐV xác nhận đủ sức khỏe tham gia cự ly đã chọn. BTC khuyến nghị người có tiền sử bệnh tim mạch, huyết áp, hô hấp, chấn thương... tham khảo ý kiến bác sĩ trước khi đăng ký. VĐV tự chịu trách nhiệm với rủi ro phát sinh do sức khỏe cá nhân.
          </div>
          <p class="small text-secondary mt-3 mb-0">Khiếu nại về kết quả gửi trực tiếp BTC trong vòng 30 phút kể từ khi công bố kết quả tạm thời. Quyết định của Ban Trọng tài và BTC là quyết định cuối cùng.</p>
        </div>
      </div>
    </section>
  </div>
</main>

<!-- Lightbox xem ảnh sơ đồ phóng to -->
<div id="funrunLightbox" class="funrun-lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh phóng to">
  <button type="button" class="funrun-lightbox__close" aria-label="Đóng">&times;</button>
  <img src="" alt="Ảnh sơ đồ phóng to" class="funrun-lightbox__img">
</div>
<?php
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/css/pages/daihoi-funrun.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/js/pages/daihoi-funrun.js', CClientScript::POS_END);
?>