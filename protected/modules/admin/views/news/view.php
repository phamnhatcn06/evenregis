<?php
$this->menu = array(
    array(
        'label' => 'Danh sách',
        'labelIcon' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => 'Thêm tin tức',
        'labelIcon' => 'Thêm tin tức',
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Cập nhật',
        'labelIcon' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
);

$this->breadcrumbs = array(
    'Tin tức' => $this->createUrl('admin'),
    'Xem chi tiết',
);
$this->Tabletitle = 'Chi tiết tin tức: ' . CHtml::encode($model->title);

$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => $model->event_id),
    array('label' => 'Tiêu đề', 'value' => $model->title),
    array('label' => 'Đường dẫn (slug)', 'value' => $model->slug),
    array('label' => 'Loại tin', 'value' => $model->getCategoryLabel()),
    array('label' => 'Danh mục', 'value' => $model->category_id),
    array('label' => 'Tin nổi bật', 'value' => News::getFeaturedLabel($model->is_featured), 'raw' => true),
    array('label' => 'Trạng thái', 'value' => News::getPublishedLabel($model->is_published), 'raw' => true),
    array('label' => 'Lượt xem', 'value' => $model->view_count),
    array('label' => 'Ngày xuất bản', 'value' => $model->published_at),
    array('label' => 'Người tạo', 'value' => $model->created_by),
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

    <?php if (!empty($model->excerpt)): ?>
    <div class="mt-3">
        <h6>Tóm tắt</h6>
        <div class="border rounded p-3 bg-light"><?php echo CHtml::encode($model->excerpt); ?></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($model->content)): ?>
    <div class="mt-3">
        <h6>Nội dung</h6>
        <div class="border rounded p-3"><?php echo $model->content; ?></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($model->thumbnail)): ?>
    <div class="mt-3">
        <h6>Ảnh đại diện</h6>
        <img src="<?php echo CHtml::encode($model->thumbnail); ?>" alt="thumbnail" style="max-width:320px;" class="img-fluid rounded border">
    </div>
    <?php endif; ?>
</div></div>
