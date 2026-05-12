<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'contents-form',
        'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
    ));
    ?>
    <?php echo $form->errorSummary($model); ?>

    <?php
    echo $form->textFieldGroup($model, 'code', array(
        'maxlength' => 255,
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
    echo $form->textAreaGroup($model, 'description', array(
        'widgetOptions' => array(
            'htmlOptions' => array(
                'class' => 'input w-full border mt-2',
                'rows' => 5
            )
        )
    ));
    ?>


    <?php
    echo $form->textFieldGroup($model, 'sort_order', array(
        'maxlength' => 11,
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
            <?php echo Yii::t('app', 'Save'); ?> </button>
    </div>

    <?php $this->endWidget(); ?>
</div><!-- form -->