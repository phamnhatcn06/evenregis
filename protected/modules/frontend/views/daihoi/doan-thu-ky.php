<?php
/**
 * Trang "Danh sách Ban Thư Ký Đoàn" - slug /doan-thu-ky.
 *
 * @var array $event
 * @var array $secretaries Mỗi phần tử: array('members' => [{name,phone}], 'units' => ["..."]).
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$accent = '#7c3aed';

$secretaries = isset($secretaries) && is_array($secretaries) ? $secretaries : array();
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
      <div class="row g-3 g-md-4">
        <?php foreach ($secretaries as $g):
          $members = isset($g['members']) && is_array($g['members']) ? $g['members'] : array();
          $units = isset($g['units']) && is_array($g['units']) ? $g['units'] : array();
        ?>
        <div class="col-12 col-lg-6">
          <div class="card border rounded-4 shadow-sm bg-white h-100 overflow-hidden" style="border-top:4px solid <?php echo $e($accent); ?> !important;">
            <!-- Thư ký phụ trách -->
            <div class="p-3 p-md-4 border-bottom bg-light">
              <div class="d-flex flex-column gap-2">
                <?php foreach ($members as $m): ?>
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                  <span class="fw-semibold text-dark d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:20px;color:<?php echo $e($accent); ?>;">account_circle</span>
                    <?php echo $e(isset($m['name']) ? $m['name'] : ''); ?>
                  </span>
                  <?php if (!empty($m['phone'])): ?>
                    <a href="tel:<?php echo $e(str_replace(' ', '', $m['phone'])); ?>" class="text-decoration-none fw-semibold d-flex align-items-center gap-1" style="color:<?php echo $e($accent); ?>;">
                      <span class="material-symbols-outlined" style="font-size:18px;">call</span>
                      <?php echo $e($m['phone']); ?>
                    </a>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <!-- Đơn vị phụ trách -->
            <div class="p-3 p-md-4">
              <span class="text-uppercase small fw-bold text-secondary d-block mb-2">Đơn vị phụ trách</span>
              <?php if (empty($units)): ?>
                <span class="text-muted small">—</span>
              <?php else: ?>
                <ol class="mb-0 ps-3 text-secondary small lh-lg">
                  <?php foreach ($units as $u): ?>
                    <li><?php echo $e($u); ?></li>
                  <?php endforeach; ?>
                </ol>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
