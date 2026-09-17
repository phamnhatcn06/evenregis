<?php
/* @var $this SportTeamsController */
/* @var $events array id=>name */
/* @var $sports SportTeams[]|array danh sách môn thể thao */

$this->breadcrumbs = array(
    'Thể thao' => array('admin'),
    'Chọn vào chung kết',
);
$this->Tabletitle = 'Chọn đội vào vòng chung kết';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/finals-manager.js',
    CClientScript::POS_END
);
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Sự kiện <span class="text-danger">*</span></label>
                <select id="finals-event" class="form-select">
                    <option value="">-- Chọn sự kiện --</option>
                    <?php foreach ($events as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Môn thi <span class="text-danger">*</span></label>
                <select id="finals-sport" class="form-select">
                    <option value="">-- Chọn môn thi --</option>
                    <?php foreach ($sports as $sport): ?>
                        <option value="<?php echo $sport->id; ?>"><?php echo CHtml::encode($sport->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<?php $this->renderPartial('//sportTeams/_final_board', array(
    'candidatesTitle' => 'Đội đủ điều kiện (đã xác nhận)',
    'finalistsTitle' => 'Đội đã vào chung kết',
    'mode' => 'sport',
    'controllerId' => 'sportTeams',
)); ?>
