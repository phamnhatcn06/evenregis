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

// Ảnh sơ đồ (nếu Ban Tổ chức đã cập nhật qua event).
$mapImage = isset($event['venue_map']) && $event['venue_map'] !== '' ? $event['venue_map'] : '';

// Các khu vực chính tại địa điểm Đại hội (theo Sổ tay).
$areas = array(
    array('icon' => 'meeting_room', 'label' => 'Hội trường chính'),
    array('icon' => 'restaurant', 'label' => 'Nhà hàng'),
    array('icon' => 'hotel', 'label' => 'Khu lưu trú'),
    array('icon' => 'sports_soccer', 'label' => 'Khu thể thao'),
    array('icon' => 'female', 'label' => 'Khu vực Miss'),
    array('icon' => 'directions_bus', 'label' => 'Xe đưa đón'),
    array('icon' => 'medical_services', 'label' => 'Y tế'),
    array('icon' => 'info', 'label' => 'Quầy Thông tin'),
    array('icon' => 'wc', 'label' => 'WC'),
    array('icon' => 'emergency_share', 'label' => 'Lối thoát hiểm'),
    array('icon' => 'pin_drop', 'label' => 'Điểm tập kết khẩn cấp'),
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
      <p class="text-secondary small mb-0">Khách sạn Mường Thanh Luxury Ninh Bình - các khu vực chức năng phục vụ Quý Đại biểu.</p>
    </div>

    <div class="card border-0 rounded-4 shadow-sm bg-white mb-4 overflow-hidden">
      <?php if ($mapImage !== ''): ?>
        <img src="<?php echo $e($mapImage); ?>" alt="Sơ đồ địa điểm Đại hội" class="w-100" style="object-fit:contain;">
      <?php else: ?>
        <div class="d-flex flex-column align-items-center justify-content-center text-secondary p-5" style="min-height:260px;background:<?php echo $e($accent); ?>0d;">
          <span class="material-symbols-outlined mb-2" style="font-size:56px;color:<?php echo $e($accent); ?>;">map</span>
          <p class="mb-0">Sơ đồ chi tiết sẽ được Ban Tổ chức cập nhật.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">place</span>
      <h2 class="fw-bold text-dark fs-5 mb-0">Các khu vực chính</h2>
    </div>

    <div class="row g-3">
      <?php foreach ($areas as $a): ?>
      <div class="col-6 col-md-4 col-xl-3">
        <div class="card border rounded-4 shadow-sm h-100">
          <div class="p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;background:<?php echo $e($accent); ?>1a;">
              <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;"><?php echo $e($a['icon']); ?></span>
            </div>
            <strong class="text-dark small"><?php echo $e($a['label']); ?></strong>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
