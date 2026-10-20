<?php
/**
 * Trang Lịch trình Đại hội (agenda-tong-the) — bố cục theo poster chính thức:
 * mỗi ngày gồm 4 buổi (Sáng / Trưa / Chiều / Tối) với màu riêng, timeline mốc
 * giờ bên trái và thẻ hoạt động tông màu buổi. Các môn thể thao song song trong
 * cùng một buổi gom thành lưới.
 *
 * Dữ liệu từ DaihoiController::actionAgenda() (Daihoi::getAgenda()):
 *   [ ['day'=>1,'title'=>'Thứ Ba, 27/10/2026','date'=>'2026-10-27',
 *      'items'=>[['time'=>'05:30','end_time'=>'08:30','title'=>..,'location'=>..,
 *                 'description'=>..,'type'=>'meal','session'=>1], ...]], ... ]
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
    return CHtml::encode($s !== null && is_scalar($s) ? (string) $s : '');
};

$eventName = $val($event, array('name', 'title'), 'Đại hội Mường Thanh 2026');

// Icon theo loại hoạt động (Material Symbols).
$typeIcon = array(
    'ceremony'    => 'groups',
    'plenary'     => 'groups',
    'competition' => 'work',
    'workshop'    => 'school',
    'sport'       => 'sports_soccer',
    'funrun'      => 'directions_run',
    'tour'        => 'photo_camera',
    'gala'        => 'card_giftcard',
    'meal'        => 'restaurant',
    'break'       => 'local_cafe',
    'other'       => 'how_to_reg',
);
// Icon riêng theo tên môn thể thao (ưu tiên hơn icon theo loại).
$sportIcon = function ($title) {
    $t = function_exists('mb_strtolower') ? mb_strtolower($title, 'UTF-8') : strtolower($title);
    if (strpos($t, 'bơi') !== false)        return 'pool';
    if (strpos($t, 'kéo co') !== false)      return 'sports_kabaddi';
    if (strpos($t, 'pickle') !== false)      return 'sports_tennis';
    if (strpos($t, 'cầu lông') !== false || strpos($t, 'bóng bàn') !== false) return 'sports_tennis';
    if (strpos($t, 'trao giải') !== false)   return 'emoji_events';
    if (strpos($t, 'bóng đá') !== false)     return 'sports_soccer';
    return 'sports';
};

// Cấu hình 4 buổi.
$sessions = array(
    1 => array('label' => 'Buổi sáng', 'icon' => 'wb_sunny',       'cls' => 'ses-morning'),
    2 => array('label' => 'Buổi trưa', 'icon' => 'light_mode',     'cls' => 'ses-noon'),
    3 => array('label' => 'Buổi chiều','icon' => 'directions_run', 'cls' => 'ses-afternoon'),
    4 => array('label' => 'Buổi tối',  'icon' => 'bedtime',        'cls' => 'ses-evening'),
);
$guessSession = function ($time) {
    $h = (int) substr($time, 0, 2);
    if ($h < 11) return 1;
    if ($h < 13) return 2;
    if ($h < 17) return 3;
    return 4;
};

// Chuẩn hoá: mỗi ngày -> các buổi -> danh sách item.
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
        $bySession = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $time = (string) $val($item, array('time', 'start_time'), '');
            $sid  = (int) $val($item, array('session'), 0);
            if ($sid < 1 || $sid > 4) {
                $sid = $guessSession($time);
            }
            $bySession[$sid][] = $item;
        }
        ksort($bySession);
        $days[] = array(
            'anchor'   => 'ngay-' . $idx,
            'num'      => (string) $val($group, array('day'), $idx),
            'title'    => (string) $val($group, array('title', 'day_title'), 'Ngày ' . $idx),
            'sessions' => $bySession,
        );
    }
}

// Render một thẻ nội dung (dùng cho cả item thường và ô môn trong lưới).
$renderCard = function ($item) use ($val, $e, $typeIcon, $sportIcon) {
    $time  = (string) $val($item, array('time', 'start_time'), '');
    $end   = (string) $val($item, array('end_time'), '');
    $title = (string) $val($item, array('title', 'name'), 'Hoạt động');
    $place = (string) $val($item, array('location', 'venue', 'place'), '');
    $desc  = (string) $val($item, array('description', 'note'), '');
    $type  = (string) $val($item, array('type', 'category'), 'other');
    $icon  = $type === 'sport' ? $sportIcon($title) : (isset($typeIcon[$type]) ? $typeIcon[$type] : 'event');
    $range = $time !== '' ? ($time . ($end !== '' ? ' - ' . $end : '')) : '';

    $html  = '<div class="agx-card">';
    $html .= '<span class="agx-card-ico material-symbols-outlined">' . $icon . '</span>';
    $html .= '<div class="agx-card-body">';
    if ($range !== '') {
        $html .= '<span class="agx-card-time">(' . $e($range) . ')</span>';
    }
    $html .= '<strong class="agx-card-title">' . $e($title) . '</strong>';
    if ($place !== '') {
        $html .= '<span class="agx-card-place">' . $e($place) . '</span>';
    }
    if ($desc !== '') {
        $html .= '<div class="agx-card-desc">' . nl2br($e($desc)) . '</div>';
    }
    $html .= '</div></div>';
    return $html;
};
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
      <h1 class="fw-bold text-white display-6 mb-2">Agenda Đại hội Mường Thanh 2026</h1>
      <p class="text-white-50 mb-0" style="max-width:640px;">
        Hội tụ bản sắc · Dẫn dắt tương lai — toàn bộ chương trình <?php echo $e($eventName); ?> theo từng ngày
        và từng buổi. Lịch có thể được Ban Tổ chức cập nhật.
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
            <span class="text-secondary small">Chương trình chi tiết theo buổi</span>
          </div>
        </div>

        <?php foreach ($d['sessions'] as $sid => $items):
          $ses = isset($sessions[$sid]) ? $sessions[$sid] : $sessions[1];
          // Tách môn thể thao (gom lưới) và hoạt động thường, giữ thứ tự.
          // Thuật toán: gặp hoạt động thường -> xả lưới thể thao đang gom (nếu có)
          // rồi render hoạt động thường; cuối buổi xả nốt phần còn lại.
          $blocks = array();   // mỗi phần tử: ['kind'=>'single'|'grid', 'items'=>[...]]
          $buf = array();
          foreach ($items as $it) {
            $type = (string) $val($it, array('type', 'category'), 'other');
            if ($type === 'sport') {
              $buf[] = $it;
            } else {
              if (!empty($buf)) { $blocks[] = array('kind' => 'grid', 'items' => $buf); $buf = array(); }
              $blocks[] = array('kind' => 'single', 'items' => array($it));
            }
          }
          if (!empty($buf)) { $blocks[] = array('kind' => 'grid', 'items' => $buf); }
        ?>
        <section class="agx-session <?php echo $ses['cls']; ?>">
          <div class="agx-ses-head">
            <span class="agx-ses-icon material-symbols-outlined"><?php echo $ses['icon']; ?></span>
            <h3 class="agx-ses-label"><?php echo $e(strtoupper($ses['label'])); ?></h3>
          </div>

          <div class="agx-rail">
            <?php foreach ($blocks as $block):
              if ($block['kind'] === 'single') {
                $it = $block['items'][0];
                $time = (string) $val($it, array('time', 'start_time'), '');
                $end  = (string) $val($it, array('end_time'), '');
              } else {
                // Lưới thể thao: mốc giờ = sớm nhất -> muộn nhất trong nhóm.
                $starts = array(); $ends = array();
                foreach ($block['items'] as $it2) {
                  $s = (string) $val($it2, array('time', 'start_time'), '');
                  $en = (string) $val($it2, array('end_time'), '');
                  if ($s !== '')  $starts[] = $s;
                  if ($en !== '') $ends[] = $en;
                }
                sort($starts); sort($ends);
                $time = !empty($starts) ? $starts[0] : '';
                $end  = !empty($ends) ? $ends[count($ends) - 1] : '';
              }
            ?>
            <div class="agx-row">
              <div class="agx-time">
                <span class="agx-time-start"><?php echo $e($time !== '' ? $time : '—'); ?></span>
                <?php if ($end !== ''): ?><span class="agx-time-end"><?php echo $e($end); ?></span><?php endif; ?>
              </div>
              <div class="agx-dot"></div>
              <div class="agx-content">
                <?php if ($block['kind'] === 'single'): ?>
                  <?php echo $renderCard($block['items'][0]); ?>
                <?php else: ?>
                  <div class="agx-grid">
                    <?php foreach ($block['items'] as $it2) { echo $renderCard($it2); } ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endforeach; ?>
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
