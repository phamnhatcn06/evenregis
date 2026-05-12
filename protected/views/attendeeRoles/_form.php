<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'attendee-roles-form',
    'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
));
?>
<?php echo $form->errorSummary($model); ?>

<?php
echo $form->textFieldGroup($model, 'attendee_id', array(
    'maxlength' => 20,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'role_id', array(
    'maxlength' => 20,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'assigned_by', array(
    'maxlength' => 20,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>


    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-save btn-sm btn-primary">
            <?php echo Yii::t('app', 'Save'); ?>        </button>
    </div>

<?php $this->endWidget(); ?>
</div><!-- form -->
