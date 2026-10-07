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

// Lọc các mục agenda khớp từ khoá (không dấu-insensitive đơn giản theo lower-case).
$matched = array();
if (!empty($agenda) && is_array($agenda) && !empty($keywords)) {
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

    <?php if (empty($matched)): ?>
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
</style>
