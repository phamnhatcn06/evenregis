<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'competitions-form',
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
            echo $form->textFieldGroup($model, 'name', array(
                'maxlength' => 255,
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Nhập tên cuộc thi'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->textFieldGroup($model, 'candidate_number_prefix', array(
                'maxlength' => 10,
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'VD: NV'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->textFieldGroup($model, 'candidate_number_pad', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'VD: 3'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?php
            echo $form->textFieldGroup($model, 'candidate_number_start', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'VD: 1'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->textFieldGroup($model, 'max_per_org', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Để trống = không giới hạn'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->dropDownListGroup($model, 'has_qualification', array(
                'widgetOptions' => array(
                    'data' => array('1' => 'Có vòng loại', '0' => 'Không có vòng loại'),
                    'htmlOptions' => array(
                        'class' => 'form-select'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-3">
            <?php
            echo $form->dropDownListGroup($model, 'allow_direct_final', array(
                'widgetOptions' => array(
                    'data' => array('0' => 'Không cho phép', '1' => 'Cho phép'),
                    'htmlOptions' => array(
                        'class' => 'form-select'
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
                'rows' => 4,
                'placeholder' => 'Mô tả cuộc thi'
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