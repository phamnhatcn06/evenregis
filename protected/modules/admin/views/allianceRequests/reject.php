<?php
$this->breadcrumbs = array(
    'Yêu cầu liên quân' => array('admin'),
    'Chi tiết #' . $model->id => array('view', 'id' => $model->id),
    'Từ chối',
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Từ chối yêu cầu liên quân #' . $model->id;
?>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Đơn vị yêu cầu:</strong> <?php echo CHtml::encode($model->requester_org_name); ?></p>
                <p><strong>Đơn vị liên quân:</strong> <?php echo CHtml::encode($model->target_org_name); ?></p>
            </div>
        </div>

        <?php echo CHtml::beginForm($this->createUrl('reject', array('id' => $model->id)), 'post'); ?>

        <div class="mb-3">
            <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
            <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Nhập lý do từ chối..." required></textarea>
        </div>

        <hr />
        <div class="footer-action">
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối yêu cầu này?')">
                <i class="fa fa-times me-1"></i> Xác nhận từ chối
            </button>
            <a href="<?php echo $this->createUrl('view', array('id' => $model->id)); ?>" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <?php echo CHtml::endForm(); ?>
    </div>
</div>
