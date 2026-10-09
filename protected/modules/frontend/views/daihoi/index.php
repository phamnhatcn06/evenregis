<?php

/**
 * Trang Website công khai Đại hội Mường Thanh - thiết kế mới (DHMT_update).
 * Bootstrap 5 + Tailwind (Play CDN local). Dữ liệu bind từ DaihoiController.
 *
 * @var array $event
 * @var array $stats
 * @var array $contents
 * @var array $agenda
 * @var array $liveMatches
 * @var array $recentMatches
 * @var array $rankings
 * @var array $news
 * @var array $slides
 * @var array $albums
 * @var bool  $hasAdminAccess
 */
$base = Yii::app()->request->baseUrl;

/** Lấy giá trị đầu tiên có trong mảng theo danh sách khoá, nếu không có trả default. */
$val = function ($arr, $keys, $default = '') {
  foreach ((array) $keys as $k) {
    if (is_array($arr) && isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
      return $arr[$k];
    }
  }
  return $default;
};
$e = function ($s) {
  return CHtml::encode($s);
};

// ----- Thông tin sự kiện -----
$eventName = $val($event, array('name', 'title'), 'ĐẠI HỘI MƯỜNG THANH 2026');
$eventSlogan = $val($event, array('slogan', 'subtitle'), 'HÀNH TRÌNH DI SẢN - RỰC CHÁY ĐAM MÊ');
$eventDestination = $val($event, array('destination', 'location', 'venue', 'place', 'city'), 'Ninh Bình');
$eventLocation = $val($event, array('location', 'venue', 'place'), $eventDestination . ' 2026');
$heroDesc = $val($event, array('hero_description', 'description'), 'Hội tụ tinh hoa tay nghề nghiệp vụ khách sạn và khát vọng bứt phá trong ngày hội thể thao quy mô nhất toàn tập đoàn.');
$coverImage = $val($event, array('cover_image', 'cover', 'banner'), '');
$eventYear = $val($event, array('year'), '');
if ($eventYear === '') {
  $fromDate = $val($event, array('from_date', 'start_date', 'starts_at'), '');
  if ($fromDate !== '') {
    $eventYear = ctype_digit((string) $fromDate) ? date('Y', (int) $fromDate) : date('Y', strtotime($fromDate));
  }
}
if ($eventYear === '') {
  $eventYear = '2026';
}
$logo = $base . '/themes/hope-ui/logo_daihoi.png';
$loginUrl = !empty($hasAdminAccess) ? $base . '/admin/default/index' : $base . '/login';
$loginLabel = !empty($hasAdminAccess) ? 'Vào quản trị' : 'Đăng nhập';
// Cổng đăng ký hoạt động (Giải chạy Fun Run + Đi tham quan) cho danh sách Vòng Chung Kết.
$portalUrl = $base . '/run';

// ----- Menu điều hướng (anchor trong trang) -----
$navItems = array(
  array('#dang-ky', 'Đăng ký'),
  array('#noi-dung', 'Chương trình'),
  array($base . '/daihoi/agenda', 'Lịch trình'),
  array($base . '/daihoi/schedule', 'Lịch thi đấu'),
  array('#tin-tuc', 'Tin tức'),
  array('#thu-vien', 'Thư viện'),
  array('#so-tay', 'Sổ tay'),
);

// ----- Bảng màu cho 6 thẻ nội dung trọng điểm -----
$cardThemes = array(
  array('border' => '#2563eb', 'badge' => 'bg-blue-50 text-primary border border-blue-200', 'icon_bg' => 'bg-primary', 'link' => 'text-primary', 'icon' => 'room_service', 'tag' => 'Thi Nghiệp Vụ'),
  array('border' => '#0d9488', 'badge' => 'bg-teal-50 text-teal-700 border border-teal-200', 'icon_bg' => 'bg-teal-600', 'link' => 'text-teal-600', 'icon' => 'sports_soccer', 'tag' => 'Thi Thể Thao'),
  array('border' => '#9333ea', 'badge' => 'bg-purple-50 text-purple-700 border border-purple-200', 'icon_bg' => 'bg-purple-600', 'link' => 'text-purple-600', 'icon' => 'diamond', 'tag' => 'Thi Sắc Đẹp'),
  array('border' => '#f59e0b', 'badge' => 'bg-amber-50 text-amber-800 border border-amber-200', 'icon_bg' => 'bg-amber-500', 'link' => 'text-amber-700', 'icon' => 'theater_comedy', 'tag' => 'Thi Văn Nghệ'),
  array('border' => '#0284c7', 'badge' => 'bg-sky-50 text-sky-700 border border-sky-200', 'icon_bg' => 'bg-sky-600', 'link' => 'text-sky-600', 'icon' => 'sprint', 'tag' => 'Thi Chạy Bộ'),
  array('border' => '#ea580c', 'badge' => 'bg-orange-50 text-orange-700 border border-orange-200', 'icon_bg' => 'bg-orange-600', 'link' => 'text-orange-600', 'icon' => 'sports_golf', 'tag' => 'Giải Chào Mừng'),
);

// Chuẩn hoá danh sách trận đấu cho khối Kết quả
$matches = !empty($liveMatches) ? $liveMatches : $recentMatches;
$statusMap = array(
  // Khoá chuẩn hoá của /api/daihoi/*
  'live' => array('cls' => 'bg-danger', 'text' => 'Đang diễn ra'),
  'done' => array('cls' => 'bg-secondary', 'text' => 'Đã kết thúc'),
  'upcoming' => array('cls' => 'bg-primary', 'text' => 'Sắp diễn ra'),
  // Khoá gốc từ bảng sport_matches.status
  'ongoing' => array('cls' => 'bg-danger', 'text' => 'Đang diễn ra'),
  'completed' => array('cls' => 'bg-secondary', 'text' => 'Đã kết thúc'),
  'scheduled' => array('cls' => 'bg-primary', 'text' => 'Sắp diễn ra'),
  'cancelled' => array('cls' => 'bg-light text-dark', 'text' => 'Đã huỷ'),
  'postponed' => array('cls' => 'bg-warning text-dark', 'text' => 'Hoãn'),
);
$initials = function ($name) {
  $parts = preg_split('/\s+/', trim((string) $name));
  $s = '';
  foreach ($parts as $p) {
    if ($p !== '' && mb_strlen($s) < 3) {
      $s .= mb_substr($p, 0, 1, 'UTF-8');
    }
  }
  return $s !== '' ? mb_strtoupper($s, 'UTF-8') : '--';
};
$matchScores = function ($m) use ($val) {
  $hs = $val($m, array('home_score'), null);
  $as = $val($m, array('away_score'), null);
  if ($hs !== null && $as !== null) {
    return array($hs, $as);
  }
  $raw = (string) $val($m, array('score'), '');
  if (preg_match('/^\s*(\d+)\s*[-:]\s*(\d+)\s*$/', $raw, $mm)) {
    return array($mm[1], $mm[2]);
  }
  return array('', '');
};

