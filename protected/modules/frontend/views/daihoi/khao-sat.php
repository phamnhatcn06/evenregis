<?php

/**
 * Trang "Khảo sát ý kiến" - slug /khao-sat.
 *
 * Hiển thị các khảo sát (link Google Form) đang bật của sự kiện hiện tại.
 * Khách bấm vào từng khảo sát để mở Google Form trong tab mới và góp ý.
 *
 * @var array $event
 * @var array $surveys  Danh sách khảo sát đang bật (mỗi item: id, title, description, form_url, icon)
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) {
  return CHtml::encode($s !== null ? (string) $s : '');
};
$eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');
$accent = '#7c3aed';

// Chỉ nhận link http/https (chặn javascript:, data:...).
$isSafeUrl = function ($url) {
  return is_string($url) && preg_match('/^https?:\/\//i', trim($url)) === 1;
};
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'survey')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Lắng nghe ý kiến của bạn</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">rate_review</span>
        Khảo sát ý kiến
      </h1>
      <p class="text-secondary small mb-0">Quý Đại biểu vui lòng dành ít phút chia sẻ cảm nhận và góp ý để Ban Tổ chức hoàn thiện hơn.</p>
    </div>

    <?php if (empty($surveys) || !is_array($surveys)): ?>
      <div class="card border-0 rounded-4 shadow-sm bg-white">
        <div class="p-5 text-center text-secondary">
          <span class="material-symbols-outlined d-block mb-2" style="font-size:48px;color:#cbd5e1;">inbox</span>
          <p class="mb-0">Hiện chưa có khảo sát nào. Vui lòng quay lại sau.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($surveys as $s): ?>
          <?php
          $title = isset($s['title']) ? $s['title'] : '';
          $desc = isset($s['description']) ? $s['description'] : '';
          $url = isset($s['form_url']) ? trim((string) $s['form_url']) : '';
          $icon = !empty($s['icon']) ? $s['icon'] : 'fa-clipboard';
          if (!$isSafeUrl($url)) {
            continue;
          }
          ?>
          <div class="col-12 col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm bg-white h-100 overflow-hidden" style="border-left:4px solid <?php echo $e($accent); ?> !important;">
              <div class="p-3 p-md-4 d-flex flex-column h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;background:<?php echo $e($accent); ?>1a;">
                    <i class="fa <?php echo $e($icon); ?>" style="color:<?php echo $e($accent); ?>;font-size:18px;"></i>
                  </div>
                  <h2 class="fw-bold text-dark fs-5 mb-0"><?php echo $e($title); ?></h2>
                </div>
                <?php if ($desc !== ''): ?>
                  <p class="text-secondary small mb-3"><?php echo $e($desc); ?></p>
                <?php endif; ?>
                <div class="mt-auto">
                  <a class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-1"
                     href="<?php echo $e($url); ?>" target="_blank" rel="noopener noreferrer"
                     style="background:<?php echo $e($accent); ?>;border:none;">
                    <span class="material-symbols-outlined" style="font-size:18px;">open_in_new</span>
                    Tham gia khảo sát
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
