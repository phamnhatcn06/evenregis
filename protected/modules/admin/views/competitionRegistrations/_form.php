<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'competition-registrations-form',
        'htmlOptions' => array('data-toggle' => 'validator'),
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
            echo $form->dropDownListGroup($model, 'competition_id', array(
                'widgetOptions' => array(
                    'data' => array('' => '-- Chọn cuộc thi --') + $competitions,
                    'htmlOptions' => array(
                        'class' => 'form-select'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-6">
            <?php
            echo $form->textFieldGroup($model, 'attendee_id', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'ID người tham dự'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?php
            echo $form->textFieldGroup($model, 'candidate_number', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Tự động cấp nếu để trống'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo $form->dropDownListGroup($model, 'status', array(
                'widgetOptions' => array(
                    'data' => CompetitionRegistrations::getStatusOptions(),
                    'htmlOptions' => array(
                        'class' => 'form-select'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <?php
    echo $form->textAreaGroup($model, 'note', array(
        'widgetOptions' => array(
            'htmlOptions' => array(
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => 'Ghi chú'
            )
        )
    ));
    ?>

    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-save me-1"></i> Lưu
        </button>
        <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <?php $this->endWidget(); ?>
</div>
