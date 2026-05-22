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

    <div class="form-group mb-3">
        <label class="form-label"><?php echo $model->getAttributeLabel('allow_alliance'); ?></label>
        <div class="form-check form-switch">
            <?php echo $form->checkBox($model, 'allow_alliance', array(
                'class' => 'form-check-input',
                'id' => 'allow-alliance-toggle',
                'value' => 1,
                'uncheckValue' => 0,
            )); ?>
        </div>
    </div>

    <div id="max-alliance-wrapper" style="display: <?php echo $model->allow_alliance ? 'block' : 'none'; ?>;">
        <?php
        echo $form->textFieldGroup($model, 'max_alliance_teams', array(
            'widgetOptions' => array(
                'htmlOptions' => array(
                    'class' => 'input w-full border mt-2',
                    'placeholder' => 'Nhập số đội liên quân tối đa (0 = không giới hạn)'
                )
            )
        ));
        ?>
    </div>


    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-save btn-sm btn-primary">
            <?php echo Yii::t('app', 'Save'); ?> </button>
    </div>

    <?php $this->endWidget(); ?>
</div><!-- form -->