<?php
/**
 * Form cấu hình bữa ăn. Bữa ăn sinh từ agenda nên chỉ cho sửa khung giờ chấm,
 * địa điểm và bật/tắt; tên/loại/ngày hiển thị chỉ đọc.
 */
$toLocal = function ($value) {
    if (!$value) return '';
    $ts = strtotime($value);
    return $ts ? date('Y-m-d\TH:i', $ts) : '';
};
?>
<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'meals-form',
        'enableClientValidation' => false,
    )); ?>
    <?php echo $form->errorSummary($model); ?>

    <input type="hidden" name="Meals[event_id]" value="<?php echo CHtml::encode($model->event_id); ?>">
    <input type="hidden" name="Meals[meal_type]" value="<?php echo CHtml::encode($model->meal_type); ?>">
    <input type="hidden" name="Meals[meal_date]" value="<?php echo CHtml::encode($model->meal_date); ?>">
    <input type="hidden" name="Meals[serving_time]" value="<?php echo CHtml::encode($model->serving_time); ?>">

    <div class="row mb-3">
        <div class="col-md-8">
            <label class="control-label">Tên bữa ăn</label>
            <input type="text" name="Meals[name]" class="form-control"
                   value="<?php echo CHtml::encode($model->name); ?>">
        </div>
        <div class="col-md-4">
            <label class="control-label">Loại bữa</label>
            <input type="text" class="form-control" value="<?php echo CHtml::encode(Meals::getTypeLabel($model->meal_type)); ?>" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="control-label">Bắt đầu chấm ăn</label>
            <input type="datetime-local" name="Meals[checkin_start]" class="form-control"
                   value="<?php echo $toLocal($model->checkin_start ?: $model->serving_time); ?>">
            <small class="text-muted">Thời điểm mở quét QR (thường trước giờ ăn).</small>
        </div>
        <div class="col-md-6">
            <label class="control-label">Kết thúc chấm ăn</label>
            <input type="datetime-local" name="Meals[checkin_end]" class="form-control"
                   value="<?php echo $toLocal($model->checkin_end); ?>">
            <small class="text-muted">Thời điểm đóng quét QR.</small>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-8">
            <label class="control-label">Địa điểm</label>
            <input type="text" name="Meals[location]" class="form-control"
                   value="<?php echo CHtml::encode($model->location); ?>" placeholder="VD: Nhà hàng tầng 1">
        </div>
        <div class="col-md-4">
            <label class="control-label d-block">Trạng thái</label>
            <div class="form-check form-switch mt-2">
                <input type="hidden" name="Meals[is_active]" value="0">
                <input type="checkbox" class="form-check-input" name="Meals[is_active]" value="1"
                       id="meal_is_active" <?php echo ((int) $model->is_active === 1 || $model->is_active === null) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="meal_is_active">Bật chấm ăn</label>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Lưu thay đổi</button>
        <a href="<?php echo $this->createUrl('admin', array('event_id' => $model->event_id)); ?>" class="btn btn-secondary">Hủy</a>
    </div>

    <?php $this->endWidget(); ?>
</div>
