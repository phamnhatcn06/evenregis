<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'slideshow-form',
    'htmlOptions' => array('data-toggle' => 'validator'),
    'enableClientValidation' => true,
    'clientOptions' => array('validateOnSubmit' => true),
)); ?>
<?php echo $form->errorSummary($model); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->dropDownListGroup($model, 'event_id', array(
            'widgetOptions' => array(
                'data' => $eventList,
                'htmlOptions' => array('class' => 'form-select', 'prompt' => '-- Chọn sự kiện --'),
            ),
        )); ?>
    </div>
    <div class="col-md-3">
        <?php echo $form->dropDownListGroup($model, 'theme', array(
            'widgetOptions' => array(
                'data' => $themeOptions,
                'htmlOptions' => array('class' => 'form-select'),
            ),
        )); ?>
    </div>
    <div class="col-md-3">
        <?php echo $form->numberFieldGroup($model, 'sort_order', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
</div>

<?php echo $form->textFieldGroup($model, 'title', array(
    'maxlength' => 255,
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
)); ?>

<?php echo $form->textFieldGroup($model, 'subtitle', array(
    'maxlength' => 500,
    'hint' => 'Dòng nhấn/kicker nhỏ phía trên tiêu đề',
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
)); ?>

<?php echo $form->textAreaGroup($model, 'description', array(
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'rows' => 3)),
)); ?>

<?php
$uploadUrl = $this->createUrl('uploadImage');
$dropzones = array(
    array('attr' => 'image', 'label' => 'Ảnh nền (desktop)', 'hint' => 'Kéo & thả hoặc bấm để chọn ảnh nền desktop (khuyến nghị chuẩn 16:9, ví dụ: 1920x1080px)'),
    array('attr' => 'mobile_image', 'label' => 'Ảnh nền (mobile)', 'hint' => 'Tuỳ chọn — ảnh nền hiển thị trên điện thoại (khuyến nghị chuẩn 16:9 hoặc 9:16)'),
);
?>
<div class="row">
    <?php foreach ($dropzones as $dz): $attr = $dz['attr']; $value = $model->$attr; ?>
    <div class="col-md-6 mb-3">
        <label class="form-label"><?php echo CHtml::encode($dz['label']); ?></label>
        <div class="slide-dropzone border rounded text-center p-3"
             data-target="<?php echo $attr; ?>"
             data-upload-url="<?php echo $uploadUrl; ?>"
             style="cursor:pointer;background:#f8f9fa;border-style:dashed !important;">
            <div class="slide-dropzone-preview mb-2" style="min-height:120px;display:flex;align-items:center;justify-content:center;">
                <?php if (!empty($value)): ?>
                    <img src="<?php echo CHtml::encode($value); ?>" alt="preview" style="max-height:140px;max-width:100%;">
                <?php else: ?>
                    <span class="text-muted"><i class="fa fa-cloud-upload fa-2x d-block mb-2"></i><?php echo CHtml::encode($dz['hint']); ?></span>
                <?php endif; ?>
            </div>
            <div class="slide-dropzone-status small text-muted"></div>
            <input type="file" class="slide-dropzone-input d-none" accept="image/*">
        </div>
        <?php echo CHtml::activeHiddenField($model, $attr, array('class' => 'slide-dropzone-value', 'id' => 'Slideshow_' . $attr)); ?>
        <?php echo $form->error($model, $attr); ?>
    </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'button_text', array(
            'maxlength' => 100,
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'button_url', array(
            'maxlength' => 500,
            'hint' => 'Link khi bấm nút (vd: #noi-dung hoặc URL)',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?php echo $form->textFieldGroup($model, 'start_at', array(
            'hint' => 'Định dạng: YYYY-MM-DD HH:MM:SS (để trống = ngay)',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->textFieldGroup($model, 'end_at', array(
            'hint' => 'Để trống = vô hạn',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->checkBoxGroup($model, 'is_active'); ?>
    </div>
</div>

<hr />
<div class="footer-action">
    <button id="btn-submit" type="submit" class="btn btn-save btn-sm btn-primary">
        <?php echo Yii::t('app', 'Save'); ?>
    </button>
</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/slideshow-form.js',
    CClientScript::POS_END
);
?>
