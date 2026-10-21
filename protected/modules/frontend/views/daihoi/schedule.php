<?php
/**
 * Trang Lịch thi đấu & Kết quả thể thao Đại hội Mường Thanh 2026.
 * Dữ liệu từ DaihoiController::actionSchedule().
 *
 * @var array $event
 * @var array $tournament
 * @var array $liveMatches
 * @var array $recentMatches
 * @var array $upcomingMatches
 * @var array $rankings
 * @var int|null $selectedSportId
 * @var string|null $selectedStatus
 * @var string|null $selectedDate
 */

$base = Yii::app()->request->baseUrl;
$val = function ($arr, $keys, $default = '') {
    foreach ((array) $keys as $k) {
        if (is_array($arr) && isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
            return $arr[$k];
        }
    }
    return $default;
};
$e = function ($s) use (&$e) {
    if (is_array($s)) {
        if (isset($s['name'])) {
            return $e($s['name']);
        }
        if (isset($s['title'])) {
            return $e($s['title']);
        }
        if (isset($s['label'])) {
            return $e($s['label']);
        }
        foreach ($s as $v) {
            if (is_scalar($v) && (string) $v !== '') {
                return CHtml::encode((string) $v);
            }
        }
        return '';
    }
    if (is_object($s)) {
        if (method_exists($s, '__toString')) {
            return CHtml::encode((string) $s);
        }
        if (isset($s->name)) {
            return $e($s->name);
        }
        if (isset($s->title)) {
            return $e($s->title);
        }
        return '';
    }
    return CHtml::encode($s !== null ? (string) $s : '');
};
$eventName = $val($event, array('name', 'title'), 'Đại hội Mường Thanh Ninh Bình 2026');

$stats = isset($tournament['stats']) ? $tournament['stats'] : array(
    'total_sports' => 0,
    'total_stages' => 0,
    'total_matches' => 0,
    'completed_matches' => 0,
    'live_matches' => 0,
    'scheduled_matches' => 0,
);

$allMatches = isset($tournament['matches']) ? $tournament['matches'] : array();
$sportsList = isset($tournament['sports']) ? $tournament['sports'] : array();
$standingsBySport = isset($tournament['standingsBySport']) ? $tournament['standingsBySport'] : array();
$swimBySport = isset($tournament['swimBySport']) ? $tournament['swimBySport'] : array();

// Đổi mili-giây sang chuỗi thành tích bơi (m:ss.cc hoặc ss.cc)
$fmtSwimTime = function ($ms) {
    if ($ms === null || $ms === '') {
        return '—';
    }
    $ms = (int) $ms;
    $totalSec = intval($ms / 1000);
    $cs = intval(round(($ms % 1000) / 10));
    $min = intval($totalSec / 60);
    $sec = $totalSec % 60;
    return $min > 0 ? sprintf('%d:%02d.%02d', $min, $sec, $cs) : sprintf('%d.%02d', $sec, $cs);
};
// Map nhanh id môn -> tên/icon (từ sportsList) để hiển thị tiêu đề khối bơi
$sportInfoById = array();
foreach ($sportsList as $sp) {
    $sportInfoById[$sp['id']] = $sp;
}

// Hàm lấy chữ cái viết tắt của tên đội / tên VĐV (Avatar chữ)
$initials = function ($name) {
    // 1. Loại bỏ mã đơn vị trong ngoặc (ví dụ: (DCH), (HUE), (MT Hà Nam)) để tính initials từ tên VĐV thật
    $clean = preg_replace('/\s*\([^)]*\)/u', '', trim((string) $name));
    // 2. Loại bỏ các tiền tố đội/liên quân nếu có
    $clean = preg_replace('/^(Liên quân\s+|Đội\s+|Khối\s+)/iu', '', trim($clean));
    $parts = preg_split('/[\s\-_]+/u', trim($clean));
    $s = '';
    foreach ($parts as $p) {
        if ($p !== '' && mb_strlen($s) < 3) {
            $s .= mb_substr($p, 0, 1, 'UTF-8');
        }
    }
    return $s !== '' ? mb_strtoupper($s, 'UTF-8') : 'MT';
};

// Định dạng ngày tiếng Việt thân thiện
$formatFriendlyDate = function ($dateStr) {
    if (empty($dateStr)) return 'Lịch thi đấu';
    $ts = strtotime($dateStr);
    if (!$ts) return $dateStr;
    $days = array('Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy');
    $w = $days[(int) date('w', $ts)];
    return $w . ', ngày ' . date('d/m/Y', $ts);
};

// Trích xuất danh sách các ngày thi đấu duy nhất
$uniqueDates = array();
foreach ($allMatches as $m) {
    $d = !empty($m['match_date']) ? $m['match_date'] : '';
    if ($d && !in_array($d, $uniqueDates)) {
        $uniqueDates[] = $d;
    }
}
sort($uniqueDates);

// Gom nhóm các trận đấu theo ngày thi đấu
$matchesByDate = array();
foreach ($allMatches as $m) {
    $d = !empty($m['match_date']) ? $m['match_date'] : '__no_date__';
    if (!isset($matchesByDate[$d])) {
        $matchesByDate[$d] = array();
    }
    $matchesByDate[$d][] = $m;
}

// Bảng màu cho từng bộ môn thể thao
$sportThemes = array(
    'FOOTBALL' => array('bg' => '#ecfdf5', 'text' => '#059669', 'border' => '#a7f3d0', 'icon' => 'sports_soccer'),
    'BADMINTON' => array('bg' => '#eff6ff', 'text' => '#2563eb', 'border' => '#bfdbfe', 'icon' => 'sports_tennis'),
    'TUG_OF_WAR' => array('bg' => '#fef3c7', 'text' => '#d97706', 'border' => '#fde68a', 'icon' => 'fitness_center'),
    'TABLE_TENNIS' => array('bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe', 'icon' => 'sports_tennis'),
    'PICKLEBALL' => array('bg' => '#f0fdfa', 'text' => '#0d9488', 'border' => '#99f6e4', 'icon' => 'sports_tennis'),
    'SWIMMING' => array('bg' => '#e0f2fe', 'text' => '#0284c7', 'border' => '#bae6fd', 'icon' => 'pool'),
);

// Helper: phân loại môn thi đấu (category, theme color, icon, label)
$getSportMeta = function ($name) {
    $n = mb_strtolower((string)$name, 'UTF-8');
    if (strpos($n, 'bóng đá') !== false) {
        return array(
            'cat' => 'football',
            'cat_name' => 'Bóng đá',
            'cat_icon' => '⚽',
            'color' => '#059669',
            'bg_light' => '#ecfdf5',
            'icon' => 'sports_soccer',
            'type_label' => (strpos($n, 'nữ') !== false ? 'Bóng đá nữ' : 'Bóng đá nam')
        );
    }
    if (strpos($n, 'bóng bàn') !== false) {
        return array(
            'cat' => 'table-tennis',
            'cat_name' => 'Bóng bàn',
            'cat_icon' => '🏓',
            'color' => '#ea580c',
            'bg_light' => '#fff7ed',
            'icon' => 'sports_tennis',
            'type_label' => 'Môn bóng bàn'
        );
    }
    if (strpos($n, 'cầu lông') !== false) {
        return array(
            'cat' => 'badminton',
            'cat_name' => 'Cầu lông',
            'cat_icon' => '🏸',
            'color' => '#0284c7',
            'bg_light' => '#f0f9ff',
            'icon' => 'sports_tennis',
            'type_label' => 'Môn cầu lông'
        );
    }
    if (strpos($n, 'pickleball') !== false) {
        return array(
            'cat' => 'pickleball',
            'cat_name' => 'Pickleball',
            'cat_icon' => '🎾',
            'color' => '#7c3aed',
            'bg_light' => '#faf5ff',
            'icon' => 'sports_tennis',
            'type_label' => 'Môn Pickleball'
        );
    }
    if (strpos($n, 'bơi') !== false) {
        return array(
            'cat' => 'swimming',
            'cat_name' => 'Bơi lội',
            'cat_icon' => '🏊',
            'color' => '#0d9488',
            'bg_light' => '#f0fdfa',
            'icon' => 'pool',
            'type_label' => 'Đường đua xanh'
        );
    }
    if (strpos($n, 'kéo co') !== false) {
        return array(
            'cat' => 'tug-of-war',
            'cat_name' => 'Kéo co',
            'cat_icon' => '🪢',
            'color' => '#e11d48',
            'bg_light' => '#fff1f2',
            'icon' => 'fitness_center',
            'type_label' => 'Đồng đội đối kháng'
        );
    }
    if (strpos($n, 'chạy') !== false || strpos($n, 'điền kinh') !== false) {
        return array(
            'cat' => 'running',
            'cat_name' => 'Điền kinh',
            'cat_icon' => '🏃',
            'color' => '#dc2626',
            'bg_light' => '#fef2f2',
            'icon' => 'directions_run',
            'type_label' => 'Điền kinh'
        );
    }
    return array(
        'cat' => 'other',
        'cat_name' => 'Nội dung khác',
        'cat_icon' => '🏆',
        'color' => '#2563eb',
        'bg_light' => '#eff6ff',
        'icon' => 'emoji_events',
        'type_label' => 'Nội dung thi đấu'
    );
};
?>

<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'schedule')); ?>

