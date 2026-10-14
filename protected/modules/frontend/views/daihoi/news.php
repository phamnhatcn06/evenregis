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
      <nav class="mb-2 small text-muted d-flex align-items-center gap-1 flex-wrap">
        <a href="<?php echo $e($base); ?>/" class="text-decoration-none text-muted">Trang chủ</a>
        <span>/</span>
        <a href="<?php echo $e($base); ?>/tin-tuc" class="text-decoration-none <?php echo $selectedCategoryId === null ? 'text-dark fw-semibold' : 'text-muted'; ?>">Tin tức</a>
        <?php if ($selectedCategoryId !== null && isset($catMap[$selectedCategoryId])): ?>
          <span>/</span>
          <span class="text-dark fw-semibold"><?php echo $e($catMap[$selectedCategoryId]['name']); ?></span>
        <?php endif; ?>
      </nav>

      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div>
          <span class="fw-bold text-uppercase small d-block mb-1" style="color:<?php echo $e($accent); ?>;">
            <?php echo ($selectedCategoryId !== null && isset($catMap[$selectedCategoryId])) ? 'Chuyên mục Đại hội' : 'Bản tin Đại hội'; ?>
          </span>
          <h1 class="fw-bold text-dark fs-3 mb-1 d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="color:<?php echo ($selectedCategoryId !== null && !empty($catMap[$selectedCategoryId]['color'])) ? $e($catMap[$selectedCategoryId]['color']) : $e($accent); ?>;">
              <?php echo ($selectedCategoryId !== null && !empty($catMap[$selectedCategoryId]['icon'])) ? $e($catMap[$selectedCategoryId]['icon']) : 'newspaper'; ?>
            </span>
            <?php
            if ($selectedCategoryId !== null && isset($catMap[$selectedCategoryId]['name'])) {
                echo $e($catMap[$selectedCategoryId]['name']);
            } else {
                echo 'Tin tức Đại hội';
            }
            ?>
          </h1>
          <p class="text-secondary small mb-0">
            <?php
            if ($selectedCategoryId !== null && !empty($catMap[$selectedCategoryId]['description'])) {
                echo $e($catMap[$selectedCategoryId]['description']);
            } else {
                echo 'Cập nhật mới nhất các hoạt động, thông báo và sự kiện trong khuôn khổ Đại hội.';
            }
            ?>
          </p>
        </div>

        <?php if ($selectedCategoryId !== null): ?>
          <div>
            <a href="<?php echo $e($base); ?>/tin-tuc" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-sm bg-white">
              <span class="material-symbols-outlined fs-6">filter_alt_off</span>
              <span>Xem tất cả tin tức</span>
            </a>
          </div>
        <?php endif; ?>
      </div>
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

      <!-- DANH SÁCH BÀI VIẾT (DẠNG LIST: ẢNH TRÁI, TIÊU ĐỀ & MÔ TẢ PHẢI) -->
      <div class="col-12 col-lg-9 order-lg-1">
        <?php if (empty($news) || !is_array($news)): ?>
          <div class="card border rounded-4 shadow-sm p-5 text-center text-secondary bg-white">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3 mx-auto" style="width:72px;height:72px;">
              <span class="material-symbols-outlined text-muted" style="font-size:36px;">feed</span>
            </div>
            <h3 class="h6 fw-bold text-dark mb-1">Chưa có bài viết nào</h3>
            <p class="text-muted small mb-3">
              <?php
              if ($selectedCategoryId !== null && isset($catMap[$selectedCategoryId]['name'])) {
                  echo 'Chưa có tin tức nào trong danh mục "' . $e($catMap[$selectedCategoryId]['name']) . '".';
              } else {
                  echo 'Chưa có tin tức nào được đăng tải.';
              }
              ?>
            </p>
            <?php if ($selectedCategoryId !== null): ?>
              <div>
                <a href="<?php echo $e($base); ?>/tin-tuc" class="btn btn-primary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                  <span class="material-symbols-outlined fs-6">arrow_back</span>
                  <span>Xem tất cả tin tức</span>
                </a>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($news as $n):
              $nid = $val($n, array('id'), '');
              $rawThumb = $val($n, array('thumbnail', 'image', 'cover', 'thumbnail_url'), '');
              $thumb = '';
              if ($rawThumb !== '') {
                  if (strpos($rawThumb, 'http://') === 0 || strpos($rawThumb, 'https://') === 0) {
                      $thumb = $rawThumb;
                  } elseif ($base !== '' && strpos($rawThumb, $base) === 0) {
                      $thumb = $rawThumb;
                  } else {
                      $thumb = rtrim($base, '/') . '/' . ltrim($rawThumb, '/');
                  }
              }
              $title = $val($n, array('title', 'name'), 'Bài viết');
              $desc = $val($n, array('excerpt', 'summary', 'short_description', 'description'), '');
              $date = $fmtDate($val($n, array('published_at', 'created_at', 'date'), ''));
              $views = $val($n, array('view_count'), '');
              $cName = $catName($n);
              $cid = (int) $val($n, array('category_id'), 0);
              $cColor = ($cid && !empty($catMap[$cid]['color'])) ? $catMap[$cid]['color'] : $accent;
              $href = $nid !== '' ? $base . '/tin-tuc/' . $e($nid) : '#';
            ?>
            <article class="card news-list-card border rounded-4 overflow-hidden shadow-sm bg-white">
              <div class="row g-0 align-items-stretch">
                <!-- ẢNH BÊN TRÁI -->
                <div class="col-12 col-md-5 col-lg-4 position-relative overflow-hidden news-img-col">
                  <a href="<?php echo $href; ?>" class="d-block w-100 h-100 position-relative news-img-link" title="<?php echo $e($title); ?>">
                    <?php if ($thumb): ?>
                      <img alt="<?php echo $e($title); ?>" class="w-100 h-100 object-fit-cover news-thumbnail" src="<?php echo $e($thumb); ?>" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-100 h-100 d-flex align-items-center justify-content-center bg-news-placeholder\' style=\'min-height:190px;\'><span class=\'material-symbols-outlined text-white fs-1\'>newspaper</span></div>';">
                    <?php else: ?>
                      <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-news-placeholder" style="min-height: 190px;">
                        <span class="material-symbols-outlined text-white fs-1">newspaper</span>
                      </div>
                    <?php endif; ?>
                    <span class="position-absolute top-0 start-0 m-2 badge text-white text-uppercase news-cat-badge" style="background:<?php echo $e($cColor ?: 'rgba(15,23,42,.85)'); ?>;">
                      <?php echo $e($cName); ?>
                    </span>
                  </a>
                </div>

                <!-- TIÊU ĐỀ VÀ MÔ TẢ NGẮN BÊN PHẢI -->
                <div class="col-12 col-md-7 col-lg-8 d-flex flex-column justify-content-between p-3 p-md-4">
                  <div>
                    <div class="d-flex align-items-center gap-3 text-muted mb-2" style="font-size:12px;">
                      <?php if ($date): ?>
                        <span class="d-inline-flex align-items-center gap-1">
                          <span class="material-symbols-outlined text-primary" style="font-size:16px;">calendar_today</span>
                          <span><?php echo $e($date); ?></span>
                        </span>
                      <?php endif; ?>
                      <?php if ($views !== ''): ?>
                        <span class="d-inline-flex align-items-center gap-1">
                          <span class="material-symbols-outlined text-secondary" style="font-size:16px;">visibility</span>
                          <span><?php echo $e($views); ?> lượt xem</span>
                        </span>
                      <?php endif; ?>
                    </div>

                    <h2 class="fw-bold mb-2 lh-sm news-title" style="font-size:18px;">
                      <a href="<?php echo $href; ?>" class="text-dark text-decoration-none hover-primary line-clamp-2">
                        <?php echo $e($title); ?>
                      </a>
                    </h2>

                    <?php if ($desc): ?>
                      <p class="text-secondary mb-3 line-clamp-3 lh-base" style="font-size:13.5px;">
                        <?php echo $e($desc); ?>
                      </p>
                    <?php endif; ?>
                  </div>

                  <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light mt-auto">
                    <a href="<?php echo $href; ?>" class="btn-read-more text-primary fw-bold small text-decoration-none d-inline-flex align-items-center gap-1">
                      <span>Đọc tiếp</span>
                      <span class="material-symbols-outlined fs-6 arrow-icon">arrow_forward</span>
                    </a>
                    <span class="badge bg-light text-muted border small fw-normal px-2 py-1">Tin tức</span>
                  </div>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<style>
