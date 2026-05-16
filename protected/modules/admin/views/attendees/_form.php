<?php
/**
 * Attendee Form - Section 15: Upload documents
 * @var Attendees $model
 * @var array $staffList
 * @var array $events
 * @var array $properties
 */

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/attendees-form.js',
    CClientScript::POS_END
);
?>

<div class="form-wrap">
    <?php $form = $this->beginWidget('CActiveForm', array(
        'id' => 'attendee-form',
        'htmlOptions' => array('enctype' => 'multipart/form-data'),
        'enableClientValidation' => false,
    )); ?>

    <?php echo $form->errorSummary($model); ?>

    <!-- Thông tin cơ bản -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fa fa-user"></i> Thông tin người tham dự</h5>
        </div>
        <div class="card-body">
            <!-- BR-REG-01: Chọn nguồn dữ liệu -->
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label fw-bold">Nguồn thông tin</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attendee_mode" id="mode_staff" value="staff" <?php echo $model->staff_id ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="mode_staff">
                                Chọn từ danh sách nhân viên
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attendee_mode" id="mode_manual" value="manual" <?php echo !$model->staff_id ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="mode_manual">
                                Tự điền thông tin
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropdown chọn staff -->
            <div id="staff-select-container"
                 data-api-url="<?php echo Yii::app()->params['externalApiUrl'] . '/api/staffs/detail'; ?>"
                 data-api-key="<?php echo Yii::app()->params['externalApiKey']; ?>"
                 style="<?php echo $model->staff_id ? '' : 'display:none;'; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <?php echo $form->labelEx($model, 'staff_id'); ?>
                            <?php echo $form->dropDownList($model, 'staff_id', $staffList, array(
                                'class' => 'form-select',
                                'prompt' => '-- Chọn nhân viên --',
                            )); ?>
                            <?php echo $form->error($model, 'staff_id'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fields nhập manual -->
            <div id="manual-fields-container" style="<?php echo !$model->staff_id ? '' : 'display:none;'; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <?php echo $form->labelEx($model, 'full_name'); ?>
                            <?php echo $form->textField($model, 'full_name', array(
                                'class' => 'form-control',
                                'placeholder' => 'Nhập họ và tên',
                            )); ?>
                            <?php echo $form->error($model, 'full_name'); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <?php echo $form->labelEx($model, 'position'); ?>
                            <?php echo $form->textField($model, 'position', array(
                                'class' => 'form-control',
                                'placeholder' => 'Nhập chức vụ',
                            )); ?>
                            <?php echo $form->error($model, 'position'); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <?php echo $form->labelEx($model, 'unit_label'); ?>
                            <?php echo $form->textField($model, 'unit_label', array(
                                'class' => 'form-control',
                                'placeholder' => 'Tên đơn vị hiển thị trên thẻ',
                            )); ?>
                            <?php echo $form->error($model, 'unit_label'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event & Property (luôn hiện) -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <?php echo $form->labelEx($model, 'event_id'); ?>
                        <?php echo $form->dropDownList($model, 'event_id', $events, array(
                            'class' => 'form-select',
                            'prompt' => '-- Chọn sự kiện --',
                        )); ?>
                        <?php echo $form->error($model, 'event_id'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <?php echo $form->labelEx($model, 'property_id'); ?>
                        <?php echo $form->dropDownList($model, 'property_id', $properties, array(
                            'class' => 'form-select',
                            'prompt' => '-- Chọn đơn vị --',
                        )); ?>
                        <?php echo $form->error($model, 'property_id'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BR-REG-02, BR-REG-03: Upload giấy tờ -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fa fa-file-image-o"></i> Giấy tờ đính kèm <span class="text-danger">*</span></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- CCCD mặt trước -->
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Ảnh CCCD mặt trước <span class="text-danger">*</span></label>
                        <input type="file" id="cccd_front_upload" name="cccd_front_file" class="form-control" accept="image/jpeg,image/png">
                        <input type="hidden" id="cccd_front_path" name="Attendees[cccd_front_path]" value="<?php echo CHtml::encode($model->cccd_front_path); ?>">
                        <small class="text-muted">JPG/PNG, tối đa 5MB</small>
                        <div id="cccd_front_error" class="text-danger small" style="display:none;"></div>
                        <div id="cccd_front_preview" class="mt-2">
                            <?php if ($model->cccd_front_path): ?>
                                <img src="<?php echo $model->cccd_front_path; ?>" class="img-thumbnail" style="max-height:150px;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- CCCD mặt sau -->
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Ảnh CCCD mặt sau <span class="text-danger">*</span></label>
                        <input type="file" id="cccd_back_upload" name="cccd_back_file" class="form-control" accept="image/jpeg,image/png">
                        <input type="hidden" id="cccd_back_path" name="Attendees[cccd_back_path]" value="<?php echo CHtml::encode($model->cccd_back_path); ?>">
                        <small class="text-muted">JPG/PNG, tối đa 5MB</small>
                        <div id="cccd_back_error" class="text-danger small" style="display:none;"></div>
                        <div id="cccd_back_preview" class="mt-2">
                            <?php if ($model->cccd_back_path): ?>
                                <img src="<?php echo $model->cccd_back_path; ?>" class="img-thumbnail" style="max-height:150px;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Ảnh chân dung -->
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Ảnh chân dung <span class="text-danger">*</span></label>
                        <input type="file" id="portrait_upload" name="portrait_file" class="form-control" accept="image/jpeg,image/png">
                        <input type="hidden" id="portrait_path" name="Attendees[portrait_path]" value="<?php echo CHtml::encode($model->portrait_path); ?>">
                        <small class="text-muted"><strong>530×530px</strong>, JPG/PNG, tối đa 5MB</small>
                        <div id="portrait_error" class="text-danger small" style="display:none;"></div>
                        <div id="portrait_preview" class="mt-2">
                            <?php if ($model->portrait_path): ?>
                                <img src="<?php echo $model->portrait_path; ?>" class="img-thumbnail" style="max-height:150px;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Hợp đồng lao động -->
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Hợp đồng lao động <span class="text-danger">*</span></label>
                        <input type="file" id="contract_upload" name="contract_file" class="form-control" accept="image/jpeg,image/png,application/pdf">
                        <input type="hidden" id="contract_path" name="Attendees[contract_path]" value="<?php echo CHtml::encode($model->contract_path); ?>">
                        <small class="text-muted">JPG/PNG/PDF, tối đa 10MB</small>
                        <div id="contract_error" class="text-danger small" style="display:none;"></div>
                        <div id="contract_preview" class="mt-2">
                            <?php if ($model->contract_path): ?>
                                <?php if (pathinfo($model->contract_path, PATHINFO_EXTENSION) === 'pdf'): ?>
                                    <div class="p-3 bg-light rounded text-center">
                                        <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                                        <p class="mb-0 mt-2">PDF đã upload</p>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo $model->contract_path; ?>" class="img-thumbnail" style="max-height:150px;">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ghi chú -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fa fa-sticky-note-o"></i> Ghi chú</h5>
        </div>
        <div class="card-body">
            <div class="form-group">
                <?php echo $form->textArea($model, 'note', array(
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ghi chú thêm (nếu có)',
                )); ?>
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="form-group d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Lưu thông tin
        </button>
        <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php $this->endWidget(); ?>
</div>
