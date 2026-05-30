<?php
$this->breadcrumbs = array(
    'Quy trình duyệt' => array('admin'),
    $workflow->name => array('view', 'id' => $workflow->id),
    'Thêm người duyệt',
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('view', array('id' => $workflow->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
        'id' => 'btn_create'
    ),
);
$this->Tabletitle = 'Thêm người duyệt cho: ' . CHtml::encode($workflow->name);

$form = $this->beginWidget('CActiveForm', array(
    'id' => 'add-approver-form',
    'enableAjaxValidation' => false,
    'htmlOptions' => array('class' => 'form-horizontal'),
));
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin người duyệt</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Bước duyệt <span class="text-danger">*</span></label>
                    <div class="col-md-3">
                        <?php
                        $stepOptions = array();
                        for ($i = 1; $i <= $workflow->total_steps; $i++) {
                            $stepOptions[$i] = 'Bước ' . $i;
                        }
                        echo $form->dropDownList($model, 'step_index', $stepOptions, array(
                            'class' => 'form-select',
                            'prompt' => '-- Chọn bước --',
                        ));
                        ?>
                        <?php echo $form->error($model, 'step_index', array('class' => 'text-danger')); ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Tên bước <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        <?php echo $form->textField($model, 'step_name', array(
                            'class' => 'form-control',
                            'placeholder' => 'VD: Giám đốc đơn vị, Nhân sự TĐ...',
                            'maxlength' => 255,
                        )); ?>
                        <?php echo $form->error($model, 'step_name', array('class' => 'text-danger')); ?>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Portal User ID <span class="text-danger">*</span></label>
                    <div class="col-md-4">
                        <?php echo $form->numberField($model, 'portal_user_id', array(
                            'class' => 'form-control',
                            'placeholder' => 'ID từ Portal SSO',
                        )); ?>
                        <?php echo $form->error($model, 'portal_user_id', array('class' => 'text-danger')); ?>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted">Lấy từ JWT token (trường "sub")</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Tên người duyệt</label>
                    <div class="col-md-9">
                        <?php echo $form->textField($model, 'portal_user_name', array(
                            'class' => 'form-control',
                            'placeholder' => 'Tên hiển thị',
                            'maxlength' => 255,
                        )); ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Email</label>
                    <div class="col-md-9">
                        <?php echo $form->textField($model, 'portal_user_email', array(
                            'class' => 'form-control',
                            'placeholder' => 'Email từ Portal',
                            'maxlength' => 255,
                        )); ?>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Áp dụng cho đơn vị</label>
                    <div class="col-md-9">
                        <?php echo $form->numberField($model, 'organization_id', array(
                            'class' => 'form-control',
                            'placeholder' => 'Để trống = tất cả đơn vị',
                        )); ?>
                        <small class="text-muted">Để trống nếu người này duyệt cho tất cả đơn vị</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Trạng thái</label>
                    <div class="col-md-9">
                        <div class="form-check form-switch">
                            <?php echo $form->checkBox($model, 'is_active', array(
                                'class' => 'form-check-input',
                                'id' => 'is_active',
                                'checked' => true,
                            )); ?>
                            <label class="form-check-label" for="is_active">Hoạt động</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo $this->createUrl('view', array('id' => $workflow->id)); ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Thêm người duyệt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="fa fa-info-circle"></i> Lưu ý</h6>
                <ul class="small text-muted mb-0">
                    <li class="mb-2"><strong>Portal User ID:</strong> Là ID của user trong hệ thống Portal SSO (trường "sub" trong JWT)</li>
                    <li class="mb-2"><strong>Tên bước:</strong> Mô tả vai trò duyệt ở bước này (GĐ đơn vị, NS TĐ...)</li>
                    <li class="mb-2"><strong>Đơn vị:</strong> Nếu chỉ định, người này chỉ duyệt đơn của đơn vị đó</li>
                    <li>Có thể thêm nhiều người cho cùng 1 bước (bất kỳ ai cũng có thể duyệt)</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Workflow: <?php echo CHtml::encode($workflow->name); ?></h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Mã:</strong> <?php echo CHtml::encode($workflow->code); ?></p>
                <p class="mb-0"><strong>Số bước:</strong> <?php echo $workflow->total_steps; ?></p>
            </div>
        </div>
    </div>
</div>

<?php $this->endWidget(); ?>