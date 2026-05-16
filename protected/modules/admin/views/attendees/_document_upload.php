<?php
/**
 * Partial view: Upload documents widget
 * Dùng trong form đăng ký để upload 4 loại giấy tờ
 *
 * @var Attendees $model
 * @var string $fieldPrefix Prefix cho field name (vd: Attendees hoặc attendees[0])
 */

$fieldPrefix = isset($fieldPrefix) ? $fieldPrefix : 'Attendees';
$index = isset($index) ? $index : '';
$namePrefix = $index !== '' ? "{$fieldPrefix}[{$index}]" : $fieldPrefix;
$idPrefix = $index !== '' ? "{$fieldPrefix}_{$index}" : $fieldPrefix;
?>

<div class="document-upload-widget" data-index="<?php echo $index; ?>">
    <div class="row g-3">
        <!-- CCCD mặt trước -->
        <div class="col-6 col-md-3">
            <div class="upload-box text-center p-2 border rounded">
                <label class="d-block mb-2 small fw-bold">
                    CCCD Trước <span class="text-danger">*</span>
                </label>
                <div class="preview-box mb-2" id="<?php echo $idPrefix; ?>_cccd_front_preview" style="height:100px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;">
                    <?php if (!empty($model->cccd_front_path)): ?>
                        <img src="<?php echo $model->cccd_front_path; ?>" style="max-height:100%;max-width:100%;">
                    <?php else: ?>
                        <i class="fa fa-id-card-o fa-2x text-muted"></i>
                    <?php endif; ?>
                </div>
                <input type="file"
                       id="<?php echo $idPrefix; ?>_cccd_front_upload"
                       name="<?php echo $namePrefix; ?>[cccd_front_file]"
                       class="form-control form-control-sm doc-upload"
                       data-type="image"
                       data-field="cccd_front"
                       accept="image/jpeg,image/png">
                <input type="hidden"
                       id="<?php echo $idPrefix; ?>_cccd_front_path"
                       name="<?php echo $namePrefix; ?>[cccd_front_path]"
                       value="<?php echo CHtml::encode($model->cccd_front_path); ?>">
                <div class="error-msg text-danger small mt-1" style="display:none;"></div>
            </div>
        </div>

        <!-- CCCD mặt sau -->
        <div class="col-6 col-md-3">
            <div class="upload-box text-center p-2 border rounded">
                <label class="d-block mb-2 small fw-bold">
                    CCCD Sau <span class="text-danger">*</span>
                </label>
                <div class="preview-box mb-2" id="<?php echo $idPrefix; ?>_cccd_back_preview" style="height:100px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;">
                    <?php if (!empty($model->cccd_back_path)): ?>
                        <img src="<?php echo $model->cccd_back_path; ?>" style="max-height:100%;max-width:100%;">
                    <?php else: ?>
                        <i class="fa fa-id-card-o fa-2x text-muted"></i>
                    <?php endif; ?>
                </div>
                <input type="file"
                       id="<?php echo $idPrefix; ?>_cccd_back_upload"
                       name="<?php echo $namePrefix; ?>[cccd_back_file]"
                       class="form-control form-control-sm doc-upload"
                       data-type="image"
                       data-field="cccd_back"
                       accept="image/jpeg,image/png">
                <input type="hidden"
                       id="<?php echo $idPrefix; ?>_cccd_back_path"
                       name="<?php echo $namePrefix; ?>[cccd_back_path]"
                       value="<?php echo CHtml::encode($model->cccd_back_path); ?>">
                <div class="error-msg text-danger small mt-1" style="display:none;"></div>
            </div>
        </div>

        <!-- Ảnh chân dung -->
        <div class="col-6 col-md-3">
            <div class="upload-box text-center p-2 border rounded">
                <label class="d-block mb-2 small fw-bold">
                    Chân dung <span class="text-danger">*</span>
                </label>
                <div class="preview-box mb-2" id="<?php echo $idPrefix; ?>_portrait_preview" style="height:100px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;">
                    <?php if (!empty($model->portrait_path)): ?>
                        <img src="<?php echo $model->portrait_path; ?>" style="max-height:100%;max-width:100%;">
                    <?php else: ?>
                        <i class="fa fa-user-circle-o fa-2x text-muted"></i>
                    <?php endif; ?>
                </div>
                <input type="file"
                       id="<?php echo $idPrefix; ?>_portrait_upload"
                       name="<?php echo $namePrefix; ?>[portrait_file]"
                       class="form-control form-control-sm doc-upload"
                       data-type="portrait"
                       data-field="portrait"
                       accept="image/jpeg,image/png">
                <input type="hidden"
                       id="<?php echo $idPrefix; ?>_portrait_path"
                       name="<?php echo $namePrefix; ?>[portrait_path]"
                       value="<?php echo CHtml::encode($model->portrait_path); ?>">
                <small class="text-muted d-block">530×530px</small>
                <div class="error-msg text-danger small mt-1" style="display:none;"></div>
            </div>
        </div>

        <!-- Hợp đồng lao động -->
        <div class="col-6 col-md-3">
            <div class="upload-box text-center p-2 border rounded">
                <label class="d-block mb-2 small fw-bold">
                    Hợp đồng <span class="text-danger">*</span>
                </label>
                <div class="preview-box mb-2" id="<?php echo $idPrefix; ?>_contract_preview" style="height:100px;background:#f8f9fa;display:flex;align-items:center;justify-content:center;">
                    <?php if (!empty($model->contract_path)): ?>
                        <?php if (pathinfo($model->contract_path, PATHINFO_EXTENSION) === 'pdf'): ?>
                            <i class="fa fa-file-pdf-o fa-2x text-danger"></i>
                        <?php else: ?>
                            <img src="<?php echo $model->contract_path; ?>" style="max-height:100%;max-width:100%;">
                        <?php endif; ?>
                    <?php else: ?>
                        <i class="fa fa-file-text-o fa-2x text-muted"></i>
                    <?php endif; ?>
                </div>
                <input type="file"
                       id="<?php echo $idPrefix; ?>_contract_upload"
                       name="<?php echo $namePrefix; ?>[contract_file]"
                       class="form-control form-control-sm doc-upload"
                       data-type="contract"
                       data-field="contract"
                       accept="image/jpeg,image/png,application/pdf">
                <input type="hidden"
                       id="<?php echo $idPrefix; ?>_contract_path"
                       name="<?php echo $namePrefix; ?>[contract_path]"
                       value="<?php echo CHtml::encode($model->contract_path); ?>">
                <small class="text-muted d-block">PDF/JPG/PNG</small>
                <div class="error-msg text-danger small mt-1" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>
