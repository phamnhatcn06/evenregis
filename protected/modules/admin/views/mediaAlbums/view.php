<?php
$this->menu = array(
    array('label' => 'Danh sách', 'labelIcon' => 'Danh sách', 'url' => $this->createUrl('admin'), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
    array('label' => 'Quản lý mục', 'labelIcon' => 'Quản lý mục', 'url' => $this->createUrl('/admin/mediaItems/admin', array('album_id' => $model->id)), 'color' => 'info', 'icon' => 'fa-images', 'id' => 'btn_items'),
    array('label' => 'Cập nhật', 'labelIcon' => 'Cập nhật', 'url' => $this->createUrl('update', array('id' => $model->id)), 'color' => 'warning', 'icon' => 'fa-pencil', 'id' => 'btn_update'),
);
$this->breadcrumbs = array(
    'Thư viện' => $this->createUrl('admin'),
    'Xem chi tiết',
);
$this->Tabletitle = 'Chi tiết album: ' . CHtml::encode($model->title);

$typeOptions = MediaAlbum::getTypeOptions();
$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => $model->event_id),
    array('label' => 'Tên album', 'value' => $model->title),
    array('label' => 'Đường dẫn (slug)', 'value' => $model->slug),
    array('label' => 'Nhãn', 'value' => $model->badge),
    array('label' => 'Loại', 'value' => isset($typeOptions[$model->type]) ? $typeOptions[$model->type] : $model->type),
    array('label' => 'Số mục', 'value' => $model->item_count),
    array('label' => 'Thứ tự', 'value' => $model->sort_order),
    array('label' => 'Hiển thị', 'value' => $model->is_active ? '<span class="badge bg-success">Bật</span>' : '<span class="badge bg-secondary">Tắt</span>', 'raw' => true),
    array('label' => 'Ngày tạo', 'value' => $model->created_at),
);

$totalAttrs = count($attributes);
if ($totalAttrs <= 4) {
    $colClass = 'col-12';
    $columns = 1;
} elseif ($totalAttrs <= 8) {
    $colClass = 'col-md-6';
    $columns = 2;
} else {
    $colClass = 'col-md-4';
    $columns = 3;
}
$perColumn = ceil($totalAttrs / $columns);
?>
<div class="card"><div class="card-body">
    <div class="row">
        <?php for ($col = 0; $col < $columns; $col++): ?>
        <div class="<?php echo $colClass; ?>">
            <table class="table table-bordered table-striped">
                <tbody>
                <?php
                $start = $col * $perColumn;
                $end = min($start + $perColumn, $totalAttrs);
                for ($i = $start; $i < $end; $i++):
                    $attr = $attributes[$i];
                ?>
                    <tr>
                        <th style="width:40%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                        <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <?php endfor; ?>
    </div>

    <?php if (!empty($model->cover_image)): ?>
    <div class="mt-3">
        <h6>Ảnh bìa</h6>
        <img src="<?php echo CHtml::encode($model->cover_image); ?>" alt="cover" style="max-width:360px;" class="img-fluid rounded border">
    </div>
    <?php endif; ?>

    <div class="mt-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-0">Các mục trong album</h6>
            <a class="btn btn-sm btn-primary" href="<?php echo $this->createUrl('/admin/mediaItems/create', array('album_id' => $model->id)); ?>">
                <i class="fa fa-plus"></i> Thêm mục
            </a>
        </div>
        <div class="row g-2">
            <?php if (!empty($items) && is_array($items)): foreach ($items as $it):
                $url = isset($it['url']) ? $it['url'] : '';
                $thumb = isset($it['thumbnail']) && $it['thumbnail'] !== '' ? $it['thumbnail'] : $url;
                $type = isset($it['type']) ? $it['type'] : 'image';
            ?>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="border rounded overflow-hidden position-relative" style="height:120px;background:#f1f5f9;">
                    <?php if ($type === 'image' && $thumb): ?>
                        <img src="<?php echo CHtml::encode($thumb); ?>" alt="item" class="w-100 h-100" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"><i class="fa fa-film fa-2x"></i></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="col-12"><p class="text-muted small mb-0">Chưa có mục nào. Bấm "Thêm mục" hoặc "Quản lý mục" để bổ sung.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div></div>
