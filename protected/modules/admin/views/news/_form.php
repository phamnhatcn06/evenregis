<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'news-form',
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
        <?php echo $form->dropDownListGroup($model, 'category', array(
            'widgetOptions' => array(
                'data' => $categoryOptions,
                'htmlOptions' => array('class' => 'form-select'),
            ),
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
        <?php echo $form->dropDownListGroup($model, 'category_id', array(
            'widgetOptions' => array(
                'data' => $newsCategories,
                'htmlOptions' => array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn danh mục --',
                ),
            ),
        )); ?>
    </div>
</div>

<?php echo $form->textFieldGroup($model, 'thumbnail', array(
    'maxlength' => 500,
    'hint' => 'Đường dẫn (URL) ảnh đại diện',
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
)); ?>

<?php echo $form->textAreaGroup($model, 'excerpt', array(
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'rows' => 3)),
)); ?>

<?php echo $form->textAreaGroup($model, 'content', array(
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'rows' => 10)),
)); ?>

<div class="row">
    <div class="col-md-4">
        <?php echo $form->textFieldGroup($model, 'published_at', array(
            'hint' => 'Định dạng: YYYY-MM-DD HH:MM:SS',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->checkBoxGroup($model, 'is_featured'); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->checkBoxGroup($model, 'is_published'); ?>
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
