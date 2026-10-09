<?php
$this->breadcrumbs = array(
    'Nội dung chạy' => $this->createUrl('admin'),
    $model->name,
);
$this->menu = array(
    array('label' => 'Danh sách', 'url' => $this->createUrl('admin'), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
    array('label' => 'Thêm mới', 'url' => $this->createUrl('create'), 'color' => 'success', 'icon' => 'fa-plus', 'id' => 'btn_create'),
    array('label' => 'Sửa', 'url' => $this->createUrl('update', array('id' => $model->id)), 'color' => 'warning', 'icon' => 'fa-edit', 'id' => 'btn_update'),
);
$this->Tabletitle = 'Chi tiết nội dung chạy';

$quota = (int) $model->quota;
$reserved = isset($model->reserved_slots) ? (int) $model->reserved_slots : 0;
$publicReg = (int) $model->registered_count;
$totalReg = $reserved + $publicReg;
$remaining = $model->remaining !== null ? (int) $model->remaining : max(0, $quota - $totalReg);

$attributes = array(
    array('label' => 'Tên nội dung', 'value' => $model->name),
    array('label' => 'Mã (prefix BIB)', 'value' => $model->code),
    array('label' => 'Giới hạn (quota)', 'value' => $quota),
    array('label' => 'Suất giữ lại (VIP/BTC)', 'value' => $reserved),
    array('label' => 'Đã đăng ký (Tổng)', 'value' => $totalReg . ' (Công khai: ' . $publicReg . ')'),
    array('label' => 'Còn lại', 'value' => $remaining),
    array('label' => 'Mở lúc', 'value' => $model->open_at ? date('d/m/Y H:i', (int) $model->open_at) : '—'),
    array('label' => 'Đóng lúc', 'value' => $model->close_at ? date('d/m/Y H:i', (int) $model->close_at) : '—'),
    array('label' => 'Trạng thái', 'value' => RunEvents::getStatusLabel($model->status), 'raw' => true),
    array('label' => 'Thứ tự', 'value' => (int) $model->sort_order),
    array('label' => 'Kích hoạt', 'value' => $model->is_active ? 'Có' : 'Không'),
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
<div class="card">
    <div class="card-body">
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
    </div>
</div>
