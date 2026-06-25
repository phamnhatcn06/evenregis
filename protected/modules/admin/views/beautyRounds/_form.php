<?php
$form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'beauty-rounds-form',
    'type' => 'horizontal',
    'enableClientValidation' => true,
    'htmlOptions' => array('class' => 'well'),
));
?>

<div class="row">
    <div class="col-md-6">
        <?php echo $form->dropDownListGroup($model, 'contest_id', array(
            'widgetOptions' => array(
                'data' => $contests,
                'htmlOptions' => array('class' => 'form-select', 'prompt' => '-- Chọn cuộc thi --'),
            ),
        )); ?>

        <?php echo $form->textFieldGroup($model, 'name', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'maxlength' => 255)),
        )); ?>

        <?php echo $form->dropDownListGroup($model, 'round_type', array(
            'widgetOptions' => array(
                'data' => BeautyRounds::getRoundTypeOptions(),
                'htmlOptions' => array('class' => 'form-select', 'prompt' => '-- Chọn loại vòng --'),
            ),
        )); ?>

        <?php echo $form->numberFieldGroup($model, 'round_order', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'min' => 1)),
        )); ?>
    </div>

    <div class="col-md-6">
        <?php echo $form->numberFieldGroup($model, 'max_score', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'step' => '0.01', 'min' => 0)),
        )); ?>

        <?php echo $form->numberFieldGroup($model, 'weight', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'step' => '0.01', 'min' => 0)),
        )); ?>

        <?php echo $form->dateTimeFieldGroup($model, 'start_time', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control')),
        )); ?>

        <?php echo $form->dateTimeFieldGroup($model, 'end_time', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control')),
        )); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php echo $form->textAreaGroup($model, 'note', array(
            'widgetOptions' => array('htmlOptions' => array('class' => 'form-control', 'rows' => 3)),
        )); ?>
    </div>
</div>

<div class="form-actions mt-3">
    <?php $this->widget('booster.widgets.TbButton', array(
        'buttonType' => 'submit',
        'context' => 'primary',
        'icon' => 'fa fa-save',
        'label' => $model->isNewRecord ? 'Tạo mới' : 'Lưu thay đổi',
    )); ?>
    <?php $this->widget('booster.widgets.TbButton', array(
        'buttonType' => 'link',
        'context' => 'secondary',
        'icon' => 'fa fa-arrow-left',
        'label' => 'Quay lại',
        'url' => array('admin'),
    )); ?>
</div>

<?php $this->endWidget(); ?>
