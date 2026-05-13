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
                    $startValue = date('Y-m-d\TH:i', $model->start_time);
                }
                ?>
                <input type="datetime-local" name="RegistrationPeriods[start_time]"
                       class="form-control" value="<?php echo $startValue; ?>" required>
                <?php echo $form->error($model, 'start_time'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'end_time'); ?>
                <?php
                $endValue = '';
                if ($model->end_time) {
                    $endValue = date('Y-m-d\TH:i', $model->end_time);
                }
                ?>
                <input type="datetime-local" name="RegistrationPeriods[end_time]"
                       class="form-control" value="<?php echo $endValue; ?>" required>
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
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check mt-2">
                    <?php echo $form->checkBox($model, 'is_active', array(
                        'class' => 'form-check-input',
                        'uncheckValue' => 0,
                    )); ?>
                    <label class="form-check-label" for="RegistrationPeriods_is_active">
                        Kích hoạt đợt đăng ký
                    </label>
                </div>
            </div>
        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('registration-periods-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var startInput = form.querySelector('input[name="RegistrationPeriods[start_time]"]');
            var endInput = form.querySelector('input[name="RegistrationPeriods[end_time]"]');

            if (startInput.value) {
                var startTimestamp = Math.floor(new Date(startInput.value).getTime() / 1000);
                var hiddenStart = document.createElement('input');
                hiddenStart.type = 'hidden';
                hiddenStart.name = 'RegistrationPeriods[start_time]';
                hiddenStart.value = startTimestamp;
                startInput.name = '_start_time_display';
                form.appendChild(hiddenStart);
            }

            if (endInput.value) {
                var endTimestamp = Math.floor(new Date(endInput.value).getTime() / 1000);
                var hiddenEnd = document.createElement('input');
                hiddenEnd.type = 'hidden';
                hiddenEnd.name = 'RegistrationPeriods[end_time]';
                hiddenEnd.value = endTimestamp;
                endInput.name = '_end_time_display';
                form.appendChild(hiddenEnd);
            }
        });
    }
});
</script>
