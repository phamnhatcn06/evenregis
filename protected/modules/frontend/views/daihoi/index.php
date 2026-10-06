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
$loginLabel = !empty($hasAdminAccess) ? 'Vào quản trị' : 'Cổng Đại Biểu';
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
    'blue' => array('grad' => 'from-blue-300 via-teal-200 to-amber-300', 'dot' => '#2dd4bf', 'bg' => 'linear-gradient(135deg,#0b1329,#1e3a8a 55%,#0f766e)'),
    'amber' => array('grad' => 'from-amber-300 via-orange-300 to-rose-300', 'dot' => '#fbbf24', 'bg' => 'linear-gradient(135deg,#7c2d12,#b45309 55%,#f59e0b)'),
    'purple' => array('grad' => 'from-purple-300 via-pink-300 to-amber-200', 'dot' => '#c084fc', 'bg' => 'linear-gradient(135deg,#581c87,#9333ea 55%,#ec4899)'),
    'teal' => array('grad' => 'from-teal-200 via-emerald-200 to-lime-200', 'dot' => '#2dd4bf', 'bg' => 'linear-gradient(135deg,#065f46,#0d9488 55%,#22c55e)'),
);
$slideList = array();
if (!empty($slides) && isset($slides[0]) && is_array($slides[0])) {
    foreach ($slides as $sl) {
        $theme = (string) $val($sl, array('theme'), 'blue');
        $slideList[] = array(
            'image' => $val($sl, array('image', 'mobile_image'), ''),
            'subtitle' => $val($sl, array('subtitle'), ''),
            'title' => $val($sl, array('title'), ''),
            'desc' => $val($sl, array('description'), ''),
            'btn_text' => $val($sl, array('button_text'), ''),
            'btn_url' => $val($sl, array('button_url'), ''),
            'theme' => isset($slideThemes[$theme]) ? $theme : 'blue',
        );
    }
}
if (empty($slideList)) {
    // Fallback: slide 1 lấy từ sự kiện, 2 slide còn lại là nội dung mặc định.
    $slideList = array(
        array('image' => $coverImage, 'subtitle' => $eventLocation, 'title' => $eventName, 'desc' => $heroDesc, 'btn_text' => 'Khám phá chương trình', 'btn_url' => '#noi-dung', 'theme' => 'blue'),
        array('image' => '', 'subtitle' => 'Giải Chạy Tiếp Sức', 'title' => 'MƯỜNG THANH FUN RUN - BỨT PHÁ TRÊN CUNG ĐƯỜNG DI SẢN', 'desc' => 'Cùng sải bước đón bình minh giữa non nước Ninh Bình kỳ vĩ với các cự ly 5KM và 10KM.', 'btn_text' => 'Tìm hiểu giải chạy', 'btn_url' => '#noi-dung', 'theme' => 'amber'),
        array('image' => '', 'subtitle' => 'Đêm Gala Tôn Vinh Bản Sắc', 'title' => 'VIỆT NAM GẤM HOA & MISS MƯỜNG THANH ' . $eventYear, 'desc' => 'Đại tiệc nghệ thuật truyền thống tôn vinh vẻ đẹp tự tin, duyên dáng và trí tuệ của cán bộ nhân viên Mường Thanh.', 'btn_text' => 'Xem chi tiết', 'btn_url' => '#tin-tuc', 'theme' => 'purple'),
    );
}
$slideCount = count($slideList);

