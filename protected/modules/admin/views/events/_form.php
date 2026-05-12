<div class="form-wrap">
    <?php $form = $this->beginWidget('CActiveForm', array(
        'id' => 'events-form',
        'htmlOptions' => array('enctype' => 'multipart/form-data'),
        'enableClientValidation' => false,
    )); ?>

    <?php echo $form->errorSummary($model); ?>
    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'name'); ?>
        <?php echo $form->textField($model, 'name', array(
            'class' => 'form-control',
            'maxlength' => 255,
        )); ?>
        <?php echo $form->error($model, 'name'); ?>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'from_date'); ?>
        <?php echo $form->textField($model, 'from_date', array(
            'class' => 'form-control',
            'placeholder' => 'Chọn ngày bắt đầu',
        )); ?>
        <?php echo $form->error($model, 'from_date'); ?>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'to_date'); ?>
        <?php echo $form->textField($model, 'to_date', array(
            'class' => 'form-control',
            'placeholder' => 'Chọn ngày kết thúc',
        )); ?>
        <?php echo $form->error($model, 'to_date'); ?>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'description'); ?>
        <?php echo $form->textArea($model, 'description', array(
            'class' => 'form-control',
            'rows' => 5,
        )); ?>
        <?php echo $form->error($model, 'description'); ?>
    </div>

    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-sm btn-primary">
            Lưu lại
        </button>
    </div>

    <?php $this->endWidget(); ?>
</div>

<?php
$baseUrl = Yii::app()->theme->baseUrl;
Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/vendor/flatpickr/dist/flatpickr.min.css');
Yii::app()->clientScript->registerScriptFile($baseUrl . '/assets/vendor/flatpickr/dist/flatpickr.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScript('flatpickr-init', "
    var Vietnamese = {
        weekdays: {
            shorthand: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            longhand: ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy']
        },
        months: {
            shorthand: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
            longhand: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12']
        },
        firstDayOfWeek: 1
    };
    var toDatePicker = flatpickr('#Events_to_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
        allowInput: true,
        locale: Vietnamese
    });
    flatpickr('#Events_from_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
        allowInput: true,
        locale: Vietnamese,
        onChange: function(selectedDates, dateStr) {
            toDatePicker.set('minDate', dateStr);
        }
    });
", CClientScript::POS_READY);
?>