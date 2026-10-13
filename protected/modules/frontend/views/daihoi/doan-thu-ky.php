<?php
/**
 * Trang "Danh sách Ban Thư Ký Đoàn" - slug /doan-thu-ky.
 *
 * @var array $event
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$accent = '#7c3aed';

// Danh sách Ban Thư Ký Đoàn - Ban Tổ chức cập nhật.
$secretaries = isset($event['secretaries']) && is_array($event['secretaries']) ? $event['secretaries'] : array();
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'secretary')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Thông tin liên hệ</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">groups</span>
        Danh sách Ban Thư Ký Đoàn
      </h1>
      <p class="text-secondary small mb-0">Ban Thư Ký Đoàn tiếp nhận thông tin, hỗ trợ Quý Đại biểu và xử lý báo hủy suất ăn trước giờ ăn 24 giờ.</p>
    </div>

    <div class="card border-0 rounded-4 shadow-sm bg-white mb-4 overflow-hidden" style="border-left:4px solid <?php echo $e($accent); ?> !important;">
      <div class="p-3 p-md-4 d-flex align-items-start gap-3">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">info</span>
        <p class="text-secondary small mb-0">Trường hợp không dùng bữa, Trưởng đoàn báo hủy suất ăn cho Ban Thư Ký Đoàn <strong>24 giờ trước giờ ăn</strong>.</p>
      </div>
    </div>

    <?php if (empty($secretaries)): ?>
      <div class="card border rounded-4 shadow-sm p-5 text-center text-secondary">
        <span class="material-symbols-outlined mb-2" style="font-size:48px;">groups</span>
        <p class="mb-0">Danh sách Ban Thư Ký Đoàn sẽ được Ban Tổ chức cập nhật.</p>
      </div>
    <?php else: ?>
      <div class="card border rounded-4 shadow-sm bg-white overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:48px;">#</th>
                <th>Họ và tên</th>
                <th>Chức danh / Đoàn</th>
                <th class="text-end">Điện thoại</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($secretaries as $i => $s): ?>
              <tr>
                <td class="text-muted"><?php echo $i + 1; ?></td>
                <td class="fw-semibold text-dark"><?php echo $e(isset($s['name']) ? $s['name'] : ''); ?></td>
                <td class="text-secondary"><?php echo $e(isset($s['role']) ? $s['role'] : ''); ?></td>
                <td class="text-end">
                  <?php if (!empty($s['phone'])): ?>
                    <a href="tel:<?php echo $e($s['phone']); ?>" class="text-decoration-none fw-semibold" style="color:<?php echo $e($accent); ?>;"><?php echo $e($s['phone']); ?></a>
                  <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </section>
</main>
