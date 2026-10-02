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

    <div class="row">
        <div class="col-md-6">
            <label class="control-label">Mở lúc</label>
            <?php echo CHtml::activeTextField($model, 'open_at', array(
                'type' => 'datetime-local',
                'class' => 'form-control',
                'value' => $model->open_at ? date('Y-m-d\TH:i', (int) $model->open_at) : '',
            )); ?>
        </div>
        <div class="col-md-6">
            <label class="control-label">Đóng lúc</label>
            <?php echo CHtml::activeTextField($model, 'close_at', array(
                'type' => 'datetime-local',
                'class' => 'form-control',
                'value' => $model->close_at ? date('Y-m-d\TH:i', (int) $model->close_at) : '',
            )); ?>
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
