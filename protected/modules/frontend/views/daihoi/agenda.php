<?php
/**
 * Trang Lịch trình Đại hội - hiển thị toàn bộ agenda, gộp nhóm theo ngày.
 * Dữ liệu từ DaihoiController::actionAgenda() (Daihoi::getAgenda()).
 *
 * Định dạng $agenda (nhóm theo ngày):
 *   [ ['day'=>1,'title'=>'Thứ Ba, 27/10/2026','date'=>'2026-10-27',
 *      'items'=>[['time'=>'05:30','end_time'=>'08:30','title'=>..,'location'=>..,
 *                 'description'=>..,'type'=>'meal'], ...]], ... ]
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
$e = function ($s) {
    if (is_array($s)) {
        foreach (array('name', 'title', 'label') as $k) {
            if (isset($s[$k]) && is_scalar($s[$k])) {
                return CHtml::encode((string) $s[$k]);
            }
        }
        return '';
    }
    return CHtml::encode($s !== null ? (string) $s : '');
};

$eventName = $val($event, array('name', 'title'), 'Đại hội Mường Thanh 2026');

// Màu / icon / nhãn theo loại hoạt động.
$typeMap = array(
    'ceremony'    => array('cls' => 'ag-ceremony',   'icon' => 'flag',              'label' => 'Nghi lễ'),
    'plenary'     => array('cls' => 'ag-plenary',    'icon' => 'groups',            'label' => 'Phiên toàn thể'),
    'competition' => array('cls' => 'ag-competition','icon' => 'workspace_premium', 'label' => 'Nghiệp vụ'),
    'workshop'    => array('cls' => 'ag-competition','icon' => 'school',            'label' => 'Chuyên đề'),
    'sport'       => array('cls' => 'ag-sport',      'icon' => 'sports_soccer',     'label' => 'Thể thao'),
    'funrun'      => array('cls' => 'ag-funrun',     'icon' => 'directions_run',    'label' => 'Fun Run'),
    'tour'        => array('cls' => 'ag-tour',       'icon' => 'landscape',         'label' => 'Tham quan'),
    'gala'        => array('cls' => 'ag-gala',       'icon' => 'celebration',       'label' => 'Gala & Lucky Draw'),
    'meal'        => array('cls' => 'ag-meal',       'icon' => 'restaurant',        'label' => 'Ẩm thực'),
    'break'       => array('cls' => 'ag-break',      'icon' => 'coffee',            'label' => 'Giải lao'),
    'other'       => array('cls' => 'ag-other',      'icon' => 'info',              'label' => 'Thông tin'),
);
$fallbackType = array('cls' => 'ag-other', 'icon' => 'event', 'label' => '');

// Chuẩn hoá danh sách nhóm ngày từ API (đã gộp sẵn).
$days = array();
if (!empty($agenda) && is_array($agenda)) {
    $idx = 0;
    foreach ($agenda as $group) {
        if (!is_array($group)) {
            continue;
        }
        $items = $val($group, array('items'), array());
        if (!is_array($items) || empty($items)) {
            continue;
        }
        $idx++;
        $days[] = array(
            'anchor' => 'ngay-' . $idx,
            'num'    => (string) $val($group, array('day'), $idx),
            'title'  => (string) $val($group, array('title', 'day_title'), 'Ngày ' . $idx),
            'items'  => $items,
        );
    }
}
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'agenda')); ?>

<main class="flex-grow-1 ag-page">
  <!-- Hero -->
  <section class="ag-hero">
    <div class="container py-5 position-relative">
      <span class="badge rounded-pill ag-hero-badge mb-3">
        <span class="material-symbols-outlined align-middle me-1" style="font-size:16px;">calendar_month</span>
        Lịch trình chính thức
      </span>
      <h1 class="fw-bold text-white display-6 mb-2">Lịch trình Đại hội</h1>
      <p class="text-white-50 mb-0" style="max-width:640px;">
        Toàn bộ chương trình <?php echo $e($eventName); ?> theo từng ngày — từ check-in, thi đấu thể thao,
        nghiệp vụ, Fun Run đến các đêm Gala. Lịch có thể được Ban Tổ chức cập nhật.
      </p>
    </div>
  </section>

  <section class="container pb-5">
    <?php if (empty($days)): ?>
      <div class="card border-0 rounded-4 shadow-sm p-5 text-center text-secondary mt-4">
        <span class="material-symbols-outlined mb-2 mx-auto text-primary" style="font-size:48px;">event_busy</span>
        <h5 class="fw-bold text-dark">Chưa có lịch trình</h5>
        <p class="mb-0">Lịch trình đại hội sẽ được công bố tại đây khi Ban Tổ chức cập nhật.</p>
      </div>
    <?php else: ?>

      <!-- Mốc nhận biết thời điểm thanh ngày bắt đầu "dính" -->
      <div class="ag-daynav-sentinel" aria-hidden="true"></div>

      <!-- Thanh điều hướng ngày (dính trên cùng khi cuộn) -->
      <div class="ag-daynav-wrap">
        <nav class="ag-daynav">
          <span class="ag-daynav-caption">
            <span class="material-symbols-outlined">calendar_view_week</span>Chọn ngày
          </span>
          <?php foreach ($days as $d): ?>
            <a href="#<?php echo $e($d['anchor']); ?>" class="ag-daynav-item">
              <span class="ag-daynav-num"><?php echo $e($d['num']); ?></span>
              <span class="ag-daynav-label"><?php echo $e($d['title']); ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      </div>

      <?php foreach ($days as $d): ?>
      <div class="ag-day" id="<?php echo $e($d['anchor']); ?>">
        <div class="ag-day-head">
          <div class="ag-day-badge"><span class="small d-block">NGÀY</span><?php echo $e($d['num']); ?></div>
          <div>
            <h2 class="fw-bold text-dark fs-4 mb-0"><?php echo $e($d['title']); ?></h2>
            <span class="text-secondary small"><?php echo count($d['items']); ?> hoạt động</span>
          </div>
        </div>

        <div class="ag-timeline">
          <?php foreach ($d['items'] as $item):
            $time    = (string) $val($item, array('time', 'start_time'), '');
            $endTime = (string) $val($item, array('end_time'), '');
            $title   = $val($item, array('title', 'name'), 'Hoạt động');
            $place   = $val($item, array('location', 'venue', 'place'), '');
            $desc    = $val($item, array('description', 'note'), '');
            $type    = (string) $val($item, array('type', 'category'), 'other');
            $t       = isset($typeMap[$type]) ? $typeMap[$type] : $fallbackType;
          ?>
          <div class="ag-item <?php echo $t['cls']; ?>">
            <div class="ag-time">
              <span class="ag-time-start"><?php echo $e($time !== '' ? $time : '—'); ?></span>
              <?php if ($endTime !== ''): ?><span class="ag-time-end"><?php echo $e($endTime); ?></span><?php endif; ?>
            </div>
            <div class="ag-dot"><span class="material-symbols-outlined"><?php echo $t['icon']; ?></span></div>
            <div class="ag-card">
              <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                <strong class="ag-title"><?php echo $e($title); ?></strong>
                <?php if ($t['label'] !== ''): ?>
                  <span class="ag-tag"><?php echo $e($t['label']); ?></span>
                <?php endif; ?>
              </div>
              <?php if ($place !== ''): ?>
                <div class="ag-place">
                  <span class="material-symbols-outlined">location_on</span><?php echo $e($place); ?>
                </div>
              <?php endif; ?>
              <?php if ($desc !== ''): ?>
                <p class="ag-desc"><?php echo $e($desc); ?></p>
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

<?php $dh = $base . '/public/daihoi'; ?>
<link rel="stylesheet" href="<?php echo $dh; ?>/agenda.css?v=<?php echo @filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/agenda.css') ?: time(); ?>">
<?php
Yii::app()->clientScript->registerScriptFile(
    $dh . '/agenda.js?v=' . (@filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/agenda.js') ?: time()),
    CClientScript::POS_END
);
?>
