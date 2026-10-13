<?php
/**
 * Trang chi tiết tin tức - slug /tin-tuc/<id>.
 *
 * @var array $event
 * @var array $article     Bài viết
 * @var array $categories  Danh mục (để hiển thị tên)
 * @var array $related     Tin liên quan cùng danh mục
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

$fmtDate = function ($raw) {
    if ($raw === '' || $raw === null) { return ''; }
    $ts = ctype_digit((string) $raw) ? (int) $raw : strtotime((string) $raw);
    if (!$ts) { return (string) $raw; }
    $days = array('Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy');
    return $days[(int) date('w', $ts)] . ', ' . date('d/m/Y', $ts);
};

$catMap = array();
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $c) {
        if (isset($c['id'])) { $catMap[(int) $c['id']] = $c; }
    }
}
$cid = (int) $val($article, array('category_id'), 0);
$categoryName = ($cid && isset($catMap[$cid]['name']))
    ? $catMap[$cid]['name']
    : $val($article, array('category_name', 'category'), 'Tin tức');

$title = $val($article, array('title', 'name'), 'Chi tiết tin tức');
$thumb = $val($article, array('thumbnail', 'image', 'cover', 'thumbnail_url'), '');
$date = $fmtDate($val($article, array('published_at', 'created_at', 'date'), ''));
$views = $val($article, array('view_count'), '');
$content = $val($article, array('content', 'body', 'description'), '');
$articleId = (int) $val($article, array('id'), 0);
?>
<?php $this->renderPartial('_subheader', array('eventName' => $eventName, 'active' => 'news')); ?>

<main class="flex-grow-1">
  <section class="container py-4">
    <nav class="mb-3 small text-muted d-flex align-items-center gap-1 flex-wrap">
      <a href="<?php echo $e($base); ?>/" class="text-decoration-none text-muted">Trang chủ</a>
      <span>/</span>
      <a href="<?php echo $e($base); ?>/tin-tuc" class="text-decoration-none text-muted">Tin tức</a>
      <span>/</span>
      <span class="text-dark"><?php echo $e($categoryName); ?></span>
    </nav>

    <div class="row g-4">
      <!-- NỘI DUNG BÀI VIẾT -->
      <div class="col-12 col-lg-8">
        <article class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden">
          <div class="p-3 p-md-4">
            <span class="badge text-white text-uppercase mb-2" style="font-size:10.5px;background:<?php echo $e($accent); ?>;"><?php echo $e($categoryName); ?></span>
            <h1 class="fw-bold text-dark fs-3 mb-2 lh-sm"><?php echo $e($title); ?></h1>
            <div class="d-flex align-items-center gap-3 text-muted small mb-3">
              <?php if ($date): ?><span class="d-inline-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">calendar_today</span><?php echo $e($date); ?></span><?php endif; ?>
              <?php if ($views !== ''): ?><span class="d-inline-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">visibility</span><?php echo $e($views); ?> lượt xem</span><?php endif; ?>
            </div>

            <?php if ($thumb): ?>
              <img src="<?php echo $e($thumb); ?>" alt="<?php echo $e($title); ?>" class="w-100 rounded-3 mb-4" style="object-fit:cover;" onerror="this.style.display='none';">
            <?php endif; ?>

            <div class="news-content text-dark">
              <?php
              // Nội dung bài viết là HTML do BTC soạn trong trình admin -> render trực tiếp.
              echo $content !== '' ? $content : '<p class="text-secondary">Nội dung bài viết đang được cập nhật.</p>';
              ?>
            </div>
          </div>
        </article>

        <div class="mt-3">
          <a href="<?php echo $e($base); ?>/tin-tuc" class="btn btn-light border rounded-pill px-3 d-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined fs-6">arrow_back</span> Quay lại danh sách tin
          </a>
        </div>
      </div>

      <!-- TIN LIÊN QUAN -->
      <div class="col-12 col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm bg-white" style="position:sticky;top:76px;">
          <div class="p-3">
            <h2 class="fw-bold text-dark fs-6 mb-3 d-flex align-items-center gap-2">
              <span class="material-symbols-outlined fs-5" style="color:<?php echo $e($accent); ?>;">read_more</span>
              Tin liên quan
            </h2>
            <?php
            $relatedShown = 0;
            if (!empty($related) && is_array($related)):
              foreach ($related as $r):
                $rid = (int) $val($r, array('id'), 0);
                if ($rid === $articleId) { continue; }
                if ($relatedShown >= 5) { break; }
                $relatedShown++;
                $rThumb = $val($r, array('thumbnail', 'image', 'cover'), '');
                $rTitle = $val($r, array('title', 'name'), 'Bài viết');
                $rDate = $fmtDate($val($r, array('published_at', 'created_at'), ''));
            ?>
            <a href="<?php echo $e($base); ?>/tin-tuc/<?php echo $rid; ?>" class="d-flex gap-2 text-decoration-none py-2 border-bottom">
              <div class="flex-shrink-0 rounded-3 overflow-hidden" style="width:68px;height:52px;background:#e2e8f0;">
                <?php if ($rThumb): ?>
                  <img src="<?php echo $e($rThumb); ?>" alt="" class="w-100 h-100" style="object-fit:cover;" onerror="this.style.display='none';">
                <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#1e3a8a,#0f766e);">
                    <span class="material-symbols-outlined text-white fs-6">newspaper</span>
                  </div>
                <?php endif; ?>
              </div>
              <div class="flex-grow-1">
                <h3 class="fw-semibold text-dark mb-1 line-clamp-2" style="font-size:13px;line-height:1.3;"><?php echo $e($rTitle); ?></h3>
                <?php if ($rDate): ?><span class="text-muted" style="font-size:11px;"><?php echo $e($rDate); ?></span><?php endif; ?>
              </div>
            </a>
            <?php endforeach; endif; ?>
            <?php if ($relatedShown === 0): ?>
              <p class="text-muted small mb-0">Chưa có tin liên quan.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<style>
.line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.news-content img { max-width:100%; height:auto; border-radius:.5rem; }
.news-content p { margin-bottom:1rem; line-height:1.7; }
.news-content h2, .news-content h3 { font-weight:700; margin:1.25rem 0 .75rem; }
.news-content a:last-child { border-bottom:0; }
</style>
