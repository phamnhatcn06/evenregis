<?php

/**
 * Thư viện ảnh - xem 1 album. Slug /thu-vien-su-kien/<slug>.
 * Trang đầu render sẵn, các trang sau lazy-load qua AJAX (/thu-vien-su-kien/photos).
 *
 * @var array $event
 * @var array $album    id, name, slug, photo_count
 * @var array $photos   trang ảnh đầu tiên (đã gắn thumb_url/web_url/original_url)
 * @var int   $lastPage
 * @var int   $total
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$eventName = isset($event['name']) ? $event['name'] : 'Đại hội Mường Thanh 2026';
$accent = '#7c3aed';

Yii::app()->clientScript->registerCssFile(
    $base . '/public/daihoi/gallery.css?v=' . (@filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/gallery.css') ?: time())
);
Yii::app()->clientScript->registerScriptFile(
    $base . '/public/daihoi/gallery.js?v=' . (@filemtime(Yii::getPathOfAlias('webroot') . '/public/daihoi/gallery.js') ?: time()),
    CClientScript::POS_END
);
?>
<?php $this->renderPartial('/daihoi/_subheader', array('eventName' => $eventName, 'active' => 'gallery')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="d-flex align-items-start justify-content-between gap-2 mb-4 flex-wrap">
      <div>
        <a href="<?php echo $e($base); ?>/thu-vien-su-kien" class="text-decoration-none small text-secondary d-inline-flex align-items-center gap-1 mb-1">
          <span class="material-symbols-outlined fs-6">arrow_back</span> Tất cả album
        </a>
        <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
          <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">photo_library</span>
          <?php echo $e($album['name']); ?>
        </h1>
        <p class="text-secondary small mb-0"><?php echo (int) $total; ?> ảnh</p>
      </div>
    </div>

    <?php if ($claimer): ?>
      <div class="alert border-0 rounded-4 d-flex align-items-center gap-2 py-2 px-3 mb-3" style="background:#f3e8ff;color:#6b21a8;">
        <span class="material-symbols-outlined">how_to_reg</span>
        <span class="small">Xin chào <strong><?php echo $e($claimer['name']); ?></strong> — bấm biểu tượng <span class="material-symbols-outlined align-middle" style="font-size:16px;">person_add</span> trên ảnh để đánh dấu <strong>ảnh của bạn</strong>.</span>
      </div>
    <?php else: ?>
      <div class="alert border-0 rounded-4 d-flex align-items-center gap-2 py-2 px-3 mb-3" style="background:#f1f5f9;color:#475569;">
        <span class="material-symbols-outlined">qr_code_scanner</span>
        <span class="small">Quét mã QR trên thẻ của bạn để <strong>đánh dấu ảnh của mình</strong>. Bạn vẫn có thể xem và tải ảnh tự do.</span>
      </div>
    <?php endif; ?>

    <div id="gallery-grid"
         class="gallery-grid<?php echo $claimer ? ' identified' : ''; ?>"
         data-album-id="<?php echo (int) $album['id']; ?>"
         data-photos-url="<?php echo $e($base . '/thu-vien-su-kien/photos'); ?>"
         data-claim-url="<?php echo $e($base . '/thu-vien-su-kien/claim'); ?>"
         data-identified="<?php echo $claimer ? '1' : '0'; ?>"
         data-last-page="<?php echo (int) $lastPage; ?>">
      <?php foreach ($photos as $p): ?>
        <?php echo $this->renderPartial('_photo_item', array('p' => $p), true); ?>
      <?php endforeach; ?>
    </div>

    <div id="gallery-sentinel" class="text-center text-secondary py-4" style="display:<?php echo $lastPage > 1 ? 'block' : 'none'; ?>;">
      <span class="spinner-border spinner-border-sm"></span> Đang tải thêm…
    </div>
  </section>
</main>

<!-- Lightbox xem ảnh lớn -->
<div id="gallery-lightbox" class="gallery-lightbox" aria-hidden="true">
  <button type="button" class="gl-close" aria-label="Đóng">
    <span class="material-symbols-outlined">close</span>
  </button>
  <button type="button" class="gl-nav gl-prev" aria-label="Ảnh trước">
    <span class="material-symbols-outlined">chevron_left</span>
  </button>
  <img class="gl-img" src="" alt="">
  <button type="button" class="gl-nav gl-next" aria-label="Ảnh sau">
    <span class="material-symbols-outlined">chevron_right</span>
  </button>
  <div class="gl-actions">
    <button type="button" class="gl-claim btn btn-sm rounded-pill fw-semibold d-inline-flex align-items-center gap-1">
      <span class="material-symbols-outlined fs-6">person_add</span><span class="gl-claim-label">Đây là ảnh của tôi</span>
    </button>
    <a class="gl-download btn btn-light btn-sm rounded-pill fw-semibold d-inline-flex align-items-center gap-1" href="" download>
      <span class="material-symbols-outlined fs-6">download</span> Tải ảnh
    </a>
  </div>
  <div class="gl-claimers" style="display:none;"></div>
</div>
