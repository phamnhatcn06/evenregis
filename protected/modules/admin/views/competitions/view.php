<?php
$this->breadcrumbs = array(
    'Cuộc thi nghiệp vụ' => array('admin'),
    $model->name,
);

$this->menu = array(
    array(
        'label' => 'Quản lý',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => 'Thêm mới',
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
    array(
        'label' => 'Xóa',
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);
$this->Tabletitle = 'Chi tiết cuộc thi: ' . $model->name;
?>

<?php
$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Tên cuộc thi', 'value' => $model->name),
    array('label' => 'Tiền tố SBD', 'value' => $model->candidate_number_prefix),
    array('label' => 'SBD bắt đầu', 'value' => $model->candidate_number_start),
    array('label' => 'Độ dài SBD', 'value' => $model->candidate_number_pad),
    array('label' => 'Giới hạn/đơn vị', 'value' => $model->max_per_org ?: 'Không giới hạn'),
    array(
        'label' => 'Có vòng loại',
        'value' => $model->has_qualification ? '<span class="badge bg-info">Có</span>' : '<span class="badge bg-secondary">Không</span>',
        'raw' => true
    ),
    array(
        'label' => 'Ghi danh thẳng CK',
        'value' => $model->allow_direct_final ? '<span class="badge bg-success">Cho phép</span>' : '<span class="badge bg-secondary">Không</span>',
        'raw' => true
    ),
    array('label' => 'Mở đăng ký', 'value' => $model->registration_open_at ? MyHelper::formatDateTime($model->registration_open_at) : ''),
    array('label' => 'Đóng đăng ký', 'value' => $model->registration_close_at ? MyHelper::formatDateTime($model->registration_close_at) : ''),
    array(
        'label' => 'Trạng thái',
        'value' => Competitions::getStatusLabel($model->is_active),
        'raw' => true
    ),
    array('label' => 'Ngày tạo', 'value' => MyHelper::formatDateTime($model->created_at)),
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

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin cuộc thi</h5>
        <form action="<?php echo $this->createUrl('assignNumbers', array('id' => $model->id)); ?>" method="post" style="display:inline;">
            <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Cấp số báo danh cho tất cả thí sinh?');">
                <i class="fa fa-sort-numeric-asc me-1"></i> Cấp số báo danh
            </button>
        </form>
    </div>
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

<?php if ($model->description): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-file-text-o me-2"></i>Mô tả</h5>
    </div>
    <div class="card-body">
        <?php echo nl2br(CHtml::encode($model->description)); ?>
    </div>
</div>
<?php endif; ?>
