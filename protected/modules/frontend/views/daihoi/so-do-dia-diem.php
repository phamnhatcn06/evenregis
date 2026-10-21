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

// Link mở trực tiếp trên ứng dụng bản đồ (Google Maps / Apple Maps đều nhận).
$mapUrl = function ($query) {
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
};

// Các địa điểm chính của Đại hội (theo "Map các khu vực sự kiện Đại hội").
$venues = array(
    array(
        'icon' => 'golf_course',
        'name' => 'Sân Golf Tràng An',
        'address' => 'Phú Long, Ninh Bình',
        'date' => '25.10.2026',
        'distance' => '26 km',
        'note' => '',
        'map' => $mapUrl('Sân Golf Tràng An Phú Long Ninh Bình'),
    ),
    array(
        'icon' => 'hotel',
        'name' => 'Khách sạn Mường Thanh Luxury Ninh Bình',
        'address' => 'Khu vực trung tâm Đại hội',
        'date' => '27 - 29.10.2026',
        'distance' => '',
        'note' => '27.10 Khai mạc: Ballroom Hoa Lư - Tầng 1 · 28.10 Thi nghiệp vụ: Tầng 2, 3 · 29.10 Gala: Ballroom Hoa Lư - Tầng 1',
        'map' => $mapUrl('Khách sạn Mường Thanh Luxury Ninh Bình'),
    ),
    array(
        'icon' => 'stadium',
        'name' => 'Nhà thi đấu tỉnh Ninh Bình',
        'address' => '320 Đinh Tiên Hoàng, Hoa Lư, Ninh Bình',
        'date' => '27 - 29.10.2026',
        'distance' => '2.4 km',
        'note' => 'Cầu lông, bóng bàn, kéo co...',
        'map' => $mapUrl('Nhà thi đấu tỉnh Ninh Bình 320 Đinh Tiên Hoàng Hoa Lư'),
    ),
    array(
        'icon' => 'sports_soccer',
        'name' => 'Sân bóng Teddy',
        'address' => 'Khu đô thị, Hoa Lư, Ninh Bình',
        'date' => '27 - 29.10.2026',
        'distance' => '2.3 km',
        'note' => 'Bóng đá',
        'map' => $mapUrl('Sân bóng Teddy Khu đô thị Hoa Lư Ninh Bình'),
    ),
);

// Sơ đồ mặt bằng các tầng tại khách sạn - mở popup xem ảnh.
$floors = array(
    array('id' => 't1', 'label' => 'Tầng 1', 'desc' => 'Sảnh chính, Hội trường, Hồ bơi', 'img' => $soDo . '/MB_t1.jpg'),
    array('id' => 't2', 'label' => 'Tầng 2', 'desc' => 'Nhà hàng, khu vực thi FB, trao giải', 'img' => $soDo . '/MB_t2.jpg'),
    array('id' => 't3', 'label' => 'Tầng 3', 'desc' => 'Hội trường thi nghiệp vụ', 'img' => $soDo . '/MB_t3.jpg'),
);
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'map')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Định hướng Đại hội</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">map</span>
        Sơ đồ địa điểm Đại hội
      </h1>
      <p class="text-secondary small mb-0">Các khu vực tổ chức sự kiện và sơ đồ mặt bằng khách sạn Mường Thanh Luxury Ninh Bình.</p>
    </div>

    <!-- Map tổng quan các khu vực sự kiện -->
    <div class="card border-0 rounded-4 shadow-sm bg-white mb-4 overflow-hidden">
      <img src="<?php echo $e($soDo); ?>/khu_vuc_thi_dau.png" alt="Map các khu vực sự kiện Đại hội" class="w-100" style="object-fit:contain;" loading="lazy">
    </div>

    <!-- Danh sách địa điểm -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">place</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Các địa điểm sự kiện</h2>
    </div>

    <div class="row g-3 mb-5">
      <?php foreach ($venues as $v): ?>
      <div class="col-12 col-lg-6">
        <div class="card border rounded-4 shadow-sm h-100">
          <div class="p-3 d-flex gap-3">
            <div class="rounded-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:<?php echo $e($accent); ?>1a;">
              <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;font-size:28px;"><?php echo $e($v['icon']); ?></span>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <strong class="text-dark"><?php echo $e($v['name']); ?></strong>
                <span class="badge rounded-pill" style="background:<?php echo $e($accent); ?>;"><?php echo $e($v['date']); ?></span>
                <?php if ($v['distance'] !== ''): ?>
                  <span class="badge rounded-pill bg-light text-dark border">
                    <span class="material-symbols-outlined align-middle" style="font-size:14px;">near_me</span>
                    <?php echo $e($v['distance']); ?>
                  </span>
                <?php endif; ?>
              </div>
              <p class="text-secondary small mb-2">
                <span class="material-symbols-outlined align-middle" style="font-size:16px;">location_on</span>
                <?php echo $e($v['address']); ?>
              </p>
              <?php if ($v['note'] !== ''): ?>
                <p class="text-muted small mb-2"><?php echo $e($v['note']); ?></p>
              <?php endif; ?>
              <a href="<?php echo $e($v['map']); ?>" target="_blank" rel="noopener"
                 class="btn btn-sm text-white d-inline-flex align-items-center gap-1" style="background:<?php echo $e($accent); ?>;">
                <span class="material-symbols-outlined" style="font-size:18px;">map</span>
                Bản đồ
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Sơ đồ mặt bằng các tầng -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">floor</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Sơ đồ mặt bằng khách sạn</h2>
    </div>
    <p class="text-secondary small mb-3">Nhấn vào từng tầng để xem sơ đồ chi tiết.</p>

    <div class="row g-3">
      <?php foreach ($floors as $f): ?>
      <div class="col-12 col-md-4">
        <button type="button" class="card border rounded-4 shadow-sm h-100 w-100 text-start p-0 bg-white"
                data-bs-toggle="modal" data-bs-target="#modal-floor-<?php echo $e($f['id']); ?>">
          <div class="rounded-top-4 overflow-hidden position-relative" style="background:#f1f5f9;">
            <img src="<?php echo $e($f['img']); ?>" alt="Sơ đồ mặt bằng <?php echo $e($f['label']); ?>" class="w-100" style="height:160px;object-fit:cover;" loading="lazy">
            <span class="position-absolute top-0 start-0 m-2 badge rounded-pill text-white" style="background:<?php echo $e($accent); ?>;"><?php echo $e($f['label']); ?></span>
            <span class="position-absolute bottom-0 end-0 m-2 badge rounded-circle bg-dark bg-opacity-50 d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
              <span class="material-symbols-outlined text-white" style="font-size:18px;">zoom_in</span>
            </span>
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
