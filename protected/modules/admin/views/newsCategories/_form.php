<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'news-categories-form',
    'htmlOptions' => array('data-toggle' => 'validator'),
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
));
?>
<?php echo $form->errorSummary($model); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->dropDownListGroup($model, 'event_id', array(
            'widgetOptions' => array(
                'data' => $eventList,
                'htmlOptions' => array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn sự kiện --',
                ),
            ),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'name', array(
            'maxlength' => 100,
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'icon', array(
            'maxlength' => 50,
            'hint' => 'Ví dụ: fa-futbol',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'color', array(
            'maxlength' => 20,
            'hint' => 'Ví dụ: #1c6ee8',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'type' => 'text')),
        )); ?>
    </div>
</div>

<?php echo $form->textAreaGroup($model, 'description', array(
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'rows' => 3)),
)); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'sort_order', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-6">
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
