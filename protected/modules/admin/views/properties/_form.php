<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'properties-form',
    'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
));
?>
<?php echo $form->errorSummary($model); ?>

<?php
echo $form->textFieldGroup($model, 'prefix', array(
    'maxlength' => 20,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'code', array(
    'maxlength' => 20,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'smile_code', array(
    'maxlength' => 20,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'name', array(
    'maxlength' => 255,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->datePickerGroup(
    $model,
    'active_date',
    array(
        'widgetOptions' => array(
            'options' => array(
                'language' => 'vi',
                'todayHighlight' => 'true',
                'todayBtn' => 'linked',
                'clearBtn' => 'true',
                'orientation' => 'bottom right',
                'format' => 'yyyy-mm-dd',
            ),
            'htmlOptions' => array(
                'class' => 'input w-full border mt-2'
            )
        ),
        'wrapperHtmlOptions' => array(
            'class' => 'col-sm-5',
        ),
    )
);
?>

<?php
echo $form->textFieldGroup($model, 'status', array(
    'maxlength' => 4,
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
