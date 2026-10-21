<?php
/**
 * Trang "Sơ đồ địa điểm Đại hội" - slug /so-do-dia-diem.
 *
 * @var array $event
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$accent = '#0d9488';
$soDo = $base . '/public/daihoi/so-do';
$mascot = $soDo . '/mascots';

// Link mở trực tiếp trên ứng dụng bản đồ (Google Maps / Apple Maps đều nhận).
$mapUrl = function ($query) {
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
};

// Lịch tại khách sạn (hub trung tâm).
$hotelSchedule = array(
    array('date' => '27.10', 'text' => 'Khai mạc: Ballroom Hoa Lư - Tầng 1'),
    array('date' => '28.10', 'text' => 'Thi nghiệp vụ: Tầng 2, 3'),
    array('date' => '29.10', 'text' => 'Gala: Ballroom Hoa Lư - Tầng 1'),
);

// Các địa điểm vệ tinh (theo "Map các khu vực sự kiện Đại hội").
$venues = array(
    'golf' => array(
        'icon' => 'golf_course',
        'name' => 'Sân Golf Tràng An',
        'address' => 'Phú Long, Ninh Bình',
        'date' => '25.10.2026',
        'distance' => '26 km',
        'note' => 'Golf',
        'img' => $mascot . '/golf.png',
        'map' => $mapUrl('Sân Golf Tràng An Phú Long Ninh Bình'),
    ),
    'stadium' => array(
        'icon' => 'stadium',
        'name' => 'Nhà thi đấu tỉnh Ninh Bình',
        'address' => '320 Đinh Tiên Hoàng, Hoa Lư, Ninh Bình',
        'date' => '27 - 29.10.2026',
        'distance' => '2.4 km',
        'note' => 'Cầu lông · Bóng bàn · Kéo co',
        'img' => $mascot . '/badminton.png',
        'map' => $mapUrl('Nhà thi đấu tỉnh Ninh Bình 320 Đinh Tiên Hoàng Hoa Lư'),
    ),
    'soccer' => array(
        'icon' => 'sports_soccer',
        'name' => 'Sân bóng Teddy',
        'address' => 'Khu đô thị, Hoa Lư, Ninh Bình',
        'date' => '27 - 29.10.2026',
        'distance' => '2.3 km',
        'note' => 'Bóng đá',
        'map' => $mapUrl('Sân bóng Teddy Khu đô thị Hoa Lư Ninh Bình'),
    ),
);

$renderVenue = function ($v, $areaClass) use ($e) {
    ?>
    <div class="dh-node <?php echo $e($areaClass); ?>">
      <div class="dh-node__head">
        <div class="dh-node__icon"><span class="material-symbols-outlined"><?php echo $e($v['icon']); ?></span></div>
        <div class="min-w-0">
          <div class="dh-node__title"><?php echo $e($v['name']); ?></div>
          <p class="dh-node__addr"><span class="material-symbols-outlined align-middle" style="font-size:14px;">location_on</span> <?php echo $e($v['address']); ?></p>
        </div>
      </div>
      <div class="dh-badges">
        <span class="dh-chip dh-chip--date"><?php echo $e($v['date']); ?></span>
        <?php if ($v['distance'] !== ''): ?>
          <span class="dh-chip dh-chip--dist"><span class="material-symbols-outlined">near_me</span><?php echo $e($v['distance']); ?></span>
        <?php endif; ?>
      </div>
      <?php if ($v['note'] !== ''): ?><p class="dh-node__note"><?php echo $e($v['note']); ?></p><?php endif; ?>
      <a href="<?php echo $e($v['map']); ?>" target="_blank" rel="noopener" class="dh-map-btn">
        <span class="material-symbols-outlined">map</span> Bản đồ
      </a>
    </div>
    <?php
};

// Sơ đồ mặt bằng các tầng.
$floors = array(
    array('id' => 't1', 'label' => 'Tầng 1', 'desc' => 'Sảnh chính · Hội trường · Hồ bơi', 'img' => $soDo . '/MB_t1.jpg'),
    array('id' => 't2', 'label' => 'Tầng 2', 'desc' => 'Nhà hàng · Khu thi FB · Trao giải', 'img' => $soDo . '/MB_t2.jpg'),
    array('id' => 't3', 'label' => 'Tầng 3', 'desc' => 'Hội trường thi nghiệp vụ', 'img' => $soDo . '/MB_t3.jpg'),
);

Yii::app()->clientScript->registerCssFile(
    $soDo . '.css?v=' . (@filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/so-do.css') ?: time())
);
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'map')); ?>

<main class="flex-grow-1 dh-map-accent">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Định hướng Đại hội</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">map</span>
        Sơ đồ địa điểm Đại hội
      </h1>
      <p class="text-secondary small mb-0">Các khu vực tổ chức sự kiện và sơ đồ mặt bằng khách sạn Mường Thanh Luxury Ninh Bình.</p>
    </div>

    <!-- Infographic hub & spokes -->
    <div class="dh-hub mb-5">
      <!-- Hub trung tâm -->
      <div class="dh-node dh-node--hub dh-area-hub">
        <div class="dh-node__head">
          <div class="dh-node__icon"><span class="material-symbols-outlined">hotel</span></div>
          <div class="min-w-0">
            <div class="dh-node__title">Khách sạn Mường Thanh Luxury Ninh Bình</div>
            <p class="dh-node__addr">Khu vực trung tâm Đại hội</p>
          </div>
        </div>
        <ul class="dh-sched">
          <?php foreach ($hotelSchedule as $s): ?>
          <li><b><?php echo $e($s['date']); ?></b><span><?php echo $e($s['text']); ?></span></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?php echo $e($mapUrl('Khách sạn Mường Thanh Luxury Ninh Bình')); ?>" target="_blank" rel="noopener" class="dh-map-btn">
          <span class="material-symbols-outlined">map</span> Bản đồ
        </a>
      </div>

      <?php
      $renderVenue($venues['golf'], 'dh-area-golf');
      $renderVenue($venues['stadium'], 'dh-area-stadium');
      $renderVenue($venues['soccer'], 'dh-area-soccer');
      ?>
    </div>

    <!-- Sơ đồ mặt bằng các tầng -->
    <div class="d-flex align-items-center gap-2 mb-2">
      <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">floor</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Sơ đồ mặt bằng khách sạn</h2>
    </div>
    <p class="text-secondary small mb-3">Nhấn vào từng tầng để xem sơ đồ chi tiết.</p>

    <div class="row g-3">
      <?php foreach ($floors as $f): ?>
      <div class="col-12 col-md-4">
        <button type="button" class="dh-floor" data-bs-toggle="modal" data-bs-target="#modal-floor-<?php echo $e($f['id']); ?>">
          <div class="dh-floor__img">
            <img src="<?php echo $e($f['img']); ?>" alt="Sơ đồ mặt bằng <?php echo $e($f['label']); ?>" loading="lazy">
            <span class="dh-floor__tag"><?php echo $e($f['label']); ?></span>
            <span class="dh-floor__zoom"><span class="material-symbols-outlined">zoom_in</span></span>
          </div>
          <div class="p-3">
            <strong class="text-dark d-block">Mặt bằng <?php echo $e($f['label']); ?></strong>
            <span class="text-secondary small"><?php echo $e($f['desc']); ?></span>
          </div>
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<!-- Modal sơ đồ từng tầng -->
<?php foreach ($floors as $f): ?>
<div class="modal fade" id="modal-floor-<?php echo $e($f['id']); ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 overflow-hidden">
      <div class="modal-header">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
          <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">floor</span>
          Sơ đồ mặt bằng <?php echo $e($f['label']); ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body p-0 bg-light text-center">
        <img src="<?php echo $e($f['img']); ?>" alt="Sơ đồ mặt bằng <?php echo $e($f['label']); ?>" class="img-fluid" loading="lazy">
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
