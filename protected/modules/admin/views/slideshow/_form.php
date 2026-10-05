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

<div class="row">
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'image', array(
            'maxlength' => 500,
            'hint' => 'URL ảnh nền (desktop)',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'mobile_image', array(
            'maxlength' => 500,
            'hint' => 'URL ảnh nền cho mobile (tuỳ chọn)',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
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
