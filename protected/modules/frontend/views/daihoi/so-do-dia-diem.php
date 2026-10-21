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
        'name' => 'Sân Golf Tràng An',
        'theme' => 'golf',
        'address' => 'Phú Long, Ninh Bình',
        'date' => '25.10.2026',
        'distance' => '26 km',
        'icon' => 'sports_golf',
        'note' => 'Thi đấu môn Golf & Giải chạy Fun Run',
        'img' => $mascot . '/golf.png',
        'map' => $mapUrl('Sân Golf Tràng An Phú Long Ninh Bình'),
        'features' => array(
            array('icon' => 'golf_course', 'text' => 'Sân 18 hố tiêu chuẩn quốc tế'),
            array('icon' => 'directions_run', 'text' => 'Điểm xuất phát Giải chạy Fun Run'),
        ),
    ),
    'stadium' => array(
        'name' => 'Nhà thi đấu tỉnh Ninh Bình',
        'theme' => 'stadium',
        'address' => '320 Đinh Tiên Hoàng, Hoa Lư, Ninh Bình',
        'date' => '27 - 29.10.2026',
        'distance' => '2.4 km',
        'icon' => 'stadium',
        'note' => 'Cầu lông · Bóng bàn · Kéo co',
        'img' => $mascot . '/badminton.png',
        'map' => $mapUrl('Nhà thi đấu tỉnh Ninh Bình 320 Đinh Tiên Hoàng Hoa Lư'),
        'sports' => array(
            array('id' => 'badminton', 'label' => 'Cầu lông', 'icon' => 'sports_tennis', 'img' => $mascot . '/badminton.png', 'note' => 'Thi đấu môn Cầu lông (Đôi nam, Đôi nữ, Đôi nam nữ)'),
            array('id' => 'table-tennis', 'label' => 'Bóng bàn', 'icon' => 'sports_tennis', 'img' => $mascot . '/table-tennis.png', 'note' => 'Thi đấu môn Bóng bàn (Đơn nam, Đơn nữ)'),
            array('id' => 'tug-of-war', 'label' => 'Kéo co', 'icon' => 'fitness_center', 'img' => $mascot . '/tug-of-war.png', 'note' => 'Thi đấu môn Kéo co đồng đội nam nữ'),
        ),
        'features' => array(
            array('icon' => 'verified', 'text' => 'Nhà thi đấu tiêu chuẩn Quốc gia'),
            array('icon' => 'stadium', 'text' => '3 bộ môn thi đấu chính thức'),
        ),
    ),
    'soccer' => array(
        'name' => 'Sân bóng Teddy',
        'theme' => 'soccer',
        'address' => 'Khu đô thị, Hoa Lư, Ninh Bình',
        'date' => '27 - 29.10.2026',
        'distance' => '2.3 km',
        'icon' => 'sports_soccer',
        'note' => 'Thi đấu môn Bóng đá',
        'img' => $mascot . '/football.png',
        'map' => $mapUrl('Sân bóng Teddy Khu đô thị Hoa Lư Ninh Bình'),
        'features' => array(
            array('icon' => 'sports_soccer', 'text' => 'Cụm sân cỏ nhân tạo 7 người hiện đại'),
            array('icon' => 'schedule', 'text' => 'Thi đấu vòng bảng & chung kết'),
        ),
    ),
);

// Sơ đồ mặt bằng các tầng
$floors = array(
    array(
        'id' => 't1',
        'label' => 'Tầng 1',
        'desc' => 'Sảnh chính · Hội trường lớn · Bể bơi',
        'img' => $soDo . '/MB_t1.jpg',
        'tags' => array('Hội trường lớn', 'Bể bơi', 'Sảnh chính', 'Photo Booth', 'Khu WC')
    ),
    array(
        'id' => 't2',
        'label' => 'Tầng 2',
        'desc' => 'Nhà hàng · Khu thi F&B · Gala & Trao giải',
        'img' => $soDo . '/MB_t2.jpg',
        'tags' => array('Nhà hàng', 'Khu thi F&B', 'Gala & Trao giải')
    ),
    array(
        'id' => 't3',
        'label' => 'Tầng 3',
        'desc' => 'Hội trường thi nghiệp vụ & Phòng chức năng',
        'img' => $soDo . '/MB_t3.jpg',
        'tags' => array('Hội trường thi nghiệp vụ', 'Phòng chức năng')
    ),
);

