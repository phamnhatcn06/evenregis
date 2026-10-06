<?php
/**
 * Trang Lịch trình Đại hội - hiển thị toàn bộ agenda, gộp nhóm theo ngày.
 * Dữ liệu từ DaihoiController::actionAgenda() (Daihoi::getAgenda()).
 *
 * @var array $event
 * @var array $agenda
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

// Chuẩn hoá ngày hiển thị từ nhiều định dạng (timestamp / chuỗi ngày).
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

// Màu badge theo loại hoạt động.
$typeMap = array(
    'plenary' => array('cls' => 'bg-primary', 'label' => 'Phiên toàn thể'),
    'ceremony' => array('cls' => 'bg-danger', 'label' => 'Nghi lễ'),
    'workshop' => array('cls' => 'bg-info text-dark', 'label' => 'Chuyên đề'),
    'break' => array('cls' => 'bg-secondary', 'label' => 'Giải lao'),
    'sport' => array('cls' => 'bg-success', 'label' => 'Thi đấu'),
    'gala' => array('cls' => 'bg-purple', 'label' => 'Gala'),
);

// Gộp các hoạt động theo ngày (giữ nguyên thứ tự xuất hiện).
$groups = array();
if (!empty($agenda) && is_array($agenda)) {
    foreach ($agenda as $item) {
        if (!is_array($item)) {
            continue;
        }
        $dateRaw = $val($item, array('date', 'day', 'agenda_date', 'start_date'), '');
        $key = $dateRaw !== '' ? (string) $dateRaw : '__none__';
        if (!isset($groups[$key])) {
            $groups[$key] = array('label' => $fmtDate($dateRaw), 'items' => array());
        }
        $groups[$key]['items'][] = $item;
    }
}
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'agenda')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="text-primary fw-bold text-uppercase small d-block mb-1">Lịch trình sự kiện</span>
      <h1 class="fw-bold text-dark fs-3 mb-1">Lịch trình Đại hội</h1>
      <p class="text-secondary small mb-0">Toàn bộ chương trình theo từng ngày. Lịch có thể được Ban Tổ chức cập nhật.</p>
    </div>

    <?php if (empty($groups)): ?>
      <div class="card border rounded-4 shadow-sm p-5 text-center text-secondary">
        <span class="material-symbols-outlined mb-2" style="font-size:48px;">event_busy</span>
        <p class="mb-0">Chưa có lịch trình nào được công bố.</p>
      </div>
    <?php else: ?>
      <?php foreach ($groups as $group): ?>
      <div class="card border rounded-4 shadow-sm bg-white mb-4 overflow-hidden">
        <?php if ($group['label'] !== ''): ?>
        <div class="px-4 py-3 border-bottom bg-slate-50 d-flex align-items-center gap-2">
          <span class="material-symbols-outlined text-primary">calendar_month</span>
          <h2 class="fw-bold text-dark fs-5 mb-0"><?php echo $e($group['label']); ?></h2>
        </div>
        <?php endif; ?>
        <div class="p-3 p-md-4">
          <?php foreach ($group['items'] as $item):
            $time = $val($item, array('time', 'time_range', 'start_time'), '');
            $endTime = $val($item, array('end_time'), '');
            if ($endTime !== '' && strpos($time, '-') === false) {
                $time = trim($time . ' - ' . $endTime);
            }
            $title = $val($item, array('title', 'name'), 'Hoạt động');
            $place = $val($item, array('location', 'venue', 'place'), '');
            $desc = $val($item, array('description', 'note'), '');
            $type = (string) $val($item, array('type', 'category'), '');
            $badge = isset($typeMap[$type]) ? $typeMap[$type] : null;
          ?>
          <div class="d-flex gap-3 py-3 border-bottom agenda-row">
            <div class="flex-shrink-0 text-end" style="min-width:86px;">
              <span class="badge bg-light text-dark border fw-bold" style="font-size:12px;"><?php echo $e($time !== '' ? $time : '—'); ?></span>
            </div>
            <div class="flex-shrink-0 d-flex flex-column align-items-center" style="width:18px;">
              <span class="rounded-circle bg-primary d-inline-block mt-1" style="width:11px;height:11px;"></span>
              <span class="flex-grow-1 bg-light" style="width:2px;"></span>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <strong class="text-dark"><?php echo $e($title); ?></strong>
                <?php if ($badge): ?>
                  <span class="badge <?php echo $badge['cls']; ?> text-white rounded-pill" style="font-size:10.5px;"><?php echo $e($badge['label']); ?></span>
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
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<style>
.agenda-row:last-child { border-bottom: 0 !important; }
.bg-purple { background-color: #7c3aed !important; }
.bg-slate-50 { background-color: #f8fafc !important; }
</style>