// ----- Thư viện (dữ liệu từ bảng media_albums) -----
$albumGradients = array(
    array('#1e3a8a', '#0f766e'), array('#0f766e', '#15803d'),
    array('#7c3aed', '#2563eb'), array('#b45309', '#db2777'),
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
        array('Golf chào mừng Đại Hội', 'ALBUM'), array('Thể thao sôi động', 'BỘ MÔN'),
        array('Nghiệp vụ Mường Thanh', 'NGHIỆP VỤ'), array('Đêm Gala Đại Hội', 'ĐÊM GALA'),
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
            <img alt="<?php echo $e($eventName); ?>" class="img-fluid" src="<?php echo $e($logo); ?>" style="height:42px;object-fit:contain;">
            <div class="d-flex flex-column">
              <span class="fw-bold text-dark lh-1" style="font-size:15px;letter-spacing:-0.02em;">ĐẠI HỘI MƯỜNG THANH</span>
              <span class="text-primary fw-bold text-uppercase" style="font-size:10.5px;letter-spacing:0.08em;"><?php echo $e($eventLocation); ?></span>
            </div>
          </a>
        </div>
        <!-- Navigation (Desktop) -->
        <div class="col-lg-6 d-none d-lg-flex justify-content-center">
          <nav class="d-flex align-items-center gap-1">
            <?php foreach ($navItems as $it): ?>
              <a class="nav-link px-3 py-2 fw-semibold text-secondary rounded hover:text-dark hover:bg-light transition" href="<?php echo $e($it[0]); ?>"><?php echo $e($it[1]); ?></a>
            <?php endforeach; ?>
          </nav>
        </div>
        <!-- Header Actions -->
        <div class="col-4 col-sm-6 col-lg-3 d-flex align-items-center justify-content-end gap-2">
          <button aria-label="Thông báo" class="btn btn-light rounded-circle p-1 position-relative d-none d-sm-flex align-items-center justify-content-center" style="width:36px;height:36px;" type="button">
            <span class="material-symbols-outlined fs-5 text-secondary">notifications</span>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-primary border border-light rounded-circle"></span>
          </button>
          <a class="btn btn-sm fw-bold px-3 py-1 rounded-pill d-none d-md-inline-flex align-items-center gap-1 shadow-sm text-white" href="<?php echo $e($portalUrl); ?>" style="background:linear-gradient(135deg,#059669 0%,#0d9488 100%);border:none;font-size:13px;">
            <span class="material-symbols-outlined" style="font-size:16px;">how_to_reg</span> Đăng ký hoạt động
          </a>
          <a class="btn btn-primary btn-sm fw-bold px-3 py-1 rounded-pill d-none d-sm-inline-flex align-items-center shadow-sm" href="<?php echo $e($loginUrl); ?>" style="background:linear-gradient(135deg,#1d4ed8 0%,#312e81 100%);border:none;font-size:13px;">
            <?php echo $e($loginLabel); ?>
          </a>
          <button aria-expanded="false" aria-label="Menu" class="btn btn-light d-lg-none p-1 border rounded" data-bs-target="#mobileNav" data-bs-toggle="collapse" type="button">
            <span class="material-symbols-outlined fs-4">menu</span>
          </button>
        </div>
      </div>
      <!-- Mobile Horizontal Scroller Nav -->
      <div class="d-lg-none mt-2 pt-2 border-top">
        <div class="nav-scroller d-flex gap-2">
          <?php foreach ($navItems as $it): ?>
            <a class="badge bg-light text-dark fw-bold text-decoration-none px-3 py-2 border rounded-pill" href="<?php echo $e($it[0]); ?>"><?php echo $e($it[1]); ?></a>
          <?php endforeach; ?>
          <a class="badge text-white fw-bold text-decoration-none px-3 py-2 rounded-pill" href="<?php echo $e($portalUrl); ?>" style="background:linear-gradient(135deg,#059669 0%,#0d9488 100%);">Đăng ký hoạt động</a>
          <a class="badge bg-primary text-white fw-bold text-decoration-none px-3 py-2 rounded-pill" href="<?php echo $e($loginUrl); ?>"><?php echo $e($loginLabel); ?></a>
        </div>
      </div>
    </div>
    <div class="rainbow-divider w-100"></div>
  </header>

  <!-- MAIN -->
  <main class="flex-grow-1">

    <!-- SECTION 1: HERO SLIDER -->
    <section class="container py-3 py-md-4">
      <div class="row"><div class="col-12">
        <div class="hero-slider-wrap position-relative w-100 rounded-4 overflow-hidden shadow-lg bg-dark">
          <div class="w-100 h-100 position-relative overflow-hidden">
            <?php foreach ($slideList as $si => $sld):
                $th = $slideThemes[$sld['theme']];
                $first = ($si === 0);
            ?>
            <div class="hero-slide position-absolute top-0 start-0 w-100 h-100 d-flex align-items-end transition-opacity duration-700 <?php echo $first ? 'opacity-100 active' : 'opacity-0 d-none'; ?>"<?php echo $sld['image'] ? '' : ' style="background:' . $e($th['bg']) . ';"'; ?>>
              <?php if ($sld['image']): ?>
                <img alt="<?php echo $e($sld['title']); ?>" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" src="<?php echo $e($sld['image']); ?>">
              <?php endif; ?>
              <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
              <div class="position-relative z-1 p-4 p-md-5 text-white" style="max-width:820px;">
                <?php if ($sld['subtitle'] !== ''): ?>
                <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill bg-white/20 backdrop-blur-md border border-white/25 text-white fw-bold text-uppercase mb-2" style="font-size:11px;">
                  <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background-color:<?php echo $e($th['dot']); ?>;"></span> <?php echo $e($sld['subtitle']); ?>
                </div>
                <?php endif; ?>
                <h2 class="fw-black text-uppercase text-white lh-sm mb-2" style="font-size:calc(1.35rem + 1.6vw);">
                  <span class="text-transparent bg-clip-text bg-gradient-to-r <?php echo $th['grad']; ?>"><?php echo $e($sld['title']); ?></span>
                </h2>
                <?php if ($sld['desc'] !== ''): ?>
                <p class="text-light mb-3 d-none d-sm-block" style="font-size:calc(0.85rem + 0.25vw);max-width:650px;"><?php echo $e($sld['desc']); ?></p>
                <?php endif; ?>
                <?php if ($sld['btn_text'] !== ''): ?>
                <a class="btn btn-light fw-bold rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow text-dark" href="<?php echo $e($sld['btn_url'] !== '' ? $sld['btn_url'] : '#noi-dung'); ?>" style="font-size:13.5px;">
                  <span><?php echo $e($sld['btn_text']); ?></span><span class="material-symbols-outlined fs-6">arrow_forward</span>
                </a>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <!-- Controls -->
          <button aria-label="Trước" class="position-absolute top-50 start-0 translate-middle-y ms-2 ms-md-3 btn btn-dark btn-sm rounded-circle p-2 text-white bg-black/40 border border-white/20 backdrop-blur-sm z-2" id="slider-prev" type="button"><span class="material-symbols-outlined fs-5">arrow_back</span></button>
          <button aria-label="Sau" class="position-absolute top-50 end-0 translate-middle-y me-2 me-md-3 btn btn-dark btn-sm rounded-circle p-2 text-white bg-black/40 border border-white/20 backdrop-blur-sm z-2" id="slider-next" type="button"><span class="material-symbols-outlined fs-5">arrow_forward</span></button>
          <!-- Indicators -->
          <div class="position-absolute bottom-0 end-0 m-3 m-md-4 d-flex align-items-center gap-2 bg-black/50 px-3 py-1 rounded-pill border border-white/20 backdrop-blur-md z-2">
            <div class="d-flex align-items-center gap-1" id="slider-dots">
              <?php for ($si = 0; $si < $slideCount; $si++): ?>
              <button class="slider-dot rounded-pill border-0 transition" data-slide="<?php echo $si; ?>" style="height:6px;width:<?php echo $si === 0 ? '24px' : '8px'; ?>;background-color:<?php echo $si === 0 ? '#2dd4bf' : 'rgba(255,255,255,0.4)'; ?>;"></button>
              <?php endfor; ?>
            </div>
            <span class="text-white-50 font-monospace small ps-1" id="slider-counter" style="font-size:11px;">01 / <?php echo str_pad($slideCount, 2, '0', STR_PAD_LEFT); ?></span>
          </div>
        </div>

        <!-- Ticker -->
        <div class="card mt-3 border rounded-3 shadow-sm bg-white p-2 p-md-3">
          <div class="row align-items-center g-2">
            <div class="col-12 d-flex align-items-center gap-2">
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase px-2 py-1 fw-bold" style="font-size:11px;">
                <span class="spinner-grow spinner-grow-sm text-primary me-1" style="width:8px;height:8px;"></span> Cập nhật
              </span>
              <p class="mb-0 text-secondary text-truncate" style="font-size:13.5px;">
                <?php if (!empty($matches)):
                    $m0 = $matches[0]; ?>
                  <strong class="text-dark"><?php echo $e($val($m0, array('sport_name', 'category'), 'Thi đấu')); ?>:</strong>
                  <?php echo $e($val($m0, array('home_name', 'home', 'team_a'), '')); ?> vs <?php echo $e($val($m0, array('away_name', 'away', 'team_b'), '')); ?>
                  <?php $v = $val($m0, array('venue', 'location'), ''); echo $v ? ' • ' . $e($v) : ''; ?>
                <?php else: ?>
                  <strong class="text-dark">Đại hội Mường Thanh <?php echo $e($eventYear); ?></strong> • Theo dõi lịch thi đấu và kết quả cập nhật liên tục.
                <?php endif; ?>
              </p>
            </div>
          </div>
        </div>
      </div></div>
    </section>

    <!-- SECTION 1B: CTA ĐĂNG KÝ HOẠT ĐỘNG (Giải chạy + Tham quan) -->
    <section class="container py-2 py-md-3" id="dang-ky">
      <div class="card border-0 rounded-4 shadow overflow-hidden" style="background:linear-gradient(135deg,#065f46 0%,#0f766e 55%,#0e7490 100%);">
        <div class="card-body p-4 p-md-5 text-white">
          <div class="row align-items-center g-3">
            <div class="col-lg-8">
              <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill bg-white/20 border border-white/25 fw-bold text-uppercase mb-2" style="font-size:11px;">
                <span class="material-symbols-outlined" style="font-size:15px;">how_to_reg</span> Dành cho danh sách Vòng Chung Kết
              </div>
              <h2 class="fw-black text-uppercase lh-sm mb-2" style="font-size:calc(1.2rem + 1vw);">Đăng ký Giải chạy &amp; Đi tham quan</h2>
              <p class="text-light mb-3" style="font-size:14px;max-width:640px;">
                Chọn cự ly chạy Fun Run (5km / 10km / 15km) và đợt đi tham quan. Số suất giới hạn,
                đăng ký theo thứ tự &mdash; ai nhanh người đó được. Đăng nhập bằng mã định danh &amp; mã PIN cá nhân.
              </p>
              <div class="d-flex flex-wrap gap-3">
                <span class="d-inline-flex align-items-center gap-1" style="font-size:13px;"><span class="material-symbols-outlined" style="font-size:18px;">sprint</span> Fun Run 3 cự ly</span>
                <span class="d-inline-flex align-items-center gap-1" style="font-size:13px;"><span class="material-symbols-outlined" style="font-size:18px;">directions_bus</span> Tham quan 3 đợt</span>
              </div>
            </div>
            <div class="col-lg-4 text-lg-end">
              <a class="btn btn-light fw-bold rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow text-dark" href="<?php echo $e($portalUrl); ?>" style="font-size:14px;">
                <span>Đăng ký ngay</span><span class="material-symbols-outlined fs-6">arrow_forward</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 2: NỘI DUNG TRỌNG ĐIỂM -->
    <section class="container py-4 py-lg-5" id="noi-dung">
      <div class="row align-items-end justify-content-between mb-4 g-3">
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
        foreach ($cards as $card):
            $t = $card['theme'];
        ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100 card-hover border shadow-sm rounded-4 p-4 bg-white" style="border-top:4px solid <?php echo $e($t['border']); ?> !important;">
            <div class="d-flex align-items-center justify-content-between mb-4">
              <span class="badge <?php echo $t['badge']; ?> px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size:10.5px;"><?php echo $e($card['tag']); ?></span>
              <div class="rounded-3 <?php echo $t['icon_bg']; ?> text-white p-2 d-flex align-items-center justify-content-center shadow-sm" style="width:40px;height:40px;">
                <span class="material-symbols-outlined fs-5"><?php echo $e($t['icon']); ?></span>
              </div>
            </div>
            <div class="mt-auto">
              <h3 class="fw-bold text-dark fs-5 mb-2"><?php echo $e($card['title']); ?></h3>
              <p class="text-secondary small mb-3 lh-base line-clamp-2"><?php echo $e($card['desc']); ?></p>
              <div class="d-inline-flex align-items-center gap-1 <?php echo $t['link']; ?> fw-bold small">
                <span>Tìm hiểu thêm</span><span class="material-symbols-outlined fs-6">arrow_forward</span>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- SECTION 3: HÔM NAY TẠI ĐẠI HỘI -->
    <section class="container py-3" id="hom-nay">
      <div class="card border rounded-4 shadow-sm bg-white p-4">
        <div class="row align-items-center justify-content-between mb-3 g-2">
          <div class="col-12 col-md-8">
            <span class="text-primary fw-bold text-uppercase small d-block mb-1">Lịch trình sự kiện</span>
            <h2 class="fw-bold text-dark fs-4 mb-1">Hôm nay tại Đại hội</h2>
            <p class="text-secondary small mb-0">Lịch hiển thị theo dữ liệu thực tế và có thể được Ban Tổ chức cập nhật theo ngày.</p>
          </div>
        </div>
        <div class="card border bg-light rounded-3 p-3">
          <div class="row align-items-center g-3">
            <div class="col-12 col-md-4 d-flex align-items-center gap-3">
              <div class="rounded-3 bg-dark text-white d-flex flex-column align-items-center justify-content-center p-2 text-center" style="width:58px;height:58px;">
                <span class="fw-black fs-4 lh-1"><?php echo $e(date('d')); ?></span>
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
              <div class="card bg-white border rounded-3 p-3">
                <div class="row align-items-center justify-content-between g-2">
                  <div class="col-12 col-sm-8">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                      <span class="badge bg-light text-dark border fw-bold" style="font-size:11px;"><?php echo $e($todayItemTime); ?></span>
                      <strong class="text-dark small"><?php echo $e($todayItemName); ?></strong>
                    </div>
                    <p class="text-muted mb-0 small"><?php echo $e($todayItemPlace); ?></p>
                  </div>
                  <div class="col-12 col-sm-4 text-sm-end">
                    <a class="btn btn-dark btn-sm rounded-pill px-3 py-1 fw-bold" href="#ket-qua" style="font-size:12.5px;">Theo dõi chi tiết</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 4: KẾT QUẢ NỔI BẬT -->
    <section class="container py-4" id="ket-qua">
      <div class="p-3 p-md-4 rounded-4 shadow-sm position-relative overflow-hidden" style="background:linear-gradient(#ffffff 0%,#f1f5f9 100%);border:1px solid #e2e8f0;">
        <div class="position-absolute top-0 end-0 rounded-circle bg-primary-subtle opacity-75" style="width:320px;height:320px;filter:blur(80px);transform:translate(30%,-30%);pointer-events:none;"></div>

        <div class="row align-items-center justify-content-between mb-4 g-3 position-relative z-1">
          <div class="col-12 col-md-7">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
              <span class="badge bg-danger text-white rounded-pill px-2 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1 shadow-sm" style="font-size:11px;">
                <span class="spinner-grow spinner-grow-sm text-white" style="width:7px;height:7px;"></span> Cập nhật trực tiếp
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
                  <div class="rounded-circle bg-teal-50 text-teal-600 border border-teal-200 d-flex align-items-center justify-content-center" style="width:28px;height:28px;">
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
          <?php endforeach; else: ?>
            <div class="col-12"><p class="text-secondary small mb-0 py-4 text-center">Chưa có trận đấu nào được cập nhật.</p></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- SECTION 5: BANNER FUN RUN -->
    <section class="container py-3 py-md-4">
      <div class="rounded-4 p-4 p-md-5 text-white shadow-lg overflow-hidden position-relative" style="background:linear-gradient(135deg,#0b1329 0%,#1e3a8a 50%,#0f766e 100%);">
        <div class="row align-items-center g-4 position-relative z-1">
          <div class="col-12 col-md-8">
            <div class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill bg-white/10 text-white-50 text-uppercase fw-bold mb-2" style="font-size:11px;">
              <span class="material-symbols-outlined fs-6">stars</span> Đại Hội Mường Thanh <?php echo $e($eventYear); ?>
            </div>
            <h2 class="fw-black text-uppercase text-white lh-1 mb-2" style="font-size:calc(1.6rem + 1.2vw);">MƯỜNG THANH FUN RUN</h2>
            <p class="fs-6 fw-bold text-info-emphasis mb-2">Chạy Giữa Miền Di Sản • <?php echo $e($eventDestination); ?></p>
            <p class="text-light opacity-75 small mb-4" style="max-width:580px;">Hành trình chạy bộ kết nối cộng đồng người Mường Thanh giữa không gian danh thắng, nơi tinh thần rèn luyện thể thao hòa quyện cùng cảnh sắc non nước Ninh Bình hùng vĩ.</p>
            <div class="row g-2">
              <div class="col-12 col-sm-4"><div class="card bg-white/10 border-white/20 p-2 text-white"><span class="text-white-50 text-uppercase fw-bold" style="font-size:10px;">Thời gian</span><strong class="small">06:00 - 10:30</strong></div></div>
              <div class="col-12 col-sm-4"><div class="card bg-white/10 border-white/20 p-2 text-white"><span class="text-white-50 text-uppercase fw-bold" style="font-size:10px;">Điểm xuất phát</span><strong class="small">Quảng trường trung tâm</strong></div></div>
              <div class="col-12 col-sm-4"><div class="card bg-white/10 border-white/20 p-2 text-white"><span class="text-white-50 text-uppercase fw-bold" style="font-size:10px;">Cự ly</span><strong class="small">5KM &amp; 10KM</strong></div></div>
            </div>
          </div>
          <div class="col-12 col-md-4 text-center d-flex flex-column align-items-center justify-content-center gap-3">
            <div class="rounded-4 p-4 shadow-lg d-flex align-items-center justify-content-center text-white" style="width:110px;height:110px;background:linear-gradient(135deg,#ea580c,#f59e0b);">
              <span class="material-symbols-outlined" style="font-size:54px;">sprint</span>
            </div>
            <a class="btn btn-light rounded-pill fw-bold px-4 py-2 text-dark shadow d-inline-flex align-items-center gap-1" href="#noi-dung" style="font-size:13.5px;">
              <span>Xem chi tiết giải chạy</span><span class="material-symbols-outlined fs-6">arrow_forward</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 6: TIN TỨC -->
    <section class="container py-4" id="tin-tuc">
      <div class="row align-items-end justify-content-between mb-4 g-2">
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
                array('thumb' => '', 'cat' => 'Thể thao', 'date' => '', 'title' => 'Các đơn vị tích cực chuẩn bị cho các nội dung thể thao', 'desc' => 'Không khí luyện tập sôi nổi tại khắp các khách sạn trong hệ thống.', 'id' => ''),
                array('thumb' => '', 'cat' => 'Thông báo', 'date' => '', 'title' => 'Hướng dẫn thủ tục check-in và thẻ Đại biểu điện tử', 'desc' => 'Mỗi đại biểu vui lòng kích hoạt mã QR định danh trước khi có mặt.', 'id' => ''),
            );
        }
        foreach ($newsItems as $n):
            $href = $n['id'] !== '' ? $base . '/daihoi/index#tin-' . $e($n['id']) : '#tin-tuc';
        ?>
        <div class="col-12 col-md-4">
          <article class="card h-100 border rounded-3 overflow-hidden shadow-sm card-hover bg-white">
            <div class="position-relative" style="height:190px;background:#e2e8f0;">
              <?php if ($n['thumb']): ?>
                <img alt="<?php echo $e($n['title']); ?>" class="w-100 h-100 object-fit-cover" src="<?php echo $e($n['thumb']); ?>">
              <?php else: ?>
                <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#1e3a8a,#0f766e);">
                  <span class="material-symbols-outlined text-white" style="font-size:40px;">newspaper</span>
                </div>
              <?php endif; ?>
              <span class="position-absolute top-0 start-0 m-2 badge bg-dark/80 text-white text-uppercase" style="font-size:10px;"><?php echo $e($n['cat']); ?></span>
            </div>
            <div class="card-body d-flex flex-column justify-content-between p-3">
              <div>
                <?php if ($n['date']): ?><span class="text-muted d-block mb-1" style="font-size:11px;"><?php echo $e($n['date']); ?></span><?php endif; ?>
                <h3 class="fw-bold text-dark fs-6 mb-2 lh-sm line-clamp-2"><?php echo $e($n['title']); ?></h3>
                <p class="text-secondary small mb-3 line-clamp-2"><?php echo $e($n['desc']); ?></p>
              </div>
              <div class="pt-2 border-top">
                <a class="text-primary fw-bold text-decoration-none small" href="<?php echo $href; ?>">Đọc tiếp →</a>
              </div>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- SECTION 7: THƯ VIỆN -->
    <section class="container py-4" id="thu-vien">
      <div class="row align-items-end justify-content-between mb-4 g-2">
        <div class="col-12 col-md-8">
          <span class="text-primary fw-bold text-uppercase small d-block mb-1">Thư viện đại hội</span>
          <h2 class="fw-black text-dark fs-3 mb-1">Khoảnh khắc đáng nhớ</h2>
          <p class="text-secondary small mb-0">Hình ảnh, video recap và các album được Ban Tổ chức cập nhật liên tục trong suốt sự kiện.</p>
        </div>
      </div>
      <div class="row g-3">
        <?php foreach ($albumList as $al):
            $albumHref = $al['id'] !== '' ? $base . '/daihoi/index#album-' . $e($al['id']) : '#thu-vien';
        ?>
        <div class="col-6 col-md-3">
          <a class="card border-0 rounded-3 overflow-hidden position-relative shadow-sm card-hover text-decoration-none text-white d-block" href="<?php echo $albumHref; ?>" style="height:240px;<?php echo $al['cover'] ? '' : 'background:linear-gradient(135deg,' . $e($al['g'][0]) . ',' . $e($al['g'][1]) . ');'; ?>">
            <?php if ($al['cover']): ?>
              <img alt="<?php echo $e($al['title']); ?>" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" src="<?php echo $e($al['cover']); ?>">
            <?php endif; ?>
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-to-t from-black/85 via-black/20 to-transparent"></div>
            <div class="position-absolute bottom-0 start-0 p-3 w-100">
              <span class="badge bg-white/20 backdrop-blur-sm text-white mb-1" style="font-size:9.5px;"><?php echo $e($al['badge']); ?></span>
              <h3 class="fw-bold fs-6 text-white mb-0 text-truncate"><?php echo $e($al['title']); ?></h3>
              <span class="text-white-50" style="font-size:11px;"><?php echo $e($al['meta']); ?></span>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- SECTION 8: SỔ TAY ĐẠI BIỂU -->
    <section class="container pt-3 pb-5" id="so-tay">
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
          foreach ($handbook as $h):
          ?>
          <div class="col-12 col-sm-6 col-lg-3">
            <a class="card h-100 border rounded-3 p-3 bg-white text-decoration-none card-hover" href="#so-tay">
              <div class="d-flex align-items-start gap-3">
                <div class="rounded-3 <?php echo $h[1]; ?> p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
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
            <img alt="Mường Thanh" class="img-fluid" src="<?php echo $e($logo); ?>" style="height:38px;object-fit:contain;">
            <span class="fw-black text-dark fs-5">MƯỜNG THANH</span>
          </div>
          <p class="text-secondary small mb-3"><?php echo $e($eventName); ?> tại <?php echo $e($eventDestination); ?>. Nơi kết nối nhiệt huyết và vinh danh tinh thần đồng đội.</p>
          <div class="d-flex align-items-center gap-2 text-muted small">
            <span class="material-symbols-outlined fs-6">location_on</span>
            <span><?php echo $e($eventLocation); ?>, Việt Nam</span>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <h4 class="fw-bold text-dark text-uppercase small mb-3">Danh mục sự kiện</h4>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#noi-dung">Nội dung trọng điểm</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#hom-nay">Lịch thi đấu bộ môn</a></li>
            <li><a class="text-secondary text-decoration-none hover:text-dark transition" href="#ket-qua">Kết quả thi đấu</a></li>
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
        <div class="col-12 col-md-6"><p class="mb-0">© <?php echo $e($eventYear); ?> Tập đoàn Mường Thanh - <?php echo $e($eventName); ?></p></div>
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
</div>
