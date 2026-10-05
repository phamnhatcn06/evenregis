<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'media-item-form',
    'htmlOptions' => array('data-toggle' => 'validator'),
    'enableClientValidation' => true,
    'clientOptions' => array('validateOnSubmit' => true),
)); ?>
<?php echo $form->errorSummary($model); ?>

<div class="alert alert-light border mb-3">
    Album: <strong><?php echo CHtml::encode($album->title); ?></strong>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $form->dropDownListGroup($model, 'type', array(
            'widgetOptions' => array(
                'data' => array('image' => 'Ảnh', 'video' => 'Video'),
                'htmlOptions' => array('class' => 'form-select'),
            ),
        )); ?>
    </div>
    <div class="col-md-3">
        <?php echo $form->numberFieldGroup($model, 'sort_order', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'title', array(
            'maxlength' => 255,
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
</div>

<?php echo $form->textFieldGroup($model, 'url', array(
    'maxlength' => 500,
    'hint' => 'URL ảnh gốc hoặc link video',
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
)); ?>

<?php echo $form->textFieldGroup($model, 'thumbnail', array(
    'maxlength' => 500,
    'hint' => 'URL ảnh thu nhỏ (tuỳ chọn)',
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
)); ?>

<?php echo $form->textAreaGroup($model, 'caption', array(
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'rows' => 2)),
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
