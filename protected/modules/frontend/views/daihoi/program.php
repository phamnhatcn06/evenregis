<?php
/**
 * Trang chương trình chi tiết Đại hội:
 *  - Tiệc Welcome / Khai mạc (tiec-welcome)
 *  - Tiệc Gala / Bế mạc (tiec-gala)
 *
 * Giao diện Timeline hiện đại, tương thích hoàn toàn với hệ thống theme Đại hội Mường Thanh 2026.
 * Bổ sung Hero banner cao cấp, 3 trụ cột điểm nhấn (Pillars), bộ lọc danh mục tương tác,
 * timeline dọc với mốc giờ trực quan, card hoạt động chuyên biệt (Chung kết Miss Mường Thanh,
 * Hội diễn Văn nghệ Gấm Hoa, các Chương nghệ thuật, Lucky Draw & DJ Performance).
 *
 * @var array  $event
 * @var array  $agenda
 * @var string $active   Tab đang mở ('welcome' | 'gala' | ...)
 * @var array  $config   eyebrow, title, subtitle, icon, accent, info[], keywords[], emptyText, program[]
 */

$base = Yii::app()->request->baseUrl;
$dh = $base . '/public/daihoi';
$e = function ($s) {
    return CHtml::encode($s !== null && is_scalar($s) ? (string) $s : '');
};

$val = function ($arr, $keys, $default = '') {
    foreach ((array) $keys as $k) {
        if (is_array($arr) && isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
            return $arr[$k];
        }
    }
    return $default;
};

$eventName = $val($event, array('name', 'title'), 'Đại hội Mường Thanh 2026');
$active = isset($active) ? $active : 'welcome';
$isWelcome = ($active === 'welcome');
$isGala = ($active === 'gala');

// Cấu hình màu sắc & chủ đề riêng cho Welcome vs Gala
$themeClass = $isWelcome ? 'prg-theme-welcome' : ($isGala ? 'prg-theme-gala' : '');
$accent = isset($config['accent']) ? $config['accent'] : ($isWelcome ? '#7c3aed' : ($isGala ? '#be123c' : '#2563eb'));

// Tiêu đề & mô tả tối ưu hóa theo theme
$pageEyebrow = isset($config['eyebrow']) && $config['eyebrow'] !== '' 
    ? $config['eyebrow'] 
    : ($isWelcome ? 'Lễ Khai Mạc & Đêm Hội Miss' : ($isGala ? 'Lễ Bế Mạc & Đêm Tiệc Gala' : 'Chương trình Đại hội'));

$pageTitle = isset($config['title']) && $config['title'] !== '' 
    ? $config['title'] 
    : ($isWelcome ? 'Khai mạc, thi Miss & Văn nghệ' : ($isGala ? 'Bế mạc Đại hội - Tiệc Gala' : 'Chương trình chi tiết'));

$pageSubtitle = isset($config['subtitle']) && $config['subtitle'] !== '' 
    ? $config['subtitle'] 
    : ($isWelcome 
        ? 'Chương trình Lễ khai mạc Đại hội, Chung kết thi Miss Mường Thanh và đêm Chung kết Hội diễn Văn nghệ "Việt Nam Gấm Hoa".' 
        : ($isGala 
            ? 'Dạ tiệc vinh danh, tri ân 88 đoàn tham dự, 3 chương đại nhạc hội đặc sắc, bốc thăm may mắn Lucky Draw và bùng nổ cùng DJ Performance.' 
            : 'Lịch trình chi tiết các hoạt động trong khuôn khổ Đại hội Mường Thanh 2026.'));

$pageIcon = isset($config['icon']) && $config['icon'] !== '' ? $config['icon'] : ($isWelcome ? 'celebration' : ($isGala ? 'nightlife' : 'event'));

// Thông tin sự kiện
$info = isset($config['info']) ? (array) $config['info'] : array();
$dateValue = $isWelcome ? 'Thứ Ba, 27/10/2026' : ($isGala ? 'Thứ Năm, 29/10/2026' : '27 - 29/10/2026');
$timeValue = $isWelcome ? '18:30 – 22:30' : ($isGala ? '18:30 – 22:00' : '18:30');
$venueValue = 'Hoa Lư Ballroom - Tầng 1';

// Quy định trang phục - Khai mạc mặc truyền thống, Gala/khác mặc dạ hội.
$dressShort = $isWelcome ? 'Nữ: Áo dài · Nam: Sơ mi, quần Âu' : 'Lịch sự / Dạ hội';
$dressDetail = $isWelcome
    ? 'Nữ: Áo dài. Nam: Sơ mi, quần Âu dài, giày; khuyến khích mặc áo dài.'
    : 'Trang phục dạ hội / lịch sự, trang trọng. Khuyến khích mang giày dép thoải mái để thuận tiện tham gia các hoạt động.';