<main class="flex-grow-1 bg-slate-50 py-3 py-lg-4" id="schedule-page-root">
  <div class="container">

    <!-- =====================================================================
         1. HERO HEADER: ẤM ÁP, THỂ THAO, TRÀN ĐẦY NĂNG LƯỢNG
         ===================================================================== -->
    <div class="schedule-hero-banner mb-4 p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden">
      <!-- Glow & Pattern background -->
      <div class="hero-glow-amber"></div>
      <div class="hero-glow-teal"></div>
      <div class="hero-pattern-dots"></div>

      <div class="row align-items-center position-relative z-1 g-4">
        <div class="col-lg-7">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white/20 text-white small fw-bold mb-3 backdrop-blur border border-white/20">
            <span class="material-symbols-outlined fs-6 text-warning">military_tech</span>
            <span>Hội Thao Mường Thanh 2026 • Tinh thần Đoàn kết - Bứt phá</span>
          </div>
          <h1 class="display-6 fw-black text-white mb-2 tracking-tight">
            Lịch Thi Đấu &amp; Kết Quả
          </h1>
          <p class="text-white-85 fs-6 mb-4 max-w-2xl lh-base">
            Cập nhật tỷ số trực tiếp, lịch tranh tài các bộ môn và bảng tổng sắp huy chương toàn đoàn theo thời gian thực.
          </p>

          <!-- Quick Navigation Links (Neo nhanh tới các phần) -->
          <div class="d-flex flex-wrap align-items-center gap-2 hero-quick-anchors">
            <?php if (!empty($stats['live_matches']) && $stats['live_matches'] > 0): ?>
              <a href="#live-section" class="btn btn-sm btn-danger rounded-pill fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                <span class="pulse-indicator-white"></span>
                <span>Trực tiếp (<?php echo (int)$stats['live_matches']; ?>)</span>
              </a>
            <?php endif; ?>
            <a href="#matches-section" class="btn btn-sm btn-light rounded-pill fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 text-dark shadow-sm">
              <span class="material-symbols-outlined fs-6 text-primary">calendar_month</span>
              <span>Lịch đấu &amp; Tỷ số</span>
            </a>
            <?php if (!empty($sportsWithRules)): ?>
              <a href="#rules-section" class="btn btn-sm btn-outline-light rounded-pill fw-bold px-3 py-2 d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-6 text-warning">gavel</span>
                <span>Xem thể thức</span>
              </a>
            <?php endif; ?>
            <?php if (!empty($standingsBySport)): ?>
              <a href="#standings-section" class="btn btn-sm btn-outline-light rounded-pill fw-bold px-3 py-2 d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-6">leaderboard</span>
                <span>Bảng đấu</span>
              </a>
            <?php endif; ?>
            <?php if (!empty($rankings)): ?>
              <a href="#rankings-section" class="btn btn-sm btn-outline-light rounded-pill fw-bold px-3 py-2 d-inline-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-6 text-warning">emoji_events</span>
                <span>Bảng huy chương</span>
              </a>
            <?php endif; ?>
            <button type="button" id="btn-manual-refresh" class="btn btn-sm btn-white/20 text-white rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1 hover:bg-white/30 transition">
              <span class="material-symbols-outlined fs-6 refresh-icon">refresh</span>
              <span>Làm mới</span>
            </button>
          </div>
        </div>

        <!-- Quick Stats Cards (Thẻ thống kê hiện đại, thân thiện) -->
        <div class="col-lg-5">
          <div class="row g-2 g-md-3">
            <div class="col-6">
              <div class="stat-pill-card p-3 rounded-4 bg-white/10 backdrop-blur border border-white/20 text-white">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small text-white-70 fw-medium">Tổng số trận</span>
                  <span class="material-symbols-outlined fs-5 text-warning">sports</span>
                </div>
                <div class="fs-2 fw-black text-warning"><?php echo (int) $stats['total_matches']; ?></div>
                <div class="small text-white-60" style="font-size:11.5px;">Toàn giải đấu</div>
              </div>
            </div>

            <div class="col-6">
              <div class="stat-pill-card p-3 rounded-4 bg-white/10 backdrop-blur border border-white/20 text-white <?php echo $stats['live_matches'] > 0 ? 'border-danger/60' : ''; ?>">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small text-white-70 fw-medium">Đang diễn ra</span>
                  <span class="pulse-indicator-live"></span>
                </div>
                <div class="fs-2 fw-black text-danger d-flex align-items-center gap-1">
                  <?php echo (int) $stats['live_matches']; ?>
                  <?php if ($stats['live_matches'] > 0): ?>
                    <span class="badge bg-danger text-white rounded-pill px-2 py-0" style="font-size:10px;">LIVE</span>
                  <?php endif; ?>
                </div>
                <div class="small text-white-60" style="font-size:11.5px;">Cập nhật tức thì</div>
              </div>
            </div>

            <div class="col-6">
              <div class="stat-pill-card p-3 rounded-4 bg-white/10 backdrop-blur border border-white/20 text-white">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small text-white-70 fw-medium">Đã kết thúc</span>
                  <span class="material-symbols-outlined fs-5 text-success">task_alt</span>
                </div>
                <div class="fs-2 fw-black text-emerald"><?php echo (int) $stats['completed_matches']; ?></div>
                <div class="small text-white-60" style="font-size:11.5px;">Đã có kết quả</div>
              </div>
            </div>

            <div class="col-6">
              <div class="stat-pill-card p-3 rounded-4 bg-white/10 backdrop-blur border border-white/20 text-white">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small text-white-70 fw-medium">Nội dung thi</span>
                  <span class="material-symbols-outlined fs-5 text-cyan">category</span>
                </div>
                <div class="fs-2 fw-black text-cyan"><?php echo (int) $stats['total_sports']; ?></div>
                <div class="small text-white-60" style="font-size:11.5px;">Môn tranh tài</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- =====================================================================
         2. BỘ ĐIỀU KHIỂN & LỌC THÔNG MINH (SMART FILTERS & SEARCH)
         ===================================================================== -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 filter-control-card sticky-filter">
      <!-- 2.1 Chọn Môn Thi Đấu (Sport Chips) -->
      <div class="sports-filter-card p-3 p-md-3 bg-white rounded-top-4 border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2.5">
          <div class="d-flex align-items-center gap-2">
            <div class="sport-section-icon-box">
              <span class="material-symbols-outlined fs-5">sports_score</span>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <span class="sport-section-title">Chọn môn thi đấu</span>
                <span class="sport-total-badge"><?php echo count($sportsList); ?> môn</span>
              </div>
            </div>
          </div>

          <!-- Chế độ xem: Thẻ lưới vs Danh sách gọn -->
          <div class="d-flex align-items-center gap-2 view-mode-switch">
            <span class="text-muted small d-none d-sm-inline" style="font-size:12px;">Chế độ xem:</span>
            <div class="view-mode-segmented-control">
              <button type="button" class="view-mode-btn active" id="btn-view-cards" title="Xem dạng thẻ">
                <span class="material-symbols-outlined fs-6 align-middle me-1">grid_view</span>
                <span>Thẻ</span>
              </button>
              <button type="button" class="view-mode-btn" id="btn-view-compact" title="Xem dạng danh sách gọn">
                <span class="material-symbols-outlined fs-6 align-middle me-1">format_list_bulleted</span>
                <span>Danh sách</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Scroller Wrapper với Nút cuộn trái/phải -->
        <div class="sports-scroller-wrapper position-relative">
          <!-- Nút cuộn Trái -->
          <button type="button" class="sports-nav-arrow sports-nav-prev is-hidden" id="btn-sports-scroll-left" aria-label="Cuộn sang trái" title="Cuộn sang trái">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>

          <!-- Thanh cuộn ngang các môn thi đấu -->
          <div class="sports-nav-scroller" id="sports-nav-scroller">
            <!-- Nút Tất Cả Môn -->
            <button type="button"
                    class="sport-filter-btn <?php echo empty($selectedSportId) ? 'active btn-brand-primary' : 'btn-light'; ?>"
                    data-sport-id=""
                    title="Tất cả các môn thi đấu (<?php echo count($allMatches); ?> trận)">
              <span class="sport-pill-icon material-symbols-outlined text-warning">emoji_events</span>
              <span class="sport-pill-name">Tất cả môn</span>
              <span class="sport-pill-badge"><?php echo count($allMatches); ?></span>
            </button>

            <!-- Từng môn thi đấu -->
            <?php foreach ($sportsList as $sp): ?>
              <?php
              $isActive = ($selectedSportId == $sp['id']);
              $btnCls = $isActive ? 'active btn-brand-primary' : 'btn-light';
              $mCount = (int)$sp['total_matches'];
              $meta = $getSportMeta($sp['name']);
              $spIcon = !empty($sp['icon']) ? $sp['icon'] : $meta['icon'];
              ?>
              <button type="button"
                      class="sport-filter-btn <?php echo $btnCls; ?>"
                      data-sport-id="<?php echo $sp['id']; ?>"
                      data-sport-name="<?php echo $e($sp['name']); ?>"
                      title="<?php echo $e($sp['name']); ?><?php echo $mCount > 0 ? " ({$mCount} trận)" : ''; ?>">
                <span class="sport-pill-icon material-symbols-outlined" style="<?php echo !$isActive ? 'color:' . $meta['color'] . ';' : ''; ?>">
                  <?php echo $e($spIcon); ?>
                </span>
                <span class="sport-pill-name"><?php echo $e($sp['name']); ?></span>
                <?php if ($mCount > 0): ?>
                  <span class="sport-pill-badge"><?php echo $mCount; ?></span>
                <?php endif; ?>
              </button>
            <?php endforeach; ?>
          </div>

          <!-- Nút cuộn Phải -->
          <button type="button" class="sports-nav-arrow sports-nav-next" id="btn-sports-scroll-right" aria-label="Cuộn sang phải" title="Cuộn sang phải">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>

      <!-- 2.2 Chọn Ngày Thi Đấu (Timeline Chips) & Trạng thái & Tìm kiếm -->
      <div class="p-3 bg-slate-50 rounded-bottom-4">
        <!-- Dòng lọc ngày (Date Timeline Pills) nếu có nhiều ngày -->
        <?php if (!empty($uniqueDates) && count($uniqueDates) > 1): ?>
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom overflow-x-auto">
            <span class="text-muted fw-bold small text-nowrap d-flex align-items-center gap-1" style="font-size:11.5px;">
              <span class="material-symbols-outlined fs-6 text-primary">calendar_today</span> Ngày:
            </span>
            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill date-filter-btn active text-nowrap px-3 py-1" data-date="">
              Tất cả ngày
            </button>
            <?php foreach ($uniqueDates as $idx => $dStr): ?>
              <?php
              $ts = strtotime($dStr);
              $dLabel = $ts ? ('Ngày ' . ($idx + 1) . ' (' . date('d/m', $ts) . ')') : $dStr;
              $matchCountInDate = isset($matchesByDate[$dStr]) ? count($matchesByDate[$dStr]) : 0;
              ?>
              <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill date-filter-btn text-nowrap px-3 py-1" data-date="<?php echo $dStr; ?>">
                <?php echo $dLabel; ?>
                <span class="badge bg-secondary text-white rounded-pill px-1 ms-1" style="font-size:9.5px;"><?php echo $matchCountInDate; ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Dòng lọc trạng thái & Ô tìm kiếm nhanh -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <!-- Nhóm trạng thái -->
          <div class="d-flex flex-wrap align-items-center gap-1 status-filter-group">
            <button type="button" class="btn btn-sm rounded-pill status-filter-btn active" data-status="">
              Tất cả (<?php echo count($allMatches); ?>)
            </button>
            <button type="button" class="btn btn-sm rounded-pill status-filter-btn status-btn-live d-inline-flex align-items-center gap-1" data-status="live">
              <span class="pulse-indicator-sm"></span> Trực tiếp (<?php echo (int) $stats['live_matches']; ?>)
            </button>
            <button type="button" class="btn btn-sm rounded-pill status-filter-btn" data-status="completed">
              Đã kết thúc (<?php echo (int) $stats['completed_matches']; ?>)
            </button>
            <button type="button" class="btn btn-sm rounded-pill status-filter-btn" data-status="scheduled">
              Đã xếp lịch (<?php echo (int) $stats['scheduled_matches']; ?>)
            </button>
            <button type="button" class="btn btn-sm rounded-pill status-filter-btn" data-status="upcoming">
              Sắp diễn ra
            </button>
          </div>

          <!-- Ô tìm kiếm tức thì -->
          <div class="search-box position-relative flex-grow-1 flex-md-grow-0" style="min-width: 250px; max-width: 360px;">
            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-6">search</span>
            <input type="text"
                   id="schedule-search-input"
                   class="form-control form-control-sm rounded-pill ps-5 pe-4 py-2 border-slate-200"
                   placeholder="Tìm đội tuyển, VĐV, sân đấu...">
            <button type="button"
                    id="btn-clear-search"
                    class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 p-0 text-muted"
                    style="display:none;" title="Xóa tìm kiếm">
              <span class="material-symbols-outlined fs-6">close</span>
            </button>
          </div>
        </div>
      </div>
    </div>


    <!-- =====================================================================
         2.5 THỂ LỆ THI ĐẤU CÁC NỘI DUNG (thu gọn)
         ===================================================================== -->
    <?php
    // Chỉ lấy các bộ môn có ít nhất một giai đoạn khai báo thể lệ hoặc thể thức
    $sportsWithRules = array();
    foreach ($sportsList as $sp) {
        if (empty($sp['stages'])) {
            continue;
        }
        foreach ($sp['stages'] as $st) {
            if (!empty($st['rules']) || !empty($st['format'])) {
                $sportsWithRules[] = $sp;
                break;
            }
        }
    }

    // Helper $getSportMeta đã được định nghĩa ở đầu file để dùng chung cho toàn bộ trang


    // Helper: phân tích cấu hình thể thức giai đoạn (badge, màu sắc, icon)
    $getStageFormatMeta = function ($format) {
        switch ($format) {
            case 'single_elimination':
                return array(
                    'label' => 'Loại trực tiếp',
                    'icon' => 'military_tech',
                    'bg' => '#fff1f2',
                    'color' => '#be123c',
                    'border' => '#fecdd3'
                );
            case 'round_robin':
                return array(
                    'label' => 'Vòng tròn tính điểm',
                    'icon' => 'sync_alt',
                    'bg' => '#ecfdf5',
                    'color' => '#047857',
                    'border' => '#a7f3d0'
                );
            case 'round_robin_knockout':
                return array(
                    'label' => 'Vòng bảng + loại trực tiếp',
                    'icon' => 'view_timeline',
                    'bg' => '#eff6ff',
                    'color' => '#1d4ed8',
                    'border' => '#bfdbfe'
                );
            case 'playoff':
                return array(
                    'label' => 'Play-off (độc lập)',
                    'icon' => 'bolt',
                    'bg' => '#faf5ff',
                    'color' => '#7e22ce',
                    'border' => '#e9d5ff'
                );
            case 'heat_lane':
            case 'time_trial':
                return array(
                    'label' => 'Tính thành tích',
                    'icon' => 'timer',
                    'bg' => '#f0fdfa',
                    'color' => '#0f766e',
                    'border' => '#99f6e4'
                );
            default:
                $label = SportStages::getFormatLabel($format);
                return array(
                    'label' => $label ?: 'Thể thức chung',
                    'icon' => 'sports',
                    'bg' => '#f8fafc',
                    'color' => '#475569',
                    'border' => '#e2e8f0'
                );
        }
    };

    // Helper: chuẩn hoá và bóc tách nội dung thể lệ để hiển thị khoa học
    $parseRulesContent = function ($text) use ($e) {
        $text = trim((string)$text);
        if ($text === '') {
            return array('spec' => '', 'points' => '', 'detail_html' => '');
        }

        $body = $text;
        // Loại bỏ tiền tố giai đoạn nếu có lặp lại
        if (preg_match('/^(Vòng loại|Vòng bảng|VCK|Chung kết)\s*:\s*/ui', $body, $pm)) {
            $body = substr($body, strlen($pm[0]));
        }

        $spec = '';
        if (preg_match('/^((?:\d+\s*phút\s*\/\s*hiệp|\d+\s*hiệp\s+thắng\s+\d+|1\s*hiệp\s+chạm\s+\d+đ)(?:\s*\([^)]+\))?)([\.;\s]+(.*))?$/u', $body, $sm)) {
            $spec = trim($sm[1]);
            $body = isset($sm[3]) ? trim($sm[3]) : '';
        }

        $points = '';
        if (preg_match('/\b(thắng\s+\d+đ[^\.;\)]*)/ui', $text, $ptm)) {
            $rawPt = trim($ptm[1]);
            $points = preg_replace('/\s*,\s*/u', ' • ', $rawPt);
        }

        $safeHtml = $e($body);
        $terms = array(
            'loại trực tiếp', 'vòng tròn', 'đối đầu → hiệu số séc', 'hiệu số → bàn thắng → đối đầu',
            'đối đầu → hiệu số hiệp', 'hiệu số ván → hiệu số điểm',
            'tranh 1-2-3-4', 'tranh 1-2', 'tranh 3-4', 'bán kết', 'chung kết',
            'Nhất bảng', 'Nhì bảng', 'Nhất mỗi bảng', 'Nhất-Nhì mỗi bảng', 'Nhất 3 bảng'
        );
        foreach ($terms as $t) {
            $safeHtml = preg_replace('/(' . preg_quote($t, '/') . ')/ui', '<strong class="highlight-term">$1</strong>', $safeHtml);
        }

        return array(
            'spec' => $spec,
            'points' => $points,
            'detail_html' => $safeHtml
        );
    };

    // Tập hợp danh sách category phục vụ thanh lọc
    $rulesCategories = array();
    foreach ($sportsWithRules as $sp) {
        $meta = $getSportMeta($sp['name']);
        $catKey = $meta['cat'];
        if (!isset($rulesCategories[$catKey])) {
            $rulesCategories[$catKey] = array(
                'key' => $catKey,
                'name' => $meta['cat_name'],
                'icon' => $meta['cat_icon'],
                'count' => 0
            );
        }
        $rulesCategories[$catKey]['count']++;
    }
    ?>
    <?php if (!empty($sportsWithRules)): ?>
      <section class="mb-4" id="rules-section">
        <details class="rules-accordion bg-white rounded-4 shadow-sm border border-slate-200 overflow-hidden">
          <summary class="d-flex align-items-center justify-content-between gap-3 p-3 p-md-4"
                   style="cursor:pointer; list-style:none;">
            <div class="d-flex align-items-center gap-3">
              <div class="d-flex align-items-center justify-content-center rounded-3 shadow-xs"
                   style="width:42px; height:42px; background:linear-gradient(135deg, #1e3a8a, #3b82f6); color:#ffffff; flex-shrink:0;">
                <span class="material-symbols-outlined fs-5">gavel</span>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h2 class="fw-bold text-dark fs-5 mb-0">Thể lệ &amp; cách thức thi đấu từng nội dung</h2>
                  <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small fw-semibold">
                    <?php echo count($sportsWithRules); ?> nội dung
                  </span>
                </div>
                <div class="text-muted small mt-0.5">
                  Quy định số hiệp, thời gian thi đấu, cách tính điểm và cơ chế xếp hạng tranh giải chi tiết
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5 rules-toggle-btn shadow-2xs" style="font-size:12.5px; font-weight:600; pointer-events:none;">
                <span class="rules-text-closed">Xem thể thức</span>
                <span class="rules-text-open">Thu gọn</span>
                <span class="material-symbols-outlined rules-chevron" style="font-size:16px;">expand_more</span>
              </span>
            </div>
          </summary>

          <div class="p-3 p-md-4 pt-3">
            <!-- Toolbar Lọc danh mục & Tìm kiếm tức thì -->
            <div class="rules-filter-toolbar rounded-3 p-2.5 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div class="d-flex flex-wrap align-items-center gap-1.5" id="rules-cat-filters">
                <button type="button" class="btn rules-cat-btn active" data-cat="all">
                  Tất cả <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"><?php echo count($sportsWithRules); ?></span>
                </button>
                <?php foreach ($rulesCategories as $c): ?>
                  <button type="button" class="btn rules-cat-btn" data-cat="<?php echo $e($c['key']); ?>">
                    <?php echo $e($c['icon'] . ' ' . $c['name']); ?>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"><?php echo (int)$c['count']; ?></span>
                  </button>
                <?php endforeach; ?>
              </div>

              <div class="position-relative" style="min-width: 220px; max-width: 320px; flex: 1;">
                <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-2.5 text-muted" style="font-size:17px;">search</span>
                <input type="text" id="rules-search-input" class="form-control form-control-sm ps-5 pe-4 rounded-pill border-slate-300" placeholder="Tìm theo tên môn hoặc quy chế..." />
                <button type="button" id="rules-search-clear" class="btn btn-link btn-sm position-absolute top-50 end-0 translate-middle-y me-1 text-muted p-0" style="display:none; text-decoration:none;">
                  <span class="material-symbols-outlined fs-6">close</span>
                </button>
              </div>
            </div>

            <!-- Grid Thẻ Thể Lệ Từng Môn -->
            <div class="row g-3" id="rules-cards-grid">
              <?php foreach ($sportsWithRules as $sp): ?>
                <?php
                $sMeta = $getSportMeta($sp['name']);
                $validStages = array();
                if (!empty($sp['stages'])) {
                    foreach ($sp['stages'] as $st) {
                        if (!empty($st['rules']) || !empty($st['format'])) {
                            $validStages[] = $st;
                        }
                    }
                }
                $numStages = count($validStages);
                $isMultiStage = ($numStages > 1);

                // Từ khoá tìm kiếm
                $searchKeywords = mb_strtolower($sp['name'] . ' ' . $sMeta['cat_name'], 'UTF-8');
                foreach ($validStages as $st) {
                    $searchKeywords .= ' ' . mb_strtolower((isset($st['name']) ? $st['name'] : '') . ' ' . (isset($st['rules']) ? $st['rules'] : '') . ' ' . (isset($st['location']) ? $st['location'] : ''), 'UTF-8');
                }
                ?>
                <div class="col-12 col-lg-6 sport-rule-item" data-cat="<?php echo $e($sMeta['cat']); ?>" data-search="<?php echo $e($searchKeywords); ?>">
                  <div class="sport-rule-card h-100 rounded-4 shadow-sm d-flex flex-column overflow-hidden bg-white">
                    <!-- Thanh nhấn màu sắc theo môn -->
                    <div class="sport-card-accent-bar" style="background:<?php echo $e($sMeta['color']); ?>;"></div>

                    <!-- Header Thẻ Môn -->
                    <div class="p-3 px-md-4 pt-3 pb-2.5 d-flex align-items-center justify-content-between border-bottom border-slate-100">
                      <div class="d-flex align-items-center gap-2.5">
                        <div class="sport-icon-squircle" style="background:<?php echo $e($sMeta['bg_light']); ?>; color:<?php echo $e($sMeta['color']); ?>;">
                          <span class="material-symbols-outlined fs-5"><?php echo $e($sMeta['icon']); ?></span>
                        </div>
                        <div>
                          <h3 class="fs-6 fw-bold text-dark mb-0 lh-sm"><?php echo $e($sp['name']); ?></h3>
                          <span class="small text-muted" style="font-size:11.5px;"><?php echo $e($sMeta['type_label']); ?></span>
                        </div>
                      </div>
                      <span class="sport-card-tag rounded-pill px-2.5 py-1 small fw-semibold">
                        <?php echo $isMultiStage ? ('Lộ trình ' . $numStages . ' chặng') : '1 giai đoạn'; ?>
                      </span>
                    </div>

                    <!-- Thân Thẻ: Hiển thị các giai đoạn thi đấu -->
                    <div class="p-3 px-md-4 py-3 flex-grow-1 d-flex flex-column justify-content-center">
                      <?php if (!$isMultiStage): ?>
                        <!-- MÔN 1 GIAI ĐOẠN -->
                        <?php foreach ($validStages as $st): ?>
                          <?php
                          $fMeta = $getStageFormatMeta($st['format']);
                          $parsed = $parseRulesContent($st['rules']);
                          ?>
                          <div class="stage-card-box">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                              <span class="fw-bold text-dark small"><?php echo $e($st['name']); ?></span>
                              <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="format-badge-pill" style="background:<?php echo $e($fMeta['bg']); ?>; color:<?php echo $e($fMeta['color']); ?>; border:1px solid <?php echo $e($fMeta['border']); ?>;">
                                  <span class="material-symbols-outlined" style="font-size:14px;"><?php echo $e($fMeta['icon']); ?></span>
                                  <?php echo $e($fMeta['label']); ?>
                                </span>
                                <?php if (!empty($st['location'])): ?>
                                  <span class="stage-location-pill">
                                    📍 <?php echo $e($st['location']); ?>
                                  </span>
                                <?php endif; ?>
                              </div>
                            </div>

                            <?php if (!empty($parsed['spec']) || !empty($parsed['points'])): ?>
                              <div class="mb-2 d-flex flex-wrap gap-1.5">
                                <?php if (!empty($parsed['spec'])): ?>
                                  <span class="rule-spec-chip">
                                    <span class="material-symbols-outlined text-primary" style="font-size:14px;">timer</span>
                                    <span>Quy cách: <strong><?php echo $e($parsed['spec']); ?></strong></span>
                                  </span>
                                <?php endif; ?>
                                <?php if (!empty($parsed['points'])): ?>
                                  <span class="rule-points-chip">
                                    <span>Điểm: <strong><?php echo $e($parsed['points']); ?></strong></span>
                                  </span>
                                <?php endif; ?>
                              </div>
                            <?php endif; ?>

                            <?php if (!empty($parsed['detail_html'])): ?>
                              <div class="rule-detail-text">
                                <?php echo $parsed['detail_html']; ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>

                      <?php else: ?>
                        <!-- MÔN NHIỀU GIAI ĐOẠN (LỘ TRÌNH ROADMAP) -->
                        <div class="stage-timeline-track">
                          <?php foreach ($validStages as $idx => $st): ?>
                            <?php
                            $stepNum = $idx + 1;
                            $isLast = ($stepNum === $numStages);
                            $fMeta = $getStageFormatMeta($st['format']);
                            $parsed = $parseRulesContent($st['rules']);
                            ?>
                            <div class="stage-timeline-step">
                              <div class="stage-step-indicator <?php echo $isLast ? 'final-step' : ''; ?>">
                                <?php echo $isLast ? '🏆' : $stepNum; ?>
                              </div>
                              <div class="stage-card-box <?php echo $isLast ? 'stage-card-final' : ''; ?>">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-1.5 mb-1.5">
                                  <span class="fw-bold text-dark small"><?php echo $e($st['name']); ?></span>
                                  <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="format-badge-pill" style="background:<?php echo $e($fMeta['bg']); ?>; color:<?php echo $e($fMeta['color']); ?>; border:1px solid <?php echo $e($fMeta['border']); ?>;">
                                      <span class="material-symbols-outlined" style="font-size:13px;"><?php echo $e($fMeta['icon']); ?></span>
                                      <?php echo $e($fMeta['label']); ?>
                                    </span>
                                    <?php if (!empty($st['location'])): ?>
                                      <span class="stage-location-pill">
                                        📍 <?php echo $e($st['location']); ?>
                                      </span>
                                    <?php endif; ?>
                                  </div>
                                </div>

                                <?php if (!empty($parsed['spec']) || !empty($parsed['points'])): ?>
                                  <div class="mb-1.5 d-flex flex-wrap gap-1.5">
                                    <?php if (!empty($parsed['spec'])): ?>
                                      <span class="rule-spec-chip">
                                        <span class="material-symbols-outlined text-primary" style="font-size:14px;">timer</span>
                                        <span><?php echo $e($parsed['spec']); ?></span>
                                      </span>
                                    <?php endif; ?>
                                    <?php if (!empty($parsed['points'])): ?>
                                      <span class="rule-points-chip">
                                        <span><?php echo $e($parsed['points']); ?></span>
                                      </span>
                                    <?php endif; ?>
                                  </div>
                                <?php endif; ?>

                                <?php if (!empty($parsed['detail_html'])): ?>
                                  <div class="rule-detail-text">
                                    <?php echo $parsed['detail_html']; ?>
                                  </div>
                                <?php endif; ?>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Trạng thái trống khi tìm kiếm không thấy -->
            <div id="rules-empty-state" class="text-center py-5" style="display:none;">
              <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-slate-100 text-slate-400 mb-3" style="width:52px; height:52px;">
                <span class="material-symbols-outlined fs-2">search_off</span>
              </div>
              <h4 class="fs-6 fw-bold text-dark mb-1">Không tìm thấy nội dung thi đấu phù hợp</h4>
              <p class="small text-muted mb-3">Vui lòng thử tìm với từ khóa khác hoặc xóa bộ lọc danh mục.</p>
              <button type="button" id="rules-reset-filter" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                Xem tất cả nội dung
              </button>
            </div>

          </div>
        </details>
      </section>
    <?php endif; ?>


    <!-- =====================================================================
         2.6 SƠ ĐỒ BƠI (LƯỢT / LÀN) & KẾT QUẢ
         ===================================================================== -->
    <?php if (!empty($swimBySport)): ?>
      <section class="mb-4" id="swim-section">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="material-symbols-outlined text-info">pool</span>
          <h2 class="fw-bold text-dark fs-5 mb-0">Sơ đồ bơi — Lượt &amp; Làn</h2>
        </div>
        <div class="row g-3">
          <?php foreach ($swimBySport as $sportId => $heats): ?>
            <?php
            $sInfo = isset($sportInfoById[$sportId]) ? $sportInfoById[$sportId] : array('name' => 'Nội dung bơi', 'icon' => 'pool');
            if ($selectedSportId && $sportId != $selectedSportId) {
                continue;
            }
            ?>
            <div class="col-12 col-xl-6" data-sport-id="<?php echo (int) $sportId; ?>">
              <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-info-subtle d-flex align-items-center gap-2 border-0">
                  <span class="material-symbols-outlined text-info"><?php echo $e(isset($sInfo['icon']) ? $sInfo['icon'] : 'pool'); ?></span>
                  <span class="fw-bold text-dark"><?php echo $e($sInfo['name']); ?></span>
                </div>
                <div class="card-body p-3">
                  <?php foreach ($heats as $heat): ?>
                    <div class="mb-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-info text-white rounded-pill px-2 py-1">Lượt <?php echo (int) $heat['heat_no']; ?></span>
                        <span class="small text-muted"><?php echo $e($heat['stage_name']); ?></span>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center" style="width:52px;">Làn</th>
                              <th>VĐV</th>
                              <th style="width:110px;">Thành tích</th>
                              <th class="text-center" style="width:56px;">Hạng</th>
                            </tr>
                          </thead>
                          <tbody>
                          <?php foreach ($heat['lanes'] as $lane): ?>
                            <?php $rank = isset($lane['rank']) ? $lane['rank'] : null; ?>
                            <tr class="<?php echo ($rank && $rank <= 3) ? 'table-warning' : ''; ?>">
                              <td class="text-center fw-bold"><?php echo (int) $lane['lane_no']; ?></td>
                              <td><?php echo $e($lane['team_name']); ?></td>
                              <td class="font-monospace"><?php echo $e($fmtSwimTime($lane['result_ms'])); ?></td>
                              <td class="text-center">
                                <?php
                                $medals = array(1 => 'Nhất', 2 => 'Nhì', 3 => 'Ba');
                                if ($rank && isset($medals[$rank])) {
                                    echo '<span class="badge bg-warning text-dark">' . $medals[$rank] . '</span>';
                                } elseif ($rank) {
                                    echo '<span class="badge bg-light text-dark">' . (int) $rank . '</span>';
                                } else {
                                    echo '—';
                                }
                                ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>


    <!-- =====================================================================
         3. KHỐI TRẬN ĐẤU ĐANG DIỄN RA (LIVE MATCHES SHOWCASE)
         ===================================================================== -->
    <?php if (!empty($tournament['liveMatches'])): ?>
      <section class="mb-4 live-matches-section" id="live-section">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-2 shadow-sm animate-pulse-gentle">
              <span class="spinner-grow spinner-grow-sm" style="width:7px;height:7px;"></span> SÂN ĐẤU ĐANG NÓNG (LIVE)
            </span>
            <h2 class="fw-bold text-dark fs-5 mb-0">Các trận đang thi đấu sôi nổi</h2>
          </div>
          <span class="text-muted small">Cập nhật điểm số tự động</span>
        </div>

        <div class="row g-3">
          <?php foreach ($tournament['liveMatches'] as $m): ?>
            <?php
            $ta = $m['team_a']['name'];
            $tb = $m['team_b']['name'];
            $sa = $m['team_a']['score'] !== null ? $m['team_a']['score'] : '0';
            $sb = $m['team_b']['score'] !== null ? $m['team_b']['score'] : '0';
            ?>
            <div class="col-12 col-md-6 col-xl-6">
              <div class="card h-100 border-danger border-2 rounded-4 shadow-sm bg-white overflow-hidden live-card-highlight">
                <!-- Live Header -->
                <div class="p-3 bg-gradient-danger text-white d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined fs-5"><?php echo $e($m['sport_icon']); ?></span>
                    <span class="fw-bold"><?php echo $e($m['sport_name']); ?></span>
                    <span class="opacity-75">• <?php echo $e($m['round']); ?></span>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($m['location'])): ?>
                      <span class="badge bg-white/20 text-white rounded-pill px-2 py-1 small backdrop-blur d-none d-sm-inline">
                        📍 <?php echo $e($m['location']); ?>
                      </span>
                    <?php endif; ?>
                    <span class="badge bg-white text-danger fw-black rounded-pill px-2 py-1 small">LIVE</span>
                  </div>
                </div>

                <!-- Live Scoreboard (Bảng tỷ số thể thao đối đầu) -->
                <div class="p-3 p-md-4">
                  <div class="row align-items-center text-center g-2">
                    <!-- Đội A -->
                    <div class="col-4 text-center">
                      <div class="avatar-squircle mx-auto mb-2 text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                           style="width:52px;height:52px;font-size:16px;background:linear-gradient(135deg,#1e3a8a,#3b82f6);">
                        <?php echo $e($initials($ta)); ?>
                      </div>
                      <div class="fw-bold text-dark small lh-sm text-truncate px-1" title="<?php echo $e($ta); ?>">
                        <?php echo $e($ta); ?>
                      </div>
                    </div>

                    <!-- Tỷ số ở giữa -->
                    <div class="col-4">
                      <div class="d-flex align-items-center justify-content-center gap-2">
                        <span class="live-digit display-5 fw-black text-danger font-monospace"><?php echo $e($sa); ?></span>
                        <span class="text-muted fs-4 fw-light">-</span>
                        <span class="live-digit display-5 fw-black text-danger font-monospace"><?php echo $e($sb); ?></span>
                      </div>
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0 small mt-1">
                        Đang tranh tài
                      </span>
                    </div>

                    <!-- Đội B -->
                    <div class="col-4 text-center">
                      <div class="avatar-squircle mx-auto mb-2 text-white fw-bold d-flex align-items-center justify-content-center shadow-sm"
                           style="width:52px;height:52px;font-size:16px;background:linear-gradient(135deg,#0d9488,#14b8a6);">
                        <?php echo $e($initials($tb)); ?>
                      </div>
                      <div class="fw-bold text-dark small lh-sm text-truncate px-1" title="<?php echo $e($tb); ?>">
                        <?php echo $e($tb); ?>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Live Footer -->
                <div class="px-3 py-2 bg-slate-50 border-top text-muted small d-flex align-items-center justify-content-between" style="font-size:12px;">
                  <span class="d-flex align-items-center gap-1">
                    <span class="material-symbols-outlined fs-6 text-danger">schedule</span>
                    <span><?php echo !empty($m['match_time']) ? $e($m['match_time']) : 'Trực tiếp hôm nay'; ?></span>
                  </span>
                  <?php if (!empty($m['location'])): ?>
                    <span class="d-flex align-items-center gap-1">
                      <span class="material-symbols-outlined fs-6 text-danger">pin_drop</span>
                      <strong class="text-dark"><?php echo $e($m['location']); ?></strong>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>


    <!-- =====================================================================
         4. KHỐI DANH SÁCH TOÀN BỘ TRẬN ĐẤU (THEO NGÀY TIMELINE HOẶC TẤT CẢ)
         ===================================================================== -->
    <section class="mb-5" id="matches-section">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
            <span class="material-symbols-outlined fs-5">event_note</span>
          </div>
          <h2 class="fw-bold text-dark fs-5 mb-0" id="match-section-title">Danh sách các trận thi đấu</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small" id="match-counter-text">
            Hiển thị <strong id="visible-matches-count" class="text-primary"><?php echo count($allMatches); ?></strong> / <?php echo count($allMatches); ?> trận
          </span>
        </div>
      </div>

      <?php if (empty($allMatches)): ?>
        <!-- EMPTY STATE THÂN THIỆN: Khi chưa có lịch thi đấu -->
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white empty-state-box">
          <div class="empty-icon-circle mx-auto mb-3 rounded-circle bg-amber-50 text-warning d-flex align-items-center justify-content-center" style="width:80px;height:80px;">
            <span class="material-symbols-outlined display-4">event_available</span>
          </div>
          <h4 class="fw-bold text-dark mb-2">Lịch thi đấu đang được Ban Trọng tài cập nhật</h4>
          <p class="text-secondary small mb-4 max-w-lg mx-auto lh-base">
            Ban Tổ chức đang tiến hành bốc thăm chia bảng và xếp lịch thi đấu các bộ môn chính thức. Quý Đại biểu và Vận động viên vui lòng đón xem hoặc bấm làm mới bên dưới.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="location.reload();">
              <span class="material-symbols-outlined fs-6 align-middle me-1">refresh</span> Tải lại trang
            </button>
            <a href="<?php echo $e($base); ?>/agenda-tong-the" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
              Xem Lịch trình Đại hội
            </a>
          </div>
        </div>
      <?php else: ?>

        <!-- Container danh sách trận đấu (Hỗ trợ Gom nhóm theo ngày hoặc hiển thị phẳng) -->
        <div id="matches-grid-container" class="view-cards-mode">
          <?php
          // Hiển thị từng nhóm ngày
          foreach ($matchesByDate as $dateKey => $dateMatches):
              $isNoDate = ($dateKey === '__no_date__');
              $dateLabel = $isNoDate ? 'Các trận đấu chưa ấn định ngày' : $formatFriendlyDate($dateKey);
          ?>
            <?php
            $dayIndex = (!$isNoDate && in_array($dateKey, $uniqueDates)) ? (array_search($dateKey, $uniqueDates) + 1) : null;
            $dayTag = '';
            if ($dayIndex !== null) {
                if ($dayIndex === 1) {
                    $dayTag = 'NGÀY TRANH TÀI 1';
                } elseif ($dayIndex === count($uniqueDates)) {
                    $dayTag = 'NGÀY ' . $dayIndex . ' • CHUNG KẾT';
                } else {
                    $dayTag = 'NGÀY TRANH TÀI ' . $dayIndex;
                }
            } elseif ($isNoDate) {
                $dayTag = 'CHƯA XẾP NGÀY';
            }
            ?>
            <div class="date-group-section mb-4" data-group-date="<?php echo $dateKey; ?>">
              <!-- Tiêu đề phân nhóm Ngày thi đấu nổi bật & chuyên nghiệp -->
              <div class="date-group-header p-3 px-md-4 mb-3 rounded-4 position-relative overflow-hidden shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="date-group-accent-line"></div>
                <div class="date-group-dots-pattern"></div>

                <div class="d-flex align-items-center gap-3 position-relative z-1">
                  <div class="date-group-icon-squircle">
                    <span class="material-symbols-outlined fs-5">calendar_month</span>
                  </div>
                  <div>
                    <?php if (!empty($dayTag)): ?>
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge date-group-day-badge">
                          <span class="date-group-pulse-dot"></span>
                          <?php echo $e($dayTag); ?>
                        </span>
                        <span class="date-group-sublabel small">Đại hội 2026</span>
                      </div>
                    <?php endif; ?>
                    <h3 class="date-group-title mb-0">
                      <?php echo $e($dateLabel); ?>
                    </h3>
                  </div>
                </div>

                <div class="position-relative z-1">
                  <span class="date-group-count-pill">
                    <span class="material-symbols-outlined text-primary" style="font-size:16px;">sports_score</span>
                    <span class="date-group-match-count"><?php echo count($dateMatches); ?> trận đấu</span>
                  </span>
                </div>
              </div>

              <!-- Gom nhóm các trận trong ngày theo bộ môn (nội dung) để tiện theo dõi -->
              <?php
              $matchesBySport = array();
              $sportOrder = array();
              foreach ($dateMatches as $m) {
                  $sid = isset($m['sport_id']) ? $m['sport_id'] : 0;
                  if (!isset($matchesBySport[$sid])) {
                      $matchesBySport[$sid] = array();
                      $sportOrder[] = $sid;
                  }
                  $matchesBySport[$sid][] = $m;
              }
              ?>
              <?php foreach ($sportOrder as $sid): ?>
                <?php
                $sportMatches = $matchesBySport[$sid];
                $firstM = $sportMatches[0];
                $sportHeaderName = $firstM['sport_name'];
                $sportHeaderIcon = !empty($firstM['sport_icon']) ? $firstM['sport_icon'] : 'emoji_events';
                $sMetaHeader = $getSportMeta($sportHeaderName);
                ?>
                <div class="sport-subgroup" data-sport-id="<?php echo $sid; ?>">
                  <!-- Thanh tiêu đề nội dung (bộ môn) -->
                  <div class="sport-subgroup-header" style="--sport-accent:<?php echo $e($sMetaHeader['color']); ?>; --sport-accent-bg:<?php echo $e($sMetaHeader['bg_light']); ?>;" role="button" tabindex="0" aria-expanded="true" title="Nhấn để thu gọn / mở rộng nội dung này">
                    <span class="sport-subgroup-icon">
                      <span class="material-symbols-outlined"><?php echo $e($sportHeaderIcon); ?></span>
                    </span>
                    <span class="sport-subgroup-name"><?php echo $e($sportHeaderName); ?></span>
                    <span class="sport-subgroup-count"><?php echo count($sportMatches); ?> trận</span>
                    <button type="button" class="sport-subgroup-toggle" aria-label="Thu gọn / mở rộng">
                      <span class="material-symbols-outlined">expand_less</span>
                    </button>
                  </div>

                  <!-- Lưới các trận đấu của nội dung này -->
                  <div class="row g-3 date-matches-row">
                    <?php foreach ($sportMatches as $m): ?>
                  <?php
                  $ta = $m['team_a']['name'];
                  $tb = $m['team_b']['name'];
                  $sa = $m['team_a']['score'];
                  $sb = $m['team_b']['score'];
                  $isWinnerA = !empty($m['team_a']['is_winner']);
                  $isWinnerB = !empty($m['team_b']['is_winner']);
                  $hasScore = ($sa !== null && $sb !== null);

                  $status = $m['status'];
                  $statusText = $m['status_text'];
                  $statusClass = $m['status_class'];

                  // Badge trạng thái thân thiện
                  $statusBadgeBg = 'bg-slate-100 text-slate-700';
                  if ($status === 'live') $statusBadgeBg = 'bg-danger text-white';
                  elseif ($status === 'completed') $statusBadgeBg = 'bg-emerald-50 text-emerald border border-emerald-subtle';
                  elseif ($status === 'scheduled') $statusBadgeBg = 'bg-blue-50 text-primary border border-blue-subtle';
                  elseif ($status === 'upcoming') $statusBadgeBg = 'bg-amber-50 text-amber border border-amber-subtle';

                  $searchText = mb_strtolower($m['sport_name'] . ' ' . $m['round'] . ' ' . $ta . ' ' . $tb . ' ' . $m['location'] . ' ' . $m['stage_name'], 'UTF-8');
                  ?>
                  <div class="col-12 col-md-6 col-xl-4 match-item-wrapper"
                       data-sport-id="<?php echo $m['sport_id']; ?>"
                       data-status="<?php echo $status; ?>"
                       data-date="<?php echo $m['match_date']; ?>"
                       data-search="<?php echo $e($searchText); ?>">

                    <!-- THẺ TRẬN ĐẤU (MATCH CARD - MODERN SPORTS DESIGN) -->
                    <div class="match-card card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden transition-all">
                      <!-- 1. Header thẻ: Môn thi + Vòng + Trạng thái -->
                      <div class="p-3 pb-2 border-bottom bg-slate-50/80 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                          <div class="sport-icon-circle rounded-circle bg-teal-50 text-teal-600 border border-teal-200 d-flex align-items-center justify-content-center flex-shrink-0" style="width:30px;height:30px;">
                            <span class="material-symbols-outlined fs-6"><?php echo $e($m['sport_icon']); ?></span>
                          </div>
                          <div class="text-truncate">
                            <span class="fw-bold text-dark small d-block lh-1 text-truncate"><?php echo $e($m['sport_name']); ?></span>
                            <span class="text-muted" style="font-size:11px;"><?php echo $e($m['round']); ?></span>
                          </div>
                        </div>

                        <span class="badge <?php echo $statusBadgeBg; ?> rounded-pill px-2 py-1 fw-bold flex-shrink-0" style="font-size:10.5px;">
                          <?php if ($status === 'live'): ?><span class="spinner-grow spinner-grow-sm me-1" style="width:6px;height:6px;"></span><?php endif; ?>
                          <?php echo $e($statusText); ?>
                        </span>
                      </div>

                      <!-- 2. Body thẻ: Đối đầu Đội A vs Đội B (Match Arena - Thẳng hàng 100%, không lồi lõm) -->
                      <div class="px-2 pt-2 pb-1">
                        <div class="match-arena-box">
                          <!-- Hàng 1: Avatar Đội A - VS / Tỷ số - Avatar Đội B (Cố định cùng hàng, Icon luôn thẳng tắp) -->
                          <div class="faceoff-row-avatars">
                            <div class="avatar-cell">
                              <div class="avatar-squircle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs <?php echo $isWinnerA ? 'ring-winner' : ''; ?>"
                                   style="width:46px;height:46px;font-size:14px;background:linear-gradient(135deg,#1d4ed8 0%,#3b82f6 100%);border:2px solid #ffffff;">
                                <?php echo $e($initials($ta)); ?>
                              </div>
                            </div>

                            <div class="center-emblem-cell">
                              <?php if ($hasScore || $status === 'live'): ?>
                                <div class="score-pill-emblem d-flex align-items-center justify-content-center gap-1 px-2.5 py-1 rounded-pill text-white font-monospace shadow-xs">
                                  <span class="fs-5 fw-black <?php echo $isWinnerA ? 'text-success' : 'text-white'; ?>"><?php echo (int) $sa; ?></span>
                                  <span class="text-white-50 fs-6">-</span>
                                  <span class="fs-5 fw-black <?php echo $isWinnerB ? 'text-success' : 'text-white'; ?>"><?php echo (int) $sb; ?></span>
                                </div>
                              <?php else: ?>
                                <div class="vs-circle-emblem">VS</div>
                              <?php endif; ?>
                            </div>

                            <div class="avatar-cell">
                              <div class="avatar-squircle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs <?php echo $isWinnerB ? 'ring-winner' : ''; ?>"
                                   style="width:46px;height:46px;font-size:14px;background:linear-gradient(135deg,#0f172a 0%,#334155 100%);border:2px solid #ffffff;">
                                <?php echo $e($initials($tb)); ?>
                              </div>
                            </div>
                          </div>

                          <!-- Hàng 2: Tên Đội A - Giờ đấu / FT - Tên Đội B (Chiều cao 36px cố định, Tên đội luôn thẳng hàng) -->
                          <div class="faceoff-row-names mt-2">
                            <div class="team-name-cell <?php echo $isWinnerA ? 'text-success' : 'text-dark'; ?>" title="<?php echo $e($ta); ?>">
                              <?php echo $e($ta); ?>
                            </div>

                            <div class="center-time-cell">
                              <?php if (!empty($m['match_hour']) && !$hasScore): ?>
                                <span class="match-time-chip"><?php echo $e($m['match_hour']); ?></span>
                              <?php elseif ($status === 'live'): ?>
                                <span class="badge bg-danger text-white rounded-pill px-2 py-0.5" style="font-size:10px;">LIVE</span>
                              <?php elseif ($isWinnerA || $isWinnerB): ?>
                                <span class="text-muted fw-bold" style="font-size:10px;">FT</span>
                              <?php else: ?>
                                <span class="text-muted small">—</span>
                              <?php endif; ?>
                            </div>

                            <div class="team-name-cell <?php echo $isWinnerB ? 'text-success' : 'text-dark'; ?>" title="<?php echo $e($tb); ?>">
                              <?php echo $e($tb); ?>
                            </div>
                          </div>

                          <!-- Hàng 3: Huy hiệu chiến thắng nếu có (Cố định, không làm lệch hàng trên) -->
                          <?php if ($isWinnerA || $isWinnerB): ?>
                            <div class="faceoff-row-winners mt-1">
                              <div class="text-center">
                                <?php if ($isWinnerA): ?>
                                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0" style="font-size:9.5px;">🏆 Thắng</span>
                                <?php endif; ?>
                              </div>
                              <div></div>
                              <div class="text-center">
                                <?php if ($isWinnerB): ?>
                                  <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0" style="font-size:9.5px;">🏆 Thắng</span>
                                <?php endif; ?>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>

                      <!-- 3. Footer thẻ: Địa điểm sân thi đấu (giờ đã hiển thị ở phần đối đầu) -->
                      <div class="px-3 py-2 border-top bg-slate-50/60 text-muted d-flex align-items-center gap-2" style="font-size:12.5px;">
                        <?php if ($status === 'live'): ?>
                          <span class="d-flex align-items-center gap-1 flex-shrink-0">
                            <span class="live-dot-blink"></span>
                            <span class="fw-bold text-danger live-blink-text">LIVE</span>
                          </span>
                        <?php endif; ?>

                        <?php if (!empty($m['location'])): ?>
                          <span class="d-flex align-items-center gap-1 flex-grow-1 text-truncate" title="<?php echo $e($m['location']); ?>">
                            <span class="material-symbols-outlined text-danger flex-shrink-0" style="font-size:19px;">pin_drop</span>
                            <span class="fw-semibold text-dark text-truncate" style="font-size:13px;"><?php echo $e($m['location']); ?></span>
                          </span>
                        <?php else: ?>
                          <span class="d-flex align-items-center gap-1 text-muted flex-grow-1" style="font-size:12.5px;">
                            <span class="material-symbols-outlined flex-shrink-0" style="font-size:19px;">pin_drop</span>
                            <span>Chờ cập nhật địa điểm</span>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <!-- HÀNG DANH SÁCH GỌN (COMPACT ROW - HIỂN THỊ KHI CHỌN VIEW LIST) -->
                    <div class="match-compact-row card border-0 rounded-3 shadow-2xs bg-white p-2 px-3 mb-2 transition-all" style="display:none;">
                      <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-2 overflow-hidden" style="min-width: 140px;">
                          <span class="material-symbols-outlined fs-5 text-primary flex-shrink-0"><?php echo $e($m['sport_icon']); ?></span>
                          <div class="text-truncate">
                            <strong class="small text-dark d-block lh-1 text-truncate"><?php echo $e($m['sport_name']); ?></strong>
                            <span class="text-muted" style="font-size:10.5px;"><?php echo $e($m['round']); ?></span>
                          </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-center gap-2 flex-grow-1 px-2" style="min-width: 220px;">
                          <span class="small fw-bold text-end text-truncate <?php echo $isWinnerA ? 'text-success' : 'text-dark'; ?>" style="max-width:210px;" title="<?php echo $e($ta); ?>"><?php echo $e($ta); ?></span>
                          <span class="badge rounded-pill <?php echo $hasScore ? 'bg-dark text-white' : 'bg-light text-dark border'; ?> px-2 py-1 font-monospace fw-bold">
                            <?php echo $hasScore ? ($sa . ' - ' . $sb) : 'vs'; ?>
                          </span>
                          <span class="small fw-bold text-start text-truncate <?php echo $isWinnerB ? 'text-success' : 'text-dark'; ?>" style="max-width:210px;" title="<?php echo $e($tb); ?>"><?php echo $e($tb); ?></span>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                          <span class="badge <?php echo $statusBadgeBg; ?> rounded-pill px-2 py-0.5 small"><?php echo $e($statusText); ?></span>
                          <?php if (!empty($m['location'])): ?>
                            <span class="text-muted small d-none d-lg-inline" style="font-size:11px;">📍 <?php echo $e($m['location']); ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>

                  </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Thông báo khi lọc không tìm thấy kết quả -->
        <div id="no-filter-match-alert" class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white mt-3" style="display:none;">
          <div class="empty-icon-circle mx-auto mb-2 rounded-circle bg-slate-100 text-muted d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
            <span class="material-symbols-outlined fs-2 text-secondary">search_off</span>
          </div>
          <h5 class="fw-bold text-dark mb-1">Không tìm thấy trận đấu phù hợp</h5>
          <p class="text-secondary small mb-3">Vui lòng thử đổi từ khóa tìm kiếm hoặc bấm nút bên dưới để đặt lại bộ lọc.</p>
          <div>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold" id="btn-reset-filters">
              <span class="material-symbols-outlined fs-6 align-middle me-1">refresh</span> Đặt lại bộ lọc
            </button>
          </div>
        </div>

      <?php endif; ?>
    </section>


    <!-- =====================================================================
         5. KHỐI BẢNG XẾP HẠNG CÁC BẢNG ĐẤU VÒNG TRÒN (GROUP STANDINGS)
         ===================================================================== -->
    <?php if (!empty($standingsBySport)): ?>
      <section class="mb-5" id="standings-section">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-emerald-subtle text-success d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
              <span class="material-symbols-outlined fs-5">leaderboard</span>
            </div>
            <div>
              <h2 class="fw-bold text-dark fs-5 mb-0">Bảng xếp hạng các bảng đấu</h2>
              <span class="text-muted small" style="font-size:11.5px;">Các nội dung thi đấu theo thể thức vòng tròn tính điểm</span>
            </div>
          </div>
          <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small fw-bold">
            🔹 Top 2 đội dẫn đầu mỗi bảng vào vòng Bán kết
          </span>
        </div>

        <?php foreach ($standingsBySport as $sId => $groups): ?>
          <?php
          $spName = 'Nội dung #' . $sId;
          $spIcon = 'sports';
          foreach ($sportsList as $sp) {
              if ($sp['id'] == $sId) {
                  $spName = $sp['name'];
                  $spIcon = $sp['icon'];
                  break;
              }
          }
          ?>
          <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white overflow-hidden standings-card">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary fs-5"><?php echo $e($spIcon); ?></span>
                <h5 class="mb-0 fw-bold text-dark fs-6"><?php echo $e($spName); ?></h5>
              </div>
              <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">Thể thức vòng tròn</span>
            </div>

            <div class="card-body p-3 p-md-4">
              <div class="row g-3">
                <?php foreach ($groups as $label => $rows): ?>
                  <div class="<?php echo count($groups) === 1 ? 'col-lg-10 mx-auto' : 'col-lg-6'; ?>">
                    <div class="border rounded-4 p-3 bg-white h-100 shadow-2xs group-table-container">
                      <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <span class="fw-bold text-dark fs-6 d-flex align-items-center gap-1">
                          <span class="material-symbols-outlined fs-5 text-warning">flag</span>
                          Bảng <?php echo $e($label); ?>
                        </span>
                        <span class="badge bg-light text-secondary border rounded-pill"><?php echo count($rows); ?> đội tuyển</span>
                      </div>

                      <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 text-center" style="font-size:12.5px;">
                          <thead>
                            <tr class="table-light text-muted" style="font-size:11px;">
                              <th style="width:36px;">#</th>
                              <th class="text-start">Đội tuyển / VĐV</th>
                              <th title="Số trận">Tr</th>
                              <th title="Thắng" class="text-success">T</th>
                              <th title="Hòa">H</th>
                              <th title="Bại" class="text-danger">B</th>
                              <th title="Hiệu số">Hiệu số</th>
                              <th title="Điểm số" class="text-primary fw-bold">Điểm</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($rows as $r): ?>
                              <?php
                              $rank = (int)$r['rank'];
                              $isQualify = ($rank <= 2);
                              $badgeCls = ($rank === 1) ? 'bg-warning text-dark fw-bold' : (($rank === 2) ? 'bg-secondary text-white fw-bold' : 'bg-light text-muted border');
                              ?>
                              <tr class="<?php echo $isQualify ? 'row-qualify' : ''; ?>">
                                <td>
                                  <span class="badge <?php echo $badgeCls; ?> rounded-circle d-inline-flex align-items-center justify-content-center" style="width:22px;height:22px;">
                                    <?php echo $rank; ?>
                                  </span>
                                </td>
                                <td class="text-start fw-bold text-dark">
                                  <div class="d-flex align-items-center gap-1 text-truncate" style="max-width: 250px;">
                                    <?php if ($rank === 1): ?><span class="text-warning small">🥇</span><?php elseif ($rank === 2): ?><span class="text-secondary small">🥈</span><?php endif; ?>
                                    <span class="text-truncate" title="<?php echo $e($r['team_name']); ?>"><?php echo $e($r['team_name']); ?></span>
                                  </div>
                                </td>
                                <td><?php echo (int) $r['played']; ?></td>
                                <td class="text-success fw-bold"><?php echo (int) $r['won']; ?></td>
                                <td><?php echo (int) $r['drawn']; ?></td>
                                <td class="text-danger"><?php echo (int) $r['lost']; ?></td>
                                <td><span class="badge bg-light text-dark border px-1"><?php echo $r['points_scored'] . ':' . $r['points_conceded']; ?></span></td>
                                <td class="text-primary fw-black fs-6"><?php echo (int) $r['points']; ?></td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>


    <!-- =====================================================================
         6. KHỐI BẢNG TỔNG SẮP HUY CHƯƠNG TOÀN ĐOÀN (MEDAL TALLY & PODIUM)
         ===================================================================== -->
    <?php if (!empty($rankings)): ?>
      <section class="mb-4" id="rankings-section">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-amber-subtle text-warning d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
              <span class="material-symbols-outlined fs-5">military_tech</span>
            </div>
            <div>
              <h2 class="fw-bold text-dark fs-5 mb-0">Bảng tổng sắp huy chương toàn đoàn</h2>
              <span class="text-muted small" style="font-size:11.5px;">Xếp hạng căn cứ theo số Huy chương Vàng, Bạc, Đồng và tổng điểm</span>
            </div>
          </div>
        </div>

        <!-- 6.1 Bục Vinh Danh Top 3 (Podium Showcase) -->
        <?php
        $hasMedals = false;
        foreach ($rankings as $item) {
            if (!empty($item['gold']) || !empty($item['silver']) || !empty($item['bronze']) || !empty($item['points'])) {
                $hasMedals = true;
                break;
            }
        }
        ?>
        <?php if ($hasMedals && count($rankings) >= 3): ?>
          <?php
          $top1 = $rankings[0];
          $top2 = $rankings[1];
          $top3 = $rankings[2];
          ?>
          <div class="row g-3 mb-4 podium-row align-items-end justify-content-center">
            <!-- Hạng 2 (Bạc) -->
            <div class="col-12 col-md-4 order-2 order-md-1">
              <div class="podium-card card border-0 rounded-4 shadow-sm bg-white p-3 text-center position-relative overflow-hidden podium-silver">
                <div class="podium-badge-icon mb-2">🥈</div>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-bold small mb-2">Hạng 2 • Á Quân</span>
                <h5 class="fw-bold text-dark fs-6 mb-2 text-truncate" title="<?php echo $e($val($top2, array('name', 'team_name'), '')); ?>">
                  <?php if (!empty($top2['prefix'])): ?><span class="badge bg-dark text-white rounded-pill me-1" style="font-size:9.5px;letter-spacing:0.3px;"><?php echo $e($top2['prefix']); ?></span><?php endif; ?>
                  <?php echo $e($val($top2, array('name', 'team_name'), '')); ?>
                </h5>
                <div class="d-flex align-items-center justify-content-center gap-2 small text-muted mb-2">
                  <span>🥇 <?php echo (int)$val($top2, 'gold', 0); ?></span>
                  <span>🥈 <?php echo (int)$val($top2, 'silver', 0); ?></span>
                  <span>🥉 <?php echo (int)$val($top2, 'bronze', 0); ?></span>
                </div>
                <div class="fw-black text-primary fs-5"><?php echo $e($val($top2, array('points', 'score'), 0)); ?> <span class="small fs-6 fw-normal text-muted">điểm</span></div>
              </div>
            </div>

            <!-- Hạng 1 (Vàng - Trung tâm cao nhất) -->
            <div class="col-12 col-md-4 order-1 order-md-2">
              <div class="podium-card card border-warning border-2 rounded-4 shadow bg-white p-4 text-center position-relative overflow-hidden podium-gold">
                <div class="podium-gold-shimmer"></div>
                <div class="podium-badge-icon mb-2 fs-1">👑</div>
                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-black small mb-2 shadow-2xs">🥇 HẠNG 1 • VÔ ĐỊCH</span>
                <h4 class="fw-black text-dark fs-6 mb-2 text-truncate" title="<?php echo $e($val($top1, array('name', 'team_name'), '')); ?>">
                  <?php if (!empty($top1['prefix'])): ?><span class="badge bg-dark text-white rounded-pill me-1" style="font-size:10px;letter-spacing:0.3px;"><?php echo $e($top1['prefix']); ?></span><?php endif; ?>
                  <?php echo $e($val($top1, array('name', 'team_name'), '')); ?>
                </h4>
                <div class="d-flex align-items-center justify-content-center gap-3 small text-muted mb-3">
                  <span class="fw-bold text-warning">🥇 <?php echo (int)$val($top1, 'gold', 0); ?> Vàng</span>
                  <span class="fw-bold text-secondary">🥈 <?php echo (int)$val($top1, 'silver', 0); ?> Bạc</span>
                  <span class="fw-bold text-danger">🥉 <?php echo (int)$val($top1, 'bronze', 0); ?> Đồng</span>
                </div>
                <div class="fw-black text-primary display-6 mb-0"><?php echo $e($val($top1, array('points', 'score'), 0)); ?> <span class="fs-6 fw-normal text-muted">điểm</span></div>
              </div>
            </div>

            <!-- Hạng 3 (Đồng) -->
            <div class="col-12 col-md-4 order-3 order-md-3">
              <div class="podium-card card border-0 rounded-4 shadow-sm bg-white p-3 text-center position-relative overflow-hidden podium-bronze">
                <div class="podium-badge-icon mb-2">🥉</div>
                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-bold small mb-2">Hạng 3 • Quý Quân</span>
                <h5 class="fw-bold text-dark fs-6 mb-2 text-truncate" title="<?php echo $e($val($top3, array('name', 'team_name'), '')); ?>">
                  <?php if (!empty($top3['prefix'])): ?><span class="badge bg-dark text-white rounded-pill me-1" style="font-size:9.5px;letter-spacing:0.3px;"><?php echo $e($top3['prefix']); ?></span><?php endif; ?>
                  <?php echo $e($val($top3, array('name', 'team_name'), '')); ?>
                </h5>
                <div class="d-flex align-items-center justify-content-center gap-2 small text-muted mb-2">
                  <span>🥇 <?php echo (int)$val($top3, 'gold', 0); ?></span>
                  <span>🥈 <?php echo (int)$val($top3, 'silver', 0); ?></span>
                  <span>🥉 <?php echo (int)$val($top3, 'bronze', 0); ?></span>
                </div>
                <div class="fw-black text-primary fs-5"><?php echo $e($val($top3, array('points', 'score'), 0)); ?> <span class="small fs-6 fw-normal text-muted">điểm</span></div>
              </div>
            </div>
          </div>
        <?php elseif (!$hasMedals): ?>
          <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-4">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px;">
                <span class="material-symbols-outlined fs-5">emoji_events</span>
              </div>
              <div>
                <div class="fw-bold text-dark small">Bảng tổng sắp huy chương toàn đoàn (Dữ liệu thời gian thực)</div>
                <div class="text-muted" style="font-size:12px;">Các nội dung tranh tài đang diễn ra. Bục vinh danh và số lượng huy chương sẽ được tự động cập nhật ngay khi các trận chung kết có kết quả chính thức.</div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- 6.2 Bảng Toàn Đoàn Chi Tiết -->
        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-slate-50">
                <tr class="text-uppercase text-secondary" style="font-size:11px;">
                  <th class="ps-4" style="width:70px;">Hạng</th>
                  <th>Đoàn thể thao / Đơn vị</th>
                  <th class="text-center" style="width:90px;"><span class="badge bg-warning text-dark rounded-pill px-2">🥇 Vàng</span></th>
                  <th class="text-center" style="width:90px;"><span class="badge bg-secondary text-white rounded-pill px-2">🥈 Bạc</span></th>
                  <th class="text-center" style="width:90px;"><span class="badge text-white rounded-pill px-2" style="background:#cd7f32;">🥉 Đồng</span></th>
                  <th class="text-center pe-4" style="width:110px;">Tổng điểm</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rankings as $i => $r):
                  $rank = (int) $val($r, array('rank', 'position'), $i + 1);
                  $name = $val($r, array('name', 'team_name', 'unit_name', 'org_name', 'organization_name'), '');
                  $prefix = $val($r, array('prefix', 'property_prefix'), '');
                  if ($prefix === '') {
                      $prefix = $initials($name);
                  }
                  $points = $val($r, array('points', 'score', 'total_points'), '0');
                  $gold = $val($r, array('gold'), 0);
                  $silver = $val($r, array('silver'), 0);
                  $bronze = $val($r, array('bronze'), 0);

                  $badgeCls = $rank === 1 ? 'bg-warning text-dark fw-black' : ($rank === 2 ? 'bg-secondary text-white fw-bold' : ($rank === 3 ? 'bg-danger text-white fw-bold' : 'bg-light text-dark border'));
                ?>
                <tr class="<?php echo ($rank <= 3 && $hasMedals) ? 'table-light/50' : ''; ?>">
                  <td class="ps-4">
                    <span class="badge <?php echo $badgeCls; ?> rounded-circle fw-bold d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:12px;">
                      <?php echo $rank; ?>
                    </span>
                  </td>
                  <td class="fw-bold text-dark">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-pill text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 px-1"
                           style="min-width:28px;height:28px;font-size:<?php echo mb_strlen($prefix) > 4 ? '8px' : (mb_strlen($prefix) > 3 ? '9px' : '10px'); ?>;background:linear-gradient(135deg,#0f172a,#334155);"
                           title="<?php echo $e($prefix); ?>">
                        <?php echo $e($prefix); ?>
                      </div>
                      <span class="text-truncate"><?php echo $e($name); ?></span>
                    </div>
                  </td>
                  <td class="text-center fw-bold text-warning"><?php echo (int)$gold; ?></td>
                  <td class="text-center fw-bold text-secondary"><?php echo (int)$silver; ?></td>
                  <td class="text-center fw-bold text-danger"><?php echo (int)$bronze; ?></td>
                  <td class="text-center pe-4 fw-black text-primary fs-6"><?php echo $e($points); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    <?php endif; ?>

  </div>