Yii::app()->clientScript->registerCssFile(
    $soDo . '.css?v=' . (@filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/so-do.css') ?: time())
);
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'map')); ?>

<main class="flex-grow-1 dh-map-accent">
  <section class="container py-4">
    <!-- Header trang -->
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Định hướng Đại hội</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">map</span>
        Sơ đồ địa điểm Đại hội
      </h1>
      <p class="text-secondary small mb-0">Các khu vực tổ chức sự kiện thi đấu thể thao và sơ đồ mặt bằng khách sạn Mường Thanh Luxury Ninh Bình.</p>
    </div>

    <!-- Banner Bản đồ toàn cảnh các khu vực sự kiện -->
    <div class="dh-master-card mb-5 p-3 p-md-4">
      <div class="row align-items-center g-4">
        <div class="col-12 col-lg-7">
          <span class="dh-chip dh-chip--hero mb-2">
            <span class="material-symbols-outlined">explore</span> Sơ đồ tổng thể Đại hội
          </span>
          <h2 class="fw-bold text-dark fs-4 mb-2">Bản đồ toàn cảnh các khu vực sự kiện</h2>
          <p class="text-secondary small mb-3" style="line-height: 1.6;">
            Trực quan hóa toàn bộ hệ thống địa điểm thi đấu, trung tâm điều hành Khách sạn Mường Thanh Luxury Ninh Bình cùng các cự ly di chuyển <b>26 km · 2.4 km · 2.3 km</b> và lịch trình sự kiện chi tiết.
          </p>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-teal-solid d-inline-flex align-items-center gap-2 fw-semibold px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modal-master-map">
              <span class="material-symbols-outlined fs-5">zoom_in</span> Xem bản đồ chi tiết (Phóng to)
            </button>
            <a href="<?php echo $e($soDo); ?>/khu_vuc_thi_dau.png" target="_blank" download="So-do-dia-diem-Dai-hoi-2026.png" class="btn btn-outline-teal d-inline-flex align-items-center gap-2 fw-semibold px-3 py-2 rounded-3">
              <span class="material-symbols-outlined fs-5">download</span> Tải ảnh gốc (HD)
            </a>
          </div>
        </div>
        <div class="col-12 col-lg-5 text-center">
          <div class="dh-master-thumb" data-bs-toggle="modal" data-bs-target="#modal-master-map" role="button" title="Nhấn để phóng to bản đồ">
            <img src="<?php echo $e($soDo); ?>/khu_vuc_thi_dau_web.jpg" alt="Bản đồ các khu vực sự kiện Đại hội" loading="lazy">
            <span class="dh-master-thumb__badge">
              <span class="material-symbols-outlined">fullscreen</span> Phóng to bản đồ
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tiêu đề cụm địa điểm -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">near_me</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Các khu vực tổ chức thi đấu & Khách sạn trung tâm</h2>
    </div>

    <!-- Infographic hub & spokes -->
    <div class="dh-hub mb-5">
      <!-- Hub trung tâm: Khách sạn -->
      <div class="dh-node dh-node--hub dh-area-hub">
        <div class="dh-node__media dh-node__media--hub">
          <span class="dh-media-tag dh-media-tag--gold">
            <span class="material-symbols-outlined">apartment</span> Trung tâm Đại hội
          </span>
          <img src="<?php echo $e($mascot); ?>/hotel.png" alt="Khách sạn Mường Thanh Luxury Ninh Bình" loading="lazy">
        </div>
        <div class="dh-node__title">Khách sạn Mường Thanh Luxury Ninh Bình</div>
        <p class="dh-node__addr"><span class="material-symbols-outlined align-middle" style="font-size:15px; color:<?php echo $e($accent); ?>;">location_on</span> Khu vực trung tâm Đại hội · Khách sạn 5 sao</p>
        <ul class="dh-sched">
          <?php foreach ($hotelSchedule as $s): ?>
          <li><b><?php echo $e($s['date']); ?></b><span><?php echo $e($s['text']); ?></span></li>
          <?php endforeach; ?>
        </ul>
        <div class="dh-feature-list mb-3">
          <div class="dh-sport-item"><span class="material-symbols-outlined">apartment</span> <span>Địa điểm lưu trú chính & Trung tâm điều hành</span></div>
          <div class="dh-sport-item"><span class="material-symbols-outlined">pool</span> <span>Tổ chức thi Bơi lội & Giao lưu Pickleball</span></div>
        </div>
        <a href="<?php echo $e($mapUrl('Khách sạn Mường Thanh Luxury Ninh Bình')); ?>" target="_blank" rel="noopener" class="dh-map-btn">
          <span class="material-symbols-outlined">map</span> Chỉ đường Google Maps
        </a>
      </div>

      <!-- Sân Golf Tràng An -->
      <div class="dh-node dh-node--golf dh-area-golf">
        <div class="dh-node__media">
          <span class="dh-media-tag">
            <span class="material-symbols-outlined">sports_golf</span> 26 km
          </span>
          <img src="<?php echo $e($venues['golf']['img']); ?>" alt="<?php echo $e($venues['golf']['name']); ?>" loading="lazy">
        </div>
        <div class="dh-node__title"><?php echo $e($venues['golf']['name']); ?></div>
        <p class="dh-node__addr"><span class="material-symbols-outlined align-middle" style="font-size:15px; color:<?php echo $e($accent); ?>;">location_on</span> <?php echo $e($venues['golf']['address']); ?></p>
        <div class="dh-badges">
          <span class="dh-chip dh-chip--date"><?php echo $e($venues['golf']['date']); ?></span>
          <span class="dh-chip dh-chip--dist"><span class="material-symbols-outlined">near_me</span><?php echo $e($venues['golf']['distance']); ?></span>
        </div>
        <div class="dh-feature-list mb-3">
          <?php foreach ($venues['golf']['features'] as $ft): ?>
            <div class="dh-sport-item"><span class="material-symbols-outlined"><?php echo $e($ft['icon']); ?></span> <span><?php echo $e($ft['text']); ?></span></div>
          <?php endforeach; ?>
        </div>
        <a href="<?php echo $e($venues['golf']['map']); ?>" target="_blank" rel="noopener" class="dh-map-btn">
          <span class="material-symbols-outlined">map</span> Chỉ đường Google Maps
        </a>
      </div>

      <!-- Nhà thi đấu tỉnh Ninh Bình (hỗ trợ chuyển môn Cầu lông / Bóng bàn / Kéo co) -->
      <div class="dh-node dh-node--stadium dh-area-stadium" id="stadium-card">
        <div class="dh-node__media">
          <span class="dh-media-tag">
            <span class="material-symbols-outlined">stadium</span> 2.4 km
          </span>
          <img id="stadium-mascot-img" src="<?php echo $e($venues['stadium']['img']); ?>" alt="<?php echo $e($venues['stadium']['name']); ?>" loading="lazy">
        </div>
        <div class="dh-node__title"><?php echo $e($venues['stadium']['name']); ?></div>
        <p class="dh-node__addr"><span class="material-symbols-outlined align-middle" style="font-size:15px; color:<?php echo $e($accent); ?>;">location_on</span> <?php echo $e($venues['stadium']['address']); ?></p>
        
        <!-- Bộ chọn 3 môn thi đấu -->
        <div class="dh-sport-tabs" role="tablist">
          <?php foreach ($venues['stadium']['sports'] as $idx => $sp): ?>
          <button type="button" class="dh-sport-tab <?php echo ($idx === 0) ? 'active' : ''; ?>" data-img="<?php echo $e($sp['img']); ?>" data-note="<?php echo $e($sp['note']); ?>">
            <span class="material-symbols-outlined align-middle" style="font-size:15px;"><?php echo $e($sp['icon']); ?></span> <?php echo $e($sp['label']); ?>
          </button>
          <?php endforeach; ?>
        </div>

        <div class="dh-badges">
          <span class="dh-chip dh-chip--date"><?php echo $e($venues['stadium']['date']); ?></span>
          <span class="dh-chip dh-chip--dist"><span class="material-symbols-outlined">near_me</span><?php echo $e($venues['stadium']['distance']); ?></span>
        </div>
        <p class="dh-node__note" id="stadium-sport-note"><?php echo $e($venues['stadium']['sports'][0]['note']); ?></p>

        <div class="dh-feature-list mb-3">
          <?php foreach ($venues['stadium']['features'] as $ft): ?>
            <div class="dh-sport-item"><span class="material-symbols-outlined"><?php echo $e($ft['icon']); ?></span> <span><?php echo $e($ft['text']); ?></span></div>
          <?php endforeach; ?>
        </div>

        <a href="<?php echo $e($venues['stadium']['map']); ?>" target="_blank" rel="noopener" class="dh-map-btn">
          <span class="material-symbols-outlined">map</span> Chỉ đường Google Maps
        </a>
      </div>

      <!-- Sân bóng Teddy -->
      <div class="dh-node dh-node--soccer dh-area-soccer">
        <div class="dh-node__media">
          <span class="dh-media-tag">
            <span class="material-symbols-outlined">sports_soccer</span> 2.3 km
          </span>
          <img src="<?php echo $e($venues['soccer']['img']); ?>" alt="<?php echo $e($venues['soccer']['name']); ?>" loading="lazy">
        </div>
        <div class="dh-node__title"><?php echo $e($venues['soccer']['name']); ?></div>
        <p class="dh-node__addr"><span class="material-symbols-outlined align-middle" style="font-size:15px; color:<?php echo $e($accent); ?>;">location_on</span> <?php echo $e($venues['soccer']['address']); ?></p>
        <div class="dh-badges">
          <span class="dh-chip dh-chip--date"><?php echo $e($venues['soccer']['date']); ?></span>
          <span class="dh-chip dh-chip--dist"><span class="material-symbols-outlined">near_me</span><?php echo $e($venues['soccer']['distance']); ?></span>
        </div>
        <div class="dh-feature-list mb-3">
          <?php foreach ($venues['soccer']['features'] as $ft): ?>
            <div class="dh-sport-item"><span class="material-symbols-outlined"><?php echo $e($ft['icon']); ?></span> <span><?php echo $e($ft['text']); ?></span></div>
          <?php endforeach; ?>
        </div>
        <a href="<?php echo $e($venues['soccer']['map']); ?>" target="_blank" rel="noopener" class="dh-map-btn">
          <span class="material-symbols-outlined">map</span> Chỉ đường Google Maps
        </a>
      </div>
    </div>

    <!-- Sơ đồ mặt bằng các tầng -->
    <div class="d-flex align-items-center gap-2 mb-2">
      <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">floor</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Sơ đồ mặt bằng khách sạn</h2>
    </div>
    <p class="text-secondary small mb-3">Nhấn vào từng tầng để xem sơ đồ mặt bằng chi tiết độ phân giải cao và phóng to.</p>

    <div class="row g-3">
      <?php foreach ($floors as $f): ?>
      <div class="col-12 col-md-4">
        <button type="button" class="dh-floor" data-bs-toggle="modal" data-bs-target="#modal-floor-<?php echo $e($f['id']); ?>" title="Nhấn để xem phóng to sơ đồ <?php echo $e($f['label']); ?>">
          <div class="dh-floor__img">
            <img src="<?php echo $e($f['img']); ?>" alt="Sơ đồ mặt bằng <?php echo $e($f['label']); ?>" loading="lazy">
            <span class="dh-floor__tag"><?php echo $e($f['label']); ?></span>
            <span class="dh-floor__zoom">
              <span class="material-symbols-outlined">zoom_in</span> Xem chi tiết
            </span>
          </div>
          <div class="p-3">
            <strong class="text-dark d-block mb-1">Mặt bằng <?php echo $e($f['label']); ?></strong>
            <span class="text-secondary small d-block"><?php echo $e($f['desc']); ?></span>
            <div class="dh-facility-chips">
              <?php foreach ($f['tags'] as $tag): ?>
                <span class="dh-facility-chip"><?php echo $e($tag); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<!-- Modal xem Bản đồ toàn cảnh Master Infographic -->
