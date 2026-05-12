<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'events-form',
        'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
    ));
    ?>
    <?php echo $form->errorSummary($model); ?>

    <?php
    echo $form->textFieldGroup($model, 'name', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
        'class' => 'input w-full border mt-2'
    ))));
    ?>

    <?php
    echo $form->datePickerGroup(
        $model,
        'from_date',
        array(
            'widgetOptions' => array(
                'options' => array(
                    'language' => 'vi',
                    'calendarWeeks' => 'true',
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
        'to_date',
        array(
            'widgetOptions' => array(
                'options' => array(
                    'language' => 'vi',
                    'calendarWeeks' => 'true',
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
    echo $form->ckEditorGroup(
        $model,
        'description',
        array(
            'wrapperHtmlOptions' => array(
                /* 'class' => 'col-sm-5', */),
            'widgetOptions' => array(
                'editorOptions' => array(
                    'fullpage' => 'js:true',
                    /* 'width' => '640', */
                    /* 'resize_maxWidth' => '640', */
                    /* 'resize_minWidth' => '320'*/
                )
            )
        )
    );
    ?>
    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-save btn-sm btn-primary">
            Lưu lại </button>
    </div>

    <?php
    $this->endWidget();
    ?>
</div><!-- form -->