.news-list-card {
  transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  border: 1px solid #e2e8f0 !important;
}
.news-list-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px -6px rgba(15, 23, 42, .08), 0 4px 8px -2px rgba(15, 23, 42, .04) !important;
  border-color: #cbd5e1 !important;
}
.news-img-col {
  background: #f1f5f9;
}
.news-img-link {
  min-height: 190px;
}
@media (min-width: 768px) {
  .news-img-link {
    height: 100%;
    min-height: 100%;
  }
}
.news-thumbnail {
  transition: transform .35s ease;
}
.news-list-card:hover .news-thumbnail {
  transform: scale(1.04);
}
.bg-news-placeholder {
  background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 50%, #0f766e 100%);
}
.news-cat-badge {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .3px;
  padding: .35rem .6rem;
  border-radius: .375rem;
  box-shadow: 0 2px 4px rgba(0,0,0,.15);
}
.news-title a {
  transition: color .2s ease;
}
.news-list-card:hover .news-title a {
  color: #2563eb !important;
}
.btn-read-more .arrow-icon {
  transition: transform .2s ease;
}
.btn-read-more:hover .arrow-icon,
.news-list-card:hover .btn-read-more .arrow-icon {
  transform: translateX(4px);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.object-fit-cover {
  object-fit: cover;
}
</style>
