<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'beauty-contestants-form',
        'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
    ));
    ?>
    <?php echo $form->errorSummary($model); ?>

    <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i>
        <strong>Lưu ý:</strong> Chỉ hiển thị nhân viên nữ đã được duyệt tham dự.
    </div>

    <div class="row">
        <div class="col-md-6">
            <?php
            echo $form->dropDownListGroup($model, 'contest_id', array(
                'widgetOptions' => array(
                    'data' => array('' => '-- Chọn cuộc thi --') + $contests,
                    'htmlOptions' => array(
                        'class' => 'form-select'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-6">
            <?php
            echo $form->dropDownListGroup($model, 'property_id', array(
                'label' => 'Đơn vị',
                'widgetOptions' => array(
                    'data' => array('' => '-- Chọn đơn vị --') + $properties,
                    'htmlOptions' => array(
                        'class' => 'form-select',
                        'id' => 'property-select'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Thí sinh <span class="text-danger">*</span></label>
                <select name="BeautyContestants[attendee_id]" id="attendee-select" class="form-select" required>
                    <option value="">-- Chọn đơn vị trước --</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <?php
            echo $form->textFieldGroup($model, 'contestant_number', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Tự động nếu để trống'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?php
            echo $form->textFieldGroup($model, 'height_cm', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'VD: 165',
                        'type' => 'number'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->textFieldGroup($model, 'weight_kg', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'VD: 50',
                        'type' => 'number'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-6">
            <?php
            echo $form->textFieldGroup($model, 'measurements', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'VD: 86-60-90'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?php
            echo $form->textFieldGroup($model, 'talent', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'VD: Múa, Hát, Piano...'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <?php
    echo $form->textAreaGroup($model, 'bio', array(
        'widgetOptions' => array(
            'htmlOptions' => array(
                'class' => 'form-control',
                'rows' => 4,
                'placeholder' => 'Tiểu sử thí sinh'
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

<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/beauty-contestants-form.js',
    CClientScript::POS_END
);
?>
