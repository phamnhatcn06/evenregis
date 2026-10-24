<?php
/**
 * Trang chương trình dùng chung cho các link Sổ tay Đại hội:
 *  - Tiệc Welcome / Khai mạc (tiec-welcome)
 *  - Lịch tham quan & Tour (lich-tham-quan)
 *  - Tiệc Gala / Bế mạc (tiec-gala)
 *
 * Hiển thị khối thông tin tĩnh (từ $config['info']) và các mục agenda liên quan
 * được lọc theo $config['keywords'].
 *
 * @var array  $event
 * @var array  $agenda
 * @var string $active   Tab đang mở (khớp key trong _subheader)
 * @var array  $config   eyebrow, title, subtitle, icon, accent, info[], keywords[], emptyText
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$val = function ($arr, $keys, $default = '') {
    foreach ((array) $keys as $k) {
        if (is_array($arr) && isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
            return $arr[$k];
        }
    }
    return $default;
};

$eventName = $val($event, array('name', 'title'), 'Đại hội Mường Thanh 2026');
$accent = isset($config['accent']) ? $config['accent'] : '#2563eb';
$keywords = isset($config['keywords']) ? (array) $config['keywords'] : array();
$info = isset($config['info']) ? (array) $config['info'] : array();

$fmtDate = function ($raw) {
    if ($raw === '' || $raw === null) {
        return '';
    }
    $ts = ctype_digit((string) $raw) ? (int) $raw : strtotime((string) $raw);
    if (!$ts) {
        return (string) $raw;
    }
    $days = array('Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy');
    return $days[(int) date('w', $ts)] . ', ' . date('d/m/Y', $ts);
};

// Chương trình chi tiết tĩnh (từ poster Agenda) — ưu tiên nếu có.
$program = isset($config['program']) && is_array($config['program']) ? $config['program'] : array();

// Lọc các mục agenda khớp từ khoá (không dấu-insensitive đơn giản theo lower-case).
$matched = array();
if (empty($program) && !empty($agenda) && is_array($agenda) && !empty($keywords)) {
    foreach ($agenda as $item) {
        if (!is_array($item)) {
            continue;
        }
        $haystack = mb_strtolower(
            $val($item, array('title', 'name'), '') . ' '
            . $val($item, array('type', 'category'), '') . ' '
            . $val($item, array('description', 'note'), ''),
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
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => $active)); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;"><?php echo $e($config['eyebrow']); ?></span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;"><?php echo $e($config['icon']); ?></span>
        <?php echo $e($config['title']); ?>
      </h1>
      <p class="text-secondary small mb-0"><?php echo $e($config['subtitle']); ?></p>
    </div>

    <?php if (!empty($info)): ?>
    <div class="card border-0 rounded-4 shadow-sm bg-white mb-4 overflow-hidden" style="border-left:4px solid <?php echo $e($accent); ?> !important;">
      <div class="p-3 p-md-4">
        <div class="row g-3">
          <?php foreach ($info as $row): ?>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;background:<?php echo $e($accent); ?>1a;">
                <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;"><?php echo $e($val($row, 'icon', 'info')); ?></span>
              </div>
              <div>
                <span class="text-uppercase text-muted d-block" style="font-size:10.5px;letter-spacing:.03em;"><?php echo $e($val($row, 'label', '')); ?></span>
                <strong class="text-dark small"><?php echo $e($val($row, 'value', '')); ?></strong>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">list_alt</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Chương trình chi tiết</h2>
    </div>

    <?php if (!empty($program)): ?>
      <?php
        // Đếm tổng số mục (không tính header chương) để biết mục cuối.
        $totalSteps = 0;
        foreach ($program as $it) { if (!isset($it['section'])) { $totalSteps++; } }
      ?>
      <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden prg" style="--prg-accent:<?php echo $e($accent); ?>;">
        <div class="p-3 p-sm-4">
          <div class="prg-timeline">
            <?php $stt = 0; $chapter = 0; foreach ($program as $item): ?>
              <?php if (isset($item['section'])): $chapter++; ?>
              <div class="prg-section">
                <span class="prg-section-no"><?php echo $e(sprintf('%02d', $chapter)); ?></span>
                <span class="prg-section-title"><?php echo $e($item['section']); ?></span>
              </div>
              <?php else: $stt++; $hasSub = !empty($item['sub']) && is_array($item['sub']); ?>
              <div class="prg-item<?php echo $stt === $totalSteps ? ' prg-item--last' : ''; ?><?php echo $hasSub ? ' prg-item--feature' : ''; ?>">
                <div class="prg-marker">
                  <span class="prg-dot"><?php echo $e(sprintf('%02d', $stt)); ?></span>
                </div>
                <div class="prg-body">
                  <span class="prg-time"><span class="material-symbols-outlined">schedule</span><?php echo $e($val($item, 'time', '—')); ?></span>
                  <div class="prg-title"><?php echo $e($val($item, 'title', 'Hoạt động')); ?></div>
                  <?php if ($hasSub): ?>
                  <ul class="prg-sub">
                    <?php foreach ($item['sub'] as $sub): ?>
                    <li><?php echo $e($sub); ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <?php endif; ?>
                </div>
              </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php elseif (empty($matched)): ?>
      <div class="card border rounded-4 shadow-sm p-5 text-center text-secondary">
        <span class="material-symbols-outlined mb-2" style="font-size:48px;">event_upcoming</span>
        <p class="mb-0"><?php echo $e(isset($config['emptyText']) ? $config['emptyText'] : 'Chương trình chi tiết sẽ được cập nhật.'); ?></p>
        <a href="<?php echo $e($base); ?>/agenda-tong-the" class="btn btn-sm btn-light border rounded-pill px-3 mt-3 mx-auto d-inline-flex align-items-center gap-1" style="width:fit-content;">
          <span class="material-symbols-outlined fs-6">calendar_month</span> Xem Agenda tổng thể
        </a>
      </div>
    <?php else: ?>
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
        <div class="p-3 p-md-4">
          <?php foreach ($matched as $item):
            $time = $val($item, array('time', 'time_range', 'start_time'), '');
            $endTime = $val($item, array('end_time'), '');
            if ($endTime !== '' && strpos($time, '-') === false) {
                $time = trim($time . ' - ' . $endTime);
            }
            $dateLabel = $fmtDate($val($item, array('date', 'day', 'agenda_date', 'start_date'), ''));
            $title = $val($item, array('title', 'name'), 'Hoạt động');
            $place = $val($item, array('location', 'venue', 'place'), '');
            $desc = $val($item, array('description', 'note'), '');
          ?>
          <div class="d-flex gap-3 py-3 border-bottom program-row">
            <div class="flex-shrink-0 text-end" style="min-width:86px;">
              <span class="badge bg-light text-dark border fw-bold" style="font-size:12px;"><?php echo $e($time !== '' ? $time : '—'); ?></span>
            </div>
            <div class="flex-shrink-0 d-flex flex-column align-items-center" style="width:18px;">
              <span class="rounded-circle d-inline-block mt-1" style="width:11px;height:11px;background:<?php echo $e($accent); ?>;"></span>
              <span class="flex-grow-1 bg-light" style="width:2px;"></span>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <strong class="text-dark"><?php echo $e($title); ?></strong>
                <?php if ($dateLabel !== ''): ?>
                  <span class="badge bg-light text-secondary border rounded-pill" style="font-size:10.5px;"><?php echo $e($dateLabel); ?></span>
                <?php endif; ?>
              </div>
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
      </div>
    <?php endif; ?>
  </section>
</main>

<style>
.program-row:last-child { border-bottom: 0 !important; }
.bg-slate-50 { background-color: #f8fafc !important; }

/* ===== Timeline chương trình chi tiết ===== */
.prg { --prg-accent: #2563eb; }
.prg-timeline { position: relative; }

/* Đường kẻ dọc chạy xuyên các mốc */
.prg-item { position: relative; display: flex; gap: 16px; padding: 0 0 22px 0; }
.prg-item::before {
  content: ""; position: absolute; left: 17px; top: 36px; bottom: -4px;
  width: 2px; background: #e9ecef;
}
.prg-item--last::before { display: none; }

/* Mốc tròn đánh số */
.prg-marker { flex: 0 0 auto; width: 36px; z-index: 1; }
.prg-dot {
  display: flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 50%;
  font-size: 13px; font-weight: 700; color: var(--prg-accent);
  background: #fff; border: 2px solid var(--prg-accent);
  box-shadow: 0 1px 3px rgba(0,0,0,.08);
}
.prg-item--feature .prg-dot {
  color: #fff; background: var(--prg-accent);
}

/* Nội dung */
.prg-body { flex: 1 1 auto; min-width: 0; padding-top: 2px; }
.prg-time {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 12px; font-weight: 600; color: var(--prg-accent);
  background: color-mix(in srgb, var(--prg-accent) 10%, #fff);
  border-radius: 999px; padding: 3px 10px 3px 8px; margin-bottom: 6px;
}
.prg-time .material-symbols-outlined { font-size: 15px; }
.prg-title { font-weight: 700; color: #1f2937; line-height: 1.4; }

/* Mục nổi bật (có chương trình con) -> bọc khung */
.prg-item--feature .prg-body {
  background: #fafbff; border: 1px solid #eef0f6;
  border-left: 3px solid var(--prg-accent);
  border-radius: 12px; padding: 12px 14px;
}
.prg-item--feature .prg-title { font-size: 15.5px; }

/* Danh sách con */
.prg-sub { list-style: none; margin: 10px 0 0; padding: 0; }
.prg-sub li {
  position: relative; padding: 5px 0 5px 20px;
  font-size: 13.5px; color: #4b5563; line-height: 1.5;
  border-top: 1px dashed #edeff4;
}
.prg-sub li:first-child { border-top: 0; }
.prg-sub li::before {
  content: ""; position: absolute; left: 4px; top: 12px;
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--prg-accent); opacity: .55;
}

/* Header chương */
.prg-section {
  display: flex; align-items: center; gap: 12px;
  margin: 8px 0 20px; padding: 11px 16px; border-radius: 12px;
  color: #fff; letter-spacing: .02em;
  background: linear-gradient(90deg, var(--prg-accent), color-mix(in srgb, var(--prg-accent) 65%, #000));
}
.prg-section:first-child { margin-top: 0; }
.prg-section-no {
  flex: 0 0 auto; font-weight: 800; font-size: 15px;
  background: rgba(255,255,255,.22); border-radius: 8px;
  padding: 2px 9px; line-height: 1.5;
}
.prg-section-title { font-weight: 700; font-size: 14px; text-transform: uppercase; }

/* Fallback cho trình duyệt không hỗ trợ color-mix */
@supports not (background: color-mix(in srgb, red, blue)) {
  .prg-time { background: #f1f5ff; }
  .prg-section { background: var(--prg-accent); }
}

@media (max-width: 575.98px) {
  .prg-item { gap: 12px; }
  .prg-item::before { left: 15px; }
  .prg-marker { width: 32px; }
  .prg-dot { width: 32px; height: 32px; font-size: 12px; }
  .prg-title { font-size: 14px; }
}
</style>
