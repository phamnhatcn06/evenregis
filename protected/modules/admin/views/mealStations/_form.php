<?php
/** Form trạm chấm ăn. Khi sửa, để trống mã truy cập = giữ nguyên mã cũ. */
$isUpdate = !empty($model->id);
?>
<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'meal-stations-form',
        'enableClientValidation' => false,
    )); ?>
    <?php echo $form->errorSummary($model); ?>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="control-label">Sự kiện <span class="text-danger">*</span></label>
            <?php echo CHtml::dropDownList('MealStations[event_id]', $model->event_id, $eventList, array(
                'class' => 'form-select',
                'empty' => '-- Chọn sự kiện --',
            )); ?>
        </div>
        <div class="col-md-6">
            <label class="control-label">Tên trạm / cửa <span class="text-danger">*</span></label>
            <input type="text" name="MealStations[name]" class="form-control"
                   value="<?php echo CHtml::encode($model->name); ?>" placeholder="VD: Cửa A - Nhà hàng tầng 1">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="control-label">Mã ngắn</label>
            <input type="text" name="MealStations[code]" class="form-control"
                   value="<?php echo CHtml::encode($model->code); ?>" placeholder="VD: A1">
        </div>
        <div class="col-md-8">
            <label class="control-label">Địa điểm</label>
            <input type="text" name="MealStations[location]" class="form-control"
                   value="<?php echo CHtml::encode($model->location); ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="control-label">
                Mã truy cập <?php echo $isUpdate ? '' : '<span class="text-danger">*</span>'; ?>
            </label>
            <input type="text" name="MealStations[passcode]" class="form-control" autocomplete="off"
                   placeholder="<?php echo $isUpdate ? 'Để trống nếu không đổi mã' : 'Tối thiểu 4 ký tự'; ?>">
            <small class="text-muted">Nhân viên nhập mã này để mở màn quét của trạm.</small>
        </div>
        <div class="col-md-6">
            <label class="control-label d-block">Trạng thái</label>
            <div class="form-check form-switch mt-2">
                <input type="hidden" name="MealStations[is_active]" value="0">
                <input type="checkbox" class="form-check-input" name="MealStations[is_active]" value="1"
                       id="station_is_active" <?php echo ((int) $model->is_active === 1 || $model->is_active === null) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="station_is_active">Kích hoạt</label>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Lưu</button>
        <a href="<?php echo $this->createUrl('admin', array('event_id' => $model->event_id)); ?>" class="btn btn-secondary">Hủy</a>
    </div>

    <?php $this->endWidget(); ?>
</div>