foreach ($info as $r) {
    $lbl = mb_strtolower($val($r, 'label', ''), 'UTF-8');
    $v = $val($r, 'value', '');
    if (strpos($lbl, 'thời gian') !== false) {
        $parts = explode(',', $v);
        if (!empty($parts[0])) $timeValue = trim($parts[0]);
        if (!empty($parts[1])) $dateValue = trim($parts[1]);
    } elseif (strpos($lbl, 'địa điểm') !== false) {
        $parts = explode('-', $v);
        if (!empty($parts[0])) $venueValue = trim($parts[0]);
    }
}

// Chương trình chi tiết từ config
$program = isset($config['program']) && is_array($config['program']) ? $config['program'] : array();

// Fallback lọc từ agenda nếu không có program tĩnh
$keywords = isset($config['keywords']) ? (array) $config['keywords'] : array();
$matched = array();
if (empty($program) && !empty($agenda) && is_array($agenda) && !empty($keywords)) {
    foreach ($agenda as $item) {
        if (!is_array($item)) continue;
        $haystack = mb_strtolower(
            $val($item, array('title', 'name'), '') . ' ' .
            $val($item, array('type', 'category'), '') . ' ' .
            $val($item, array('description', 'note'), ''),
            'UTF-8'
        );
        foreach ($keywords as $kw) {
            if ($kw !== '' && mb_strpos($haystack, mb_strtolower($kw, 'UTF-8'), 0, 'UTF-8') !== false) {
                $matched[] = $item;
                break;
            }
        }
    }
}

// Helper phân loại hoạt động phục vụ hiển thị Icon, Tag, Màu sắc và Bộ lọc
$categorizeItem = function ($title, $sub = array()) use ($isWelcome, $isGala) {
    $t = mb_strtolower($title, 'UTF-8');
    
    if (strpos($t, 'miss') !== false) {
        return array('category' => 'miss', 'tag' => '👑 Chung kết Miss', 'icon' => 'diamond', 'badgeCls' => 'prg-tag--miss', 'itemCls' => 'prg-tl-item--highlight');
    }
    if (strpos($t, 'văn nghệ') !== false || strpos($t, 'gấm hoa') !== false) {
        return array('category' => 'contest', 'tag' => '🎭 Hội diễn Văn nghệ', 'icon' => 'palette', 'badgeCls' => 'prg-tag--contest', 'itemCls' => 'prg-tl-item--highlight');
    }
    if (strpos($t, 'trao giải') !== false || strpos($t, 'kỷ niệm chương') !== false) {
        return array('category' => 'award', 'tag' => '🏆 Vinh danh & Trao giải', 'icon' => 'emoji_events', 'badgeCls' => 'prg-tag--award', 'itemCls' => 'prg-tl-item--gold');
    }
    if (strpos($t, 'lucky draw') !== false || strpos($t, 'bốc thăm') !== false) {
        return array('category' => 'luckydraw', 'tag' => '🎁 Bốc thăm may mắn', 'icon' => 'featured_seasonal_and_gifts', 'badgeCls' => 'prg-tag--luckydraw', 'itemCls' => 'prg-tl-item--gold');
    }
    if (strpos($t, 'dj') !== false) {
        return array('category' => 'dj', 'tag' => '🎧 DJ Performance', 'icon' => 'headphones', 'badgeCls' => 'prg-tag--dj', 'itemCls' => 'prg-tl-item--dj');
    }
    if (strpos($t, 'nghệ thuật') !== false || strpos($t, 'tiết mục') !== false || strpos($t, 'ca sĩ') !== false) {
        return array('category' => 'music', 'tag' => '🎤 Biểu diễn nghệ thuật', 'icon' => 'mic_external_on', 'badgeCls' => 'prg-tag--music', 'itemCls' => 'prg-tl-item--highlight');
    }
    if (strpos($t, 'đón khách') !== false) {
        return array('category' => 'protocol', 'tag' => '🚪 Đón khách & Check-in', 'icon' => 'how_to_reg', 'badgeCls' => 'prg-tag--protocol', 'itemCls' => '');
    }
    if (strpos($t, 'thắp đuốc') !== false) {
        return array('category' => 'protocol', 'tag' => '🔥 Nghi thức thắp đuốc', 'icon' => 'local_fire_department', 'badgeCls' => 'prg-tag--protocol', 'itemCls' => 'prg-tl-item--gold');
    }
    if (strpos($t, 'clip') !== false) {
        return array('category' => 'protocol', 'tag' => '🎬 Phim tài liệu', 'icon' => 'smart_display', 'badgeCls' => 'prg-tag--protocol', 'itemCls' => '');
    }
    if (strpos($t, 'phát biểu') !== false || strpos($t, 'khai mạc') !== false || strpos($t, 'bế mạc') !== false || strpos($t, 'giới thiệu') !== false || strpos($t, 'bắt đầu') !== false) {
        return array('category' => 'protocol', 'tag' => '✨ Nghi thức Đại hội', 'icon' => 'flag', 'badgeCls' => 'prg-tag--protocol', 'itemCls' => '');
    }
    if (strpos($t, 'kết thúc') !== false) {
        return array('category' => 'protocol', 'tag' => '🏁 Kết thúc', 'icon' => 'task_alt', 'badgeCls' => '', 'itemCls' => '');
    }
    return array('category' => 'other', 'tag' => 'Hoạt động', 'icon' => 'schedule', 'badgeCls' => '', 'itemCls' => '');
};

