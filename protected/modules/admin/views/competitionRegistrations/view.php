<?php
$this->breadcrumbs = array(
    'Đăng ký thi nghiệp vụ' => array('admin'),
    $model->candidate_number ?: 'ID: ' . $model->id,
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
$this->Tabletitle = 'Chi tiết đăng ký: ' . ($model->candidate_number ?: 'ID: ' . $model->id);
?>

<?php
$competitionName = isset($model->competition) ? $model->competition->name : $model->competition_id;
$attendeeName = isset($model->attendee) ? $model->attendee->full_name : $model->attendee_id;

$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Số báo danh', 'value' => $model->candidate_number ?: '<span class="text-muted">Chưa cấp</span>', 'raw' => true),
    array('label' => 'Cuộc thi', 'value' => $competitionName),
    array('label' => 'Thí sinh', 'value' => $attendeeName),
    array(
        'label' => 'Trạng thái',
        'value' => CompetitionRegistrations::getStatusLabel($model->status),
        'raw' => true
    ),
    array('label' => 'Ngày đăng ký', 'value' => MyHelper::formatDateTime($model->registered_at)),
    array('label' => 'Người xác nhận', 'value' => $model->confirmed_by),
    array('label' => 'Ngày xác nhận', 'value' => MyHelper::formatDateTime($model->confirmed_at)),
    array('label' => 'Ghi chú', 'value' => $model->note),
);

$totalAttrs = count($attributes);
$colClass = 'col-md-6';
$columns = 2;
$perColumn = ceil($totalAttrs / $columns);
?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin đăng ký</h5>
        <?php if ($model->status == CompetitionRegistrations::STATUS_PENDING): ?>
        <form action="<?php echo $this->createUrl('confirm', array('id' => $model->id)); ?>" method="post" style="display:inline;">
            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Xác nhận đăng ký này?');">
                <i class="fa fa-check me-1"></i> Xác nhận
            </button>
        </form>
        <?php endif; ?>
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