<div class="modal fade" id="modal-master-map" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
      <div class="modal-header bg-white py-3 px-4 border-bottom">
        <div>
          <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-1">
            <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">explore</span>
            Bản đồ toàn cảnh các khu vực sự kiện Đại hội 2026
          </h5>
          <span class="text-secondary small">Trực quan hóa cự ly di chuyển, địa điểm thi đấu và lịch trình</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="<?php echo $e($soDo); ?>/khu_vuc_thi_dau.png" target="_blank" download="So-do-dia-diem-Dai-hoi-2026.png" class="btn btn-sm btn-outline-teal d-none d-sm-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6">download</span> Tải bản gốc HD
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
      </div>
      <div class="modal-body p-0 text-center bg-dark position-relative">
        <div class="dh-modal-viewer">
          <img id="master-map-view" src="<?php echo $e($soDo); ?>/khu_vuc_thi_dau_web.jpg" alt="Bản đồ các khu vực sự kiện Đại hội" style="transform: scale(1); max-height: 80vh;" loading="lazy">
        </div>
        <!-- Thanh công cụ phóng to / thu nhỏ -->
        <div class="text-center pb-3">
          <div class="dh-zoom-toolbar">
            <button type="button" class="dh-zoom-btn" onclick="dhZoomImage('master-map-view', -0.2)" title="Thu nhỏ">
              <span class="material-symbols-outlined">zoom_out</span>
            </button>
            <button type="button" class="dh-zoom-btn" onclick="dhZoomImage('master-map-view', 0)" title="Về ban đầu">
              <span class="material-symbols-outlined">restart_alt</span>
            </button>
            <button type="button" class="dh-zoom-btn" onclick="dhZoomImage('master-map-view', 0.2)" title="Phóng to">
              <span class="material-symbols-outlined">zoom_in</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal sơ đồ từng tầng có công cụ phóng to / thu nhỏ -->