// Định nghĩa 3 Trụ cột điểm nhấn (Pillars)
$pillars = array();
if ($isWelcome) {
    $pillars = array(
        array(
            'icon' => 'local_fire_department',
            'eyebrow' => 'Nghi thức Đại hội',
            'title' => 'Khai mạc & Thắp đuốc',
            'desc' => 'Clip "Hành trình ngọn lửa di sản", nghi thức thắp đuốc thiêng liêng và phát biểu khai mạc Đại hội.',
        ),
        array(
            'icon' => 'diamond',
            'eyebrow' => 'Chung kết sắc đẹp',
            'title' => 'Miss Mường Thanh 2026',
            'desc' => 'Màn đồng diễn Top 30, trình diễn trang phục bản sắc Top 15, phần thi ứng xử Top 5 cùng Ca sĩ Oplus Band.',
        ),
        array(
            'icon' => 'palette',
            'eyebrow' => 'Hội diễn toàn quốc',
            'title' => 'Việt Nam Gấm Hoa',
            'desc' => '6 liên quân đơn vị tranh tài nghệ thuật đặc sắc, công bố và vinh danh trao giải thưởng chung cuộc.',
        ),
    );
} elseif ($isGala) {
    $pillars = array(
        array(
            'icon' => 'military_tech',
            'eyebrow' => 'Tri ân & Tôn vinh',
            'title' => 'Vinh danh 88 Đoàn',
            'desc' => 'Trao kỷ niệm chương và cờ lưu niệm cho 88 đoàn đại biểu, chụp ảnh kỷ niệm dấu ấn Đại hội 2026.',
        ),
        array(
            'icon' => 'theater_comedy',
            'eyebrow' => 'Đại nhạc hội',
            'title' => '3 Chương Nghệ thuật',
            'desc' => 'Đại tiệc âm nhạc với 3 ca sĩ nổi tiếng: An Thu An (Chương 1), Hà Myo (Chương 2) và Phạm Anh Duy (Chương 3).',
        ),
        array(
            'icon' => 'celebration',
            'eyebrow' => 'Bùng nổ sàn diễn',
            'title' => 'Lucky Draw & DJ Party',
            'desc' => 'Bốc thăm trúng thưởng Giải Đặc biệt, Nhất & Nhì cùng 2 set DJ cực cháy khuấy động đêm tiệc.',
        ),
    );
}

// Danh mục bộ lọc
$filterTabs = array();
if ($isWelcome) {
    $filterTabs = array(
        array('id' => 'all', 'label' => 'Tất cả', 'icon' => 'list_alt'),
        array('id' => 'protocol', 'label' => 'Nghi thức', 'icon' => 'flag'),
        array('id' => 'miss', 'label' => 'Miss Mường Thanh', 'icon' => 'diamond'),
        array('id' => 'contest', 'label' => 'Văn nghệ Gấm Hoa', 'icon' => 'palette'),
        array('id' => 'award', 'label' => 'Trao giải', 'icon' => 'emoji_events'),
    );
} elseif ($isGala) {
    $filterTabs = array(
        array('id' => 'all', 'label' => 'Tất cả', 'icon' => 'list_alt'),
        array('id' => 'protocol', 'label' => 'Nghi thức & Tri ân', 'icon' => 'flag'),
        array('id' => 'music', 'label' => '3 Chương Nghệ thuật', 'icon' => 'theater_comedy'),
        array('id' => 'luckydraw', 'label' => 'Lucky Draw', 'icon' => 'featured_seasonal_and_gifts'),
        array('id' => 'dj', 'label' => 'DJ Party', 'icon' => 'headphones'),
    );
}
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => $active)); ?>

