<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'media-album-form',
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
        <?php echo $form->dropDownListGroup($model, 'type', array(
            'widgetOptions' => array(
                'data' => $typeOptions,
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

<div class="row">
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'title', array(
            'maxlength' => 255,
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'slug', array(
            'maxlength' => 255,
            'hint' => 'Để trống sẽ tự sinh từ tên album',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php echo $form->textFieldGroup($model, 'cover_image', array(
            'maxlength' => 500,
            'hint' => 'URL ảnh bìa album',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->textFieldGroup($model, 'badge', array(
            'maxlength' => 50,
            'hint' => 'Nhãn góc: ALBUM / BỘ MÔN...',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
</div>

<?php echo $form->textAreaGroup($model, 'description', array(
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'rows' => 3)),
)); ?>

<?php echo $form->checkBoxGroup($model, 'is_active'); ?>

<hr />
<div class="footer-action">
    <button id="btn-submit" type="submit" class="btn btn-save btn-sm btn-primary">
        <?php echo Yii::t('app', 'Save'); ?>
    </button>
</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
