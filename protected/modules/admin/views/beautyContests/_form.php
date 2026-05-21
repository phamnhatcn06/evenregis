<?php
$form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'beauty-contests-form',
    'type' => 'horizontal',
    'htmlOptions' => array('class' => 'needs-validation'),
));
?>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->dropDownListGroup($model, 'event_id', array(
            'widgetOptions' => array(
                'data' => $events,
                'htmlOptions' => array('prompt' => '-- Chọn sự kiện --', 'class' => 'form-select'),
            ),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'name', array(
            'widgetOptions' => array('htmlOptions' => array('maxlength' => 255)),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->dropDownListGroup($model, 'gender', array(
            'widgetOptions' => array(
                'data' => BeautyContests::getGenderOptions(),
                'htmlOptions' => array('prompt' => '-- Chọn giới tính --', 'class' => 'form-select'),
            ),
        )); ?>
    </div>
    <div class="col-md-3">
        <?php echo $form->numberFieldGroup($model, 'age_min', array(
            'widgetOptions' => array('htmlOptions' => array('min' => 18, 'max' => 60)),
        )); ?>
    </div>
    <div class="col-md-3">
        <?php echo $form->numberFieldGroup($model, 'age_max', array(
            'widgetOptions' => array('htmlOptions' => array('min' => 18, 'max' => 60)),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->dateFieldGroup($model, 'registration_open_at', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control')),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->dateFieldGroup($model, 'registration_close_at', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control')),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->dateFieldGroup($model, 'contest_date', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control')),
        )); ?>
    </div>
    <div class="col-md-6">
        <?php echo $form->textFieldGroup($model, 'location', array(
            'widgetOptions' => array('htmlOptions' => array('maxlength' => 255)),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?php echo $form->textFieldGroup($model, 'candidate_prefix', array(
            'widgetOptions' => array('htmlOptions' => array('maxlength' => 50, 'placeholder' => 'VD: MS')),
        )); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->numberFieldGroup($model, 'candidate_start', array(
            'widgetOptions' => array('htmlOptions' => array('min' => 1, 'value' => $model->candidate_start ?: 1)),
        )); ?>
    </div>
    <div class="col-md-4">
        <?php echo $form->numberFieldGroup($model, 'max_per_org', array(
            'widgetOptions' => array('htmlOptions' => array('min' => 1, 'placeholder' => 'Tối đa thí sinh mỗi đơn vị')),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php echo $form->textAreaGroup($model, 'description', array(
            'widgetOptions' => array('htmlOptions' => array('rows' => 3)),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->checkBoxGroup($model, 'is_active', array(
            'widgetOptions' => array('htmlOptions' => array('value' => 1)),
        )); ?>
    </div>
</div>

<div class="form-actions">
    <?php $this->widget('booster.widgets.TbButton', array(
        'buttonType' => 'submit',
        'context' => 'primary',
        'label' => $model->isNewRecord ? 'Tạo mới' : 'Cập nhật',
    )); ?>
    <?php $this->widget('booster.widgets.TbButton', array(
        'buttonType' => 'link',
        'context' => 'secondary',
        'label' => 'Hủy',
        'url' => $this->createUrl('admin'),
    )); ?>
</div>

<?php $this->endWidget(); ?>
