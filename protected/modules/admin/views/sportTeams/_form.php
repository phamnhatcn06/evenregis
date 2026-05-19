<div class="form-wrap">
    <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
        'id' => 'sport-teams-form',
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
            echo $form->dropDownListGroup($model, 'event_id', array(
                'widgetOptions' => array(
                    'data' => array('' => '-- Chọn sự kiện --') + $events,
                    'htmlOptions' => array(
                        'class' => 'form-select',
                        'id' => 'event-select'
                    )
                )
            ));
            ?>
        </div>
        <div class="col-md-6">
            <?php
            $sportOptions = array('' => '-- Chọn môn thể thao --');
            foreach ($sports as $sport) {
                $sportOptions[$sport->id] = $sport->name;
            }
            echo $form->dropDownListGroup($model, 'sport_id', array(
                'widgetOptions' => array(
                    'data' => $sportOptions,
                    'htmlOptions' => array(
                        'class' => 'form-select'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?php
            echo $form->dropDownListGroup($model, 'property_id', array(
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
        <div class="col-md-6">
            <?php
            echo $form->textFieldGroup($model, 'team_name', array(
                'widgetOptions' => array(
                    'htmlOptions' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Tên đội'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?php
            echo $form->dropDownListGroup($model, 'is_alliance', array(
                'widgetOptions' => array(
                    'data' => array(0 => 'Không', 1 => 'Có'),
                    'htmlOptions' => array(
                        'class' => 'form-select',
                        'id' => 'is-alliance-select'
                    )
                )
            ));
            ?>
        </div>
    </div>

    <div id="alliance-section" class="card bg-light mt-3" style="display:none;">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-users me-2"></i>Chọn đơn vị liên quân</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small">Chỉ hiển thị các đơn vị cùng khu vực</p>
            <div id="alliance-org-list">
                <p class="text-muted">Vui lòng chọn đơn vị chính trước</p>
            </div>
        </div>
    </div>

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
    Yii::app()->theme->baseUrl . '/assets/js/pages/sport-teams-form.js',
    CClientScript::POS_END
);
?>
