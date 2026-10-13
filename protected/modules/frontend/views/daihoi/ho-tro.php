<?php
/**
 * Trang "Các đầu mối hỗ trợ" - slug /ho-tro.
 * Nội dung lấy theo Sổ tay Đại hội (Y tế, Xe cấp cứu, An ninh - PCCC).
 *
 * @var array $event
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$accent = '#059669';

// Các nhóm đầu mối hỗ trợ (theo Sổ tay Đại hội).
$groups = array(
    array(
        'title' => 'Y tế',
        'icon' => 'medical_services',
        'accent' => '#dc2626',
        'note' => 'Phòng y tế thường trực 24/24 giờ trong suốt thời gian diễn ra Đại hội.',
        'contacts' => array(
            array('role' => 'Phụ trách phòng y tế', 'name' => 'Bác sĩ Hoàng Xuân Kỳ (PGĐ)', 'phone' => '0982080035'),
        ),
    ),
    array(
        'title' => 'Xe cấp cứu / vận chuyển',
        'icon' => 'emergency',
        'accent' => '#ea580c',
        'note' => 'Khi có sự cố y tế, báo ngay cho Bác sĩ Quang Tuấn Anh và giữ thông thoáng lối đi cho xe cứu thương.',
        'contacts' => array(
            array('role' => 'Lái xe', 'name' => 'Nguyễn Văn Hưng', 'phone' => '0973588031'),
            array('role' => 'Bác sĩ', 'name' => 'Quang Tuấn Anh', 'phone' => '0394115345'),
            array('role' => 'Điều dưỡng', 'name' => 'Ngô Thị Tình', 'phone' => '0972254800'),
        ),
    ),
    array(
        'title' => 'An ninh - PCCC',
        'icon' => 'local_fire_department',
        'accent' => '#2563eb',
        'note' => 'Khi có báo động khẩn cấp, bình tĩnh di chuyển theo hướng dẫn của đội An ninh.',
        'contacts' => array(
            array('role' => 'Liên hệ khẩn cấp', 'name' => 'Mr. Đoàn Thế Cương', 'phone' => '0912211929'),
        ),
    ),
);
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'support')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Thông tin liên hệ</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">support_agent</span>
        Các đầu mối hỗ trợ
      </h1>
      <p class="text-secondary small mb-0">Các số điện thoại và đầu mối hỗ trợ Quý Đại biểu trong suốt thời gian diễn ra Đại hội.</p>
    </div>

    <div class="row g-3">
      <?php foreach ($groups as $g): ?>
      <div class="col-12 col-lg-6">
        <div class="card border-0 rounded-4 shadow-sm bg-white h-100 overflow-hidden" style="border-left:4px solid <?php echo $e($g['accent']); ?> !important;">
          <div class="p-3 p-md-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;background:<?php echo $e($g['accent']); ?>1a;">
                <span class="material-symbols-outlined" style="color:<?php echo $e($g['accent']); ?>;"><?php echo $e($g['icon']); ?></span>
              </div>
              <h2 class="fw-bold text-dark fs-5 mb-0"><?php echo $e($g['title']); ?></h2>
            </div>
            <?php foreach ($g['contacts'] as $c): ?>
            <div class="d-flex align-items-center justify-content-between gap-2 py-2 border-bottom">
              <div>
                <span class="text-uppercase text-muted d-block" style="font-size:10.5px;letter-spacing:.03em;"><?php echo $e($c['role']); ?></span>
                <strong class="text-dark small"><?php echo $e($c['name']); ?></strong>
              </div>
              <a href="tel:<?php echo $e($c['phone']); ?>" class="btn btn-sm fw-bold rounded-pill px-3 d-inline-flex align-items-center gap-1 text-white flex-shrink-0" style="background:<?php echo $e($g['accent']); ?>;">
                <span class="material-symbols-outlined fs-6">call</span><?php echo $e($c['phone']); ?>
              </a>
            </div>
            <?php endforeach; ?>
            <p class="text-secondary small mb-0 mt-3"><?php echo $e($g['note']); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
