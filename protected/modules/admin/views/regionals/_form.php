<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'regionals-form',
    'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
));
?>
<?php echo $form->errorSummary($model); ?>

<div class="row">
    <div class="col-md-6">
        <?php
        echo $form->textFieldGroup($model, 'code', array(
            'maxlength' => 50,
            'widgetOptions' => array(
                'htmlOptions' => array(
                    'class' => 'form-control',
                    'placeholder' => 'VD: KV01, MB, MN...'
                )
            )
        ));
        ?>
    </div>
    <div class="col-md-6">
        <?php
        echo $form->textFieldGroup($model, 'name', array(
            'maxlength' => 255,
            'widgetOptions' => array(
                'htmlOptions' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Ten khu vuc'
                )
            )
        ));
        ?>
    </div>
</div>

<?php
echo $form->textAreaGroup($model, 'description', array(
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'form-control',
            'rows' => 3,
            'placeholder' => 'Mo ta khu vuc (khong bat buoc)'
        )
    )
));
?>

<?php
echo $form->dropDownListGroup($model, 'status', array(
    'widgetOptions' => array(
        'data' => array(1 => 'Hoat dong', 0 => 'Khong hoat dong'),
        'htmlOptions' => array(
            'class' => 'form-select'
        )
    )
));
?>

<hr />
<div class="footer-action">
    <button id="btn-submit" type="submit" class="btn btn-save btn-sm btn-primary">
        <?php echo Yii::t('app', 'Save'); ?>
    </button>
    <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-sm btn-secondary">
        <?php echo Yii::t('app', 'Cancel'); ?>
    </a>
</div>

<?php $this->endWidget(); ?>
</div>
