<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'run-events-form',
        'htmlOptions' => array('data-toggle' => 'validator'),
        'enableClientValidation' => true,
        'clientOptions' => array('validateOnSubmit' => true),
    ));
    ?>
    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <div class="col-md-8">
            <?php echo $form->textFieldGroup($model, 'name', array(
                'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'placeholder' => 'VD: 5km Nam 40+')),
            )); ?>
        </div>
        <div class="col-md-4">
            <?php echo $form->textFieldGroup($model, 'code', array(
                'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'placeholder' => 'VD: 5K (prefix BIB)')),
            )); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?php echo $form->dropDownListGroup($model, 'event_id', array(
                'widgetOptions' => array(
                    'data' => $eventList,
                    'htmlOptions' => array('class' => 'form-select', 'empty' => '-- Chọn sự kiện --'),
                ),
            )); ?>
        </div>
        <div class="col-md-3">
            <?php echo $form->textFieldGroup($model, 'quota', array(
                'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'type' => 'number', 'min' => 0)),
            )); ?>
        </div>
        <div class="col-md-3">
            <?php echo $form->textFieldGroup($model, 'sort_order', array(
                'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'type' => 'number', 'min' => 0)),
            )); ?>
        </div>
    </div>

<?php
    $fmtPicker = function ($value) {
        if (!$value) return '';
        $ts = is_numeric($value) ? (int) $value : strtotime($value);
        return $ts ? date('d-m-Y H:i', $ts) : '';
    };
    ?>
    <div class="row">
        <div class="col-md-6">
            <label class="control-label">Mở lúc</label>
            <input type="text" id="open_at_picker" class="form-control"
                value="<?php echo $fmtPicker($model->open_at); ?>" placeholder="dd-mm-yyyy hh:mm" autocomplete="off">
            <input type="hidden" name="RunEvents[open_at]" id="open_at_hidden"
                value="<?php echo CHtml::encode($model->open_at); ?>">
        </div>
        <div class="col-md-6">
            <label class="control-label">Đóng lúc</label>
            <input type="text" id="close_at_picker" class="form-control"
                value="<?php echo $fmtPicker($model->close_at); ?>" placeholder="dd-mm-yyyy hh:mm" autocomplete="off">
            <input type="hidden" name="RunEvents[close_at]" id="close_at_hidden"
                value="<?php echo CHtml::encode($model->close_at); ?>">
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6">
            <label class="control-label">Hạn chót xin hủy</label>
            <input type="text" id="cancel_until_picker" class="form-control"
                value="<?php echo $fmtPicker($model->cancel_until); ?>" placeholder="dd-mm-yyyy hh:mm" autocomplete="off">
            <input type="hidden" name="RunEvents[cancel_until]" id="cancel_until_hidden"
                value="<?php echo CHtml::encode($model->cancel_until); ?>">
            <small class="text-muted">Sau mốc này người đăng ký không thể xin hủy. Để trống nếu cho hủy đến khi đóng đăng ký.</small>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6">
            <?php echo $form->dropDownListGroup($model, 'status', array(
                'widgetOptions' => array(
                    'data' => array(
                        RunEvents::STATUS_OPEN => 'Đang mở',
                        RunEvents::STATUS_CLOSED => 'Đã đóng',
                    ),
                    'htmlOptions' => array('class' => 'form-select'),
                ),
            )); ?>
        </div>
        <div class="col-md-6">
            <?php echo $form->dropDownListGroup($model, 'is_active', array(
                'widgetOptions' => array(
                    'data' => array(1 => 'Kích hoạt', 0 => 'Tắt'),
                    'htmlOptions' => array('class' => 'form-select'),
                ),
            )); ?>
        </div>
    </div>

    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-save me-1"></i>Lưu
        </button>
    </div>

    <?php $this->endWidget(); ?>
</div>
