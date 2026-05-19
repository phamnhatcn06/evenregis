<?php
$this->breadcrumbs = array(
    'Yêu cầu liên quân' => array('admin'),
    'Chi tiết #' . $model->id,
);

$menuItems = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);

if ($model->status == AllianceRequests::STATUS_PENDING) {
    array_unshift($menuItems, array(
        'label' => 'Từ chối',
        'url' => $this->createUrl('reject', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-times',
    ));
}

$this->menu = $menuItems;
$this->Tabletitle = 'Chi tiết yêu cầu liên quân #' . $model->id;
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin yêu cầu</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">ID</th>
                                <td><?php echo CHtml::encode($model->id); ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Đơn vị yêu cầu</th>
                                <td><strong><?php echo CHtml::encode($model->requester_org_name); ?></strong></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Đơn vị liên quân</th>
                                <td><strong><?php echo CHtml::encode($model->target_org_name); ?></strong></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Trạng thái</th>
                                <td><?php echo AllianceRequests::getStatusLabel($model->status); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">Người yêu cầu</th>
                                <td><?php echo CHtml::encode($model->requested_by_name); ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Ngày yêu cầu</th>
                                <td><?php echo MyHelper::formatDateTime($model->requested_at); ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Người duyệt</th>
                                <td><?php echo $model->reviewed_by_name ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Ngày duyệt</th>
                                <td><?php echo $model->reviewed_at ? MyHelper::formatDateTime($model->reviewed_at) : '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if (!empty($model->note)): ?>
                    <div class="mt-3">
                        <h6>Ghi chú</h6>
                        <p><?php echo nl2br(CHtml::encode($model->note)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($model->rejection_reason)): ?>
                    <div class="alert alert-danger mt-3">
                        <h6><i class="fa fa-exclamation-circle me-2"></i>Lý do từ chối</h6>
                        <p class="mb-0"><?php echo nl2br(CHtml::encode($model->rejection_reason)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php if ($model->status == AllianceRequests::STATUS_PENDING): ?>
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-dark">Xử lý yêu cầu</h5>
                </div>
                <div class="card-body">
                    <?php echo CHtml::beginForm($this->createUrl('approve', array('id' => $model->id)), 'post'); ?>
                    <button type="submit" class="btn btn-success btn-lg w-100 mb-3" onclick="return confirm('Bạn có chắc chắn muốn xác nhận liên quân?')">
                        <i class="fa fa-check me-2"></i> Xác nhận liên quân
                    </button>
                    <?php echo CHtml::endForm(); ?>

                    <a href="<?php echo $this->createUrl('reject', array('id' => $model->id)); ?>" class="btn btn-danger btn-lg w-100">
                        <i class="fa fa-times me-2"></i> Từ chối
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