<?php foreach ($floors as $f): ?>
<div class="modal fade" id="modal-floor-<?php echo $e($f['id']); ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
      <div class="modal-header bg-white py-3 px-4 border-bottom">
        <div>
          <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-1">
            <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">floor</span>
            Sơ đồ mặt bằng <?php echo $e($f['label']); ?>
          </h5>
          <span class="text-secondary small"><?php echo $e($f['desc']); ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="<?php echo $e($f['img']); ?>" target="_blank" download="So-do-mat-bang-<?php echo $e($f['id']); ?>.jpg" class="btn btn-sm btn-outline-teal d-none d-sm-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6">download</span> Tải ảnh 4K
          </a>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
      </div>
      <div class="modal-body p-0 text-center bg-dark position-relative">
        <div class="dh-modal-viewer">
          <img id="floor-view-<?php echo $e($f['id']); ?>" src="<?php echo $e($f['img']); ?>" alt="Sơ đồ mặt bằng <?php echo $e($f['label']); ?>" style="transform: scale(1); max-height: 80vh;" loading="lazy">
        </div>
        <div class="text-center pb-3">
          <div class="dh-zoom-toolbar">
            <button type="button" class="dh-zoom-btn" onclick="dhZoomImage('floor-view-<?php echo $e($f['id']); ?>', -0.2)" title="Thu nhỏ">
              <span class="material-symbols-outlined">zoom_out</span>
            </button>
            <button type="button" class="dh-zoom-btn" onclick="dhZoomImage('floor-view-<?php echo $e($f['id']); ?>', 0)" title="Về ban đầu">
              <span class="material-symbols-outlined">restart_alt</span>
            </button>
            <button type="button" class="dh-zoom-btn" onclick="dhZoomImage('floor-view-<?php echo $e($f['id']); ?>', 0.2)" title="Phóng to">
              <span class="material-symbols-outlined">zoom_in</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
