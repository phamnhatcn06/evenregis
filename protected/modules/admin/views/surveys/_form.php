<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'survey-form',
    'htmlOptions' => array('data-toggle' => 'validator'),
    'enableClientValidation' => true,
    'clientOptions' => array('validateOnSubmit' => true),
)); ?>
<?php echo $form->errorSummary($model); ?>

<div class="row">
    <div class="col-md-8">
        <?php echo $form->dropDownListGroup($model, 'event_id', array(
            'widgetOptions' => array(
                'data' => $eventList,
                'htmlOptions' => array('class' => 'form-select', 'prompt' => '-- Chọn sự kiện --'),
            ),
        )); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->numberFieldGroup($model, 'sort_order', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
</div>

<?php echo $form->textFieldGroup($model, 'title', array(
    'maxlength' => 255,
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
)); ?>

<?php echo $form->textFieldGroup($model, 'form_url', array(
    'maxlength' => 1000,
    'hint' => 'Dán link Google Form (ví dụ: https://docs.google.com/forms/d/e/.../viewform hoặc https://forms.gle/...)',
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'placeholder' => 'https://')),
)); ?>

<?php echo $form->textAreaGroup($model, 'description', array(
    'hint' => 'Mô tả ngắn hiển thị dưới tiêu đề khảo sát (tối đa 500 ký tự).',
    'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2', 'rows' => 3, 'maxlength' => 500)),
)); ?>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'icon', array(
            'maxlength' => 100,
            'hint' => 'Tuỳ chọn — tên icon Font Awesome, ví dụ: fa-comments, fa-star, fa-clipboard',
            'widgetOptions' => array('htmlOptions' => array('class' => 'input w-full border mt-2')),
        )); ?>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="mb-2">
            <?php echo $form->checkBoxGroup($model, 'is_active', array(
                'hint' => 'Bật để hiển thị khảo sát này trên trang Khảo sát công khai.',
            )); ?>
        </div>
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
