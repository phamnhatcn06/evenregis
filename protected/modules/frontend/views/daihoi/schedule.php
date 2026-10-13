<?php
/**
 * Trang Lịch thi đấu & Kết quả thể thao Đại hội.
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
$e = function ($s) { return CHtml::encode($s); };
$eventName = $val($event, array('name', 'title'), 'Đại hội Mường Thanh 2026');

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

$initials = function ($name) {
    $clean = preg_replace('/^(Liên quân\s+|Đội\s+)/iu', '', trim((string) $name));
    $parts = preg_split('/[\s\-_]+/u', trim($clean));
    $s = '';
    foreach ($parts as $p) {
        if ($p !== '' && mb_strlen($s) < 3) {
            $s .= mb_substr($p, 0, 1, 'UTF-8');
        }
    }
    return $s !== '' ? mb_strtoupper($s, 'UTF-8') : 'MT';
};
?>

<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'schedule')); ?>

<main class="flex-grow-1 bg-slate-50 py-4 py-lg-5" id="schedule-page-root">
  <div class="container">

    <!-- HERO HEADER & THỐNG KÊ GIẢI ĐẤU -->
    <div class="schedule-hero-card mb-4 p-4 p-md-5 rounded-4 shadow-sm position-relative overflow-hidden">
      <div class="row align-items-center position-relative z-1">
        <div class="col-lg-7 mb-3 mb-lg-0">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white/20 text-white small fw-bold mb-2 backdrop-blur">
            <span class="material-symbols-outlined fs-6">sports</span>
            Đại hội Thể dục Thể thao Mường Thanh 2026
          </div>
          <h1 class="display-6 fw-black text-white mb-2 tracking-tight">
            Lịch Thi Đấu &amp; Kết Quả
          </h1>
          <p class="text-white-80 fs-6 mb-0 max-w-2xl">
            Cập nhật lịch thi đấu, bảng xếp hạng và tỷ số các bộ môn thi đấu chính thức theo thời gian thực.
          </p>
        </div>

        <div class="col-lg-5">
          <div class="row g-2 text-center">
            <div class="col-6 col-sm-3 col-lg-6">
              <div class="schedule-stat-box p-3 rounded-3 bg-white/10 backdrop-blur border border-white/15 text-white">
                <div class="fs-3 fw-black text-warning"><?php echo (int) $stats['total_matches']; ?></div>
                <div class="small text-white-70 fw-medium">Tổng số trận</div>
              </div>
            </div>
            <div class="col-6 col-sm-3 col-lg-6">
              <div class="schedule-stat-box p-3 rounded-3 bg-white/10 backdrop-blur border border-white/15 text-white">
                <div class="fs-3 fw-black text-danger d-flex align-items-center justify-content-center gap-1">
                  <?php if ($stats['live_matches'] > 0): ?>
                    <span class="pulse-indicator"></span>
                  <?php endif; ?>
                  <?php echo (int) $stats['live_matches']; ?>
                </div>
                <div class="small text-white-70 fw-medium">Đang diễn ra</div>
              </div>
            </div>
            <div class="col-6 col-sm-3 col-lg-6">
              <div class="schedule-stat-box p-3 rounded-3 bg-white/10 backdrop-blur border border-white/15 text-white">
                <div class="fs-3 fw-black text-success"><?php echo (int) $stats['completed_matches']; ?></div>
                <div class="small text-white-70 fw-medium">Đã kết thúc</div>
              </div>
            </div>
            <div class="col-6 col-sm-3 col-lg-6">
              <div class="schedule-stat-box p-3 rounded-3 bg-white/10 backdrop-blur border border-white/15 text-white">
                <div class="fs-3 fw-black text-cyan"><?php echo (int) $stats['total_sports']; ?></div>
                <div class="small text-white-70 fw-medium">Nội dung thi</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Background Glow Effect -->
      <div class="hero-glow-1"></div>
      <div class="hero-glow-2"></div>
    </div>

    <!-- THANH CHỌN MÔN THI ĐẤU (SPORTS TABS) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
      <div class="p-3 bg-white border-bottom">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-uppercase fw-bold text-muted small d-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6 text-primary">category</span> Chọn môn thi đấu
          </span>
          <span class="badge bg-light text-secondary border rounded-pill">
            <?php echo count($sportsList); ?> bộ môn
          </span>
        </div>

        <div class="sports-nav-scroller d-flex gap-2 pb-1 overflow-x-auto">
          <button type="button"
                  class="sport-filter-btn btn btn-sm rounded-pill fw-bold text-nowrap d-flex align-items-center gap-2 px-3 py-2 <?php echo empty($selectedSportId) ? 'active btn-primary' : 'btn-light'; ?>"
                  data-sport-id="">
            <span class="material-symbols-outlined fs-6">all_inclusive</span>
            <span>Tất cả môn</span>
            <span class="badge bg-white/20 text-white rounded-pill px-2 py-0 ms-1 small"><?php echo count($allMatches); ?></span>
          </button>

          <?php foreach ($sportsList as $sp): ?>
            <?php
            $isActive = ($selectedSportId == $sp['id']);
            $btnCls = $isActive ? 'active btn-primary' : 'btn-light';
            $mCount = $sp['total_matches'];
            ?>
            <button type="button"
                    class="sport-filter-btn btn btn-sm rounded-pill fw-bold text-nowrap d-flex align-items-center gap-2 px-3 py-2 <?php echo $btnCls; ?>"
                    data-sport-id="<?php echo $sp['id']; ?>"
                    data-sport-name="<?php echo $e($sp['name']); ?>">
              <span class="material-symbols-outlined fs-6 text-primary"><?php echo $e($sp['icon']); ?></span>
              <span><?php echo $e($sp['name']); ?></span>
              <?php if ($mCount > 0): ?>
                <span class="badge <?php echo $isActive ? 'bg-white text-primary' : 'bg-primary text-white'; ?> rounded-pill px-2 py-0 ms-1" style="font-size:10.5px;">
                  <?php echo $mCount; ?>
                </span>
              <?php endif; ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- THANH TÌM KIẾM VÀ LỌC TRẠNG THÁI -->
      <div class="p-3 bg-slate-50 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <!-- Lọc trạng thái -->
        <div class="d-flex flex-wrap align-items-center gap-2 status-filter-group">
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter-btn active" data-status="">
            Tất cả (<?php echo count($allMatches); ?>)
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger rounded-pill status-filter-btn d-flex align-items-center gap-1" data-status="live">
            <span class="pulse-indicator-sm"></span> Trực tiếp (<?php echo (int) $stats['live_matches']; ?>)
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter-btn" data-status="completed">
            Đã kết thúc (<?php echo (int) $stats['completed_matches']; ?>)
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary rounded-pill status-filter-btn" data-status="scheduled">
            Đã xếp lịch (<?php echo (int) $stats['scheduled_matches']; ?>)
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter-btn" data-status="upcoming">
            Sắp diễn ra
          </button>
        </div>

        <!-- Ô tìm kiếm nhanh -->
        <div class="search-box position-relative" style="min-width: 260px; max-width: 380px;">
          <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-6">search</span>
          <input type="text"
                 id="schedule-search-input"
                 class="form-control form-control-sm rounded-pill ps-5 pe-4 py-2 border-slate-200"
                 placeholder="Tìm theo tên đội, VĐV, sân đấu...">
          <button type="button"
                  id="btn-clear-search"
                  class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 p-0 text-muted"
                  style="display:none;">
            <span class="material-symbols-outlined fs-6">close</span>
          </button>
        </div>
      </div>
    </div>

    <!-- KHỐI 1: TRẬN ĐẤU ĐANG DIỄN RA (NẾU CÓ) -->
    <?php if (!empty($tournament['liveMatches'])): ?>
      <div class="mb-4 live-matches-section">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-2 shadow-sm">
            <span class="spinner-grow spinner-grow-sm" style="width:8px;height:8px;"></span> Trực tiếp
          </span>
          <h2 class="fw-bold text-dark fs-5 mb-0">Các trận đang thi đấu sôi nổi</h2>
        </div>

        <div class="row g-3">
          <?php foreach ($tournament['liveMatches'] as $m): ?>
            <?php
            $ta = $m['team_a']['name'];
            $tb = $m['team_b']['name'];
            $sa = $m['team_a']['score'] !== null ? $m['team_a']['score'] : '0';
            $sb = $m['team_b']['score'] !== null ? $m['team_b']['score'] : '0';
            ?>
            <div class="col-12 col-md-6 col-xl-4">
              <div class="live-match-card card h-100 border-danger border-2 rounded-4 shadow-sm bg-white overflow-hidden">
                <div class="p-3 bg-danger text-white d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined fs-5"><?php echo $e($m['sport_icon']); ?></span>
                    <span class="fw-bold small"><?php echo $e($m['sport_name']); ?> • <?php echo $e($m['round']); ?></span>
                  </div>
                  <span class="badge bg-white text-danger fw-black rounded-pill px-2 py-1 small">LIVE</span>
                </div>

                <div class="p-3">
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2 bg-slate-50 border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold avatar-initials"
                           style="width:34px;height:34px;font-size:12px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);">
                        <?php echo $e($initials($ta)); ?>
                      </div>
                      <span class="fw-bold text-dark small"><?php echo $e($ta); ?></span>
                    </div>
                    <span class="fs-3 fw-black text-primary font-monospace px-2"><?php echo $e($sa); ?></span>
                  </div>

                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-slate-50 border-start border-4 border-secondary">
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold avatar-initials"
                           style="width:34px;height:34px;font-size:12px;background:linear-gradient(135deg,#475569,#64748b);">
                        <?php echo $e($initials($tb)); ?>
                      </div>
                      <span class="fw-bold text-dark small"><?php echo $e($tb); ?></span>
                    </div>
                    <span class="fs-3 fw-black text-secondary font-monospace px-2"><?php echo $e($sb); ?></span>
                  </div>
                </div>

                <?php if (!empty($m['location']) || !empty($m['match_time'])): ?>
                  <div class="px-3 py-2 bg-light border-top text-muted small d-flex align-items-center justify-content-between">
                    <?php if (!empty($m['match_time'])): ?>
                      <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6 text-danger">alarm</span> <?php echo $e($m['match_time']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($m['location'])): ?>
                      <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6 text-danger">pin_drop</span> <?php echo $e($m['location']); ?></span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- KHỐI 2: TOÀN BỘ DANH SÁCH LỊCH THI ĐẤU (GRID THẺ TRẬN ĐẤU HIỆN ĐẠI) -->
    <div class="mb-5">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="material-symbols-outlined text-primary fs-4">event_note</span>
          <h2 class="fw-bold text-dark fs-5 mb-0" id="match-section-title">Danh sách các trận thi đấu</h2>
        </div>
        <span class="text-muted small" id="match-counter-text">
          Hiển thị <strong id="visible-matches-count"><?php echo count($allMatches); ?></strong> / <?php echo count($allMatches); ?> trận
        </span>
      </div>

      <?php if (empty($allMatches)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
          <div class="text-muted mb-3">
            <span class="material-symbols-outlined display-3 text-secondary opacity-50">event_busy</span>
          </div>
          <h4 class="fw-bold text-dark">Chưa có lịch thi đấu được cập nhật</h4>
          <p class="text-secondary small mb-3">
            Ban tổ chức đang tiến hành bốc thăm chia bảng và xếp lịch các bộ môn. Vui lòng quay lại sau!
          </p>
        </div>
      <?php else: ?>
        <div class="row g-3" id="matches-grid-container">
          <?php foreach ($allMatches as $m): ?>
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

            $searchText = mb_strtolower($m['sport_name'] . ' ' . $m['round'] . ' ' . $ta . ' ' . $tb . ' ' . $m['location'] . ' ' . $m['stage_name'], 'UTF-8');
            ?>
            <div class="col-12 col-md-6 col-xl-4 match-item-wrapper"
                 data-sport-id="<?php echo $m['sport_id']; ?>"
                 data-status="<?php echo $status; ?>"
                 data-date="<?php echo $m['match_date']; ?>"
                 data-search="<?php echo $e($searchText); ?>">

              <div class="match-card card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden transition-all">
                <!-- Thẻ Header: Môn + Vòng đấu + Trạng thái -->
                <div class="p-3 pb-2 border-bottom bg-slate-50 d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <div class="sport-icon-circle rounded-circle bg-teal-50 text-teal-600 border d-flex align-items-center justify-content-center flex-shrink-0" style="width:30px;height:30px;">
                      <span class="material-symbols-outlined fs-6"><?php echo $e($m['sport_icon']); ?></span>
                    </div>
                    <div class="text-truncate">
                      <span class="fw-bold text-dark small d-block lh-1 text-truncate"><?php echo $e($m['sport_name']); ?></span>
                      <span class="text-muted" style="font-size:11px;"><?php echo $e($m['round']); ?></span>
                    </div>
                  </div>

                  <span class="badge <?php echo $statusClass; ?> rounded-pill px-2 py-1 fw-semibold flex-shrink-0" style="font-size:10.5px;">
                    <?php if ($status === 'live'): ?><span class="spinner-grow spinner-grow-sm me-1" style="width:6px;height:6px;"></span><?php endif; ?>
                    <?php echo $e($statusText); ?>
                  </span>
                </div>

                <!-- Thẻ Body: Đội A vs Đội B -->
                <div class="p-3">
                  <!-- Đội A -->
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2 team-row <?php echo $isWinnerA ? 'winner-row' : ''; ?>"
                       style="background-color:#f8fafc; border-left: 3px solid <?php echo $isWinnerA ? '#10b981' : '#3b82f6'; ?>;">
                    <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 avatar-initials"
                           style="width:32px;height:32px;font-size:11px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);">
                        <?php echo $e($initials($ta)); ?>
                      </div>
                      <div class="text-truncate">
                        <span class="fw-bold <?php echo $isWinnerA ? 'text-success' : 'text-dark'; ?> small d-block text-truncate">
                          <?php echo $e($ta); ?>
                        </span>
                        <?php if ($isWinnerA): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0" style="font-size:9.5px;">Thắng</span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <span class="badge rounded-3 px-2 py-1 <?php echo $isWinnerA ? 'text-success bg-emerald-50' : 'text-primary bg-blue-50'; ?> fw-black fs-4 font-monospace flex-shrink-0">
                      <?php echo $hasScore ? (int) $sa : '-'; ?>
                    </span>
                  </div>

                  <!-- Đội B -->
                  <div class="d-flex align-items-center justify-content-between p-2 rounded-3 team-row <?php echo $isWinnerB ? 'winner-row' : ''; ?>"
                       style="background-color:#f8fafc; border-left: 3px solid <?php echo $isWinnerB ? '#10b981' : '#64748b'; ?>;">
                    <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                      <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0 avatar-initials"
                           style="width:32px;height:32px;font-size:11px;background:linear-gradient(135deg,#475569,#64748b);">
                        <?php echo $e($initials($tb)); ?>
                      </div>
                      <div class="text-truncate">
                        <span class="fw-semibold <?php echo $isWinnerB ? 'text-success fw-bold' : 'text-secondary'; ?> small d-block text-truncate">
                          <?php echo $e($tb); ?>
                        </span>
                        <?php if ($isWinnerB): ?>
                          <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0" style="font-size:9.5px;">Thắng</span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <span class="badge rounded-3 px-2 py-1 <?php echo $isWinnerB ? 'text-success bg-emerald-50' : 'text-secondary bg-light border'; ?> fw-bold fs-4 font-monospace flex-shrink-0">
                      <?php echo $hasScore ? (int) $sb : '-'; ?>
                    </span>
                  </div>
                </div>

                <!-- Thẻ Footer: Giờ thi đấu & Địa điểm sân -->
                <div class="px-3 py-2 border-top bg-light text-muted d-flex align-items-center justify-content-between gap-2" style="font-size:11.5px;">
                  <span class="d-flex align-items-center gap-1 text-truncate">
                    <span class="material-symbols-outlined text-secondary fs-6 flex-shrink-0">schedule</span>
                    <span class="text-truncate"><?php echo !empty($m['match_time']) ? $e($m['match_time']) : 'Chờ xếp giờ'; ?></span>
                  </span>

                  <?php if (!empty($m['location'])): ?>
                    <span class="d-flex align-items-center gap-1 flex-shrink-0 text-truncate" title="<?php echo $e($m['location']); ?>">
                      <span class="material-symbols-outlined text-danger fs-6 flex-shrink-0">pin_drop</span>
                      <span class="badge bg-white text-dark border px-2 py-0.5 rounded-pill"><?php echo $e($m['location']); ?></span>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Thông báo khi lọc không tìm thấy kết quả -->
        <div id="no-filter-match-alert" class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white mt-3" style="display:none;">
          <div class="text-muted mb-2">
            <span class="material-symbols-outlined fs-1 text-secondary opacity-50">search_off</span>
          </div>
          <h5 class="fw-bold text-dark">Không tìm thấy trận đấu phù hợp</h5>
          <p class="text-secondary small mb-3">Vui lòng thử đổi từ khóa tìm kiếm hoặc chọn bộ lọc trạng thái khác.</p>
          <div>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-reset-filters">
              <span class="material-symbols-outlined fs-6 align-middle me-1">refresh</span> Xóa bộ lọc
            </button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- KHỐI 3: BẢNG XẾP HẠNG CÁC BẢNG ĐẤU VÒNG TRÒN (GROUP STANDINGS) -->
    <?php if (!empty($standingsBySport)): ?>
      <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="material-symbols-outlined text-success fs-4">leaderboard</span>
          <h2 class="fw-bold text-dark fs-5 mb-0">Bảng xếp hạng các bảng đấu</h2>
        </div>

        <?php foreach ($standingsBySport as $sId => $groups): ?>
          <?php
          $spName = 'Nội dung #' . $sId;
          foreach ($sportsList as $sp) {
              if ($sp['id'] == $sId) { $spName = $sp['name']; break; }
          }
          ?>
          <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
              <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success text-white rounded-pill px-2 py-1 small">Vòng tròn</span>
                <h5 class="mb-0 fw-bold text-dark"><?php echo $e($spName); ?></h5>
              </div>
            </div>

            <div class="card-body p-3">
              <div class="row g-3">
                <?php foreach ($groups as $label => $rows): ?>
                  <div class="<?php echo count($groups) === 1 ? 'col-lg-8 mx-auto' : 'col-lg-6'; ?>">
                    <div class="border rounded-3 p-3 bg-white h-100 shadow-2xs">
                      <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark fs-6">
                          <i class="fa fa-flag text-primary me-1"></i>Bảng <?php echo $e($label); ?>
                        </span>
                        <span class="badge bg-light text-muted border"><?php echo count($rows); ?> đội</span>
                      </div>

                      <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 text-center" style="font-size:12.5px;">
                          <thead class="table-light">
                            <tr>
                              <th style="width:36px;">#</th>
                              <th class="text-start">Đội / VĐV</th>
                              <th>Trận</th>
                              <th class="text-success">T</th>
                              <th>H</th>
                              <th class="text-danger">B</th>
                              <th>Hiệu số</th>
                              <th class="text-primary fw-bold">Điểm</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($rows as $r): ?>
                              <?php
                              $rank = (int)$r['rank'];
                              $medalCls = ($rank === 1) ? 'bg-warning text-dark' : (($rank === 2) ? 'bg-secondary text-white' : (($rank === 3) ? 'bg-bronze text-white' : 'bg-light text-muted border'));
                              ?>
                              <tr>
                                <td>
                                  <span class="badge <?php echo $medalCls; ?> rounded-circle" style="width:22px;height:22px;line-height:16px;">
                                    <?php echo $rank; ?>
                                  </span>
                                </td>
                                <td class="text-start fw-semibold text-dark"><?php echo $e($r['team_name']); ?></td>
                                <td><?php echo (int) $r['played']; ?></td>
                                <td class="text-success fw-bold"><?php echo (int) $r['won']; ?></td>
                                <td><?php echo (int) $r['drawn']; ?></td>
                                <td class="text-danger"><?php echo (int) $r['lost']; ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $r['points_scored'] . ':' . $r['points_conceded']; ?></span></td>
                                <td class="text-primary fw-bold fs-6"><?php echo (int) $r['points']; ?></td>
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
      </div>
    <?php endif; ?>

    <!-- KHỐI 4: BẢNG XẾP HẠNG TOÀN ĐOÀN TỔNG HỢP (NẾU CÓ) -->
    <?php if (!empty($rankings)): ?>
      <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="material-symbols-outlined text-warning fs-4">military_tech</span>
          <h2 class="fw-bold text-dark fs-5 mb-0">Bảng xếp hạng huy chương toàn đoàn</h2>
        </div>

        <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-slate-50">
                <tr class="text-uppercase text-secondary" style="font-size:11px;">
                  <th class="ps-4" style="width:70px;">Hạng</th>
                  <th>Đơn vị / Đoàn thể thao</th>
                  <th class="text-center" style="width:80px;"><span class="badge bg-warning text-dark">🥇 Vàng</span></th>
                  <th class="text-center" style="width:80px;"><span class="badge bg-secondary text-white">🥈 Bạc</span></th>
                  <th class="text-center" style="width:80px;"><span class="badge text-white" style="background:#cd7f32;">🥉 Đồng</span></th>
                  <th class="text-center pe-4" style="width:100px;">Tổng điểm</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rankings as $i => $r):
                  $rank = (int) $val($r, array('rank', 'position'), $i + 1);
                  $name = $val($r, array('name', 'team_name', 'unit_name', 'org_name', 'organization_name'), '');
                  $points = $val($r, array('points', 'score', 'total_points'), '0');
                  $gold = $val($r, array('gold'), 0);
                  $silver = $val($r, array('silver'), 0);
                  $bronze = $val($r, array('bronze'), 0);
                  $medalCls = $rank === 1 ? 'bg-warning text-dark' : ($rank === 2 ? 'bg-secondary text-white' : ($rank === 3 ? 'bg-danger text-white' : 'bg-light text-dark border'));
                ?>
                <tr>
                  <td class="ps-4">
                    <span class="badge <?php echo $medalCls; ?> rounded-circle fw-bold d-inline-flex align-items-center justify-content-center" style="width:30px;height:30px;">
                      <?php echo $e($rank); ?>
                    </span>
                  </td>
                  <td class="fw-semibold text-dark"><?php echo $e($name); ?></td>
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
      </div>
    <?php endif; ?>

  </div>
</main>

<style>
/* Design tokens & micro-animations */
.bg-slate-50 { background-color: #f8fafc !important; }
.bg-teal-50 { background-color: #f0fdfa !important; }
.text-teal-600 { color: #0d9488 !important; }
.bg-blue-50 { background-color: #eff6ff !important; }
.bg-emerald-50 { background-color: #ecfdf5 !important; }
.bg-bronze { background-color: #cd7f32 !important; }
.text-cyan { color: #38bdf8 !important; }

/* Hero Card */
.schedule-hero-card {
  background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0369a1 100%);
}
.hero-glow-1 {
  position: absolute;
  top: -60px;
  right: -60px;
  width: 240px;
  height: 240px;
  background: radial-gradient(circle, rgba(56,189,248,0.3) 0%, rgba(0,0,0,0) 70%);
  border-radius: 50%;
  pointer-events: none;
}
.hero-glow-2 {
  position: absolute;
  bottom: -80px;
  left: 30%;
  width: 300px;
  height: 200px;
  background: radial-gradient(circle, rgba(16,185,129,0.2) 0%, rgba(0,0,0,0) 70%);
  border-radius: 50%;
  pointer-events: none;
}
.backdrop-blur { backdrop-filter: blur(8px); }
.text-white-70 { color: rgba(255, 255, 255, 0.7); }
.text-white-80 { color: rgba(255, 255, 255, 0.85); }

/* Pulse Indicator */
.pulse-indicator {
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
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #ef4444;
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: pulse-ring 1.4s infinite cubic-bezier(0.66, 0, 0, 1);
}
@keyframes pulse-ring {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Match Card Hover */
.match-card {
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}
.match-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.12), 0 4px 8px -4px rgba(15, 23, 42, 0.06) !important;
}

/* Winner Row Highlight */
.winner-row {
  background-color: #ecfdf5 !important;
}

/* Sports Nav Scroller */
.sports-nav-scroller::-webkit-scrollbar {
  height: 4px;
}
.sports-nav-scroller::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var sportBtns = document.querySelectorAll('.sport-filter-btn');
  var statusBtns = document.querySelectorAll('.status-filter-btn');
  var searchInput = document.getElementById('schedule-search-input');
  var clearSearchBtn = document.getElementById('btn-clear-search');
  var matchItems = document.querySelectorAll('.match-item-wrapper');
  var counterEl = document.getElementById('visible-matches-count');
  var noMatchAlert = document.getElementById('no-filter-match-alert');
  var resetBtn = document.getElementById('btn-reset-filters');
  var matchSectionTitle = document.getElementById('match-section-title');

  var currentSportId = "<?php echo (string)$selectedSportId; ?>";
  var currentStatus = "<?php echo (string)$selectedStatus; ?>";
  var currentSearch = "";

  function applyFilters() {
    var visibleCount = 0;
    var searchLower = currentSearch.trim().toLowerCase();

    matchItems.forEach(function (item) {
      var sId = item.getAttribute('data-sport-id') || '';
      var st = item.getAttribute('data-status') || '';
      var searchData = item.getAttribute('data-search') || '';

      var matchSport = (!currentSportId || sId === currentSportId);
      var matchStatus = (!currentStatus || st === currentStatus);
      var matchSearch = (!searchLower || searchData.indexOf(searchLower) !== -1);

      if (matchSport && matchStatus && matchSearch) {
        item.style.display = '';
        visibleCount++;
      } else {
        item.style.display = 'none';
      }
    });

    if (counterEl) counterEl.textContent = visibleCount;
    if (noMatchAlert) noMatchAlert.style.display = (visibleCount === 0) ? '' : 'none';
  }

  // Chọn môn thi đấu
  sportBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      sportBtns.forEach(function (b) { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-light'); });
      btn.classList.remove('btn-light');
      btn.classList.add('active', 'btn-primary');

      currentSportId = btn.getAttribute('data-sport-id') || '';
      var sportName = btn.getAttribute('data-sport-name') || '';
      if (matchSectionTitle) {
        matchSectionTitle.textContent = currentSportId ? ('Lịch thi đấu: ' + sportName) : 'Danh sách các trận thi đấu';
      }
      applyFilters();
    });
  });

  // Chọn trạng thái
  statusBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      statusBtns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      currentStatus = btn.getAttribute('data-status') || '';
      applyFilters();
    });
  });

  // Ô tìm kiếm
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

  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      currentSportId = '';
      currentStatus = '';
      currentSearch = '';
      if (searchInput) searchInput.value = '';
      if (clearSearchBtn) clearSearchBtn.style.display = 'none';

      sportBtns.forEach(function (b, idx) {
        if (idx === 0) { b.classList.add('active', 'btn-primary'); b.classList.remove('btn-light'); }
        else { b.classList.remove('active', 'btn-primary'); b.classList.add('btn-light'); }
      });
      statusBtns.forEach(function (b, idx) {
        if (idx === 0) b.classList.add('active');
        else b.classList.remove('active');
      });
      if (matchSectionTitle) matchSectionTitle.textContent = 'Danh sách các trận thi đấu';
      applyFilters();
    });
  }

  // Khởi chạy bộ lọc ban đầu
  applyFilters();
});
</script>
