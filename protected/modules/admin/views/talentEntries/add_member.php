<?php
$this->breadcrumbs = array(
    'Tiết mục văn nghệ' => array('admin'),
    $entry->title => array('view', 'id' => $entry->id),
    'Thêm thành viên',
);

$this->menu = array(
    array(
        'label' => 'Quay lại tiết mục',
        'url' => $this->createUrl('view', array('id' => $entry->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Thêm thành viên vào tiết mục: ' . CHtml::encode($entry->title);
?>

<div class="card">
    <div class="card-body">
        <?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
            'id' => 'talent-entry-member-form',
            'htmlOptions' => array('data-toggle' => 'validator'),
            'enableClientValidation' => true,
        ));
        ?>
        <?php echo $form->errorSummary($model); ?>

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
            <div class="col-md-6">
                <?php
                echo $form->textFieldGroup($model, 'role', array(
                    'widgetOptions' => array(
                        'htmlOptions' => array(
                            'class' => 'form-control',
                            'placeholder' => 'VD: Ca sĩ chính, Vũ công, Nhạc công...'
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
            <a href="<?php echo $this->createUrl('view', array('id' => $entry->id)); ?>" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <?php $this->endWidget(); ?>
    </div>
</div>
