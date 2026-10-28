<?php

/**
 * Thư viện ảnh - danh sách album. Slug /thu-vien-su-kien.
 *
 * @var array $event  Sự kiện hiện tại
 * @var array $albums Danh sách album công khai (mỗi phần tử: id, name, slug, photo_count, cover_path)
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$eventName = isset($event['name']) ? $event['name'] : 'Đại hội Mường Thanh 2026';
$accent = '#7c3aed';
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'gallery')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Hình ảnh đại hội</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">photo_library</span>
        Thư viện ảnh
      </h1>
      <p class="text-secondary small mb-0">Chọn một album để xem và tải ảnh. Quét QR trên thẻ của bạn để đánh dấu ảnh của mình.</p>
    </div>

    <?php if (empty($albums)): ?>
      <div class="text-center text-secondary py-5">
        <span class="material-symbols-outlined" style="font-size:48px;opacity:.4;">photo_library</span>
        <p class="mt-2 mb-0">Chưa có album ảnh nào.</p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($albums as $a):
          $cover = !empty($a['cover_path']) ? GalleryAlbums::mediaUrl($a['cover_path']) : '';
          $url = $base . '/thu-vien-su-kien/' . rawurlencode($a['slug']);
        ?>
          <div class="col-6 col-md-4 col-lg-3">
            <a href="<?php echo $e($url); ?>" class="card border-0 rounded-4 shadow-sm overflow-hidden h-100 text-decoration-none">
              <div class="ratio ratio-4x3 bg-light">
                <?php if ($cover): ?>
                  <img src="<?php echo $e($cover); ?>" alt="<?php echo $e($a['name']); ?>" class="w-100 h-100" style="object-fit:cover;" loading="lazy">
                <?php else: ?>
                  <div class="d-flex align-items-center justify-content-center text-muted">
                    <span class="material-symbols-outlined" style="font-size:40px;opacity:.4;">image</span>
                  </div>
                <?php endif; ?>
              </div>
              <div class="p-3">
                <h2 class="fw-semibold text-dark fs-6 mb-1 text-truncate"><?php echo $e($a['name']); ?></h2>
                <span class="text-secondary small d-inline-flex align-items-center gap-1">
                  <span class="material-symbols-outlined fs-6">image</span>
                  <?php echo (int) $a['photo_count']; ?> ảnh
                </span>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
