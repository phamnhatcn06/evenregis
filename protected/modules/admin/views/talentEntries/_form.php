<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'talent-entries-form',
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
            echo $form->dropDownListGroup($model, 'show_id', array(
                'widgetOptions' => array(
                    'data' => array('' => '-- Chọn hội diễn --') + $shows,
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
                'widgetOptions' => array(
                    'data' => array('' => '-- Chọn đơn vị --') + $properties,
                    'htmlOptions' => array(
                        'class' => 'form-select'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <?php
            echo $form->textFieldGroup($model, 'title', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Tên tiết mục'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo $form->dropDownListGroup($model, 'category_id', array(
                'widgetOptions' => array(
                    'data' => array('' => '-- Chọn thể loại --') + $categories,
                    'htmlOptions' => array(
                        'class' => 'form-select'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?php
            echo $form->textFieldGroup($model, 'participant_count', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Số người tham gia',
                        'type' => 'number',
                        'min' => 1
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo $form->textFieldGroup($model, 'duration_seconds', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Thời lượng (giây)',
                        'type' => 'number',
                        'min' => 1
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo $form->textFieldGroup($model, 'performance_order', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Thứ tự biểu diễn',
                        'type' => 'number'
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
                'placeholder' => 'Mô tả tiết mục'
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