// Interactive Sport Switcher cho Nhà thi đấu Ninh Bình
document.addEventListener('DOMContentLoaded', function () {
  var sportTabs = document.querySelectorAll('.dh-sport-tab');
  var mascotImg = document.getElementById('stadium-mascot-img');
  var sportNote = document.getElementById('stadium-sport-note');

  sportTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      sportTabs.forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');

      var newImg = tab.getAttribute('data-img');
      var newNote = tab.getAttribute('data-note');

      if (mascotImg && newImg) {
        mascotImg.style.opacity = '0';
        mascotImg.style.transform = 'scale(0.9)';
        setTimeout(function () {
          mascotImg.src = newImg;
          mascotImg.style.opacity = '1';
          mascotImg.style.transform = 'scale(1)';
        }, 150);
      }
      if (sportNote && newNote) {
        sportNote.textContent = newNote;
      }
    });
  });
});

// Interactive Zoom Controller
var dhZoomLevels = {};
function dhZoomImage(id, delta) {
  var img = document.getElementById(id);
  if (!img) return;
  if (typeof dhZoomLevels[id] === 'undefined') {
    dhZoomLevels[id] = 1;
  }
  if (delta === 0) {
    dhZoomLevels[id] = 1;
  } else {
    dhZoomLevels[id] = Math.max(0.6, Math.min(3.0, dhZoomLevels[id] + delta));
  }
  img.style.transform = 'scale(' + dhZoomLevels[id] + ')';
}
</script>
