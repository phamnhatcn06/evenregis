<?php
/**
 * Footer dùng chung cho các trang công khai của Đại hội.
 * Partial tự tính các biến cần thiết nên render độc lập được ở mọi trang dùng
 * layout `daihoi`. Trang gọi có thể truyền thêm $event để hiển thị đúng thông tin.
 *
 * @var array|null $event   Thông tin sự kiện (tùy chọn).
 */
$base = Yii::app()->request->baseUrl;
$logo = $base . '/themes/hope-ui/logo_daihoi.png';

$e = function ($s) {
    return CHtml::encode($s !== null ? (string) $s : '');
};

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
$eventDestination = $pick(array('destination', 'location', 'venue', 'place', 'city'), 'Ninh Bình');
$eventLocation = $pick(array('location', 'venue', 'place'), (string) $eventDestination . ' 2026');
$eventYear = $pick(array('year'), date('Y'));

$hasPortalToken = !empty(Yii::app()->session['sso_token']) || AuthHandler::isAuthenticated();
$loginUrl = $hasPortalToken ? ($base . '/admin/default/index') : ($base . '/login');
?>
  <footer class="bg-white border-top mt-auto py-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-3">
          <div class="d-flex align-items-center gap-2 mb-3">
            <img alt="Mường Thanh" class="img-fluid" src="<?php echo $e($logo); ?>" style="height:40px;object-fit:contain;">
            <span class="fw-black text-dark fs-5">MƯỜNG THANH</span>
          </div>
          <p class="text-secondary small mb-3"><?php echo $e($eventName); ?> tại <?php echo $e($eventDestination); ?>. Nơi kết nối nhiệt huyết và vinh danh tinh thần đồng đội.</p>
          <div class="d-flex align-items-center gap-2 text-muted small">
            <span class="material-symbols-outlined fs-6 text-primary">location_on</span>
            <span><?php echo $e($eventLocation); ?>, Việt Nam</span>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <h4 class="fw-bold text-dark text-uppercase small mb-3">Danh mục sự kiện</h4>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/#noi-dung">Nội dung trọng điểm</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/agenda-tong-the">Lịch trình Đại hội</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/lich-thi-the-thao">Lịch thi đấu &amp; kết quả</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/tin-tuc">Tin nhanh hoạt động</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/#thu-vien">Thư viện ảnh &amp; video</a></li>
          </ul>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <h4 class="fw-bold text-dark text-uppercase small mb-3">Dành cho Đại biểu</h4>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/#so-tay">Sổ tay Đại biểu điện tử</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($loginUrl); ?>">Cổng đăng nhập Đại biểu</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/#so-tay">Quy chế &amp; điều lệ thi đấu</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/so-do-dia-diem">Sơ đồ địa điểm Đại hội</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/doan-thu-ky">Ban Thư Ký Đoàn</a></li>
          </ul>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <h4 class="fw-bold text-dark text-uppercase small mb-3">Ban Tổ Chức</h4>
          <div class="d-flex flex-column gap-2 small text-secondary">
            <div class="d-flex align-items-start gap-2">
              <span class="material-symbols-outlined text-muted fs-6">mail</span>
              <div><span class="text-muted d-block" style="font-size:11px;">Email ban tổ chức:</span><span class="text-dark fw-semibold">daihoi@muongthanh.vn</span></div>
            </div>
            <div class="d-flex align-items-start gap-2">
              <span class="material-symbols-outlined text-muted fs-6">schedule</span>
              <div><span class="text-muted d-block" style="font-size:11px;">Thời gian diễn ra:</span><span class="text-dark fw-semibold"><?php echo $e($eventLocation); ?></span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="row align-items-center justify-content-between pt-4 mt-4 border-top text-muted small g-2">
        <div class="col-12 col-md-6">
          <p class="mb-0">© <?php echo $e($eventYear); ?> Tập đoàn Mường Thanh - <?php echo $e($eventName); ?></p>
        </div>
        <div class="col-12 col-md-6 text-md-end">
          <div class="d-inline-flex gap-3">
            <a class="text-muted text-decoration-none hover:text-dark" href="<?php echo $e($base); ?>/#so-tay">Điều khoản</a><span>•</span>
            <a class="text-muted text-decoration-none hover:text-dark" href="<?php echo $e($base); ?>/#so-tay">Bảo mật thông tin</a><span>•</span>
            <a class="text-muted text-decoration-none hover:text-dark" href="<?php echo $e($base); ?>/ho-tro">Hỗ trợ kỹ thuật</a>
          </div>
        </div>
      </div>
    </div>
  </footer>
