<?php
/**
 * Quản trị Thư viện ảnh - danh sách album.
 *
 * @var PhotoAlbumsController $this
 * @var array $albums
 */
$this->breadcrumbs = array('Thư viện ảnh');
$this->Tabletitle = 'Thư viện ảnh - Quản lý album';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/photo-albums-index.js?v=' . time(),
    CClientScript::POS_END
);

$e = function ($s) { return CHtml::encode($s); };
?>
<div class="card">
    <div class="card-body">
        <?php if (empty($albums)): ?>
            <div class="text-center text-muted py-5">
                <i class="fa fa-image fa-3x mb-3 d-block"></i>
                Chưa có album nào. Hãy tải ảnh vào <code>storage/app/public/gallery/&lt;Tên album&gt;/</code>
                rồi chạy lệnh <code>php artisan gallery:import</code>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width:90px;">Ảnh bìa</th>
                            <th>Tên album</th>
                            <th style="width:90px;" class="text-center">Số ảnh</th>
                            <th style="width:110px;" class="text-center">Lượt đánh dấu</th>
                            <th style="width:90px;" class="text-center">Thứ tự</th>
                            <th style="width:110px;" class="text-center">Hiển thị</th>
                            <th style="width:260px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($albums as $a):
                            $cover = !empty($a['cover_path']) ? GalleryAlbums::mediaUrl($a['cover_path']) : '';
                        ?>
                            <tr>
                                <td>
                                    <?php if ($cover): ?>
                                        <img src="<?php echo $e($cover); ?>" alt="" style="width:72px;height:54px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fa fa-image"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo $e($a['name']); ?></strong>
                                    <div class="text-muted small"><?php echo $e($a['slug']); ?></div>
                                </td>
                                <td class="text-center"><?php echo (int) $a['photo_count']; ?></td>
                                <td class="text-center"><?php echo (int) $a['claim_total']; ?></td>
                                <td class="text-center"><?php echo (int) $a['sort_order']; ?></td>
                                <td class="text-center">
                                    <?php if (!empty($a['is_public'])): ?>
                                        <span class="badge bg-success">Công khai</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đang ẩn</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo $this->createUrl('photos', array('id' => $a['id'])); ?>" class="btn btn-sm btn-info text-white">
                                        <i class="fa fa-images"></i> Quản lý ảnh
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary btn-edit-album"
                                        data-id="<?php echo (int) $a['id']; ?>"
                                        data-name="<?php echo $e($a['name']); ?>"
                                        data-public="<?php echo (int) $a['is_public']; ?>"
                                        data-sort="<?php echo (int) $a['sort_order']; ?>">
                                        <i class="fa fa-edit"></i> Sửa
                                    </button>
                                    <?php
                                    MyHelper::renderDeleteButton(array(
                                        'url'   => $this->createUrl('delete', array('id' => $a['id'])),
                                        'id'    => 'del_' . (int) $a['id'],
                                        'icon'  => 'fa-trash',
                                        'label' => 'Xoá',
                                    ), 'btn btn-sm btn-danger');
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal sửa album -->
<div class="modal fade" id="modalEditAlbum" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditAlbum" method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa album</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="<?php echo Yii::app()->request->csrfTokenName; ?>" value="<?php echo Yii::app()->request->csrfToken; ?>">
                    <div class="mb-3">
                        <label class="form-label">Tên album</label>
                        <input type="text" class="form-control" name="name" id="ea_name" required maxlength="255">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Hiển thị</label>
                            <select class="form-select" name="is_public" id="ea_public">
                                <option value="1">Công khai</option>
                                <option value="0">Đang ẩn</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Thứ tự</label>
                            <input type="number" class="form-control" name="sort_order" id="ea_sort" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_save_album">
                        <i class="fa fa-save me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.PHOTO_ALBUM_UPDATE_URL = '<?php echo $this->createUrl('update', array('id' => '__ID__')); ?>';
</script>