// Khối "Hôm nay": lấy ngày/hoạt động đầu tiên trong agenda
$todayTitle = 'Ngày thi đấu khởi động các bộ môn';
$todaySub = $eventDestination;
$todayItemTime = '08:00 - 17:30';
$todayItemName = 'Vòng loại các bộ môn thi đấu';
$todayItemPlace = 'Cụm sân thi đấu Đại hội';
if (!empty($agenda) && isset($agenda[0]) && is_array($agenda[0])) {
  $a0 = $agenda[0];
  $todayTitle = $val($a0, array('title', 'name'), $todayTitle);
  $todayItemName = $val($a0, array('title', 'name'), $todayItemName);
  $todayItemTime = $val($a0, array('time', 'time_range', 'start_time'), $todayItemTime);
  $todayItemPlace = $val($a0, array('location', 'venue', 'place'), $todayItemPlace);
}

// ----- Hero slider (dữ liệu từ bảng slideshows) -----
// Map màu nhấn -> class gradient (span tiêu đề) + màu chấm indicator.
$slideThemes = array(
  'blue' => array('grad' => 'from-blue-300 via-teal-200 to-amber-300', 'dot' => '#2dd4bf', 'bg' => 'linear-gradient(135deg,#09132b 0%,#173479 45%,#0d5f57 100%)'),
  'amber' => array('grad' => 'from-amber-300 via-orange-300 to-rose-300', 'dot' => '#fbbf24', 'bg' => 'linear-gradient(135deg,#451a03 0%,#854d0e 45%,#d97706 100%)'),
  'purple' => array('grad' => 'from-purple-300 via-pink-300 to-amber-200', 'dot' => '#c084fc', 'bg' => 'linear-gradient(135deg,#3b0764 0%,#6b21a8 45%,#db2777 100%)'),
  'teal' => array('grad' => 'from-teal-200 via-emerald-200 to-lime-200', 'dot' => '#2dd4bf', 'bg' => 'linear-gradient(135deg,#042f2e 0%,#0d9488 45%,#16a34a 100%)'),
);

$slideList = array();
if (!empty($slides) && isset($slides[0]) && is_array($slides[0])) {
  foreach ($slides as $sl) {
    $theme = (string) $val($sl, array('theme'), 'blue');
    $slideList[] = array(
      'image' => $val($sl, array('image', 'mobile_image'), ''),
      'mobile_image' => $val($sl, array('mobile_image'), ''),
      'subtitle' => $val($sl, array('subtitle'), ''),
      'title' => $val($sl, array('title'), ''),
      'desc' => $val($sl, array('description'), ''),
      'btn_text' => $val($sl, array('button_text'), ''),
      'btn_url' => $val($sl, array('button_url'), ''),
      'theme' => isset($slideThemes[$theme]) ? $theme : 'blue',
    );
  }
}

// Bổ sung các slide tiêu biểu để hero carousel luôn đầy đủ, sinh động (ít nhất 3-4 slide)
$curatedSlides = array(
  array('image' => '', 'subtitle' => 'Giải Chạy Tiếp Sức', 'title' => 'MƯỜNG THANH FUN RUN - BỨT PHÁ TRÊN CUNG ĐƯỜNG DI SẢN', 'desc' => 'Cùng sải bước đón bình minh giữa non nước Ninh Bình kỳ vĩ với các cự ly 5KM, 10KM và 15km.', 'btn_text' => 'Tìm hiểu giải chạy', 'btn_url' => '#dang-ky', 'theme' => 'amber'),
  array('image' => '', 'subtitle' => 'Đêm Gala Tôn Vinh Bản Sắc', 'title' => 'VIỆT NAM GẤM HOA & MISS MƯỜNG THANH ' . $eventYear, 'desc' => 'Đại tiệc nghệ thuật truyền thống tôn vinh vẻ đẹp tự tin, duyên dáng và trí tuệ của cán bộ nhân viên Mường Thanh.', 'btn_text' => 'Xem chi tiết', 'btn_url' => '#noi-dung', 'theme' => 'purple'),
  array('image' => '', 'subtitle' => 'Hội Tụ Tinh Hoa Nghề Nghiệp', 'title' => 'ĐỈNH CAO TAY NGHỀ & TRANH TÀI THỂ THAO RỰC LỬA', 'desc' => 'Chuẩn mực nghiệp vụ khách sạn thắp sáng tinh thần hiếu khách cùng ngọn lửa thể thao rực cháy của các đoàn vận động viên.', 'btn_text' => 'Khám phá ngay', 'btn_url' => '#noi-dung', 'theme' => 'teal'),
);

if (empty($slideList)) {
  $slideList = array_merge(
    array(array('image' => $coverImage, 'subtitle' => $eventLocation, 'title' => $eventName, 'desc' => $heroDesc, 'btn_text' => 'Khám phá chương trình', 'btn_url' => '#noi-dung', 'theme' => 'blue')),
    $curatedSlides
  );
} else {
  // Nếu DB có ít slide (vd chỉ có 1 slide), tự động bổ sung slide tiêu biểu
  foreach ($curatedSlides as $cs) {
    if (count($slideList) >= 2) break;
    $slideList[] = $cs;
  }
}
$slideCount = count($slideList);