</main>

<!-- Nút Cuộn Lên Đầu Trang Nhanh (Floating Back to Top) -->
<button type="button" id="btn-back-to-top" class="btn btn-primary rounded-circle shadow position-fixed bottom-0 end-0 m-4 d-flex align-items-center justify-content-center"
        style="width:44px;height:44px;z-index:99;display:none;transition:all .3s ease;" title="Cuộn lên đầu trang">
  <span class="material-symbols-outlined fs-5">arrow_upward</span>
</button>

<!-- Toast thông báo thân thiện -->
<div class="position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 1050">
  <div id="schedule-toast" class="toast align-items-center text-white bg-dark border-0 rounded-pill px-3 shadow" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body small fw-medium" id="schedule-toast-msg">
        Đang tải dữ liệu...
      </div>
    </div>
  </div>
</div>


<!-- =====================================================================
     STYLE TÙY BIẾN CHO GIAO DIỆN THỂ THAO THÂN THIỆN
     ===================================================================== -->
<style>
/* Thể lệ & Thể thức thi đấu accordion */
.rules-accordion {
  transition: all 0.25s ease;
  border-color: #e2e8f0 !important;
}
.rules-accordion > summary {
  list-style: none;
  user-select: none;
  transition: background-color 0.2s ease;
}
.rules-accordion > summary::-webkit-details-marker { display: none; }
.rules-accordion > summary:hover { background-color: #f8fafc; }
.rules-accordion[open] > summary { border-bottom: 1px solid #f1f5f9; }
.rules-accordion[open] .rules-chevron { transform: rotate(180deg); }
.rules-chevron { transition: transform .25s cubic-bezier(0.4, 0, 0.2, 1); }

.rules-accordion .rules-text-open { display: none; }
.rules-accordion .rules-text-closed { display: inline; }
.rules-accordion[open] .rules-text-open { display: inline; }
.rules-accordion[open] .rules-text-closed { display: none; }

.rules-accordion > summary:hover .rules-toggle-btn {
  background-color: #0d6efd;
  color: #ffffff;
}
.rules-accordion[open] .rules-toggle-btn {
  background-color: #f1f5f9;
  color: #475569;
  border-color: #cbd5e1;
}
.rules-accordion[open] > summary:hover .rules-toggle-btn {
  background-color: #e2e8f0;
  color: #1e293b;
}

/* Toolbar & Lọc danh mục thể lệ */
.rules-filter-toolbar {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
}
.rules-cat-btn {
  font-size: 12.5px;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 9999px;
  background: #ffffff;
  color: #475569;
  border: 1px solid #e2e8f0;
  transition: all 0.15s ease;
}
.rules-cat-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #cbd5e1;
}
.rules-cat-btn.active {
  background: #0f172a;
  color: #ffffff;
  border-color: #0f172a;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
}
.rules-cat-btn.active .badge {
  background-color: rgba(255, 255, 255, 0.2) !important;
  color: #ffffff !important;
}

/* Thẻ Môn Thi Đấu Thể Lệ */
.sport-rule-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  border: 1px solid #e2e8f0;
}
.sport-rule-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.03) !important;
  border-color: #cbd5e1 !important;
}
.sport-card-accent-bar {
  height: 3.5px;
  width: 100%;
}
.sport-icon-squircle {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.sport-card-tag {
  background: #f1f5f9;
  color: #334155 !important;
  font-size: 11px;
  font-weight: 600;
  border: 1px solid #e2e8f0;
}

/* Khung Chặng Thi Đấu */
.stage-card-box {
  background: #f8fafc;
  border: 1px solid #edf2f7;
  border-radius: 12px;
  padding: 12px 14px;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}
.stage-card-box:hover {
  background: #f1f5f9;
  border-color: #e2e8f0;
}
.stage-card-final {
  border-left: 3px solid #f59e0b !important;
}

/* Stepper Lộ Trình Đa Giai Đoạn */
.stage-timeline-track {
  position: relative;
  padding-left: 28px;
}
.stage-timeline-track::before {
  content: '';
  position: absolute;
  top: 14px;
  bottom: 20px;
  left: 10px;
  width: 2px;
  background: linear-gradient(to bottom, #94a3b8, #cbd5e1);
  border-radius: 1px;
}
.stage-timeline-step {
  position: relative;
  margin-bottom: 14px;
}
.stage-timeline-step:last-child {
  margin-bottom: 0;
}
.stage-step-indicator {
  position: absolute;
  left: -28px;
  top: 10px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #ffffff;
  border: 2px solid #3b82f6;
  color: #1d4ed8;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  z-index: 2;
}
.stage-step-indicator.final-step {
  border-color: #f59e0b;
  color: #b45309;
  background: #fffbeb;
}

/* Badge Format & Chips */
.format-badge-pill {
  font-size: 11px;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 9999px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  line-height: 1.25;
}
.stage-location-pill {
  font-size: 10.5px;
  background: #ffffff;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 9999px;
  padding: 2px 8px;
  display: inline-flex;
  align-items: center;
}
.rule-spec-chip {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 3px 9px;
  font-size: 11.5px;
  color: #1e293b;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.rule-points-chip {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #15803d;
  border-radius: 8px;
  padding: 3px 9px;
  font-size: 11.5px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.rule-detail-text {
  font-size: 12.5px;
  line-height: 1.55;
  color: #334155;
}
.highlight-term {
  font-weight: 600;
  color: #0f172a;
}

/* Design tokens */
.bg-slate-50 { background-color: #f8fafc !important; }
.bg-emerald { color: #10b981 !important; }
.text-cyan { color: #06b6d4 !important; }
.text-amber { color: #f59e0b !important; }
.backdrop-blur { backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
.text-white-85 { color: rgba(255, 255, 255, 0.88); }
.text-white-70 { color: rgba(255, 255, 255, 0.72); }
.text-white-60 { color: rgba(255, 255, 255, 0.6); }

/* Hero Banner */
.schedule-hero-banner {
  background: linear-gradient(135deg, #0a192f 0%, #173878 50%, #0d9488 100%);
}
.hero-glow-amber {
  position: absolute;
  top: -80px;
  right: 10%;
  width: 280px;
  height: 280px;
  background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, rgba(0,0,0,0) 70%);
  border-radius: 50%;
  pointer-events: none;
}
.hero-glow-teal {
  position: absolute;
  bottom: -90px;
  left: 20%;
  width: 340px;
  height: 240px;
  background: radial-gradient(circle, rgba(13, 148, 136, 0.25) 0%, rgba(0,0,0,0) 70%);
  border-radius: 50%;
  pointer-events: none;
}
.hero-pattern-dots {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 24px 24px;
  pointer-events: none;
}
.stat-pill-card {
  transition: transform 0.2s ease, background-color 0.2s ease;
}
.stat-pill-card:hover {
  transform: translateY(-2px);
  background-color: rgba(255, 255, 255, 0.15) !important;
}

/* Pulsing indicators */
.pulse-indicator-white {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #ffffff;
  animation: pulse-ring-white 1.4s infinite cubic-bezier(0.66, 0, 0, 1);
}
@keyframes pulse-ring-white {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}
.pulse-indicator-live {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #ef4444;
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: pulse-ring 1.4s infinite cubic-bezier(0.66, 0, 0, 1);
}
.pulse-indicator-sm {
  display: inline-block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: #ef4444;
  animation: pulse-ring 1.4s infinite cubic-bezier(0.66, 0, 0, 1);
}
@keyframes pulse-ring {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(239, 68, 68, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Live blinking indicator trên thẻ trận đấu */
.live-dot-blink {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #ef4444;
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: live-dot-pulse 1s ease-in-out infinite;
}
@keyframes live-dot-pulse {
  0%   { opacity: 1; box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
  70%  { opacity: 0.4; box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
  100% { opacity: 1; box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.live-blink-text {
  letter-spacing: 0.5px;
  animation: live-text-blink 1s steps(2, start) infinite;
}
@keyframes live-text-blink {
  0%, 100% { opacity: 1; }
  50%      { opacity: 0.45; }
}

/* Sticky Filter Card */
.sticky-filter {
  transition: box-shadow 0.2s ease;
}

/* =========================================================================
   BỘ LỌC MÔN THI ĐẤU (PREMIUM SPORT CHIPS & SCROLLER)
   ========================================================================= */
.sports-filter-card {
  position: relative;
  background: #ffffff;
}

/* Section Header & Icon Box */
.sport-section-icon-box {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #2563eb;
  border: 1px solid #bfdbfe;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(37, 99, 235, 0.08);
}
.sport-section-title {
  font-size: 14.5px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.2px;
  line-height: 1.2;
}
.sport-total-badge {
  background: #f1f5f9;
  color: #2563eb;
  font-size: 11px;
  font-weight: 700;
  padding: 2.5px 8.5px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  display: inline-flex;
  align-items: center;
  line-height: 1.2;
}

/* Chế độ xem: Segmented Control kiểu iOS / Apple */
.view-mode-segmented-control {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  padding: 3px;
  display: inline-flex;
  gap: 2px;
}
.view-mode-btn {
  border: none !important;
  background: transparent !important;
  color: #64748b !important;
  font-size: 12px;
  font-weight: 600;
  padding: 4.5px 12px;
  border-radius: 999px;
  cursor: pointer;
  transition: all 0.18s ease;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  line-height: 1.3;
}
.view-mode-btn:hover {
  color: #0f172a !important;
}
.view-mode-btn.active {
  background: #ffffff !important;
  color: #0f172a !important;
  font-weight: 700 !important;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
}

/* Scroller Container & Navigation Arrows */
.sports-scroller-wrapper {
  position: relative;
  padding: 2px 0;
}

/* Nút cuộn tròn điều hướng trái / phải */
.sports-nav-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #1e293b;
  box-shadow: 0 3px 12px rgba(15, 23, 42, 0.14);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  padding: 0;
  user-select: none;
}
.sports-nav-arrow .material-symbols-outlined {
  font-size: 20px;
}
.sports-nav-arrow:hover {
  background: #ffffff;
  color: #2563eb;
  border-color: #93c5fd;
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 6px 18px rgba(37, 99, 235, 0.22);
}
.sports-nav-arrow.is-hidden {
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transform: translateY(-50%) scale(0.8);
}
.sports-nav-prev {
  left: -6px;
}
.sports-nav-next {
  right: -6px;
}

/* Thanh cuộn ngang các môn - Ẩn hoàn toàn thanh cuộn thô của trình duyệt */
.sports-nav-scroller {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none !important; /* Firefox */
  -ms-overflow-style: none !important; /* IE/Edge */
  padding: 3px 4px 6px 4px;
  cursor: grab;
  user-select: none;
}
.sports-nav-scroller::-webkit-scrollbar {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
}
.sports-nav-scroller.is-dragging {
  cursor: grabbing !important;
  scroll-behavior: auto !important;
}
.sports-nav-scroller.is-dragging .sport-filter-btn {
  pointer-events: none;
}

/* Sport Filter Chip Button */
.sport-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 6.5px 13px;
  border-radius: 999px;
  white-space: nowrap;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.3;
  background: #ffffff;
  color: #334155;
  border: 1.5px solid #e2e8f0;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
  cursor: pointer;
  transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
  user-select: none;
  flex-shrink: 0;
  text-decoration: none;
}
.sport-filter-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #0f172a;
  transform: translateY(-1.5px);
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}
.sport-filter-btn.active,
.sport-filter-btn.btn-brand-primary {
  background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #3b82f6 100%) !important;
  border-color: #1d4ed8 !important;
  color: #ffffff !important;
  font-weight: 700;
  transform: translateY(-1.5px);
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35), 0 2px 4px rgba(37, 99, 235, 0.2) !important;
}

/* Icon môn thể thao trong pill */
.sport-pill-icon {
  font-size: 18px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}
.sport-filter-btn:hover .sport-pill-icon {
  transform: scale(1.12);
}
.sport-filter-btn.active .sport-pill-icon,
.sport-filter-btn.btn-brand-primary .sport-pill-icon {
  color: #ffffff !important;
}

/* Tên môn thể thao */
.sport-pill-name {
  font-size: 13px;
  letter-spacing: -0.1px;
}

/* Badge đếm số trận */
.sport-pill-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 19px;
  height: 19px;
  padding: 0 6px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 700;
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
  line-height: 1;
}
.sport-filter-btn:hover .sport-pill-badge {
  background: #e2e8f0;
  color: #0f172a;
}
.sport-filter-btn.active .sport-pill-badge,
.sport-filter-btn.btn-brand-primary .sport-pill-badge {
  background: rgba(255, 255, 255, 0.25) !important;
  color: #ffffff !important;
  border: 1px solid rgba(255, 255, 255, 0.35) !important;
  backdrop-filter: blur(4px);
}


/* Status Filter Buttons */
.status-filter-btn {
  background-color: #ffffff;
  color: #475569;
  border: 1px solid #e2e8f0;
  font-weight: 600;
  padding: 4px 12px;
  font-size: 12.5px;
  transition: all 0.18s ease;
}
.status-filter-btn:hover {
  background-color: #f1f5f9;
  color: #1e293b;
}
.status-filter-btn.active {
  background-color: #1e293b;
  color: #ffffff;
  border-color: #1e293b;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
}
.status-filter-btn.status-btn-live.active {
  background-color: #dc2626;
  border-color: #dc2626;
  color: #ffffff;
}

/* Date Filter Buttons */
.date-filter-btn {
  font-size: 12px;
  font-weight: 600;
  transition: all 0.18s ease;
}
.date-filter-btn.active {
  background-color: #2563eb !important;
  color: #ffffff !important;
  border-color: #2563eb !important;
}

/* Avatar squircle */
.avatar-squircle {
  border-radius: 12px;
  transition: transform 0.2s ease;
}
.ring-winner {
  box-shadow: 0 0 0 2px #10b981;
}

/* SPORT SUBGROUP — Nhóm & thanh tiêu đề nội dung (bộ môn) trong từng ngày */
.sport-subgroup {
  margin-bottom: 1.25rem;
}
.sport-subgroup:last-child {
  margin-bottom: 0;
}
.sport-subgroup-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 0.85rem;
  padding: 9px 14px;
  border-radius: 12px;
  background: var(--sport-accent-bg, #f1f5f9);
  border: 1px solid rgba(15, 23, 42, 0.06);
  border-left: 4px solid var(--sport-accent, #2563eb);
  cursor: pointer;
  user-select: none;
  transition: box-shadow 0.15s ease, filter 0.15s ease;
}
.sport-subgroup-header:hover {
  filter: brightness(0.98);
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}
.sport-subgroup-toggle {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  padding: 0;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--sport-accent, #2563eb);
}
.sport-subgroup-toggle:hover {
  background: rgba(15, 23, 42, 0.06);
}
.sport-subgroup-toggle .material-symbols-outlined {
  font-size: 20px;
  transition: transform 0.2s ease;
}
.sport-subgroup.collapsed .date-matches-row {
  display: none !important;
}
.sport-subgroup.collapsed .sport-subgroup-header {
  margin-bottom: 0;
}
.sport-subgroup.collapsed .sport-subgroup-toggle .material-symbols-outlined {
  transform: rotate(180deg);
}
.sport-subgroup-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 9px;
  background: #ffffff;
  color: var(--sport-accent, #2563eb);
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
  flex-shrink: 0;
}
.sport-subgroup-icon .material-symbols-outlined {
  font-size: 19px;
}
.sport-subgroup-name {
  font-weight: 700;
  font-size: 14.5px;
  color: #0f172a;
  line-height: 1.2;
  margin-right: auto;
}
.sport-subgroup-count {
  flex-shrink: 0;
  font-size: 11.5px;
  font-weight: 600;
  color: var(--sport-accent, #2563eb);
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 999px;
  padding: 2px 10px;
}

/* Match Card Hover & Transitions */
.match-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  border: 1px solid #eef2f6 !important;
}
.match-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 28px -6px rgba(15, 23, 42, 0.1), 0 4px 10px -2px rgba(15, 23, 42, 0.04) !important;
  border-color: #cbd5e1 !important;
}

/* MATCH ARENA BOX — Khối đối đầu nổi bật và cố định thẳng hàng */
.match-arena-box {
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 12px 8px 10px 8px;
  box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.9), 0 2px 5px rgba(15, 23, 42, 0.03);
}
.faceoff-row-avatars {
  display: grid;
  grid-template-columns: 1fr 64px 1fr;
  align-items: center;
  justify-items: center;
  height: 48px;
}
.faceoff-row-names {
  display: grid;
  grid-template-columns: 1fr 64px 1fr;
  align-items: start;
  justify-items: center;
}
.faceoff-row-winners {
  display: grid;
  grid-template-columns: 1fr 64px 1fr;
  align-items: center;
}
.avatar-cell, .center-emblem-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}
.center-time-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding-top: 1px;
}
.vs-circle-emblem {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ffffff;
  border: 2px solid #e2e8f0;
  color: #ef4444;
  font-weight: 900;
  font-size: 11px;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
.match-time-chip {
  font-size: 14px;
  font-weight: 800;
  color: #1d4ed8;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 9999px;
  padding: 3px 12px;
  white-space: nowrap;
  box-shadow: 0 1px 2px rgba(37, 99, 235, 0.08);
}
.team-name-cell {
  width: 100%;
  height: 38px;
  max-height: 38px;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.35;
  padding: 0 4px;
  word-break: break-word;
}
.score-pill-emblem {
  background: #0f172a !important;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Compact Row Styling */
.match-compact-row {
  border: 1px solid #e2e8f0 !important;
  transition: background-color 0.18s ease, transform 0.18s ease;
}
.match-compact-row:hover {
  background-color: #f8fafc !important;
  transform: translateX(2px);
}

/* Date Group Header — Nổi bật, phong cách Đại hội thể thao */
.date-group-header {
  background: linear-gradient(135deg, #0a192f 0%, #173878 52%, #1e40af 100%) !important;
  border: 1px solid rgba(255, 255, 255, 0.14) !important;
  border-left: 0 !important;
  box-shadow: 0 6px 20px -3px rgba(10, 25, 47, 0.28), 0 2px 6px -1px rgba(10, 25, 47, 0.12) !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.date-group-header:hover {
  box-shadow: 0 10px 24px -3px rgba(10, 25, 47, 0.35) !important;
}
.date-group-accent-line {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 5px;
  background: linear-gradient(to bottom, #f59e0b 0%, #ec4899 50%, #38bdf8 100%);
  z-index: 2;
}
.date-group-dots-pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
  background-size: 18px 18px;
  pointer-events: none;
  z-index: 0;
}
.date-group-icon-squircle {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.22);
  color: #38bdf8;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
  flex-shrink: 0;
}
.date-group-day-badge {
  background: linear-gradient(135deg, #f59e0b, #d97706) !important;
  color: #0f172a !important;
  font-size: 10.5px !important;
  font-weight: 800 !important;
  letter-spacing: 0.4px;
  border-radius: 9999px !important;
  padding: 3px 9px !important;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
}
.date-group-pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #0f172a;
  display: inline-block;
}
.date-group-sublabel {
  color: rgba(255, 255, 255, 0.7);
  font-size: 11.5px;
  font-weight: 500;
}
.date-group-title {
  color: #ffffff !important;
  font-size: 18px;
  font-weight: 800;
  letter-spacing: -0.2px;
  line-height: 1.25;
}
.date-group-count-pill {
  background: #ffffff;
  color: #1e3a8a !important;
  font-weight: 800;
  font-size: 13px;
  padding: 6px 15px;
  border-radius: 9999px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: transform 0.15s ease;
}
.date-group-count-pill:hover {
  transform: scale(1.03);
}

/* Qualify Row in Standings */
.row-qualify {
  background-color: #f0fdf4 !important;
}

/* Podium Cards */
.podium-card {
  transition: transform 0.2s ease;
}
.podium-card:hover {
  transform: translateY(-4px);
}
.podium-gold {
  border-color: #f59e0b !important;
  box-shadow: 0 12px 28px -4px rgba(245, 158, 11, 0.25) !important;
}
.podium-gold-shimmer {
  position: absolute;
  top: 0;
  left: -100%;
  width: 60%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  animation: gold-shimmer 3s infinite;
}
@keyframes gold-shimmer {
  100% { left: 160%; }
}
.podium-silver {
  border-top: 4px solid #94a3b8 !important;
}
.podium-bronze {
  border-top: 4px solid #cd7f32 !important;
}
.podium-badge-icon {
  font-size: 32px;
  line-height: 1;
}

/* Refresh Animation */
.refresh-spin {
  animation: spin-anim 0.8s linear infinite;
}
@keyframes spin-anim {
  100% { transform: rotate(360deg); }
}
</style>


<!-- =====================================================================
     JAVASCRIPT: TƯƠNG TÁC LỌC, TÌM KIẾM, ĐỔI CHẾ ĐỘ XEM & LÀM MỚI
     ===================================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var sportBtns = document.querySelectorAll('.sport-filter-btn');
  var dateBtns = document.querySelectorAll('.date-filter-btn');
  var statusBtns = document.querySelectorAll('.status-filter-btn');
  var searchInput = document.getElementById('schedule-search-input');
  var clearSearchBtn = document.getElementById('btn-clear-search');
  var matchItems = document.querySelectorAll('.match-item-wrapper');
  var dateGroups = document.querySelectorAll('.date-group-section');
  var counterEl = document.getElementById('visible-matches-count');
  var noMatchAlert = document.getElementById('no-filter-match-alert');
  var resetBtn = document.getElementById('btn-reset-filters');
  var matchSectionTitle = document.getElementById('match-section-title');

  var btnViewCards = document.getElementById('btn-view-cards');
  var btnViewCompact = document.getElementById('btn-view-compact');
  var matchesGridContainer = document.getElementById('matches-grid-container');

  var btnManualRefresh = document.getElementById('btn-manual-refresh');
  var btnBackToTop = document.getElementById('btn-back-to-top');

  var currentSportId = "<?php echo (string)$selectedSportId; ?>";
  var currentDate = "<?php echo (string)$selectedDate; ?>";
  var currentStatus = "<?php echo (string)$selectedStatus; ?>";
  var currentSearch = "";
  var currentViewMode = "cards"; // 'cards' | 'compact'

  function showToast(message) {
    var toastEl = document.getElementById('schedule-toast');
    var msgEl = document.getElementById('schedule-toast-msg');
    if (toastEl && msgEl && typeof bootstrap !== 'undefined') {
      msgEl.textContent = message;
      var toast = new bootstrap.Toast(toastEl, { delay: 2500 });
      toast.show();
    }
  }

  function applyFilters() {
    var totalVisible = 0;
    var searchLower = currentSearch.trim().toLowerCase();

    // Duyệt qua từng nhóm ngày
    dateGroups.forEach(function (group) {
      var groupDate = group.getAttribute('data-group-date') || '';
      var groupVisibleCount = 0;

      // Duyệt từng nhóm nội dung (bộ môn) bên trong nhóm ngày
      var subgroups = group.querySelectorAll('.sport-subgroup');
      subgroups.forEach(function (sub) {
        var subVisibleCount = 0;
        var itemsInSub = sub.querySelectorAll('.match-item-wrapper');

        itemsInSub.forEach(function (item) {
          var sId = item.getAttribute('data-sport-id') || '';
          var mDate = item.getAttribute('data-date') || '';
          var st = item.getAttribute('data-status') || '';
          var searchData = item.getAttribute('data-search') || '';

          var matchSport = (!currentSportId || sId === currentSportId);
          var matchDate = (!currentDate || mDate === currentDate);
          var matchStatus = (!currentStatus || st === currentStatus);
          var matchSearch = (!searchLower || searchData.indexOf(searchLower) !== -1);

          if (matchSport && matchDate && matchStatus && matchSearch) {
            item.style.display = '';
            subVisibleCount++;
            groupVisibleCount++;
            totalVisible++;
          } else {
            item.style.display = 'none';
          }
        });

        // Cập nhật số trận trên thanh tiêu đề nội dung & ẩn nhóm nếu rỗng
        var subCount = sub.querySelector('.sport-subgroup-count');
        if (subCount) {
          subCount.textContent = subVisibleCount + ' trận';
        }
        sub.style.display = (subVisibleCount === 0) ? 'none' : '';
      });

      // Cập nhật số lượng trận hiển thị trong huy hiệu nhóm ngày
      var groupBadge = group.querySelector('.date-group-match-count');
      if (groupBadge) {
        groupBadge.textContent = groupVisibleCount + ' trận đấu';
      }

      // Nếu trong nhóm ngày không có trận nào khớp, ẩn cả khối ngày đó
      if (groupVisibleCount === 0) {
        group.style.display = 'none';
      } else {
        group.style.display = '';
      }
    });

    if (counterEl) counterEl.textContent = totalVisible;
    if (noMatchAlert) noMatchAlert.style.display = (totalVisible === 0) ? '' : 'none';
  }

  // 1. Chuyển đổi môn thi đấu
  sportBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var sId = btn.getAttribute('data-sport-id') || '';
      currentSportId = sId;

      // Đồng bộ trạng thái active cho tất cả nút thể thao (ở cả thanh trên và thanh inline)
      sportBtns.forEach(function (b) {
        var bId = b.getAttribute('data-sport-id') || '';
        var isMatch = (bId === sId);
        if (isMatch) {
          b.classList.add('active', 'btn-brand-primary');
          b.classList.remove('btn-light');
        } else {
          b.classList.remove('active', 'btn-brand-primary');
          b.classList.add('btn-light');
        }
      });

      var sportName = btn.getAttribute('data-sport-name') || '';
      if (matchSectionTitle) {
        matchSectionTitle.textContent = currentSportId ? ('Lịch thi đấu: ' + sportName) : 'Danh sách các trận thi đấu';
      }
      applyFilters();

      // Cuộn mượt nút được chọn vào giữa tầm nhìn
      try {
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      } catch (err) {}
    });
  });

  // 1.1 Khởi tạo điều khiển cuộn thanh môn thi đấu (Scroll arrows, Mouse drag & Wheel)
  var sportsScroller = document.getElementById('sports-nav-scroller');
  var sportsArrowLeft = document.getElementById('btn-sports-scroll-left');
  var sportsArrowRight = document.getElementById('btn-sports-scroll-right');

  if (sportsScroller && sportsArrowLeft && sportsArrowRight) {
    var updateSportsArrows = function () {
      var sLeft = sportsScroller.scrollLeft;
      var maxScroll = sportsScroller.scrollWidth - sportsScroller.clientWidth;
      
      if (maxScroll <= 4) {
        sportsArrowLeft.classList.add('is-hidden');
        sportsArrowRight.classList.add('is-hidden');
        return;
      }
      
      if (sLeft <= 8) {
        sportsArrowLeft.classList.add('is-hidden');
      } else {
        sportsArrowLeft.classList.remove('is-hidden');
      }
      
      if (sLeft >= maxScroll - 8) {
        sportsArrowRight.classList.add('is-hidden');
      } else {
        sportsArrowRight.classList.remove('is-hidden');
      }
    };

    sportsArrowLeft.addEventListener('click', function (e) {
      e.preventDefault();
      sportsScroller.scrollBy({ left: -260, behavior: 'smooth' });
    });

    sportsArrowRight.addEventListener('click', function (e) {
      e.preventDefault();
      sportsScroller.scrollBy({ left: 260, behavior: 'smooth' });
    });

    sportsScroller.addEventListener('scroll', updateSportsArrows, { passive: true });
    window.addEventListener('resize', updateSportsArrows);
    setTimeout(updateSportsArrows, 120);

    // Cuộn ngang bằng con lăn chuột khi rê trên thanh môn
    sportsScroller.addEventListener('wheel', function (e) {
      if (e.deltaY !== 0 && !e.shiftKey) {
        e.preventDefault();
        sportsScroller.scrollLeft += (e.deltaY * 0.9);
      }
    }, { passive: false });

    // Kéo chuột để cuộn (Mouse Drag)
    // Chỉ bật trạng thái kéo khi chuột DI CHUYỂN vượt ngưỡng, để click thường không bị
    // CSS `.is-dragging .sport-filter-btn { pointer-events: none }` chặn.
    var isPressing = false;
    var isDragging = false;
    var startX, startScrollLeft;
    var DRAG_THRESHOLD = 5;

    sportsScroller.addEventListener('mousedown', function (e) {
      if (e.target.closest('.sports-nav-arrow')) return;
      isPressing = true;
      isDragging = false;
      startX = e.pageX - sportsScroller.offsetLeft;
      startScrollLeft = sportsScroller.scrollLeft;
    });

    document.addEventListener('mouseup', function () {
      isPressing = false;
      if (isDragging) {
        isDragging = false;
        // Gỡ class ở lần tick sau để chặn đúng cú click kết thúc thao tác kéo
        setTimeout(function () {
          sportsScroller.classList.remove('is-dragging');
        }, 0);
      }
    });

    sportsScroller.addEventListener('mousemove', function (e) {
      if (!isPressing) return;
      var x = e.pageX - sportsScroller.offsetLeft;
      if (!isDragging) {
        if (Math.abs(x - startX) < DRAG_THRESHOLD) return;
        isDragging = true;
        sportsScroller.classList.add('is-dragging');
      }
      e.preventDefault();
      var walk = (x - startX) * 1.3;
      sportsScroller.scrollLeft = startScrollLeft - walk;
    });
  }

  // 2. Chuyển đổi ngày thi đấu
  dateBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      dateBtns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      currentDate = btn.getAttribute('data-date') || '';
      applyFilters();
    });
  });

  // 3. Chuyển đổi trạng thái trận đấu
  statusBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      statusBtns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      currentStatus = btn.getAttribute('data-status') || '';
      applyFilters();
    });
  });

  // 4. Ô tìm kiếm
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentSearch = searchInput.value;
      if (clearSearchBtn) clearSearchBtn.style.display = currentSearch ? '' : 'none';
      applyFilters();
    });
  }

  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', function () {
      searchInput.value = '';
      currentSearch = '';
      clearSearchBtn.style.display = 'none';
      applyFilters();
      searchInput.focus();
    });
  }

  // 5. Nút Đặt lại toàn bộ bộ lọc
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      currentSportId = '';
      currentDate = '';
      currentStatus = '';
      currentSearch = '';
      if (searchInput) searchInput.value = '';
      if (clearSearchBtn) clearSearchBtn.style.display = 'none';

      sportBtns.forEach(function (b) {
        var bId = b.getAttribute('data-sport-id') || '';
        if (!bId) {
          b.classList.add('active', 'btn-brand-primary');
          b.classList.remove('btn-light');
        } else {
          b.classList.remove('active', 'btn-brand-primary');
          b.classList.add('btn-light');
        }
      });
      dateBtns.forEach(function (b, idx) {
        if (idx === 0) b.classList.add('active');
        else b.classList.remove('active');
      });
      statusBtns.forEach(function (b, idx) {
        if (idx === 0) b.classList.add('active');
        else b.classList.remove('active');
      });
      if (matchSectionTitle) matchSectionTitle.textContent = 'Danh sách các trận thi đấu';
      applyFilters();
      showToast('Đã xóa tất cả bộ lọc');
    });
  }

  // 5.5 Thu gọn / mở rộng từng nhóm nội dung (bộ môn)
  function toggleSubgroup(header) {
    var sub = header.closest('.sport-subgroup');
    if (!sub) return;
    var collapsed = sub.classList.toggle('collapsed');
    header.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
  }

  document.querySelectorAll('.sport-subgroup-header').forEach(function (header) {
    header.addEventListener('click', function () {
      toggleSubgroup(header);
    });
    header.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleSubgroup(header);
      }
    });
  });

  // 6. Chuyển đổi chế độ xem: Thẻ (Cards) vs Danh sách gọn (Compact)
  function setViewMode(mode) {
    currentViewMode = mode;
    var cardElements = document.querySelectorAll('.match-card');
    var compactElements = document.querySelectorAll('.match-compact-row');

    if (mode === 'compact') {
      if (btnViewCompact) btnViewCompact.classList.add('active');
      if (btnViewCards) btnViewCards.classList.remove('active');
      cardElements.forEach(function (el) { el.style.display = 'none'; });
      compactElements.forEach(function (el) { el.style.display = ''; });
    } else {
      if (btnViewCards) btnViewCards.classList.add('active');
      if (btnViewCompact) btnViewCompact.classList.remove('active');
      cardElements.forEach(function (el) { el.style.display = ''; });
      compactElements.forEach(function (el) { el.style.display = 'none'; });
    }
  }

  if (btnViewCards) {
    btnViewCards.addEventListener('click', function () { setViewMode('cards'); });
  }
  if (btnViewCompact) {
    btnViewCompact.addEventListener('click', function () { setViewMode('compact'); });
  }

  // 7. Nút Làm Mới Dữ Liệu
  if (btnManualRefresh) {
    btnManualRefresh.addEventListener('click', function () {
      var icon = btnManualRefresh.querySelector('.refresh-icon');
      if (icon) icon.classList.add('refresh-spin');
      btnManualRefresh.disabled = true;

      showToast('Đang cập nhật tỷ số mới nhất...');

      setTimeout(function () {
        if (icon) icon.classList.remove('refresh-spin');
        btnManualRefresh.disabled = false;
        showToast('Đã đồng bộ dữ liệu thi đấu mới nhất!');
      }, 700);
    });
  }

  // 8. Back to top button
  window.addEventListener('scroll', function () {
    if (btnBackToTop) {
      if (window.scrollY > 350) {
        btnBackToTop.style.display = 'flex';
      } else {
        btnBackToTop.style.display = 'none';
      }
    }
  });

  if (btnBackToTop) {
    btnBackToTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 9. Lọc & Tìm kiếm nhanh cho khối Thể lệ & Thể thức
  var rulesCatButtons = document.querySelectorAll('.rules-cat-btn');
  var rulesSearchInput = document.getElementById('rules-search-input');
  var rulesSearchClear = document.getElementById('rules-search-clear');
  var rulesItems = document.querySelectorAll('.sport-rule-item');
  var rulesEmptyState = document.getElementById('rules-empty-state');
  var rulesResetBtn = document.getElementById('rules-reset-filter');

  var rulesActiveCat = 'all';
  var rulesSearchTerm = '';

  function filterRules() {
    var visible = 0;
    var term = rulesSearchTerm.trim().toLowerCase();

    rulesItems.forEach(function (item) {
      var cat = item.getAttribute('data-cat');
      var searchData = (item.getAttribute('data-search') || '').toLowerCase();
      var itemText = item.textContent.toLowerCase();

      var catOk = (rulesActiveCat === 'all' || cat === rulesActiveCat);
      var searchOk = (!term || searchData.indexOf(term) !== -1 || itemText.indexOf(term) !== -1);

      if (catOk && searchOk) {
        item.style.display = '';
        visible++;
      } else {
        item.style.display = 'none';
      }
    });

    if (rulesEmptyState) {
      rulesEmptyState.style.display = (visible === 0) ? 'block' : 'none';
    }
    if (rulesSearchClear) {
      rulesSearchClear.style.display = (rulesSearchTerm.length > 0) ? 'inline-block' : 'none';
    }
  }

  rulesCatButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      rulesCatButtons.forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
      rulesActiveCat = this.getAttribute('data-cat');
      filterRules();
    });
  });

  if (rulesSearchInput) {
    rulesSearchInput.addEventListener('input', function () {
      rulesSearchTerm = this.value;
      filterRules();
    });
  }

  if (rulesSearchClear) {
    rulesSearchClear.addEventListener('click', function () {
      rulesSearchInput.value = '';
      rulesSearchTerm = '';
      filterRules();
      rulesSearchInput.focus();
    });
  }

  if (rulesResetBtn) {
    rulesResetBtn.addEventListener('click', function () {
      rulesActiveCat = 'all';
      rulesSearchTerm = '';
      if (rulesSearchInput) rulesSearchInput.value = '';
      rulesCatButtons.forEach(function (b) {
        b.classList.toggle('active', b.getAttribute('data-cat') === 'all');
      });
      filterRules();
    });
  }

  // 10. Mở accordion thể thức khi người dùng click vào neo hoặc truy cập link #rules-section
  function checkOpenRulesHash() {
    if (window.location.hash === '#rules-section') {
      var rAcc = document.querySelector('.rules-accordion');
      if (rAcc) rAcc.open = true;
    }
  }
  window.addEventListener('hashchange', checkOpenRulesHash);
  checkOpenRulesHash();

  document.querySelectorAll('a[href="#rules-section"]').forEach(function (link) {
    link.addEventListener('click', function () {
      var rAcc = document.querySelector('.rules-accordion');
      if (rAcc) {
        rAcc.open = true;
      }
    });
  });

  // Khởi chạy bộ lọc ban đầu
  applyFilters();
});
</script>