<main class="flex-grow-1 <?php echo $themeClass; ?>">

  <!-- ========================================================================
       1. HERO SECTION
       ======================================================================== -->
  <section class="prg-hero">
    <div class="prg-hero-sparkle prg-hero-sparkle-1"></div>
    <div class="prg-hero-sparkle prg-hero-sparkle-2"></div>
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-9 col-xl-8">
          <div class="prg-hero-badge">
            <span class="material-symbols-outlined" style="font-size:18px;"><?php echo $e($pageIcon); ?></span>
            <span><?php echo $e($pageEyebrow); ?></span>
          </div>

          <h1 class="prg-hero-title"><?php echo $e($pageTitle); ?></h1>
          <p class="prg-hero-desc"><?php echo $e($pageSubtitle); ?></p>

          <div class="prg-hero-meta">
            <div class="prg-meta-pill">
              <span class="material-symbols-outlined">calendar_month</span>
              <span>Ngày tổ chức: <strong><?php echo $e($dateValue); ?></strong></span>
            </div>
            <div class="prg-meta-pill">
              <span class="material-symbols-outlined">schedule</span>
              <span>Thời gian: <strong><?php echo $e($timeValue); ?></strong></span>
            </div>
            <div class="prg-meta-pill">
              <span class="material-symbols-outlined">location_on</span>
              <span>Địa điểm: <strong><?php echo $e($venueValue); ?></strong></span>
            </div>
            <div class="prg-meta-pill">
              <span class="material-symbols-outlined">styler</span>
              <span>Trang phục: <strong><?php echo $e($dressShort); ?></strong></span>
            </div>
          </div>

          <div class="prg-hero-actions">
            <a href="#timelineSection" class="prg-btn-primary">
              <span class="material-symbols-outlined" style="font-size:18px;">calendar_view_day</span>
              <span>Xem Timeline chương trình</span>
            </a>
            <a href="<?php echo $e($base); ?>/so-do-dia-diem" class="prg-btn-outline">
              <span class="material-symbols-outlined" style="font-size:18px;">map</span>
              <span>Sơ đồ Ballroom Hoa Lư</span>
            </a>
            <a href="<?php echo $e($base); ?>/agenda-tong-the" class="prg-btn-outline">
              <span class="material-symbols-outlined" style="font-size:18px;">calendar_month</span>
              <span>Agenda tổng thể</span>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-xl-4 d-none d-lg-block text-end">
          <div class="d-inline-flex flex-column align-items-center text-center p-4 rounded-4" style="background:rgba(255,255,255,0.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.18);box-shadow:0 12px 36px rgba(0,0,0,0.25);">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;background:rgba(255,255,255,0.15);color:var(--prg-gold);box-shadow:0 8px 24px rgba(0,0,0,0.15);">
              <span class="material-symbols-outlined" style="font-size:36px;"><?php echo $e($pageIcon); ?></span>
            </div>
            <span class="badge rounded-pill bg-white text-dark fw-bold px-3 py-1 mb-2" style="font-size:12px;">ĐẠI HỘI 2026</span>
            <div class="fw-bold text-white fs-6 mb-1">Mường Thanh Luxury Ninh Bình</div>
            <div class="small text-white-50">Sảnh chính Hoa Lư Ballroom</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================================================================
       2. PILLARS / HIGHLIGHTS SECTION
       ======================================================================== -->
  <?php if (!empty($pillars)): ?>
  <section class="container prg-pillars">
    <div class="row g-3">
      <?php foreach ($pillars as $idx => $pill): ?>
      <div class="col-12 col-md-4">
        <div class="prg-pillar-card">
          <div class="prg-pillar-icon">
            <span class="material-symbols-outlined"><?php echo $e($pill['icon']); ?></span>
          </div>
          <div>
            <span class="prg-pillar-eyebrow"><?php echo $e($pill['eyebrow']); ?></span>
            <h2 class="prg-pillar-title"><?php echo $e($pill['title']); ?></h2>
            <p class="prg-pillar-desc"><?php echo $e($pill['desc']); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ========================================================================
       3. TIMELINE & CHƯƠNG TRÌNH CHI TIẾT
       ======================================================================== -->
  <section class="container pb-5" id="timelineSection">

    <!-- Header phần Timeline & Thanh điều khiển -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:rgba(var(--prg-accent-rgb),0.12);color:var(--prg-accent);">
          <span class="material-symbols-outlined fs-5">schedule</span>
        </div>
        <h2 class="fw-black text-dark fs-4 mb-0">Timeline chương trình</h2>
        <span class="badge rounded-pill bg-light text-secondary border fw-bold px-2 py-1" id="prgVisibleCount">
          <?php echo count($program); ?> mốc hoạt động
        </span>
      </div>
      <div class="small text-muted">
        <span class="material-symbols-outlined fs-6 align-middle text-success">check_circle</span>
        <span>Lịch trình theo poster ban tổ chức</span>
      </div>
    </div>

    <!-- Toolbar: Bộ lọc danh mục + Tìm kiếm -->
    <div class="prg-toolbar">
      <?php if (!empty($filterTabs)): ?>
      <div class="prg-filters" id="prgFilters">
        <?php foreach ($filterTabs as $f): ?>
        <button type="button" class="prg-filter-btn<?php echo $f['id'] === 'all' ? ' is-active' : ''; ?>" data-filter="<?php echo $e($f['id']); ?>">
          <span class="material-symbols-outlined fs-6"><?php echo $e($f['icon']); ?></span>
          <span><?php echo $e($f['label']); ?></span>
        </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="prg-search-box">
        <span class="material-symbols-outlined prg-search-icon">search</span>
        <input type="text" class="prg-search-input" id="prgSearchInput" placeholder="Tìm tiết mục, ca sĩ, mốc giờ..." />
      </div>
    </div>

    <!-- KHỐI TIMELINE -->
    <?php if (!empty($program)): ?>
      <div class="prg-timeline-wrap">
        <div class="prg-timeline-spine" id="prgTimelineSpine">
          <?php
          $stt = 0;
          foreach ($program as $item):
              // Nếu là header Section (Chương 1, Chương 2, Chương 3 trong Gala)
              if (isset($item['section'])):
                  $secTitle = (string) $item['section'];
                  // Phân tách số chương nếu có
                  $secNo = '01';
                  if (preg_match('/Chương\s*(\d+)/i', $secTitle, $m)) {
                      $secNo = sprintf('%02d', (int) $m[1]);
                  }
          ?>
              <div class="prg-chapter-divider prg-tl-item-wrap" data-category="music">
                <div class="prg-chapter-card">
                  <div class="prg-chapter-left">
                    <span class="prg-chapter-num">CHƯƠNG <?php echo $e($secNo); ?></span>
                    <h3 class="prg-chapter-title"><?php echo $e($secTitle); ?></h3>
                  </div>
                  <div class="prg-chapter-artist">
                    <span class="material-symbols-outlined fs-6">mic_external_on</span>
                    <span>Đại tiệc nghệ thuật</span>
                  </div>
                </div>
              </div>
          <?php
                  continue;
              endif;

              $stt++;
              $time = (string) $val($item, 'time', '—');
              $title = (string) $val($item, 'title', 'Hoạt động');
              $sub = isset($item['sub']) && is_array($item['sub']) ? $item['sub'] : array();
              
              // Phân tích mốc giờ (bắt đầu - kết thúc)
              $timeParts = explode('-', $time);
              $timeStart = trim($timeParts[0]);
              $timeEnd = isset($timeParts[1]) ? trim($timeParts[1]) : '';
              
              // Phân loại hoạt động
              $cat = $categorizeItem($title, $sub);
              $isMissCard = ($cat['category'] === 'miss');
              $isContestCard = ($cat['category'] === 'contest');
              $isLuckyDraw = ($cat['category'] === 'luckydraw');
              $isDJ = ($cat['category'] === 'dj');
          ?>
              <div class="prg-tl-item <?php echo $cat['itemCls']; ?> prg-tl-item-wrap" data-category="<?php echo $e($cat['category']); ?>" data-text="<?php echo $e(mb_strtolower($title . ' ' . implode(' ', $sub), 'UTF-8')); ?>">
                
                <!-- Cột mốc giờ bên trái (Desktop) -->
                <div class="prg-tl-time">
                  <span class="prg-tl-time-start"><?php echo $e($timeStart); ?></span>
                  <?php if ($timeEnd !== ''): ?>
                    <span class="prg-tl-time-end"><?php echo $e($timeEnd); ?></span>
                  <?php endif; ?>
                </div>

                <!-- Nút mốc tròn trung tâm -->
                <div class="prg-tl-node">
                  <div class="prg-tl-dot" title="<?php echo $e($time); ?>">
                    <span class="material-symbols-outlined"><?php echo $e($cat['icon']); ?></span>
                  </div>
                </div>

                <!-- Thẻ nội dung bên phải -->
                <div class="prg-tl-content">
                  <div class="prg-card <?php echo $isMissCard ? 'prg-card--miss' : ''; ?> <?php echo $isContestCard ? 'prg-card--contest' : ''; ?> <?php echo $isLuckyDraw ? 'prg-card--luckydraw' : ''; ?> <?php echo $isDJ ? 'prg-card--dj' : ''; ?>">
                    
                    <!-- Mốc giờ trên mobile -->
                    <div class="prg-mobile-time">
                      <span class="material-symbols-outlined" style="font-size:14px;">schedule</span>
                      <span><?php echo $e($time); ?></span>
                    </div>

                    <div class="prg-card-header">
                      <span class="prg-tag <?php echo $cat['badgeCls']; ?>">
                        <span class="material-symbols-outlined"><?php echo $e($cat['icon']); ?></span>
                        <span><?php echo $e($cat['tag']); ?></span>
                      </span>

                      <?php if ($isDJ): ?>
                        <div class="prg-dj-waves" title="Live DJ Music">
                          <span class="prg-dj-bar"></span>
                          <span class="prg-dj-bar"></span>
                          <span class="prg-dj-bar"></span>
                          <span class="prg-dj-bar"></span>
                          <span class="prg-dj-bar"></span>
                        </div>
                      <?php endif; ?>
                    </div>

                    <h3 class="prg-card-title"><?php echo $e($title); ?></h3>

                    <!-- TRƯỜNG HỢP ĐẶC BIỆT 1: CHUNG KẾT MISS MƯỜNG THANH -->
                    <?php if ($isMissCard && !empty($sub)): ?>
                      <div class="prg-miss-stepper">
                        <?php 
                        $stepNo = 0;
                        foreach ($sub as $sText): 
                            $stepNo++;
                            $isGuest = (strpos(mb_strtolower($sText, 'UTF-8'), 'oplus') !== false);
                        ?>
                          <div class="prg-miss-step">
                            <span class="prg-step-badge"><?php echo $e(sprintf('%02d', $stepNo)); ?></span>
                            <div class="prg-step-text">
                              <?php if ($isGuest): ?>
                                <span class="badge rounded-pill bg-danger text-white px-2 py-1 me-1 small">Khách mời</span>
                              <?php endif; ?>
                              <span><?php echo $e($sText); ?></span>
                            </div>
                            <?php if ($isGuest): ?>
                              <span class="material-symbols-outlined text-danger fs-5">mic</span>
                            <?php elseif (strpos($sText, 'TOP 5') !== false): ?>
                              <span class="material-symbols-outlined text-warning fs-5">crown</span>
                            <?php else: ?>
                              <span class="material-symbols-outlined text-muted fs-6">navigate_next</span>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>

                    <!-- TRƯỜNG HỢP ĐẶC BIỆT 2: HỘI DIỄN VĂN NGHỆ GẤM HOA (6 ĐỘI THI) -->
                    <?php elseif ($isContestCard && !empty($sub)): ?>
                      <div class="prg-team-grid">
                        <?php 
                        $teamIdx = 0;
                        foreach ($sub as $teamText): 
                            $teamIdx++;
                            // Tách tên đội và tên tiết mục trong ngoặc đơn nếu có
                            $unit = $teamText;
                            $work = '';
                            if (preg_match('/^(.*?)\s*\((.*?)\)$/u', $teamText, $tm)) {
                                $unit = trim(str_replace('Đội thi -', '', $tm[1]));
                                $work = trim($tm[2]);
                            } else {
                                $unit = trim(str_replace('Đội thi -', '', $teamText));
                            }
                        ?>
                          <div class="prg-team-card">
                            <span class="prg-team-no"><?php echo $e(sprintf('%02d', $teamIdx)); ?></span>
                            <div class="flex-grow-1">
                              <div class="prg-team-unit"><?php echo $e($unit); ?></div>
                              <?php if ($work !== ''): ?>
                                <div class="prg-team-work">"<?php echo $e($work); ?>"</div>
                              <?php endif; ?>
                            </div>
                            <span class="material-symbols-outlined text-success fs-6">stars</span>
                          </div>
                        <?php endforeach; ?>
                      </div>

                    <!-- NỘI DUNG PHỤ THƯỜNG -->
                    <?php elseif (!empty($sub)): ?>
                      <ul class="list-unstyled mb-0 mt-2 d-flex flex-column gap-1">
                        <?php foreach ($sub as $sText): ?>
                          <li class="d-flex align-items-center gap-2 small text-secondary">
                            <span class="material-symbols-outlined text-primary fs-6">arrow_right</span>
                            <span><?php echo $e($sText); ?></span>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>

                  </div>
                </div>

              </div>
          <?php endforeach; ?>
        </div>
      </div>

    <!-- KHÔNG TÌM THẤY KẾT QUẢ KHI TÌM KIẾM -->
    <div class="card border rounded-4 shadow-sm p-5 text-center text-secondary d-none" id="prgEmptySearchResult">
      <span class="material-symbols-outlined mb-2 text-muted" style="font-size:48px;">search_off</span>
      <h3 class="fw-bold text-dark fs-5 mb-1">Không tìm thấy nội dung phù hợp</h3>
      <p class="mb-3 small">Vui lòng thử từ khóa khác hoặc bấm nút "Tất cả" bên dưới.</p>
      <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 mx-auto" id="prgResetSearchBtn">
        Xem tất cả mốc chương trình
      </button>
    </div>

    <?php elseif (!empty($matched)): ?>
      <!-- Fallback hiển thị danh sách từ Agenda -->
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden p-4">
        <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
          <span class="material-symbols-outlined fs-5">info</span>
          <span>Dữ liệu chương trình được đồng bộ tự động từ Agenda chính thức của Đại hội.</span>
        </div>
        <?php foreach ($matched as $item): 
            $time = $val($item, array('time', 'time_range', 'start_time'), '—');
            $endTime = $val($item, 'end_time', '');
            if ($endTime !== '' && strpos($time, '-') === false) {
                $time .= ' - ' . $endTime;
            }
            $title = $val($item, array('title', 'name'), 'Hoạt động');
            $place = $val($item, array('location', 'venue', 'place'), '');
            $desc = $val($item, array('description', 'note'), '');
        ?>
          <div class="d-flex gap-3 py-3 border-bottom">
            <span class="badge bg-light text-dark border fw-bold px-3 py-2" style="font-size:12px;height:fit-content;"><?php echo $e($time); ?></span>
            <div>
              <strong class="text-dark d-block fs-6 mb-1"><?php echo $e($title); ?></strong>
              <?php if ($place !== ''): ?>
                <div class="text-muted small d-flex align-items-center gap-1 mb-1">
                  <span class="material-symbols-outlined fs-6">location_on</span> <?php echo $e($place); ?>
                </div>
              <?php endif; ?>
              <?php if ($desc !== ''): ?>
                <p class="text-secondary small mb-0"><?php echo $e($desc); ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card border rounded-4 shadow-sm p-5 text-center text-secondary">
        <span class="material-symbols-outlined mb-2" style="font-size:48px;">event_upcoming</span>
        <p class="mb-0"><?php echo $e(isset($config['emptyText']) ? $config['emptyText'] : 'Chương trình chi tiết sẽ được Ban Tổ chức cập nhật.'); ?></p>
        <a href="<?php echo $e($base); ?>/agenda-tong-the" class="btn btn-sm btn-light border rounded-pill px-3 mt-3 mx-auto d-inline-flex align-items-center gap-1" style="width:fit-content;">
          <span class="material-symbols-outlined fs-6">calendar_month</span> Xem Agenda tổng thể
        </a>
      </div>
    <?php endif; ?>

    <!-- ========================================================================
         4. LƯU Ý DÀNH CHO QUÝ ĐẠI BIỂU & TRANG PHỤC
         ======================================================================== -->
    <div class="prg-guidelines-card">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="material-symbols-outlined fs-4" style="color:var(--prg-accent);">help_center</span>
        <h3 class="fw-bold text-dark fs-5 mb-0">Lưu ý tham dự chương trình</h3>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <div class="prg-guideline-item">
            <div class="prg-guideline-icon">
              <span class="material-symbols-outlined">badge</span>
            </div>
            <div>
              <strong class="text-dark small d-block mb-1">Đeo thẻ Đại biểu</strong>
              <span class="text-secondary small">Quý Đại biểu vui lòng luôn đeo thẻ Đại biểu Mường Thanh 2026 khi vào sảnh tiệc Hoa Lư Ballroom.</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="prg-guideline-item">
            <div class="prg-guideline-icon">
              <span class="material-symbols-outlined">checkroom</span>
            </div>
            <div>
              <strong class="text-dark small d-block mb-1">Quy định trang phục</strong>
              <span class="text-secondary small"><?php echo $e($dressDetail); ?></span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="prg-guideline-item">
            <div class="prg-guideline-icon">
              <span class="material-symbols-outlined">alarm</span>
            </div>
            <div>
              <strong class="text-dark small d-block mb-1">Thời gian đón khách</strong>
              <span class="text-secondary small">Sảnh mở cửa đón khách từ 18:30. Quý Đại biểu nên có mặt sớm để check-in và chụp ảnh lưu niệm tại Backdrop.</span>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="prg-guideline-item">
            <div class="prg-guideline-icon">
              <span class="material-symbols-outlined">table_restaurant</span>
            </div>
            <div>
              <strong class="text-dark small d-block mb-1">Sơ đồ bàn tiệc</strong>
              <span class="text-secondary small">Chỗ ngồi được bố trí theo từng đơn vị và đoàn đại biểu. Vui lòng xem sơ đồ hướng dẫn tại cửa Ballroom.</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================================================
         5. LIÊN KẾT NHANH ĐIỀU HƯỚNG
         ======================================================================== -->
    <div class="card border-0 rounded-4 shadow-sm p-4 mt-4 text-center" style="background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);">
      <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
        <span class="fw-bold text-dark small">Khám phá các nội dung khác của Đại hội:</span>
        <a href="<?php echo $e($base); ?>/agenda-tong-the" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
          <span class="material-symbols-outlined fs-6 align-middle">calendar_month</span> Agenda 3 ngày
        </a>
        <a href="<?php echo $e($base); ?>/lich-thi-the-thao" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
          <span class="material-symbols-outlined fs-6 align-middle">emoji_events</span> Lịch thi đấu thể thao
        </a>
        <a href="<?php echo $e($base); ?>/lich-tham-quan" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
          <span class="material-symbols-outlined fs-6 align-middle">tour</span> Tour Tràng An
        </a>
        <a href="<?php echo $e($base); ?>/so-tay-dai-hoi" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">
          <span class="material-symbols-outlined fs-6 align-middle">menu_book</span> Sổ tay Đại hội
        </a>
      </div>
    </div>

  </section>
