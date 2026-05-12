<div class="form-wrap">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'staffs-form',
    'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
));
?>
<?php echo $form->errorSummary($model); ?>

<?php
echo $form->textFieldGroup($model, 'unique_code', array(
    'maxlength' => 100,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'department_code', array(
    'maxlength' => 255,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'id_card', array(
    'maxlength' => 50,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'rank_id', array(
    'maxlength' => 50,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'position_code', array(
    'maxlength' => 255,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'property_code', array(
    'maxlength' => 255,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'division_code', array(
    'maxlength' => 255,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'code', array(
    'maxlength' => 50,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'is_lecturer', array(
    'maxlength' => 4,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'curren_job_id', array(
    'maxlength' => 4,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'lecturer_type', array(
    'maxlength' => 20,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'first_name', array(
    'maxlength' => 100,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'last_name', array(
    'maxlength' => 100,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'full_name', array(
    'maxlength' => 255,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'email', array(
    'maxlength' => 100,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'phone', array(
    'maxlength' => 100,
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
    'birthday',
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
echo $form->datePickerGroup(
    $model,
    'terminate_date',
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
echo $form->datePickerGroup(
    $model,
    'join_hotel_date',
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
echo $form->datePickerGroup(
    $model,
    'end_testing_date',
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
echo $form->textFieldGroup($model, 'married', array(
    'maxlength' => 1,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'gender', array(
    'maxlength' => 11,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textFieldGroup($model, 'address', array(
    'maxlength' => 255,
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2'
        )
    )
));
?>

<?php
echo $form->textAreaGroup($model, 'notes', array(
    'widgetOptions' => array(
        'htmlOptions' => array(
            'class' => 'input w-full border mt-2',
            'rows' => 5
        )
    )
));
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

<?php
echo $form->textFieldGroup($model, 'staff_type', array(
    'maxlength' => 255,
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
