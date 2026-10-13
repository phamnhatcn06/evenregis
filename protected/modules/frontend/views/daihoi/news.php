<?php
/**
 * Trang Tin tức chung của Đại hội - slug /tin-tuc.
 * Hiển thị toàn bộ tin tức; sidebar là các danh mục đã cấu hình.
 * Click vào bài viết -> trang chi tiết /tin-tuc/<id>.
 *
 * @var array  $event
 * @var array  $news                 Danh sách bài viết
 * @var array  $categories           Danh mục tin tức (sidebar)
 * @var mixed  $selectedCategoryId   Danh mục đang lọc (null = tất cả)
 */
$base = Yii::app()->request->baseUrl;
$e = function ($s) { return CHtml::encode($s); };
$val = function ($arr, $keys, $default = '') {
    foreach ((array) $keys as $k) {
        if (is_array($arr) && isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) {
            return $arr[$k];
        }
    }
    return $default;
};
$eventName = $val($event, array('name', 'title'), 'Đại hội Mường Thanh 2026');
$accent = '#2563eb';
$selectedCategoryId = isset($selectedCategoryId) && $selectedCategoryId !== '' ? (int) $selectedCategoryId : null;

$fmtDate = function ($raw) {
    if ($raw === '' || $raw === null) { return ''; }
    $ts = ctype_digit((string) $raw) ? (int) $raw : strtotime((string) $raw);
    if (!$ts) { return (string) $raw; }
    return date('d/m/Y', $ts);
};

// Map danh mục id -> thông tin để hiển thị tên/màu.
$catMap = array();
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $c) {
        if (isset($c['id'])) {
            $catMap[(int) $c['id']] = $c;
        }
    }
}
$catName = function ($item) use ($val, $catMap) {
    $cid = (int) $val($item, array('category_id'), 0);
    if ($cid && isset($catMap[$cid]['name'])) {
        return $catMap[$cid]['name'];
    }
    return $val($item, array('category_name', 'category'), 'Tin tức');
};
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'news')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <div class="mb-4">
      <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">Bản tin Đại hội</span>
      <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="color:<?php echo $e($accent); ?>;">newspaper</span>
        Tin tức Đại hội
      </h1>
      <p class="text-secondary small mb-0">Cập nhật mới nhất các hoạt động, thông báo và sự kiện trong khuôn khổ Đại hội.</p>
    </div>

    <div class="row g-4">
      <!-- SIDEBAR: Danh mục -->
      <div class="col-12 col-lg-3 order-lg-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white" style="position:sticky;top:76px;">
          <div class="p-3">
            <h2 class="fw-bold text-dark fs-6 mb-3 d-flex align-items-center gap-2">
              <span class="material-symbols-outlined fs-5" style="color:<?php echo $e($accent); ?>;">category</span>
              Danh mục tin tức
            </h2>
            <div class="d-flex flex-column gap-1">
              <a href="<?php echo $e($base); ?>/tin-tuc" class="d-flex align-items-center justify-content-between text-decoration-none rounded-3 px-3 py-2 <?php echo $selectedCategoryId === null ? 'text-white fw-semibold' : 'text-dark'; ?>" style="<?php echo $selectedCategoryId === null ? 'background:' . $e($accent) . ';' : ''; ?>">
                <span class="d-flex align-items-center gap-2"><span class="material-symbols-outlined fs-6">list</span>Tất cả tin tức</span>
              </a>
              <?php if (!empty($categories) && is_array($categories)): ?>
                <?php foreach ($categories as $c):
                  $cid = (int) $val($c, array('id'), 0);
                  $cColor = $val($c, array('color'), $accent);
                  $cIcon = $val($c, array('icon'), 'label');
                  $isActive = $selectedCategoryId === $cid;
                ?>
                <a href="<?php echo $e($base); ?>/tin-tuc?category_id=<?php echo $cid; ?>" class="d-flex align-items-center justify-content-between text-decoration-none rounded-3 px-3 py-2 <?php echo $isActive ? 'text-white fw-semibold' : 'text-dark'; ?>" style="<?php echo $isActive ? 'background:' . $e($cColor) . ';' : ''; ?>">
                  <span class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined fs-6" style="<?php echo $isActive ? '' : 'color:' . $e($cColor) . ';'; ?>"><?php echo $e($cIcon); ?></span>
                    <?php echo $e($val($c, array('name'), 'Danh mục')); ?>
                  </span>
                </a>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted small mb-0 px-2">Chưa có danh mục nào được cấu hình.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- DANH SÁCH BÀI VIẾT -->
      <div class="col-12 col-lg-9 order-lg-1">
        <?php if (empty($news) || !is_array($news)): ?>
          <div class="card border rounded-4 shadow-sm p-5 text-center text-secondary">
            <span class="material-symbols-outlined mb-2" style="font-size:48px;">feed</span>
            <p class="mb-0">Chưa có tin tức nào<?php echo $selectedCategoryId !== null ? ' trong danh mục này' : ''; ?>.</p>
          </div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($news as $n):
              $nid = $val($n, array('id'), '');
              $thumb = $val($n, array('thumbnail', 'image', 'cover', 'thumbnail_url'), '');
              $title = $val($n, array('title', 'name'), 'Bài viết');
              $desc = $val($n, array('excerpt', 'summary', 'short_description', 'description'), '');
              $date = $fmtDate($val($n, array('published_at', 'created_at', 'date'), ''));
              $views = $val($n, array('view_count'), '');
              $href = $nid !== '' ? $base . '/tin-tuc/' . $e($nid) : '#';
            ?>
            <div class="col-12 col-md-6">
              <article class="card h-100 border rounded-4 overflow-hidden shadow-sm">
                <a href="<?php echo $href; ?>" class="text-decoration-none d-flex flex-column h-100">
                  <div class="position-relative" style="height:180px;background:#e2e8f0;">
                    <?php if ($thumb): ?>
                      <img alt="<?php echo $e($title); ?>" class="w-100 h-100 object-fit-cover" src="<?php echo $e($thumb); ?>" onerror="this.style.display='none';">
                    <?php else: ?>
                      <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#1e3a8a,#0f766e);">
                        <span class="material-symbols-outlined text-white" style="font-size:40px;">newspaper</span>
                      </div>
                    <?php endif; ?>
                    <span class="position-absolute top-0 start-0 m-2 badge text-white text-uppercase" style="font-size:10px;background:rgba(15,23,42,.72);"><?php echo $e($catName($n)); ?></span>
                  </div>
                  <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div>
                      <div class="d-flex align-items-center gap-2 text-muted mb-1" style="font-size:11px;">
                        <?php if ($date): ?><span class="d-inline-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">calendar_today</span><?php echo $e($date); ?></span><?php endif; ?>
                        <?php if ($views !== ''): ?><span class="d-inline-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">visibility</span><?php echo $e($views); ?></span><?php endif; ?>
                      </div>
                      <h3 class="fw-bold text-dark fs-6 mb-2 lh-sm line-clamp-2"><?php echo $e($title); ?></h3>
                      <?php if ($desc): ?><p class="text-secondary small mb-0 line-clamp-2"><?php echo $e($desc); ?></p><?php endif; ?>
                    </div>
                    <span class="text-primary fw-bold small d-inline-flex align-items-center gap-1 mt-3">Đọc tiếp <span>→</span></span>
                  </div>
                </a>
              </article>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<style>
.line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.object-fit-cover { object-fit: cover; }
</style>
