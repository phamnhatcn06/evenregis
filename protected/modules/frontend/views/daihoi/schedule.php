<?php
/**
 * Trang Lịch thi đấu - trận đang diễn ra (LIVE), sắp/vừa diễn ra và bảng xếp hạng.
 * Dữ liệu từ DaihoiController::actionSchedule().
 *
 * @var array $event
 * @var array $liveMatches
 * @var array $recentMatches
 * @var array $rankings
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

$statusMap = array(
    'live' => array('cls' => 'bg-danger', 'text' => 'Đang diễn ra'),
    'done' => array('cls' => 'bg-secondary', 'text' => 'Đã kết thúc'),
    'upcoming' => array('cls' => 'bg-primary', 'text' => 'Sắp diễn ra'),
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

// Render một thẻ trận đấu.
$renderMatch = function ($m) use ($e, $val, $statusMap, $initials, $matchScores) {
    $st = isset($statusMap[$val($m, array('status'), '')]) ? $statusMap[$m['status']] : $statusMap['done'];
    $sc = $matchScores($m);
    $home = $val($m, array('home_name', 'home', 'team_a'), '');
    $away = $val($m, array('away_name', 'away', 'team_b'), '');
    $meta = trim($val($m, array('time', 'kickoff_time'), '') . ($val($m, array('venue', 'location'), '') ? ' • ' . $val($m, array('venue', 'location'), '') : ''));
    ?>
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden" style="border-top:4px solid #10b981 !important;">
        <div class="p-3 pb-2 border-bottom bg-slate-50 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-teal-50 text-teal-600 border d-flex align-items-center justify-content-center" style="width:28px;height:28px;">
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
        <?php if ($meta !== ''): ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light text-muted" style="font-size:11.5px;">
          <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined text-secondary fs-6">schedule</span> <?php echo $e($meta); ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php
};
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'schedule')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="text-primary fw-bold text-uppercase small d-block mb-1">Thể thao Đại hội</span>
      <h1 class="fw-bold text-dark fs-3 mb-1">Lịch thi đấu &amp; Kết quả</h1>
      <p class="text-secondary small mb-0">Tỷ số và lịch cập nhật theo thời gian thực từ các bộ môn tại Đại hội.</p>
    </div>

    <!-- Đang diễn ra -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="badge bg-danger text-white rounded-pill px-2 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1" style="font-size:11px;">
        <span class="spinner-grow spinner-grow-sm" style="width:7px;height:7px;"></span> Trực tiếp
      </span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Đang diễn ra</h2>
    </div>
    <div class="row g-3 mb-4">
      <?php if (!empty($liveMatches)): foreach ($liveMatches as $m) { $renderMatch($m); } else: ?>
        <div class="col-12"><p class="text-secondary small mb-0 py-3 text-center">Hiện chưa có trận đấu nào đang diễn ra.</p></div>
      <?php endif; ?>
    </div>

    <!-- Vừa/sắp diễn ra -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="material-symbols-outlined text-primary">event</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Trận đấu gần đây &amp; sắp tới</h2>
    </div>
    <div class="row g-3 mb-4">
      <?php if (!empty($recentMatches)): foreach ($recentMatches as $m) { $renderMatch($m); } else: ?>
        <div class="col-12"><p class="text-secondary small mb-0 py-3 text-center">Chưa có lịch thi đấu được cập nhật.</p></div>
      <?php endif; ?>
    </div>

    <!-- Bảng xếp hạng -->
    <?php if (!empty($rankings)): ?>
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="material-symbols-outlined text-warning">leaderboard</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Bảng xếp hạng tạm thời</h2>
    </div>
    <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-slate-50">
            <tr class="text-uppercase text-secondary" style="font-size:11px;">
              <th class="ps-4" style="width:70px;">Hạng</th>
              <th>Đơn vị / Đội</th>
              <th class="text-center" style="width:90px;">Điểm</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rankings as $i => $r):
              $rank = (int) $val($r, array('rank', 'position'), $i + 1);
              $name = $val($r, array('name', 'team_name', 'unit_name', 'org_name'), '');
              $points = $val($r, array('points', 'score', 'total_points'), '0');
              $medalCls = $rank === 1 ? 'bg-warning text-dark' : ($rank === 2 ? 'bg-secondary text-white' : ($rank === 3 ? 'bg-danger text-white' : 'bg-light text-dark border'));
            ?>
            <tr>
              <td class="ps-4">
                <span class="badge <?php echo $medalCls; ?> rounded-circle fw-bold d-inline-flex align-items-center justify-content-center" style="width:30px;height:30px;"><?php echo $e($rank); ?></span>
              </td>
              <td class="fw-semibold text-dark"><?php echo $e($name); ?></td>
              <td class="text-center fw-bold text-primary"><?php echo $e($points); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </section>
</main>

<style>
.bg-slate-50 { background-color: #f8fafc !important; }
.bg-teal-50 { background-color: #f0fdfa !important; }
.text-teal-600 { color: #0d9488 !important; }
</style>
