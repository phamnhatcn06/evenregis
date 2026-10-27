<?php
/**
 * Trang công bố người trúng giải Lucky Draw (public /luckydraw).
 *
 * @var array  $groups    Người trúng nhóm theo hạng giải (tier => [winner,...])
 * @var int    $total     Tổng số người trúng
 * @var string $eventName Tên sự kiện
 */
$e = function ($s) {
    return CHtml::encode($s !== null ? (string) $s : '');
};
$accent = '#d4af37'; // vàng gold cho không khí trao giải
?>
<?php $this->renderPartial('../daihoi/_subheader', array('eventName' => $eventName, 'active' => 'lucky')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="text-center mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Chúc mừng</span>
      <h1 class="fw-bold text-dark fs-2 mb-1 d-flex align-items-center justify-content-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;font-size:34px;">trophy</span>
        Người trúng giải Lucky Draw
      </h1>
      <p class="text-secondary small mb-0">Danh sách người may mắn trúng thưởng tại <?php echo $e($eventName); ?>.</p>
    </div>

    <?php if (empty($groups)): ?>
      <div class="card border-0 rounded-4 shadow-sm bg-white">
        <div class="p-5 text-center text-secondary">
          <span class="material-symbols-outlined d-block mb-2" style="font-size:48px;color:#cbd5e1;">inbox</span>
          <p class="mb-0">Chưa có kết quả trúng giải. Vui lòng quay lại sau.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($groups as $tier => $winners): ?>
        <div class="mb-4">
          <h2 class="fw-bold fs-4 mb-3 d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill"
              style="background:<?php echo $e($accent); ?>1a;color:#8a6d1a;">
            <span class="material-symbols-outlined">workspace_premium</span>
            <?php echo $e($tier); ?>
            <span class="badge rounded-pill" style="background:<?php echo $e($accent); ?>;color:#fff;"><?php echo count($winners); ?></span>
          </h2>

          <div class="row g-3">
            <?php foreach ($winners as $w): ?>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 rounded-4 shadow-sm bg-white h-100 overflow-hidden"
                     style="border-left:4px solid <?php echo $e($accent); ?> !important;">
                  <div class="p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                         style="width:48px;height:48px;background:<?php echo $e($accent); ?>26;color:#8a6d1a;font-size:14px;">
                      <?php echo $e($w['lucky_code']); ?>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                      <div class="fw-bold text-dark text-truncate"><?php echo $e($w['full_name']); ?></div>
                      <?php if (!empty($w['unit_label'])): ?>
                        <div class="text-secondary small text-truncate"><?php echo $e($w['unit_label']); ?></div>
                      <?php endif; ?>
                      <?php if (!empty($w['prize_name'])): ?>
                        <div class="small text-truncate" style="color:#8a6d1a;">
                          <span class="material-symbols-outlined" style="font-size:14px;vertical-align:-2px;">redeem</span>
                          <?php echo $e($w['prize_name']); ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <p class="text-center text-secondary small mt-2">Tổng cộng <b><?php echo (int) $total; ?></b> người trúng giải.</p>
    <?php endif; ?>
  </section>
</main>
