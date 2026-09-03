<div class="form-wrap">
    <?php $form = $this->beginWidget('CActiveForm', array(
        'id' => 'events-form',
        'htmlOptions' => array('enctype' => 'multipart/form-data'),
        'enableClientValidation' => false,
    )); ?>

    <?php echo $form->errorSummary($model); ?>

    <h6 class="fw-semibold text-primary mb-3"><i class="fa fa-info-circle me-1"></i>Thông tin chung</h6>
    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'name'); ?>
        <?php echo $form->textField($model, 'name', array(
            'class' => 'form-control',
            'maxlength' => 255,
        )); ?>
        <?php echo $form->error($model, 'name'); ?>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'slogan'); ?>
        <?php echo $form->textField($model, 'slogan', array(
            'class' => 'form-control',
            'maxlength' => 255,
            'placeholder' => 'Khẩu hiệu sự kiện',
        )); ?>
        <?php echo $form->error($model, 'slogan'); ?>
    </div>

    <div class="row">
        <div class="col-md-6 form-group mb-3">
            <?php echo $form->labelEx($model, 'destination'); ?>
            <?php echo $form->textField($model, 'destination', array(
                'class' => 'form-control',
                'maxlength' => 100,
                'placeholder' => 'Ninh Bình, Đà Nẵng...',
            )); ?>
            <?php echo $form->error($model, 'destination'); ?>
        </div>
        <div class="col-md-6 form-group mb-3">
            <?php echo $form->labelEx($model, 'organizer'); ?>
            <?php echo $form->textField($model, 'organizer', array(
                'class' => 'form-control',
                'maxlength' => 255,
                'placeholder' => 'Đơn vị tổ chức',
            )); ?>
            <?php echo $form->error($model, 'organizer'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group mb-3">
            <?php echo $form->labelEx($model, 'from_date'); ?>
            <?php echo $form->textField($model, 'from_date', array(
                'class' => 'form-control',
                'placeholder' => 'Chọn ngày bắt đầu',
            )); ?>
            <?php echo $form->error($model, 'from_date'); ?>
        </div>
        <div class="col-md-6 form-group mb-3">
            <?php echo $form->labelEx($model, 'to_date'); ?>
            <?php echo $form->textField($model, 'to_date', array(
                'class' => 'form-control',
                'placeholder' => 'Chọn ngày kết thúc',
            )); ?>
            <?php echo $form->error($model, 'to_date'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group mb-3">
            <?php echo $form->labelEx($model, 'duration_days'); ?>
            <?php echo $form->numberField($model, 'duration_days', array(
                'class' => 'form-control',
                'min' => 1,
            )); ?>
            <?php echo $form->error($model, 'duration_days'); ?>
        </div>
        <div class="col-md-4 form-group mb-3">
            <?php echo $form->labelEx($model, 'duration_nights'); ?>
            <?php echo $form->numberField($model, 'duration_nights', array(
                'class' => 'form-control',
                'min' => 0,
            )); ?>
            <?php echo $form->error($model, 'duration_nights'); ?>
        </div>
        <div class="col-md-4 form-group mb-3">
            <?php echo $form->labelEx($model, 'max_sports_per_attendee'); ?>
            <?php echo $form->numberField($model, 'max_sports_per_attendee', array(
                'class' => 'form-control',
                'min' => 1,
            )); ?>
            <?php echo $form->error($model, 'max_sports_per_attendee'); ?>
        </div>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'hero_description'); ?>
        <?php echo $form->textArea($model, 'hero_description', array(
            'class' => 'form-control',
            'rows' => 2,
            'placeholder' => 'Mô tả ngắn cho hero section',
        )); ?>
        <?php echo $form->error($model, 'hero_description'); ?>
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
    <h6 class="fw-semibold text-primary mb-3"><i class="fa fa-image me-1"></i>Hình ảnh & Linh vật</h6>
    <div class="row">
        <div class="col-md-6 form-group mb-3">
            <?php echo $form->labelEx($model, 'cover_image'); ?>
            <?php if (!empty($model->cover_image)): ?>
                <div class="mb-2">
                    <img src="<?php echo CHtml::encode($model->cover_image); ?>" alt="Ảnh bìa" class="img-thumbnail" style="max-height:120px;" />
                </div>
            <?php endif; ?>
            <?php echo CHtml::activeHiddenField($model, 'cover_image'); ?>
            <?php echo CHtml::fileField('cover_image_file', '', array(
                'class' => 'form-control',
                'accept' => 'image/*',
            )); ?>
            <small class="text-muted">Chọn ảnh để tải lên (JPG, PNG). Để trống nếu giữ ảnh hiện tại.</small>
            <?php echo $form->error($model, 'cover_image'); ?>
        </div>
        <div class="col-md-6 form-group mb-3">
            <?php echo $form->labelEx($model, 'mascot_image'); ?>
            <?php if (!empty($model->mascot_image)): ?>
                <div class="mb-2">
                    <img src="<?php echo CHtml::encode($model->mascot_image); ?>" alt="Ảnh linh vật" class="img-thumbnail" style="max-height:120px;" />
                </div>
            <?php endif; ?>
            <?php echo CHtml::activeHiddenField($model, 'mascot_image'); ?>
            <?php echo CHtml::fileField('mascot_image_file', '', array(
                'class' => 'form-control',
                'accept' => 'image/*',
            )); ?>
            <small class="text-muted">Chọn ảnh để tải lên (JPG, PNG). Để trống nếu giữ ảnh hiện tại.</small>
            <?php echo $form->error($model, 'mascot_image'); ?>
        </div>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'mascot_link'); ?>
        <?php echo $form->textField($model, 'mascot_link', array(
            'class' => 'form-control',
            'maxlength' => 255,
            'placeholder' => 'Link đặt linh vật',
        )); ?>
        <?php echo $form->error($model, 'mascot_link'); ?>
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