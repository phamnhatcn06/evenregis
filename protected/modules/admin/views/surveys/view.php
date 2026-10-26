<?php
$this->menu = array(
    array('label' => 'Danh sách', 'labelIcon' => 'Danh sách', 'url' => $this->createUrl('admin'), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
    array('label' => 'Thêm khảo sát', 'labelIcon' => 'Thêm khảo sát', 'url' => $this->createUrl('create'), 'color' => 'success', 'icon' => 'fa-plus', 'id' => 'btn_create'),
    array('label' => 'Cập nhật', 'labelIcon' => 'Cập nhật', 'url' => $this->createUrl('update', array('id' => $model->id)), 'color' => 'warning', 'icon' => 'fa-pencil', 'id' => 'btn_update'),
);
$this->breadcrumbs = array(
    'Khảo sát ý kiến' => $this->createUrl('admin'),
    'Xem chi tiết',
);
$this->Tabletitle = 'Chi tiết khảo sát: ' . CHtml::encode($model->title);

$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => $model->event_id),
    array('label' => 'Tiêu đề', 'value' => $model->title),
    array('label' => 'Thứ tự', 'value' => $model->sort_order),
    array('label' => 'Icon', 'value' => $model->icon),
    array('label' => 'Hiển thị', 'value' => $model->is_active ? '<span class="badge bg-success">Bật</span>' : '<span class="badge bg-secondary">Tắt</span>', 'raw' => true),
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

    <?php if (!empty($model->form_url)): ?>
    <div class="mt-3">
        <h6>Link Google Form</h6>
        <a href="<?php echo CHtml::encode($model->form_url); ?>" target="_blank" rel="noopener noreferrer">
            <?php echo CHtml::encode($model->form_url); ?>
        </a>
    </div>
    <?php endif; ?>

    <?php if (!empty($model->description)): ?>
    <div class="mt-3">
        <h6>Mô tả</h6>
        <div class="border rounded p-3 bg-light"><?php echo CHtml::encode($model->description); ?></div>
    </div>
    <?php endif; ?>
</div></div>
