<?php
/**
 * Quản trị Thư viện ảnh - quản lý ảnh trong 1 album.
 *
 * @var PhotoAlbumsController $this
 * @var array $album
 * @var array $photos  mỗi ảnh: id, thumb_url, is_cover, claim_count, claimer_names
 */
$this->breadcrumbs = array(
    'Thư viện ảnh' => $this->createUrl('index'),
    $album['name'],
);
$this->Tabletitle = 'Ảnh trong album: ' . $album['name'];

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/photo-albums-photos.js?v=' . time(),
    CClientScript::POS_END
);

$e = function ($s) { return CHtml::encode($s); };
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="<?php echo $this->createUrl('index'); ?>" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left"></i> Về danh sách album
            </a>
            <span class="text-muted small"><?php echo count($photos); ?> ảnh · bấm biểu tượng ⭐ để đặt ảnh bìa, 👥 để xem người đã đánh dấu</span>
        </div>

        <?php if (empty($photos)): ?>
            <div class="text-center text-muted py-5"><i class="fa fa-image fa-3x mb-3 d-block"></i>Album chưa có ảnh.</div>
        <?php else: ?>
            <div id="admin-photo-grid"
                 class="admin-photo-grid"
                 data-setcover-url="<?php echo $this->createUrl('setCover', array('id' => '__ID__')); ?>"
                 data-delete-url="<?php echo $this->createUrl('deletePhoto', array('id' => '__ID__')); ?>"
                 data-csrf-name="<?php echo Yii::app()->request->csrfTokenName; ?>"
                 data-csrf="<?php echo Yii::app()->request->csrfToken; ?>">
                <?php foreach ($photos as $p):
                    $names = isset($p['claimer_names']) && is_array($p['claimer_names']) ? $p['claimer_names'] : array();
                ?>
                    <figure class="apg-item<?php echo !empty($p['is_cover']) ? ' is-cover' : ''; ?>"
                            data-id="<?php echo (int) $p['id']; ?>"
                            data-names="<?php echo $e(implode(', ', $names)); ?>">
                        <img src="<?php echo $e($p['thumb_url']); ?>" alt="<?php echo $e($p['file_name']); ?>" loading="lazy">
                        <?php if (!empty($p['is_cover'])): ?>
                            <span class="apg-cover-badge"><i class="fa fa-star"></i> Ảnh bìa</span>
                        <?php endif; ?>
                        <span class="apg-claim" title="Số người đã đánh dấu">
                            <i class="fa fa-users"></i> <span class="apg-claim-n"><?php echo (int) $p['claim_count']; ?></span>
                        </span>
                        <div class="apg-actions">
                            <button type="button" class="btn btn-sm btn-light apg-setcover" title="Đặt làm ảnh bìa"><i class="fa fa-star"></i></button>
                            <?php if (!empty($names)): ?>
                                <button type="button" class="btn btn-sm btn-light apg-who" title="Người đã đánh dấu"><i class="fa fa-users"></i></button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-danger apg-del" title="Xoá ảnh"><i class="fa fa-trash"></i></button>
                        </div>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;}
.apg-item{position:relative;margin:0;aspect-ratio:1/1;border-radius:10px;overflow:hidden;background:#eee;box-shadow:0 1px 3px rgba(0,0,0,.1);}
.apg-item.is-cover{outline:3px solid #f59e0b;outline-offset:-3px;}
.apg-item img{width:100%;height:100%;object-fit:cover;display:block;}
.apg-cover-badge{position:absolute;top:6px;left:6px;background:#f59e0b;color:#fff;font-size:10.5px;padding:2px 7px;border-radius:999px;}
.apg-claim{position:absolute;top:6px;right:6px;background:rgba(0,0,0,.55);color:#fff;font-size:11px;padding:2px 7px;border-radius:999px;}
.apg-actions{position:absolute;bottom:0;left:0;right:0;display:flex;gap:4px;justify-content:center;padding:6px;background:linear-gradient(to top,rgba(0,0,0,.5),transparent);opacity:0;transition:opacity .2s;}
.apg-item:hover .apg-actions{opacity:1;}
</style>
