<?php
$eventList = array();
foreach ($events as $e) {
    $eId = isset($e['id']) ? $e['id'] : (isset($e->id) ? $e->id : null);
    $eName = isset($e['name']) ? $e['name'] : (isset($e->name) ? $e->name : '');
    if ($eId) {
        $eventList[$eId] = $eName;
    }
}
?>

<div class="form-wrap">
    <?php $form = $this->beginWidget('CActiveForm', array(
        'id' => 'registration-periods-form',
        'enableClientValidation' => false,
    )); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'event_id'); ?>
                <?php echo $form->dropDownList($model, 'event_id', $eventList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn sự kiện --',
                )); ?>
                <?php echo $form->error($model, 'event_id'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'name'); ?>
                <?php echo $form->textField($model, 'name', array(
                    'class' => 'form-control',
                    'maxlength' => 255,
                    'placeholder' => 'VD: Đợt 1 - Đăng ký chính thức',
                )); ?>
                <?php echo $form->error($model, 'name'); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'start_time'); ?>
                <?php
                $startValue = '';
                if ($model->start_time) {
                    $startValue = date('d-m-Y H:i', $model->start_time);
                }
                ?>
                <input type="text" id="start_time_picker" class="form-control"
                    value="<?php echo $startValue; ?>" placeholder="dd-mm-yyyy hh:mm" required>
                <input type="hidden" name="RegistrationPeriods[start_time]" id="start_time_hidden"
                    value="<?php echo $model->start_time; ?>">
                <?php echo $form->error($model, 'start_time'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'end_time'); ?>
                <?php
                $endValue = '';
                if ($model->end_time) {
                    $endValue = date('d-m-Y H:i', $model->end_time);
                }
                ?>
                <input type="text" id="end_time_picker" class="form-control"
                    value="<?php echo $endValue; ?>" placeholder="dd-mm-yyyy hh:mm" required>
                <input type="hidden" name="RegistrationPeriods[end_time]" id="end_time_hidden"
                    value="<?php echo $model->end_time; ?>">
                <?php echo $form->error($model, 'end_time'); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'max_per_org'); ?>
                <?php echo $form->numberField($model, 'max_per_org', array(
                    'class' => 'form-control',
                    'min' => 1,
                    'placeholder' => 'Để trống nếu không giới hạn',
                )); ?>
                <?php echo $form->error($model, 'max_per_org'); ?>
                <small class="text-muted">Số người tối đa mỗi đơn vị được đăng ký</small>
            </div>
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'note'); ?>
                <?php echo $form->textArea($model, 'note', array(
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ghi chú thêm về đợt đăng ký',
                )); ?>
                <?php echo $form->error($model, 'note'); ?>
            </div>
        </div>
    </div>



    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-save me-1"></i>Lưu lại
        </button>
        <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <?php $this->endWidget(); ?>
</div>

<?php
Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/vendor/flatpickr/dist/flatpickr.min.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/vendor/flatpickr/dist/flatpickr.min.js', CClientScript::POS_END);
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var config = {
        enableTime: true,
        dateFormat: 'd-m-Y H:i',
        time_24hr: true,
        allowInput: true
    };

    flatpickr('#start_time_picker', Object.assign({}, config, {
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                document.getElementById('start_time_hidden').value = Math.floor(selectedDates[0].getTime() / 1000);
            }
        }
    }));

    flatpickr('#end_time_picker', Object.assign({}, config, {
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                document.getElementById('end_time_hidden').value = Math.floor(selectedDates[0].getTime() / 1000);
            }
        }
    }));
});
</script>