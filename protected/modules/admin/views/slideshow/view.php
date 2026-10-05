<?php
$this->menu = array(
    array('label' => 'Danh sách', 'labelIcon' => 'Danh sách', 'url' => $this->createUrl('admin'), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
    array('label' => 'Thêm slide', 'labelIcon' => 'Thêm slide', 'url' => $this->createUrl('create'), 'color' => 'success', 'icon' => 'fa-plus', 'id' => 'btn_create'),
    array('label' => 'Cập nhật', 'labelIcon' => 'Cập nhật', 'url' => $this->createUrl('update', array('id' => $model->id)), 'color' => 'warning', 'icon' => 'fa-pencil', 'id' => 'btn_update'),
);
$this->breadcrumbs = array(
    'Slideshow' => $this->createUrl('admin'),
    'Xem chi tiết',
);
$this->Tabletitle = 'Chi tiết slide: ' . CHtml::encode($model->title);

$themeOptions = Slideshow::getThemeOptions();
$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => $model->event_id),
    array('label' => 'Tiêu đề', 'value' => $model->title),
    array('label' => 'Dòng nhấn', 'value' => $model->subtitle),
    array('label' => 'Màu nhấn', 'value' => isset($themeOptions[$model->theme]) ? $themeOptions[$model->theme] : $model->theme),
    array('label' => 'Thứ tự', 'value' => $model->sort_order),
    array('label' => 'Nhãn nút', 'value' => $model->button_text),
    array('label' => 'Link nút', 'value' => $model->button_url),
    array('label' => 'Bắt đầu hiển thị', 'value' => $model->start_at),
    array('label' => 'Kết thúc hiển thị', 'value' => $model->end_at),
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

    <?php if (!empty($model->description)): ?>
    <div class="mt-3">
        <h6>Mô tả</h6>
        <div class="border rounded p-3 bg-light"><?php echo CHtml::encode($model->description); ?></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($model->image)): ?>
    <div class="mt-3">
        <h6>Ảnh nền</h6>
        <img src="<?php echo CHtml::encode($model->image); ?>" alt="slide" style="max-width:480px;" class="img-fluid rounded border">
    </div>
    <?php endif; ?>
</div></div>