</main>

<!-- Nạp Stylesheet & Script riêng cho trang chương trình -->
<link rel="stylesheet" href="<?php echo $dh; ?>/program.css?v=<?php echo @filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/program.css') ?: time(); ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {
  var filterBtns = document.querySelectorAll('.prg-filter-btn');
  var searchInput = document.getElementById('prgSearchInput');
  var items = document.querySelectorAll('.prg-tl-item-wrap');
  var visibleCountBadge = document.getElementById('prgVisibleCount');
  var emptyResult = document.getElementById('prgEmptySearchResult');
  var resetSearchBtn = document.getElementById('prgResetSearchBtn');

  var currentFilter = 'all';
  var currentSearch = '';

  function applyFilter() {
    var count = 0;
    var q = currentSearch.trim().toLowerCase();

    items.forEach(function (el) {
      var itemCategory = el.getAttribute('data-category') || '';
      var itemText = el.getAttribute('data-text') || el.innerText.toLowerCase();

      var matchCat = (currentFilter === 'all') || (itemCategory === currentFilter);
      var matchSearch = (q === '') || (itemText.indexOf(q) !== -1);

      if (matchCat && matchSearch) {
        el.classList.remove('is-hidden');
        count++;
      } else {
        el.classList.add('is-hidden');
      }
    });

    if (visibleCountBadge) {
      visibleCountBadge.textContent = count + ' mốc hoạt động';
    }

    if (emptyResult) {
      if (count === 0 && items.length > 0) {
        emptyResult.classList.remove('d-none');
      } else {
        emptyResult.classList.add('d-none');
      }
    }
  }

  // Click chọn bộ lọc
  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterBtns.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      currentFilter = btn.getAttribute('data-filter') || 'all';
      applyFilter();
    });
  });

  // Tìm kiếm tức thì
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentSearch = this.value;
      applyFilter();
    });
  }

  // Reset tìm kiếm
  if (resetSearchBtn) {
    resetSearchBtn.addEventListener('click', function () {
      if (searchInput) searchInput.value = '';
      currentSearch = '';
      currentFilter = 'all';
      filterBtns.forEach(function (b) {
        if (b.getAttribute('data-filter') === 'all') {
          b.classList.add('is-active');
        } else {
          b.classList.remove('is-active');
        }
      });
      applyFilter();
    });
  }
});
</script>

<?php 
// Footer chuẩn của theme Đại hội
$this->renderPartial('_site_footer', array('event' => $event)); 
?>
