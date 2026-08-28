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
        'label' => 'Thêm danh mục',
        'labelIcon' => 'Thêm danh mục',
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
    'Danh mục tin tức' => $this->createUrl('admin'),
    'Xem chi tiết',
);
$this->Tabletitle = 'Chi tiết danh mục: ' . CHtml::encode($model->name);

$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => $model->event_id),
    array('label' => 'Tên danh mục', 'value' => $model->name),
    array('label' => 'Slug', 'value' => $model->slug),
    array(
        'label' => 'Biểu tượng',
        'value' => $model->icon ? '<i class="fa ' . CHtml::encode($model->icon) . '"></i> ' . CHtml::encode($model->icon) : '',
        'raw' => true,
    ),
    array(
        'label' => 'Màu sắc',
        'value' => $model->color ? '<span style="display:inline-block;width:16px;height:16px;border-radius:3px;background:' . CHtml::encode($model->color) . ';vertical-align:middle;"></span> ' . CHtml::encode($model->color) : '',
        'raw' => true,
    ),
    array('label' => 'Thứ tự', 'value' => $model->sort_order),
    array('label' => 'Trạng thái', 'value' => NewsCategories::getActiveLabel($model->is_active), 'raw' => true),
    array('label' => 'Ngày tạo', 'value' => $model->created_at),
    array('label' => 'Ngày cập nhật', 'value' => $model->updated_at),
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

    <?php if (!empty($model->description)): ?>
    <div class="mt-3">
        <h6>Mô tả</h6>
        <div class="border rounded p-3 bg-light"><?php echo CHtml::encode($model->description); ?></div>
    </div>
    <?php endif; ?>
</div></div>
