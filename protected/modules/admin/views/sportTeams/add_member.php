<?php
$this->breadcrumbs = array(
    'Đội thể thao' => array('admin'),
    $team->team_name => array('view', 'id' => $team->id),
    'Thêm thành viên',
);

$this->menu = array(
    array(
        'label' => 'Quay lại đội',
        'url' => $this->createUrl('view', array('id' => $team->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Thêm thành viên vào đội: ' . CHtml::encode($team->team_name);
?>

<div class="card">
    <div class="card-body">
        <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
            'id' => 'sport-team-member-form',
            'htmlOptions' => array('data-toggle' => 'validator'),
            'enableClientValidation' => true,
        ));
        ?>
        <?php echo $form->errorSummary($model); ?>

        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2"></i>
            <strong>Lưu ý:</strong> Mỗi người chỉ được đăng ký tối đa <?php echo SportTeamMembers::MAX_SPORTS_PER_ATTENDEE; ?> môn thể thao.
        </div>

        <div class="row">
            <div class="col-md-6">
                <?php
                $attendeeOptions = array('' => '-- Chọn người tham dự --');
                foreach ($attendees as $att) {
                    $attendeeOptions[$att->id] = $att->full_name . (isset($att->staff_code) ? ' (' . $att->staff_code . ')' : '');
                }
                echo $form->dropDownListGroup($model, 'attendee_id', array(
                    'widgetOptions' => array(
                        'data' => $attendeeOptions,
                        'htmlOptions' => array(
                            'class' => 'form-select'
                        )
                    )
                ));
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo $form->textFieldGroup($model, 'jersey_number', array(
                    'widgetOptions' => array(
                        'htmlOptions' => array(
                            'class' => 'form-control',
                            'placeholder' => 'Số áo'
                        )
                    )
                ));
                ?>
            </div>
            <div class="col-md-3">
                <?php
                echo $form->textFieldGroup($model, 'position', array(
                    'widgetOptions' => array(
                        'htmlOptions' => array(
                            'class' => 'form-control',
                            'placeholder' => 'Vị trí'
                        )
                    )
                ));
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <?php
                echo $form->dropDownListGroup($model, 'is_captain', array(
                    'widgetOptions' => array(
                        'data' => array(0 => 'Không', 1 => 'Đội trưởng'),
                        'htmlOptions' => array(
                            'class' => 'form-select'
                        )
                    )
                ));
                ?>
            </div>
        </div>

        <hr />
        <div class="footer-action">
            <button id="btn-submit" type="submit" class="btn btn-sm btn-primary">
                <i class="fa fa-save me-1"></i> Thêm thành viên
            </button>
            <a href="<?php echo $this->createUrl('view', array('id' => $team->id)); ?>" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <?php $this->endWidget(); ?>
    </div>
</div>
