<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp' => array('/admin/beautyContests/admin'),
    'Vòng thi' => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    'Gắn thí sinh',
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Gắn thí sinh vào vòng: ' . CHtml::encode($model->name);

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/beauty-rounds-assign.js',
    CClientScript::POS_END
);
?>

<div class="row">
    <!-- Thí sinh có thể gắn -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fa fa-users me-2"></i>Thí sinh có thể gắn</h6>
            </div>
            <div class="card-body p-0">
                <div class="p-2 border-bottom">
                    <input type="text" id="search_available" class="form-control form-control-sm"
                           placeholder="Tìm kiếm theo tên, SBD, đơn vị...">
                </div>
                <div class="list-group list-group-flush" id="available_list" style="max-height:500px;overflow-y:auto;">
                    <?php if (empty($availableContestants)): ?>
                        <div class="list-group-item text-muted text-center">Không có thí sinh nào</div>
                    <?php else: ?>
                        <?php foreach ($availableContestants as $c): ?>
                        <label class="list-group-item list-group-item-action d-flex align-items-center"
                               data-search="<?php echo CHtml::encode(strtolower($c['contestant_name'] . ' ' . $c['contestant_number'] . ' ' . $c['property_name'])); ?>">
                            <input type="checkbox" class="form-check-input me-2 contestant-checkbox"
                                   value="<?php echo $c['registration_id']; ?>">
                            <?php if (!empty($c['photo_portrait'])): ?>
                                <img src="<?php echo CHtml::encode($c['photo_portrait']); ?>"
                                     class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">
                            <?php else: ?>
                                <span class="me-2 text-muted" style="width:40px;text-align:center;">
                                    <i class="fa fa-user"></i>
                                </span>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?php echo CHtml::encode($c['contestant_number']); ?> - <?php echo CHtml::encode($c['contestant_name']); ?></div>
                                <small class="text-muted"><?php echo CHtml::encode($c['property_name']); ?></small>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Nút chuyển -->
    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
        <button type="button" id="btn_assign" class="btn btn-primary mb-2" style="width:120px;">
            <i class="fa fa-arrow-right me-1"></i>Gắn
        </button>
        <button type="button" id="btn_assign_all" class="btn btn-outline-primary mb-4" style="width:120px;">
            <i class="fa fa-angle-double-right me-1"></i>Gắn tất cả
        </button>
        <button type="button" id="btn_remove" class="btn btn-danger mb-2" style="width:120px;">
            <i class="fa fa-arrow-left me-1"></i>Bỏ
        </button>
        <button type="button" id="btn_remove_all" class="btn btn-outline-danger" style="width:120px;">
            <i class="fa fa-angle-double-left me-1"></i>Bỏ tất cả
        </button>
    </div>

    <!-- Thí sinh đã gắn -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fa fa-check-circle me-2"></i>Thí sinh trong vòng (<span id="assigned_count"><?php echo count($assignedContestants); ?></span>)</h6>
            </div>
            <div class="card-body p-0">
                <div class="p-2 border-bottom">
                    <input type="text" id="search_assigned" class="form-control form-control-sm"
                           placeholder="Tìm kiếm...">
                </div>
                <div class="list-group list-group-flush" id="assigned_list" style="max-height:500px;overflow-y:auto;">
                    <?php if (empty($assignedContestants)): ?>
                        <div class="list-group-item text-muted text-center" id="empty_assigned">Chưa có thí sinh nào</div>
                    <?php else: ?>
                        <?php foreach ($assignedContestants as $c): ?>
                        <label class="list-group-item list-group-item-action d-flex align-items-center"
                               data-search="<?php echo CHtml::encode(strtolower($c['contestant_name'] . ' ' . $c['contestant_number'] . ' ' . $c['property_name'])); ?>"
                               data-id="<?php echo $c['registration_id']; ?>">
                            <input type="checkbox" class="form-check-input me-2 assigned-checkbox"
                                   value="<?php echo $c['registration_id']; ?>">
                            <?php if (!empty($c['photo_portrait'])): ?>
                                <img src="<?php echo CHtml::encode($c['photo_portrait']); ?>"
                                     class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">
                            <?php else: ?>
                                <span class="me-2 text-muted" style="width:40px;text-align:center;">
                                    <i class="fa fa-user"></i>
                                </span>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="fw-bold"><?php echo CHtml::encode($c['contestant_number']); ?> - <?php echo CHtml::encode($c['contestant_name']); ?></div>
                                <small class="text-muted"><?php echo CHtml::encode($c['property_name']); ?></small>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="round_id" value="<?php echo $model->id; ?>">
<input type="hidden" id="assign_url" value="<?php echo $this->createUrl('assignContestants', array('id' => $model->id)); ?>">
