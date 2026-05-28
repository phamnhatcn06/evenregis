<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'approval-workflow-form',
    'enableAjaxValidation' => false,
    'htmlOptions' => array('class' => 'form-horizontal'),
));
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin quy trình duyệt</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Mã quy trình <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <?php echo $form->textField($model, 'code', array(
                            'class' => 'form-control',
                            'placeholder' => 'VD: STANDARD, SIMPLE...',
                            'maxlength' => 50,
                        )); ?>
                        <?php echo $form->error($model, 'code', array('class' => 'text-danger')); ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Tên quy trình <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <?php echo $form->textField($model, 'name', array(
                            'class' => 'form-control',
                            'placeholder' => 'VD: Quy trình duyệt chuẩn',
                            'maxlength' => 255,
                        )); ?>
                        <?php echo $form->error($model, 'name', array('class' => 'text-danger')); ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Mô tả</label>
                    <div class="col-md-9">
                        <?php echo $form->textArea($model, 'description', array(
                            'class' => 'form-control',
                            'rows' => 3,
                            'placeholder' => 'Mô tả về quy trình này...',
                        )); ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Số bước duyệt <span class="text-danger">*</span></label>
                    <div class="col-md-3">
                        <?php echo $form->numberField($model, 'total_steps', array(
                            'class' => 'form-control',
                            'min' => 1,
                            'max' => 10,
                            'value' => $model->total_steps ? $model->total_steps : 1,
                        )); ?>
                        <?php echo $form->error($model, 'total_steps', array('class' => 'text-danger')); ?>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Tổng số cấp duyệt (1-10)</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Tùy chọn</label>
                    <div class="col-md-9">
                        <div class="form-check form-switch mb-2">
                            <?php echo $form->checkBox($model, 'is_default', array(
                                'class' => 'form-check-input',
                                'id' => 'is_default',
                            )); ?>
                            <label class="form-check-label" for="is_default">Đặt làm quy trình mặc định</label>
                        </div>
                        <div class="form-check form-switch">
                            <?php echo $form->checkBox($model, 'is_active', array(
                                'class' => 'form-check-input',
                                'id' => 'is_active',
                                'checked' => $model->isNewRecord || $model->is_active,
                            )); ?>
                            <label class="form-check-label" for="is_active">Hoạt động</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo $model->isNewRecord ? 'Tạo mới' : 'Cập nhật'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="fa fa-info-circle"></i> Hướng dẫn</h6>
                <p class="small text-muted mb-2">
                    <strong>Số bước duyệt:</strong> Là tổng số cấp duyệt trong quy trình.
                </p>
                <p class="small text-muted mb-2">
                    <strong>Ví dụ 3 bước:</strong><br>
                    1. Giám đốc đơn vị<br>
                    2. Nhân sự TĐ<br>
                    3. GĐ Nhân sự tập đoàn
                </p>
                <p class="small text-muted mb-0">
                    Sau khi tạo workflow, bạn cần vào chi tiết để gán người duyệt cho từng bước.
                </p>
            </div>
        </div>
    </div>
</div>

<?php $this->endWidget(); ?>
