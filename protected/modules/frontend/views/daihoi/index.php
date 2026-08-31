<?php
/**
 * Trang Website công khai Đại hội Mường Thanh.
 * Dữ liệu lấy qua API: event (/api/events), news (/api/admin/news),
 * các khối tổng hợp/realtime (/api/daihoi/*) - truyền từ DaihoiController.
 *
 * @var array $event
 * @var array $stats
 * @var array $contents
 * @var array $agenda
 * @var array $liveMatches
 * @var array $recentMatches
 * @var array $rankings
 * @var array $news
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
// Nguồn: /api/events. Field fallback hỗ trợ thêm cấu trúc landing /api/daihoi/event
// (slogan, destination, duration_text, hero_description, cover_image, mascot_image...).
$eventName = $val($event, array('name', 'title'), 'ĐẠI HỘI MƯỜNG THANH 2026');
$eventSlogan = $val($event, array('slogan', 'subtitle'), 'HỘI TỤ BẢN SẮC – DẪN DẮT TƯƠNG LAI');
$eventDestination = $val($event, array('destination', 'location', 'venue', 'place', 'city'), 'Ninh Bình');
$eventLocation = $val($event, array('location', 'venue', 'place'), $eventDestination . ' · Việt Nam');
$heroDesc = $val($event, array('hero_description', 'description'), 'Hành trình kết nối bản sắc, con người và những giá trị bền vững.');
$organizer = $val($event, array('organizer'), 'Hệ thống Mường Thanh Hospitality');
$coverImage = $val($event, array('cover_image', 'cover', 'banner'), '');
$mascotImage = $val($event, array('mascot_image', 'mascot'), '');
$mascotLink = $val($event, array('mascot_link'), '');
$fromDate = $val($event, array('from_date', 'start_date', 'starts_at'), '');
$toDate = $val($event, array('to_date', 'end_date', 'ends_at'), '');

// Thời lượng: ưu tiên duration_text, nếu không có thì tính từ from_date - to_date
$eventDuration = $val($event, array('duration_text', 'duration'), '');
if ($eventDuration === '') {
    $dDays = (int) $val($event, array('duration_days'), 0);
    $dNights = (int) $val($event, array('duration_nights'), 0);
    if ($dDays > 0) {
        $eventDuration = sprintf('%02d ngày %02d đêm', $dDays, $dNights);
    }
}
if ($eventDuration === '' && $fromDate !== '' && $toDate !== '') {
    $d1 = strtotime($fromDate);
    $d2 = strtotime($toDate);
    if ($d1 && $d2 && $d2 >= $d1) {
        $days = (int) round(($d2 - $d1) / 86400) + 1;
        $eventDuration = sprintf('%02d ngày %02d đêm', $days, max(0, $days - 1));
    }
}
if ($eventDuration === '') {
    $eventDuration = '04 ngày 03 đêm';
}

// Năm sự kiện
$eventYear = $val($event, array('year'), '');
if ($eventYear === '') {
    $month = $val($event, array('event_month'), '');
    if ($month !== '' && preg_match('/(\d{4})/', $month, $mm)) {
        $eventYear = $mm[1];
    }
}
if ($eventYear === '' && $fromDate !== '') {
    $eventYear = date('Y', strtotime($fromDate));
}
if ($eventYear === '') {
    $eventYear = '2026';
}

// ----- Mốc đếm ngược -----
// Ưu tiên countdown_seconds (API landing) -> mốc = now + seconds.
// Nếu không có thì suy từ ngày khai mạc (from_date).
$countdownSeconds = (int) $val($event, array('countdown_seconds'), 0);
if ($countdownSeconds > 0) {
    $target = date('c', time() + $countdownSeconds);
} elseif ($fromDate !== '' && ctype_digit((string) $fromDate)) {
    $target = date('c', (int) $fromDate);
} elseif ($fromDate !== '') {
    $ts = strtotime($fromDate);
    // Nếu chỉ có ngày (không giờ) thì mặc định 08:00
    $target = date('Y-m-d\TH:i:s', $ts ? $ts + (date('H', $ts) == 0 ? 8 * 3600 : 0) : strtotime('2026-10-16 08:00:00'));
} else {
    $target = '2026-10-16T08:00:00';
}
?>
<div id="daihoi-root" data-base-url="<?php echo $base; ?>" data-countdown-target="<?php echo $e($target); ?>">
  <main id="public-view">

    <header class="public-header">
      <div class="container public-header-inner">
        <a href="<?php echo $base; ?>/" class="brand-lockup">
          <div class="brand-symbol">MT</div>
          <div class="brand-copy">
            <strong><?php echo $e($eventName); ?></strong>
            <small><?php echo $e($eventLocation); ?></small>
          </div>
        </a>

        <nav class="desktop-nav">
          <a href="#gioi-thieu">Giới thiệu</a>
          <a href="#noi-dung">Nội dung Đại hội</a>
          <a href="#lich-trinh">Lịch trình</a>
          <a href="#ket-qua">Kết quả</a>
          <a href="#tin-tuc">Tin tức</a>
        </nav>

        <a class="btn btn-light" href="<?php echo $base; ?>/login">Đăng nhập nội bộ</a>
      </div>
    </header>

    <section class="hero">
      <div class="container hero-grid">
        <div>
          <div class="hero-kicker">✦ <?php echo $e($eventLocation); ?></div>

          <h1 class="hero-title">
            ĐẠI HỘI<br>
            <span class="gradient-text">MƯỜNG THANH</span><br>
            <?php echo $e($eventYear); ?>
          </h1>

          <p class="hero-slogan"><?php echo $e($eventSlogan); ?></p>

          <div class="hero-meta">
            <span><?php echo $e($eventDuration); ?></span>
            <span><?php echo $e($organizer); ?></span>
            <span><?php echo $e($eventLocation); ?></span>
          </div>

          <div class="hero-actions">
            <a href="#noi-dung" class="btn btn-primary">Khám phá Đại hội →</a>
            <a href="#lich-trinh" class="btn btn-light">Xem lịch trình</a>
          </div>

          <div class="countdown-wrap">
            <div class="countdown">
              <div class="count-item"><strong id="days">--</strong><small>Ngày</small></div>
              <div class="count-item"><strong id="hours">--</strong><small>Giờ</small></div>
              <div class="count-item"><strong id="minutes">--</strong><small>Phút</small></div>
              <div class="count-item"><strong id="seconds">--</strong><small>Giây</small></div>
            </div>
          </div>
        </div>

        <div class="hero-visual">
          <div class="visual-card"<?php echo $coverImage ? ' style="background-image:linear-gradient(145deg, rgba(28,110,232,.82), rgba(116,88,234,.78)), url(\'' . $e($coverImage) . '\');background-size:cover;background-position:center;"' : ''; ?>>
            <div class="visual-top">
              <span class="visual-event-code">Official Event Portal</span>
              <span class="visual-year"><?php echo $e($eventYear); ?></span>
            </div>
            <?php if ($mascotImage): ?>
              <?php if ($mascotLink): ?><a href="<?php echo $e($mascotLink); ?>" target="_blank" rel="noopener"><?php endif; ?>
              <img class="mascot-placeholder" src="<?php echo $e($mascotImage); ?>" alt="Linh vật Đại hội" style="object-fit:contain;background:transparent;border:0;" />
              <?php if ($mascotLink): ?></a><?php endif; ?>
            <?php else: ?>
              <div class="mascot-placeholder">Đặt linh vật chính thức tại đây</div>
            <?php endif; ?>
            <div class="visual-content">
              <h2><?php echo nl2br($e($eventDestination)); ?><br>miền di sản</h2>
              <p><?php echo $e($heroDesc); ?></p>
            </div>
          </div>
          <div class="floating-card float-one">
            <small>Thời lượng</small>
            <strong><?php echo $e($eventDuration); ?></strong>
          </div>
          <div class="floating-card float-two">
            <small>Điểm đến</small>
            <strong><?php echo $e($eventDestination); ?></strong>
          </div>
        </div>
      </div>
    </section>

    <?php // ----- Thống kê -----
    $statItems = array();
    if (!empty($stats)) {
        // Hỗ trợ cả dạng mảng danh sách [{value,label}] và dạng object {key:value}
        if (isset($stats[0]) && is_array($stats[0])) {
            foreach ($stats as $s) {
                $statItems[] = array($val($s, array('value', 'count'), ''), $val($s, array('label', 'name'), ''));
            }
        } else {
            $statItems[] = array($val($stats, array('days', 'total_days'), '04'), 'Ngày hội tụ toàn hệ thống');
            $statItems[] = array($val($stats, array('contents', 'total_contents'), '05'), 'Nội dung trọng điểm');
            $statItems[] = array($val($stats, array('sports', 'total_sports'), '08'), 'Môn thể thao thi đấu');
            $statItems[] = array($val($stats, array('units', 'total_units'), '62'), 'Đơn vị tham gia');
        }
    }
    if (empty($statItems)) {
        $statItems = array(
            array('04', 'Ngày hội tụ toàn hệ thống'),
            array('05', 'Nội dung trọng điểm'),
            array('08', 'Môn thể thao thi đấu'),
            array('01', 'Hành trình giàu bản sắc'),
        );
    }
    ?>
    <section class="section-tight" id="gioi-thieu">
      <div class="container">
        <div class="stats-grid">
          <?php foreach ($statItems as $s): ?>
          <div class="stat-card">
            <strong><?php echo $e($s[0]); ?></strong>
            <span><?php echo $e($s[1]); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <?php // ----- Nội dung Đại hội -----
    $themes = array('skills', 'sports', 'miss', 'gala', 'race');
    ?>
    <section class="section" id="noi-dung">
      <div class="container">
        <span class="eyebrow">Nội dung Đại hội</span>
        <h2 class="section-title">Những hành trình<br><span class="gradient-text">đáng mong đợi</span></h2>
        <p class="section-subtitle">
          Nơi hội tụ năng lực nghề nghiệp, tinh thần thể thao, vẻ đẹp văn hóa và dấu ấn Mường Thanh trên miền di sản Ninh Bình.
        </p>

        <div class="program-grid">
          <?php if (!empty($contents)): $i = 0; ?>
            <?php foreach ($contents as $c): $i++;
              $size = $i <= 2 ? 'large' : 'medium';
              $theme = $themes[($i - 1) % count($themes)];
            ?>
            <article class="program-card <?php echo $size; ?> <?php echo $theme; ?>">
              <div class="program-inner">
                <span class="program-number"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></span>
                <h3><?php echo $e($val($c, array('name', 'title'), '')); ?></h3>
                <p><?php echo $e($val($c, array('description', 'summary', 'short_description'), '')); ?></p>
              </div>
            </article>
            <?php endforeach; ?>
          <?php else: ?>
            <article class="program-card large skills"><div class="program-inner"><span class="program-number">01</span><h3>Mường Thanh Pro Skills</h3><p>Thi nghiệp vụ khách sạn, lan tỏa chuẩn mực phục vụ và tinh thần nghề nghiệp.</p></div></article>
            <article class="program-card large sports"><div class="program-inner"><span class="program-number">02</span><h3>Mường Thanh Sports Festival</h3><p>08 môn thể thao với tinh thần đoàn kết, bền bỉ và bứt phá.</p></div></article>
            <article class="program-card medium miss"><div class="program-inner"><span class="program-number">03</span><h3>Hương Sắc Mường Thanh</h3><p>Tôn vinh vẻ đẹp, sự tự tin và bản sắc riêng của các đơn vị.</p></div></article>
            <article class="program-card medium gala"><div class="program-inner"><span class="program-number">04</span><h3>Việt Nam Gấm Hoa & Gala</h3><p>Không gian nghệ thuật, kết nối và tôn vinh những dấu ấn nổi bật.</p></div></article>
            <article class="program-card medium race"><div class="program-inner"><span class="program-number">05</span><h3>Heritage Race</h3><p>Giữa miền di sản, mỗi bước chạy là một hành trình kết nối.</p></div></article>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <?php // ----- Lịch trình -----
    // Gom agenda theo ngày (day_index / day). Nếu API trả sẵn theo ngày thì dùng trực tiếp.
    $days = array();
    if (!empty($agenda)) {
        foreach ($agenda as $a) {
            $dayKey = $val($a, array('day_index', 'day', 'day_number'), null);
            if ($dayKey === null) {
                // Danh sách các ngày ở cấp cao nhất
                $days[] = array(
                    'label' => $val($a, array('day_label', 'label'), ''),
                    'title' => $val($a, array('title', 'name'), ''),
                    'desc' => $val($a, array('description', 'summary'), ''),
                    'active' => (bool) $val($a, array('is_active', 'active'), false),
                );
            } else {
                if (!isset($days[$dayKey])) {
                    $days[$dayKey] = array('label' => 'Ngày ' . str_pad($dayKey, 2, '0', STR_PAD_LEFT), 'title' => '', 'desc' => array(), 'active' => false);
                }
                $days[$dayKey]['desc'][] = $val($a, array('title', 'name'), '');
            }
        }
        // Chuẩn hoá desc dạng mảng thành chuỗi
        foreach ($days as &$d) {
            if (is_array($d['desc'])) {
                $d['desc'] = implode(' · ', array_filter($d['desc']));
            }
        }
        unset($d);
    }
    ?>
    <section class="section" id="lich-trinh">
      <div class="container">
        <span class="eyebrow">Lịch trình tổng quan</span>
        <h2 class="section-title"><?php echo $e($eventDuration); ?><br><span class="gradient-text">trọn vẹn trải nghiệm</span></h2>

        <div class="timeline">
          <?php if (!empty($days)): $idx = 0; ?>
            <?php foreach ($days as $d): $idx++; ?>
            <article class="day-card <?php echo !empty($d['active']) ? 'active' : ''; ?>">
              <small><?php echo $e($d['label'] !== '' ? $d['label'] : 'Ngày ' . str_pad($idx, 2, '0', STR_PAD_LEFT)); ?></small>
              <h3><?php echo $e($d['title']); ?></h3>
              <p><?php echo $e($d['desc']); ?></p>
            </article>
            <?php endforeach; ?>
          <?php else: ?>
            <article class="day-card active"><small>Ngày 01</small><h3>Hội tụ</h3><p>Đón đoàn · Check-in · Họp trưởng đoàn · Lễ dâng hương · Khai mạc</p></article>
            <article class="day-card"><small>Ngày 02</small><h3>Bứt phá</h3><p>Thi nghiệp vụ · Thi đấu thể thao · Các nội dung vòng loại</p></article>
            <article class="day-card"><small>Ngày 03</small><h3>Tỏa sáng</h3><p>Heritage Race · Chung kết · Việt Nam Gấm Hoa · Hương Sắc Mường Thanh</p></article>
            <article class="day-card"><small>Ngày 04</small><h3>Dấu ấn</h3><p>Gala Dinner · Vinh danh · Trao giải · Khép lại hành trình</p></article>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <?php // ----- Kết quả -----
    $renderMatch = function ($m) use ($val, $e) {
        $status = $val($m, array('status'), '');
        $badges = array(
            'live' => '<span class="status live">LIVE</span>',
            'done' => '<span class="status done">Kết thúc</span>',
            'upcoming' => '<span class="status upcoming">Sắp đấu</span>',
        );
        $score = $val($m, array('score'), '');
        if ($score === '') {
            $score = $val($m, array('time', 'kickoff_time'), '');
        }
        echo '<div class="match-row">';
        echo '<span class="team">' . $e($val($m, array('home_name', 'home', 'team_a'), '')) . '</span>';
        echo '<span class="score">' . $e($score) . '</span>';
        echo '<span class="team right">' . $e($val($m, array('away_name', 'away', 'team_b'), '')) . '</span>';
        echo isset($badges[$status]) ? $badges[$status] : '';
        echo '</div>';
    };
    ?>
    <section class="section" id="ket-qua">
      <div class="container">
        <span class="eyebrow">Cập nhật mới nhất</span>
        <h2 class="section-title">Theo dõi kết quả<br><span class="gradient-text">theo thời gian thực</span></h2>

        <div class="results-grid">
          <div class="surface-card">
            <div class="card-head">
              <h3>Kết quả nổi bật</h3>
              <span class="status live">LIVE</span>
            </div>
            <div class="card-body">
              <div class="match-list" id="live-match-list">
                <?php
                $matches = !empty($liveMatches) ? $liveMatches : $recentMatches;
                if (!empty($matches)):
                    foreach ($matches as $m) {
                        $renderMatch($m);
                    }
                else: ?>
                  <p style="color:var(--muted);font-size:13px;">Chưa có trận đấu nào được cập nhật.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="surface-card">
            <div class="card-head">
              <h3>Bảng xếp hạng tạm thời</h3>
              <a href="#" style="font-size:11px;font-weight:800;color:var(--brand-blue)">Xem đầy đủ →</a>
            </div>
            <div class="card-body">
              <div class="rank-list" id="rank-list">
                <?php if (!empty($rankings)): $ri = 0; ?>
                  <?php foreach ($rankings as $r): $ri++; ?>
                  <div class="rank-row">
                    <span class="rank-index"><?php echo $ri; ?></span>
                    <span class="rank-name"><?php echo $e($val($r, array('name', 'org_name', 'unit_name'), '')); ?></span>
                    <span class="rank-points"><?php echo $e($val($r, array('points', 'score'), 0)); ?> điểm</span>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p style="color:var(--muted);font-size:13px;">Chưa có dữ liệu xếp hạng.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php // ----- Tin tức ----- ?>
    <section class="section" id="tin-tuc">
      <div class="container">
        <span class="eyebrow">Tin tức Đại hội</span>
        <h2 class="section-title">Những câu chuyện<br><span class="gradient-text">đang được viết tiếp</span></h2>

        <div class="news-grid">
          <?php if (!empty($news)): ?>
            <?php foreach ($news as $n):
              $thumb = $val($n, array('thumbnail', 'image', 'cover', 'thumbnail_url'), '');
              $newsId = $val($n, array('id'), '');
              $href = $newsId !== '' ? $base . '/daihoi/index#tin-' . $e($newsId) : '#';
            ?>
            <a class="news-card" href="<?php echo $href; ?>">
              <div class="news-thumb"<?php echo $thumb ? ' style="background-image:url(\'' . $e($thumb) . '\');background-size:cover;background-position:center;"' : ''; ?>></div>
              <div class="news-copy">
                <small><?php echo $e($val($n, array('category_name', 'category'), 'Tin tức')); ?></small>
                <h3><?php echo $e($val($n, array('title', 'name'), '')); ?></h3>
              </div>
            </a>
            <?php endforeach; ?>
          <?php else: ?>
            <article class="news-card"><div class="news-thumb"></div><div class="news-copy"><small>Thông báo BTC</small><h3>Sẵn sàng cho hành trình hội tụ tại miền di sản Ninh Bình</h3></div></article>
            <article class="news-card"><div class="news-thumb"></div><div class="news-copy"><small>Sports Festival</small><h3>Các đơn vị tích cực chuẩn bị cho 08 nội dung thể thao</h3></div></article>
            <article class="news-card"><div class="news-thumb"></div><div class="news-copy"><small>Heritage Race</small><h3>Khám phá cung đường chạy giữa thiên nhiên và di sản Ninh Bình</h3></div></article>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <footer class="public-footer">
      <div class="container footer-inner">
        <div class="brand-lockup">
          <div class="brand-symbol">MT</div>
          <div class="brand-copy">
            <strong><?php echo $e($eventName); ?></strong>
            <small><?php echo $e($eventSlogan); ?></small>
          </div>
        </div>
        <p class="footer-copy">
          Cổng thông tin chính thức phục vụ truyền thông, cập nhật lịch trình và kết quả Đại hội Mường Thanh 2026 tại Ninh Bình.
        </p>
      </div>
    </footer>

    <nav class="mobile-bottom-nav">
      <a class="active" href="#gioi-thieu" style="text-align:center;color:#fff;"><span>⌂</span>Trang chủ</a>
      <a href="#lich-trinh" style="text-align:center;"><span>◴</span>Lịch trình</a>
      <a href="#ket-qua" style="text-align:center;"><span>⚡</span>Kết quả</a>
      <a href="#tin-tuc" style="text-align:center;"><span>✦</span>Tin tức</a>
      <a href="<?php echo $base; ?>/login" style="text-align:center;"><span>●</span>Tài khoản</a>
    </nav>
  </main>
</div>
