<?php
$this->breadcrumbs = array(
    'Bữa ăn' => $this->createUrl('admin', array('event_id' => $model->event_id)),
    'Thêm thủ công',
);
$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin', array('event_id' => $model->event_id)),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);
$this->Tabletitle = 'Thêm bữa ăn thủ công';

$mealTypes = array(
    Meals::TYPE_BREAKFAST => 'Ăn sáng',
    Meals::TYPE_LUNCH     => 'Ăn trưa',
    Meals::TYPE_DINNER    => 'Ăn tối',
    Meals::TYPE_SNACK     => 'Ăn nhẹ',
    Meals::TYPE_BANQUET   => 'Tiệc',
);

$toLocal = function ($value) {
    if (!$value) return '';
    $ts = strtotime($value);
    return $ts ? date('Y-m-d\TH:i', $ts) : '';
};
?>
<div class="card">
    <div class="card-body">
        <div class="form-wrap">
            <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
                'id' => 'meals-create-form',
                'enableClientValidation' => false,
            )); ?>
            <?php echo $form->errorSummary($model); ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="control-label">Sự kiện <span class="text-danger">*</span></label>
                    <select name="Meals[event_id]" class="form-select">
                        <option value="">-- Chọn sự kiện --</option>
                        <?php foreach ($eventList as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo ($model->event_id == $id) ? 'selected' : ''; ?>>
                                <?php echo CHtml::encode($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="control-label">Tên bữa ăn <span class="text-danger">*</span></label>
                    <input type="text" name="Meals[name]" class="form-control"
                           value="<?php echo CHtml::encode($model->name); ?>" placeholder="VD: Tiệc chào mừng">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="control-label">Loại bữa <span class="text-danger">*</span></label>
                    <select name="Meals[meal_type]" class="form-select">
                        <?php foreach ($mealTypes as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($model->meal_type == $value) ? 'selected' : ''; ?>>
                                <?php echo CHtml::encode($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="control-label">Ngày ăn <span class="text-danger">*</span></label>
                    <input type="date" name="Meals[meal_date]" class="form-control"
                           value="<?php echo CHtml::encode($model->meal_date); ?>">
                </div>
                <div class="col-md-4">
                    <label class="control-label">Giờ phục vụ</label>
                    <input type="datetime-local" name="Meals[serving_time]" class="form-control"
                           value="<?php echo $toLocal($model->serving_time); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="control-label">Bắt đầu chấm ăn</label>
                    <input type="datetime-local" name="Meals[checkin_start]" class="form-control"
                           value="<?php echo $toLocal($model->checkin_start); ?>">
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
                               id="meal_is_active" <?php echo ((int) $model->is_active === 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="meal_is_active">Bật chấm ăn</label>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Thêm bữa ăn</button>
                <a href="<?php echo $this->createUrl('admin', array('event_id' => $model->event_id)); ?>" class="btn btn-secondary">Hủy</a>
            </div>

            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>