// ----- Thư viện (dữ liệu từ bảng media_albums) -----
$albumGradients = array(
  array('#1e3a8a', '#0f766e'),
  array('#0f766e', '#15803d'),
  array('#7c3aed', '#2563eb'),
  array('#b45309', '#db2777'),
);
$albumList = array();
if (!empty($albums) && isset($albums[0]) && is_array($albums[0])) {
  $i = 0;
  foreach ($albums as $ab) {
    $g = $albumGradients[$i % count($albumGradients)];
    $count = (int) $val($ab, array('item_count'), 0);
    $albumList[] = array(
      'title' => $val($ab, array('title', 'name'), ''),
      'badge' => $val($ab, array('badge'), 'ALBUM'),
      'cover' => $val($ab, array('cover_image', 'cover'), ''),
      'meta' => $count > 0 ? $count . ' mục' : 'Cập nhật liên tục',
      'id' => $val($ab, array('id'), ''),
      'g' => $g,
    );
    $i++;
  }
}
if (empty($albumList)) {
  $defaultAlbums = array(
    array('Golf chào mừng Đại Hội', 'ALBUM'),
    array('Thể thao sôi động', 'BỘ MÔN'),
    array('Nghiệp vụ Mường Thanh', 'NGHIỆP VỤ'),
    array('Đêm Gala Đại Hội', 'ĐÊM GALA'),
  );
  foreach ($defaultAlbums as $i => $d) {
    $albumList[] = array('title' => $d[0], 'badge' => $d[1], 'cover' => '', 'meta' => 'Cập nhật liên tục', 'id' => '', 'g' => $albumGradients[$i % count($albumGradients)]);
  }
}
?>
<div id="daihoi-root" data-base-url="<?php echo $e($base); ?>">

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
          <a class="btn btn-sm fw-bold px-3 py-2 rounded-pill d-none d-md-inline-flex align-items-center gap-1 shadow-sm text-white btn-shimmer" href="<?php echo $e($portalUrl); ?>" style="background:linear-gradient(135deg,#059669 0%,#0d9488 100%);border:none;font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:17px;">how_to_reg</span> Đăng ký hoạt động
          </a>
          <a class="btn btn-primary btn-sm fw-bold px-3 py-2 rounded-pill d-none d-sm-inline-flex align-items-center shadow-sm btn-shimmer" href="<?php echo $e($loginUrl); ?>" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;font-size:13px;">
            <?php echo $e($loginLabel); ?>
          </a>
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
        <a class="btn fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1 text-white btn-shimmer" href="<?php echo $e($portalUrl); ?>" style="background:linear-gradient(135deg,#059669 0%,#0d9488 100%);border:none;font-size:14px;">
          <span class="material-symbols-outlined" style="font-size:18px;">how_to_reg</span> Đăng ký hoạt động
        </a>
        <a class="btn btn-primary fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center btn-shimmer" href="<?php echo $e($loginUrl); ?>" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;font-size:14px;"><?php echo $e($loginLabel); ?></a>
      </div>
    </div>
  </div>

  <!-- MAIN -->
  <main class="flex-grow-1">

    <!-- SECTION 1: HERO SLIDER (FULL WIDTH) -->
    <section class="hero-slider-section position-relative w-100 overflow-hidden">
      <div class="hero-slider-wrap position-relative w-100 overflow-hidden bg-dark">
        <!-- Slide Countdown Progress Bar -->
        <div class="slider-progress-track">
          <div class="slider-progress-bar" id="slider-progress-bar"></div>
        </div>

        <div class="hero-slider-track w-100 position-relative overflow-hidden">
          <?php foreach ($slideList as $si => $sld):
            $th = $slideThemes[$sld['theme']];
            $first = ($si === 0);
            $hasImg = !empty($sld['image']);
          ?>
            <?php
              $slideLink = isset($sld['btn_url']) ? trim((string) $sld['btn_url']) : '';
              $hasLink = ($slideLink !== '' && $slideLink !== '#');
              $mobileImg = (!empty($sld['mobile_image'])) ? $sld['mobile_image'] : '';
            ?>
            <div class="hero-slide w-100 <?php echo $hasImg ? '' : 'has-no-image'; ?> <?php echo $first ? 'opacity-100 active' : 'opacity-0 d-none'; ?>" style="<?php echo $hasImg ? '' : 'background:' . $e($th['bg']) . ';'; ?>">
              <?php if (!$hasImg): ?>
                <!-- Decorative fallback (chỉ hiện khi slide không có ảnh) -->
                <div class="position-absolute top-0 start-0 w-100 h-100 hero-slide-grid-pattern opacity-25 pointer-events-none"></div>
                <div class="glow-orb" style="top:15%;right:15%;width:340px;height:340px;background:radial-gradient(circle,<?php echo $e($th['dot']); ?>35 0%,transparent 70%);"></div>
              <?php else: ?>
                <?php $imgTag = '<picture class="slide-picture">'
                    . ($mobileImg !== '' ? '<source media="(max-width: 575.98px)" srcset="' . $e($mobileImg) . '">' : '')
                    . '<img alt="' . $e($sld['title']) . '" class="w-100 d-block slide-bg-img" src="' . $e($sld['image']) . '" onerror="this.closest(\'.slide-picture\').style.display=\'none\';">'
                    . '</picture>'; ?>
                <?php if ($hasLink): ?>
                  <a class="slide-link d-block w-100" href="<?php echo $e($slideLink); ?>" aria-label="<?php echo $e($sld['title'] !== '' ? $sld['title'] : 'Xem chi tiết'); ?>"><?php echo $imgTag; ?></a>
                <?php else: ?>
                  <div class="slide-link d-block w-100"><?php echo $imgTag; ?></div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- Controls -->
        <button aria-label="Trước" class="position-absolute top-50 start-0 translate-middle-y ms-2 ms-md-4 slider-control-btn z-2" id="slider-prev" type="button"><span class="material-symbols-outlined fs-5">arrow_back</span></button>
        <button aria-label="Sau" class="position-absolute top-50 end-0 translate-middle-y me-2 me-md-4 slider-control-btn z-2" id="slider-next" type="button"><span class="material-symbols-outlined fs-5">arrow_forward</span></button>
        <!-- Indicators -->
        <div class="position-absolute bottom-0 end-0 m-3 m-md-4 me-xl-5 mb-xl-4 d-flex align-items-center gap-2 slider-indicators-pill px-3 py-1 rounded-pill z-2">
          <div class="d-flex align-items-center gap-1" id="slider-dots">
            <?php foreach ($slideList as $si => $sld):
              $th = $slideThemes[$sld['theme']];
            ?>
              <button class="slider-dot rounded-pill border-0" data-slide="<?php echo $si; ?>" data-active-color="<?php echo $e($th['dot']); ?>" style="height:6px;width:<?php echo $si === 0 ? '28px' : '8px'; ?>;background-color:<?php echo $si === 0 ? $e($th['dot']) : 'rgba(255,255,255,0.4)'; ?>;"></button>
            <?php endforeach; ?>
          </div>
          <span class="text-white-50 font-monospace small ps-1" id="slider-counter" style="font-size:11px;">01 / <?php echo str_pad($slideCount, 2, '0', STR_PAD_LEFT); ?></span>
        </div>
      </div>
    </section>

    <!-- TICKER -->
    <section class="container pt-3 pt-md-4 pb-1 reveal-on-scroll">
      <div class="card border rounded-3 shadow-sm bg-white p-2 p-md-3 card-hover" style="border-left: 4px solid #1d4ed8 !important;">
        <div class="row align-items-center g-2">
          <div class="col-12 d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase px-2 py-1 fw-bold d-inline-flex align-items-center gap-1" style="font-size:11px;">
              <span class="pulse-live-dot bg-primary me-1" style="width:7px;height:7px;"></span> Cập nhật
            </span>
            <p class="mb-0 text-secondary text-truncate" style="font-size:13.5px;">
              <?php if (!empty($matches)):
                $m0 = $matches[0]; ?>
                <strong class="text-dark"><?php echo $e($val($m0, array('sport_name', 'category'), 'Thi đấu')); ?>:</strong>
                <?php echo $e($val($m0, array('home_name', 'home', 'team_a'), '')); ?> vs <?php echo $e($val($m0, array('away_name', 'away', 'team_b'), '')); ?>
                <?php $v = $val($m0, array('venue', 'location'), '');
                echo $v ? ' • ' . $e($v) : ''; ?>
              <?php else: ?>
                <strong class="text-dark">Đại hội Mường Thanh <?php echo $e($eventYear); ?></strong> • Theo dõi lịch thi đấu và kết quả cập nhật liên tục trực tiếp từ các cụm sân.
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 1B: CTA ĐĂNG KÝ HOẠT ĐỘNG (Giải chạy + Tham quan) -->
    <section class="container py-2 py-md-3 reveal-scale" id="dang-ky">
      <div class="card border-0 rounded-4 shadow-lg overflow-hidden position-relative" style="background:linear-gradient(135deg,#064e3b 0%,#0f766e 45%,#0369a1 100%);">
        <!-- Ambient glowing orbs -->
        <div class="glow-orb" style="top:-20%;right:-10%;width:320px;height:320px;background:radial-gradient(circle,rgba(45,212,191,0.25) 0%,transparent 70%);"></div>
        <div class="glow-orb" style="bottom:-30%;left:10%;width:280px;height:280px;background:radial-gradient(circle,rgba(56,189,248,0.2) 0%,transparent 70%);"></div>

        <div class="card-body p-4 p-md-5 text-white position-relative z-1">
          <div class="row align-items-center g-3">
            <div class="col-lg-8">
              <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill glass-badge fw-bold text-uppercase mb-2" style="font-size:11px;">
                <span class="material-symbols-outlined" style="font-size:15px;">how_to_reg</span> Dành cho danh sách Vòng Chung Kết
              </div>
              <h2 class="fw-black text-uppercase lh-sm mb-2" style="font-size:calc(1.3rem + 1vw);">Đăng ký Giải chạy &amp; Đi tham quan</h2>
              <p class="text-light opacity-90 mb-3" style="font-size:14.5px;max-width:640px;">
                Chọn cự ly chạy Fun Run (5km / 10km / 15km) và đợt đi tham quan. Số suất giới hạn,
                đăng ký theo thứ tự &mdash; ai nhanh người đó được. Đăng nhập bằng mã định danh &amp; mã PIN cá nhân.
              </p>
              <div class="d-flex flex-wrap gap-2 gap-sm-3">
                <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill glass-badge" style="font-size:13px;"><span class="material-symbols-outlined" style="font-size:18px;">sprint</span> Fun Run 3 cự ly</span>
                <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill glass-badge" style="font-size:13px;"><span class="material-symbols-outlined" style="font-size:18px;">directions_bus</span> Tham quan 3 đợt</span>
              </div>
            </div>
            <div class="col-lg-4 text-lg-end">
              <a class="btn btn-light fw-bold rounded-pill px-4 py-3 d-inline-flex align-items-center gap-2 shadow-lg text-dark btn-shimmer" href="<?php echo $e($portalUrl); ?>" style="font-size:14.5px;">
                <span>Đăng ký tham gia ngay</span><span class="material-symbols-outlined fs-6">arrow_forward</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 2: NỘI DUNG TRỌNG ĐIỂM -->
    <section class="container py-4 py-lg-5" id="noi-dung">
      <div class="row align-items-end justify-content-between mb-4 g-3 reveal-on-scroll">
        <div class="col-12 col-lg-8">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-blue-50 border border-blue-200 text-primary fw-bold text-uppercase mb-2" style="font-size:11px;">
            <span class="rounded-circle bg-primary d-inline-block" style="width:7px;height:7px;"></span> Nội dung trọng điểm Đại hội <?php echo $e($eventYear); ?>
          </div>
          <h2 class="fw-black text-dark mb-2" style="font-size:calc(1.4rem + 1vw);">Những hành trình đáng mong đợi</h2>
          <p class="text-secondary mb-0" style="font-size:14.5px;">Nơi hội tụ bản lĩnh nghề nghiệp, ý chí tranh tài thể thao rực lửa và tinh hoa văn hóa di sản của đại gia đình Mường Thanh.</p>
        </div>
      </div>

      <div class="row g-3 g-md-4">
        <?php
        $cards = array();
        if (!empty($contents)) {
          $i = 0;
          foreach ($contents as $c) {
            if ($i >= 6) break;
            $t = $cardThemes[$i % count($cardThemes)];
            $cards[] = array(
              'theme' => $t,
              'title' => $val($c, array('name', 'title'), ''),
              'desc' => $val($c, array('description', 'summary', 'short_description'), ''),
              'tag' => $val($c, array('type_label', 'category'), $t['tag']),
            );
            $i++;
          }
        }
        if (empty($cards)) {
          $defaults = array(
            array('Nghiệp vụ Mường Thanh', 'Chuẩn mực nghề nghiệp đỉnh cao, thắp sáng tinh thần hiếu khách chân thành và tay nghề vượt trội toàn chuỗi khách sạn.'),
            array('Thể thao sôi động', 'Sôi nổi tranh tài Bóng đá, Pickleball, Tennis và Cầu lông. Nơi ngọn lửa thể thao và tinh thần đồng đội bùng cháy hết mình.'),
            array('Miss Mường Thanh', 'Tôn vinh nét đẹp duyên dáng, trí tuệ sắc sảo cùng sự tự tin tỏa sáng của nữ cán bộ nhân viên Mường Thanh khắp ba miền.'),
            array('Việt Nam Gấm Hoa', 'Sân khấu đại tiệc nghệ thuật rực rỡ sắc màu vùng miền, khắc họa niềm tự hào truyền thống và hồn thiêng non nước ngàn năm.'),
            array('Mường Thanh Fun Run', 'Hành trình vạn dặm kết nối triệu bước chân xuyên lòng di sản Tràng An kỳ vĩ. Thử thách bứt phá giới hạn và gắn kết cộng đồng.'),
            array('Golf chào mừng', 'Giải đấu giao lưu thể thao đẳng cấp cao hội tụ ban lãnh đạo tập đoàn cùng các quan khách đối tác quý.'),
          );
          foreach ($defaults as $i => $d) {
            $t = $cardThemes[$i % count($cardThemes)];
            $cards[] = array('theme' => $t, 'title' => $d[0], 'desc' => $d[1], 'tag' => $t['tag']);
          }
        }
        $delays = array('delay-100', 'delay-150', 'delay-200', 'delay-250', 'delay-300', 'delay-350');
        foreach ($cards as $ci => $card):
          $t = $card['theme'];
          $delayClass = isset($delays[$ci]) ? $delays[$ci] : 'delay-100';
        ?>
          <div class="col-12 col-md-6 col-lg-4 reveal-on-scroll <?php echo $delayClass; ?>">
            <div class="card h-100 card-hover border shadow-sm rounded-4 p-4 bg-white" style="border-top:4px solid <?php echo $e($t['border']); ?> !important;">
              <div class="d-flex align-items-center justify-content-between mb-4">
                <span class="badge <?php echo $t['badge']; ?> px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size:10.5px;"><?php echo $e($card['tag']); ?></span>
                <div class="rounded-3 <?php echo $t['icon_bg']; ?> text-white p-2 d-flex align-items-center justify-content-center shadow-sm icon-bounce" style="width:40px;height:40px;">
                  <span class="material-symbols-outlined fs-5"><?php echo $e($t['icon']); ?></span>
                </div>
              </div>
              <div class="mt-auto">
                <h3 class="fw-bold text-dark fs-5 mb-2"><?php echo $e($card['title']); ?></h3>
                <p class="text-secondary small mb-3 lh-base line-clamp-2"><?php echo $e($card['desc']); ?></p>
                <div class="d-inline-flex align-items-center gap-1 <?php echo $t['link']; ?> fw-bold small">
                  <span>Tìm hiểu thêm</span><span class="material-symbols-outlined fs-6 arrow-slide">arrow_forward</span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- SECTION 3: HÔM NAY TẠI ĐẠI HỘI -->
    <section class="container py-3 reveal-on-scroll" id="hom-nay">
      <div class="card border rounded-4 shadow-sm bg-white p-4">
        <div class="row align-items-center justify-content-between mb-3 g-2">
          <div class="col-12 col-md-8">
            <span class="text-primary fw-bold text-uppercase small d-block mb-1">
              <span class="pulse-live-dot bg-primary me-1" style="width:7px;height:7px;"></span> Lịch trình sự kiện
            </span>
            <h2 class="fw-bold text-dark fs-4 mb-1">Hôm nay tại Đại hội</h2>
            <p class="text-secondary small mb-0">Lịch hiển thị theo dữ liệu thực tế và có thể được Ban Tổ chức cập nhật theo ngày.</p>
          </div>
        </div>
        <div class="card border bg-light rounded-3 p-3">
          <div class="row align-items-center g-3">
            <div class="col-12 col-md-4 d-flex align-items-center gap-3">
              <div class="rounded-3 bg-dark text-white d-flex flex-column align-items-center justify-content-center p-2 text-center shadow-sm" style="width:62px;height:62px;background:linear-gradient(135deg,#0f172a,#1e293b);">
                <span class="fw-black fs-4 lh-1 text-teal-300"><?php echo $e(date('d')); ?></span>
                <span class="text-white-50 text-uppercase fw-bold" style="font-size:10px;">Th<?php echo $e(date('n')); ?></span>
              </div>
              <div>
                <div class="d-flex align-items-center gap-1 text-muted small fw-bold">
                  <span class="rounded-circle bg-primary d-inline-block" style="width:7px;height:7px;"></span> <?php echo $e($todaySub); ?>
                </div>
                <h4 class="fw-bold text-dark fs-6 mb-0 mt-1"><?php echo $e($todayTitle); ?></h4>
              </div>
            </div>
            <div class="col-12 col-md-8">
              <div class="card bg-white border rounded-3 p-3 card-hover">
                <div class="row align-items-center justify-content-between g-2">
                  <div class="col-12 col-sm-8">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                      <span class="badge bg-light text-dark border fw-bold" style="font-size:11px;"><?php echo $e($todayItemTime); ?></span>
                      <strong class="text-dark small"><?php echo $e($todayItemName); ?></strong>
                    </div>
                    <p class="text-muted mb-0 small"><?php echo $e($todayItemPlace); ?></p>
                  </div>
                  <div class="col-12 col-sm-4 text-sm-end">
                    <a class="btn btn-dark btn-sm rounded-pill px-3 py-1 fw-bold btn-shimmer" href="<?php echo $e($base); ?>/daihoi/schedule" style="font-size:12.5px;">Theo dõi chi tiết</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 4: KẾT QUẢ NỔI BẬT -->
    <section class="container py-4 reveal-on-scroll" id="ket-qua">
      <div class="p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden" style="background:linear-gradient(#ffffff 0%,#f1f5f9 100%);border:1px solid #e2e8f0;">
        <div class="position-absolute top-0 end-0 rounded-circle bg-primary-subtle opacity-75" style="width:340px;height:340px;filter:blur(80px);transform:translate(30%,-30%);pointer-events:none;"></div>

        <div class="row align-items-center justify-content-between mb-4 g-3 position-relative z-1">
          <div class="col-12 col-md-7">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
              <span class="badge bg-danger text-white rounded-pill px-2 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-sm" style="font-size:11px;">
                <span class="pulse-live-dot bg-white me-1" style="width:6px;height:6px;"></span> Cập nhật trực tiếp
              </span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-3 text-white d-flex align-items-center justify-content-center shadow-sm" style="width:38px;height:38px;background:linear-gradient(135deg,#1d4ed8,#0d9488);">
                <span class="material-symbols-outlined fs-5">emoji_events</span>
              </div>
              <h2 class="fw-black text-dark fs-3 mb-0">Kết quả thi đấu nổi bật</h2>
            </div>
            <p class="text-secondary small mb-0 mt-1">Bảng tỷ số cập nhật theo thời gian thực từ các bộ môn tại Đại hội Mường Thanh <?php echo $e($eventYear); ?>.</p>
          </div>
          <div class="col-12 col-md-5 text-md-end">
            <a class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3 py-1 d-inline-flex align-items-center gap-1" href="<?php echo $e($base); ?>/daihoi/schedule">
              <span>Xem bảng xếp hạng &amp; lịch đấu</span><span class="material-symbols-outlined fs-6 arrow-slide">arrow_forward</span>
            </a>
          </div>
        </div>

        <div class="row g-3 position-relative z-1" id="live-match-grid">
          <?php if (!empty($matches)): foreach (array_slice($matches, 0, 3) as $m):
              $st = isset($statusMap[$val($m, array('status'), '')]) ? $statusMap[$m['status']] : $statusMap['done'];
              $sc = $matchScores($m);
              $home = $val($m, array('home_name', 'home', 'team_a'), '');
              $away = $val($m, array('away_name', 'away', 'team_b'), '');
              $meta = trim($val($m, array('time', 'kickoff_time'), '') . ($val($m, array('venue', 'location'), '') ? ' • ' . $val($m, array('venue', 'location'), '') : ''));
          ?>
              <div class="col-12 col-md-4">
                <div class="card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden card-hover" style="border-top:4px solid #10b981 !important;">
                  <div class="p-3 pb-2 border-bottom bg-slate-50 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle bg-teal-50 text-teal-600 border border-teal-200 d-flex align-items-center justify-content-center icon-bounce" style="width:28px;height:28px;">
                        <span class="material-symbols-outlined fs-6">sports_soccer</span>
                      </div>
                      <div>
                        <span class="fw-bold text-dark small d-block lh-1"><?php echo $e($val($m, array('sport_name', 'category'), 'Thi đấu')); ?></span>
                        <span class="text-muted" style="font-size:10.5px;"><?php echo $e($val($m, array('round_name', 'stage'), '')); ?></span>
                      </div>
                    </div>
                    <span class="badge <?php echo $st['cls']; ?> text-white rounded-pill px-2 py-1 fw-semibold" style="font-size:10px;"><?php echo $e($st['text']); ?></span>
                  </div>
                  <div class="p-3">
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2" style="background-color:#f8fafc;border-left:3px solid #2563eb;">
                      <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);"><?php echo $e($initials($home)); ?></div>
                        <span class="fw-bold text-dark small"><?php echo $e($home); ?></span>
                      </div>
                      <span class="badge rounded-3 px-2 py-1 text-primary fw-black fs-4 font-monospace" style="background-color:#dbeafe;"><?php echo $e($sc[0]); ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3">
                      <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-light text-secondary border d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;"><?php echo $e($initials($away)); ?></div>
                        <span class="fw-semibold text-secondary small"><?php echo $e($away); ?></span>
                      </div>
                      <span class="badge rounded-3 px-2 py-1 text-secondary fw-bold fs-4 font-monospace bg-light border"><?php echo $e($sc[1]); ?></span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light text-muted" style="font-size:11.5px;">
                    <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined text-secondary fs-6">schedule</span> <?php echo $e($meta); ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach;
          else: ?>
            <!-- Trạng thái chuẩn bị khởi tranh thể thao hấp dẫn -->
            <div class="col-12 col-md-4">
              <div class="card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden card-hover" style="border-top:4px solid #2563eb !important;">
                <div class="p-3 pb-2 border-bottom bg-slate-50 d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-blue-50 text-primary border border-blue-200 d-flex align-items-center justify-content-center icon-bounce" style="width:28px;height:28px;">
                      <span class="material-symbols-outlined fs-6">sports_soccer</span>
                    </div>
                    <div>
                      <span class="fw-bold text-dark small d-block lh-1">Bóng Đá Nam</span>
                      <span class="text-muted" style="font-size:10.5px;">Vòng Bảng Toàn Đoàn</span>
                    </div>
                  </div>
                  <span class="badge bg-primary text-white rounded-pill px-2 py-1 fw-semibold" style="font-size:10px;">Sắp diễn ra</span>
                </div>
                <div class="p-3">
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2" style="background-color:#f8fafc;border-left:3px solid #2563eb;">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);">MB</div>
                      <span class="fw-bold text-dark small">Khối Miền Bắc</span>
                    </div>
                    <span class="badge rounded-3 px-2 py-1 text-primary fw-black fs-5 font-monospace" style="background-color:#dbeafe;">--</span>
                  </div>
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle bg-light text-secondary border d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;">MT</div>
                      <span class="fw-semibold text-secondary small">Khối Miền Trung</span>
                    </div>
                    <span class="badge rounded-3 px-2 py-1 text-secondary fw-bold fs-5 font-monospace bg-light border">--</span>
                  </div>
                </div>
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light text-muted" style="font-size:11.5px;">
                  <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined text-secondary fs-6">schedule</span> 08:30 • Sân vận động trung tâm</span>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden card-hover" style="border-top:4px solid #0d9488 !important;">
                <div class="p-3 pb-2 border-bottom bg-slate-50 d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-teal-50 text-teal-600 border border-teal-200 d-flex align-items-center justify-content-center icon-bounce" style="width:28px;height:28px;">
                      <span class="material-symbols-outlined fs-6">sports_tennis</span>
                    </div>
                    <div>
                      <span class="fw-bold text-dark small d-block lh-1">Pickleball &amp; Tennis</span>
                      <span class="text-muted" style="font-size:10.5px;">Đôi Nam Nữ Phối Hợp</span>
                    </div>
                  </div>
                  <span class="badge bg-teal-600 text-white rounded-pill px-2 py-1 fw-semibold" style="font-size:10px;">Sắp diễn ra</span>
                </div>
                <div class="p-3">
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2" style="background-color:#f8fafc;border-left:3px solid #0d9488;">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;background:linear-gradient(135deg,#0d9488,#10b981);">MN</div>
                      <span class="fw-bold text-dark small">Khối Miền Nam</span>
                    </div>
                    <span class="badge rounded-3 px-2 py-1 text-teal-700 fw-black fs-5 font-monospace" style="background-color:#ccfbf1;">--</span>
                  </div>
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle bg-light text-secondary border d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;">VP</div>
                      <span class="fw-semibold text-secondary small">Văn Phòng Tập Đoàn</span>
                    </div>
                    <span class="badge rounded-3 px-2 py-1 text-secondary fw-bold fs-5 font-monospace bg-light border">--</span>
                  </div>
                </div>
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light text-muted" style="font-size:11.5px;">
                  <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined text-secondary fs-6">schedule</span> 09:15 • Cụm sân thể thao đa năng</span>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden card-hover" style="border-top:4px solid #f59e0b !important;">
                <div class="p-3 pb-2 border-bottom bg-slate-50 d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-amber-50 text-amber-700 border border-amber-200 d-flex align-items-center justify-content-center icon-bounce" style="width:28px;height:28px;">
                      <span class="material-symbols-outlined fs-6">sprint</span>
                    </div>
                    <div>
                      <span class="fw-bold text-dark small d-block lh-1">Mường Thanh Fun Run</span>
                      <span class="text-muted" style="font-size:10.5px;">Tiếp sức di sản Tràng An</span>
                    </div>
                  </div>
                  <span class="badge bg-amber-500 text-white rounded-pill px-2 py-1 fw-semibold" style="font-size:10px;">Chuẩn bị</span>
                </div>
                <div class="p-3">
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2" style="background-color:#f8fafc;border-left:3px solid #f59e0b;">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;background:linear-gradient(135deg,#f59e0b,#ea580c);">5K</div>
                      <span class="fw-bold text-dark small">Cự ly 5KM, 10KM, 15KM</span>
                    </div>
                    <span class="badge rounded-3 px-2 py-1 text-amber-800 fw-black fs-5 font-monospace" style="background-color:#fef3c7;">OPEN</span>
                  </div>
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle bg-light text-secondary border d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;">ALL</div>
                      <span class="fw-semibold text-secondary small">Vận động viên toàn đoàn</span>
                    </div>
                    <span class="badge rounded-3 px-2 py-1 text-secondary fw-bold fs-5 font-monospace bg-light border">READY</span>
                  </div>
                </div>
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light text-muted" style="font-size:11.5px;">
                  <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined text-secondary fs-6">schedule</span> 06:00 • Cung đường di sản Tràng An</span>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- SECTION 5: BANNER FUN RUN -->
    <section class="container py-3 py-md-4 reveal-scale">
      <div class="rounded-4 p-4 p-md-5 text-white shadow-lg overflow-hidden position-relative" style="background:linear-gradient(135deg,#0b1329 0%,#1e3a8a 50%,#0f766e 100%);">
        <!-- Floating ambient glowing light -->
        <div class="glow-orb" style="top:10%;right:15%;width:320px;height:320px;background:radial-gradient(circle,rgba(56,189,248,0.25) 0%,transparent 70%);"></div>

        <div class="row align-items-center g-4 position-relative z-1">
          <div class="col-12 col-md-8">
            <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill glass-badge text-white-50 text-uppercase fw-bold mb-2" style="font-size:11px;">
              <span class="material-symbols-outlined fs-6 text-warning">stars</span> Đại Hội Mường Thanh <?php echo $e($eventYear); ?>
            </div>
            <h2 class="fw-black text-uppercase text-white lh-1 mb-2" style="font-size:calc(1.6rem + 1.2vw);">MƯỜNG THANH FUN RUN</h2>
            <p class="fs-6 fw-bold text-info-emphasis mb-2">Chạy Giữa Miền Di Sản • <?php echo $e($eventDestination); ?></p>
            <p class="text-light opacity-90 small mb-4" style="max-width:580px;">Hành trình chạy bộ kết nối cộng đồng người Mường Thanh giữa không gian danh thắng, nơi tinh thần rèn luyện thể thao hòa quyện cùng cảnh sắc non nước Ninh Bình hùng vĩ.</p>
            <div class="row g-2">
              <div class="col-12 col-sm-4">
                <div class="card glass-badge border-0 p-3 text-white card-hover"><span class="text-white-50 text-uppercase fw-bold" style="font-size:10px;">Thời gian</span><strong class="small">06:00 - 10:30</strong></div>
              </div>
              <div class="col-12 col-sm-4">
                <div class="card glass-badge border-0 p-3 text-white card-hover"><span class="text-white-50 text-uppercase fw-bold" style="font-size:10px;">Điểm xuất phát</span><strong class="small">Quảng trường trung tâm</strong></div>
              </div>
              <div class="col-12 col-sm-4">
                <div class="card glass-badge border-0 p-3 text-white card-hover"><span class="text-white-50 text-uppercase fw-bold" style="font-size:10px;">Cự ly</span><strong class="small">5KM, 10KM &amp; 15KM</strong></div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4 text-center d-flex flex-column align-items-center justify-content-center gap-3">
            <div class="rounded-4 p-4 shadow-lg d-flex align-items-center justify-content-center text-white icon-bounce" style="width:110px;height:110px;background:linear-gradient(135deg,#ea580c,#f59e0b);box-shadow:0 12px 30px rgba(234,88,12,0.4) !important;">
              <span class="material-symbols-outlined" style="font-size:54px;">sprint</span>
            </div>
            <a class="btn btn-light rounded-pill fw-bold px-4 py-2 text-dark shadow-lg d-inline-flex align-items-center gap-1 btn-shimmer" href="#noi-dung" style="font-size:13.5px;">
              <span>Xem chi tiết giải chạy</span><span class="material-symbols-outlined fs-6 arrow-slide">arrow_forward</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 6: TIN TỨC -->
    <section class="container py-4" id="tin-tuc">
      <div class="row align-items-end justify-content-between mb-4 g-2 reveal-on-scroll">
        <div class="col-12 col-md-8">
          <span class="text-primary fw-bold text-uppercase small d-block mb-1">Tin tức &amp; Thông báo</span>
          <h2 class="fw-black text-dark fs-3 mb-1">Thông tin mới nhất</h2>
          <p class="text-secondary small mb-0">Cập nhật liên tục các tin tức, hoạt động và thông báo mới nhất từ Ban Tổ Chức.</p>
        </div>
      </div>
      <div class="row g-4">
        <?php
        $newsItems = array();
        if (!empty($news)) {
          foreach (array_slice($news, 0, 3) as $n) {
            $newsItems[] = array(
              'thumb' => $val($n, array('thumbnail', 'image', 'cover', 'thumbnail_url'), ''),
              'cat' => $val($n, array('category_name', 'category'), 'Tin tức'),
              'date' => $val($n, array('published_at', 'created_at', 'date'), ''),
              'title' => $val($n, array('title', 'name'), ''),
              'desc' => $val($n, array('excerpt', 'summary', 'short_description', 'description'), ''),
              'id' => $val($n, array('id'), ''),
            );
          }
        }
        if (empty($newsItems)) {
          $newsItems = array(
            array('thumb' => '', 'cat' => 'Ban Tổ Chức', 'date' => '', 'title' => 'Sẵn sàng cho hành trình hội tụ tại miền di sản Ninh Bình', 'desc' => 'Ban Tổ chức hoàn tất công tác chuẩn bị cho các nội dung trọng điểm của Đại hội.', 'id' => ''),
            array('thumb' => '', 'cat' => 'Thể thao', 'date' => '', 'title' => 'Các đơn vị tích cực chuẩn bị cho các nội dung thể thao', 'desc' => 'Không khí luyện tập sôi nổi tại khắp các khách sạn trong toàn hệ thống.', 'id' => ''),
            array('thumb' => '', 'cat' => 'Thông báo', 'date' => '', 'title' => 'Hướng dẫn thủ tục check-in và thẻ Đại biểu điện tử', 'desc' => 'Mỗi đại biểu vui lòng kích hoạt mã QR định danh trước khi có mặt tại sự kiện.', 'id' => ''),
          );
        }
        $newsDelays = array('delay-100', 'delay-200', 'delay-300');
        foreach ($newsItems as $ni => $n):
          $href = $n['id'] !== '' ? $base . '/daihoi/index#tin-' . $e($n['id']) : '#tin-tuc';
          $nDelay = isset($newsDelays[$ni]) ? $newsDelays[$ni] : 'delay-100';
        ?>
          <div class="col-12 col-md-4 reveal-on-scroll <?php echo $nDelay; ?>">
            <article class="card h-100 border rounded-3 overflow-hidden shadow-sm card-hover bg-white">
              <div class="position-relative img-zoom-wrap" style="height:190px;background:#e2e8f0;">
                <?php if ($n['thumb']): ?>
                  <img alt="<?php echo $e($n['title']); ?>" class="w-100 h-100 object-fit-cover" src="<?php echo $e($n['thumb']); ?>" onerror="this.style.display='none';">
                <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#1e3a8a,#0f766e);">
                    <span class="material-symbols-outlined text-white icon-bounce" style="font-size:42px;">newspaper</span>
                  </div>
                <?php endif; ?>
                <span class="position-absolute top-0 start-0 m-2 badge glass-card-dark text-white text-uppercase" style="font-size:10px;"><?php echo $e($n['cat']); ?></span>
              </div>
              <div class="card-body d-flex flex-column justify-content-between p-3">
                <div>
                  <?php if ($n['date']): ?><span class="text-muted d-block mb-1" style="font-size:11px;"><?php echo $e($n['date']); ?></span><?php endif; ?>
                  <h3 class="fw-bold text-dark fs-6 mb-2 lh-sm line-clamp-2"><?php echo $e($n['title']); ?></h3>
                  <p class="text-secondary small mb-3 line-clamp-2"><?php echo $e($n['desc']); ?></p>
                </div>
                <div class="pt-2 border-top">
                  <a class="text-primary fw-bold text-decoration-none small d-inline-flex align-items-center gap-1" href="<?php echo $href; ?>">
                    <span>Đọc tiếp</span><span class="arrow-slide">→</span>
                  </a>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- SECTION 7: THƯ VIỆN -->
    <section class="container py-4" id="thu-vien">
      <div class="row align-items-end justify-content-between mb-4 g-2 reveal-on-scroll">
        <div class="col-12 col-md-8">
          <span class="text-primary fw-bold text-uppercase small d-block mb-1">Thư viện đại hội</span>
          <h2 class="fw-black text-dark fs-3 mb-1">Khoảnh khắc đáng nhớ</h2>
          <p class="text-secondary small mb-0">Hình ảnh, video recap và các album được Ban Tổ chức cập nhật liên tục trong suốt sự kiện.</p>
        </div>
      </div>
      <div class="row g-3">
        <?php
        $albumDelays = array('delay-100', 'delay-200', 'delay-300', 'delay-400');
        foreach ($albumList as $ai => $al):
          $albumHref = $al['id'] !== '' ? $base . '/daihoi/index#album-' . $e($al['id']) : '#thu-vien';
          $aDelay = isset($albumDelays[$ai % count($albumDelays)]) ? $albumDelays[$ai % count($albumDelays)] : 'delay-100';
        ?>
          <div class="col-6 col-md-3 reveal-on-scroll <?php echo $aDelay; ?>">
            <a class="card border-0 rounded-3 overflow-hidden position-relative shadow-sm card-hover text-decoration-none text-white d-block img-zoom-wrap" href="<?php echo $albumHref; ?>" style="height:240px;<?php echo $al['cover'] ? '' : 'background:linear-gradient(135deg,' . $e($al['g'][0]) . ',' . $e($al['g'][1]) . ');'; ?>">
              <?php if ($al['cover']): ?>
                <img alt="<?php echo $e($al['title']); ?>" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" src="<?php echo $e($al['cover']); ?>" onerror="this.style.display='none';">
              <?php endif; ?>
              <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>
              <!-- Floating camera icon watermark on top right -->
              <div class="position-absolute top-0 end-0 m-2 text-white-50">
                <span class="material-symbols-outlined fs-5">photo_camera</span>
              </div>
              <div class="position-absolute bottom-0 start-0 p-3 w-100">
                <span class="badge glass-badge text-white mb-1" style="font-size:9.5px;"><?php echo $e($al['badge']); ?></span>
                <h3 class="fw-bold fs-6 text-white mb-0 text-truncate"><?php echo $e($al['title']); ?></h3>
                <span class="text-white-50" style="font-size:11px;"><?php echo $e($al['meta']); ?></span>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- SECTION 8: SỔ TAY ĐẠI BIỂU -->
    <section class="container pt-3 pb-5 reveal-on-scroll" id="so-tay">
      <div class="card border rounded-4 shadow-sm bg-white p-4">
        <div class="row align-items-center justify-content-between mb-4 g-2">
          <div class="col-12 col-md-8">
            <span class="text-primary fw-bold text-uppercase small d-block mb-1">Thông tin hữu ích</span>
            <h2 class="fw-black text-dark fs-4 mb-1">Sổ tay Đại biểu</h2>
            <p class="text-secondary small mb-0">Những thông tin cần biết trong suốt thời gian tham dự Đại hội.</p>
          </div>
        </div>
        <div class="row g-3">
          <?php
          $handbook = array(
            array('luggage', 'bg-blue-50 text-primary border border-blue-200', 'Hành trang chuẩn bị', 'Checklist trang phục, giấy tờ và đồ dùng cá nhân cần thiết.'),
            array('restaurant', 'bg-amber-50 text-amber-700 border border-amber-200', 'Ăn uống &amp; Lưu trú', 'Khung giờ dùng bữa tiệc buffet và sơ đồ phân phòng khách sạn.'),
            array('medical_services', 'bg-teal-50 text-teal-700 border border-teal-200', 'Y tế &amp; Hỗ trợ 24/7', 'Trực cấp cứu, tủ thuốc di động và hướng dẫn an toàn sự kiện.'),
            array('contact_phone', 'bg-purple-50 text-purple-700 border border-purple-200', 'Liên hệ &amp; Bản đồ BTC', 'Danh bạ thường trực tiểu ban và sơ đồ di chuyển các điểm thi.'),
          );
          foreach ($handbook as $hi => $h):
            $hDelay = isset($albumDelays[$hi % count($albumDelays)]) ? $albumDelays[$hi % count($albumDelays)] : 'delay-100';
          ?>
            <div class="col-12 col-sm-6 col-lg-3 reveal-on-scroll <?php echo $hDelay; ?>">
              <a class="card h-100 border rounded-3 p-3 bg-white text-decoration-none card-hover" href="#so-tay">
                <div class="d-flex align-items-start gap-3">
                  <div class="rounded-3 <?php echo $h[1]; ?> p-2 d-flex align-items-center justify-content-center flex-shrink-0 icon-bounce" style="width:42px;height:42px;">
                    <span class="material-symbols-outlined fs-5"><?php echo $e($h[0]); ?></span>
                  </div>
                  <div>
                    <h4 class="fw-bold text-dark fs-6 mb-1"><?php echo $h[2]; ?></h4>
                    <p class="text-secondary small mb-0 lh-sm"><?php echo $e($h[3]); ?></p>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
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
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#noi-dung">Nội dung trọng điểm</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/daihoi/agenda">Lịch trình Đại hội</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($base); ?>/daihoi/schedule">Lịch thi đấu &amp; kết quả</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#tin-tuc">Tin nhanh hoạt động</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#thu-vien">Thư viện ảnh &amp; video</a></li>
          </ul>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <h4 class="fw-bold text-dark text-uppercase small mb-3">Dành cho Đại biểu</h4>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#so-tay">Sổ tay Đại biểu điện tử</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="<?php echo $e($loginUrl); ?>">Cổng đăng nhập Đại biểu</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#so-tay">Quy chế &amp; điều lệ thi đấu</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#so-tay">Sơ đồ xe &amp; lưu trú</a></li>
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
            <a class="text-muted text-decoration-none hover:text-dark" href="#so-tay">Điều khoản</a><span>•</span>
            <a class="text-muted text-decoration-none hover:text-dark" href="#so-tay">Bảo mật thông tin</a><span>•</span>
            <a class="text-muted text-decoration-none hover:text-dark" href="<?php echo $e($base); ?>/">Hỗ trợ kỹ thuật</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- FLOATING BACK TO TOP BUTTON -->
  <button id="back-to-top" aria-label="Lên đầu trang" type="button">
    <span class="material-symbols-outlined fs-5">keyboard_arrow_up</span>
  </button>

</div>