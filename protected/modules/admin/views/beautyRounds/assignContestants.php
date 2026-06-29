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
                <h6 class="mb-0"><i class="fa fa-users me-2"></i>Thí sinh có thể gắn (<span id="available_count"><?php echo count($availableContestants); ?></span>)</h6>
            </div>
            <div class="card-body p-0">
                <div class="p-2 border-bottom">
                    <input type="text" id="search_available" class="form-control form-control-sm"
                        placeholder="Tìm kiếm theo tên, SBD, đơn vị...">
                </div>
                <div class="list-group list-group-flush" id="available_list" style="max-height:450px;overflow-y:auto;">
                    <?php if (empty($availableContestants)): ?>
                        <div class="list-group-item text-muted text-center" id="empty_available">Không có thí sinh nào</div>
                    <?php else: ?>
                        <?php foreach ($availableContestants as $c): ?>
                            <label class="list-group-item list-group-item-action d-flex align-items-center contestant-item"
                                data-id="<?php echo $c['id']; ?>"
                                data-number="<?php echo CHtml::encode($c['contestant_number']); ?>"
                                data-name="<?php echo CHtml::encode($c['contestant_name']); ?>"
                                data-property="<?php echo CHtml::encode($c['property_name']); ?>"
                                data-photo="<?php echo CHtml::encode($c['photo_portrait']); ?>"
                                data-search="<?php echo CHtml::encode(strtolower($c['contestant_name'] . ' ' . $c['contestant_number'] . ' ' . $c['property_name'])); ?>">
                                <input type="checkbox" class="form-check-input me-2 available-checkbox">
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
        <button type="button" id="btn_move_right" class="btn btn-primary mb-2" style="width:150px;">
            <i class="fa fa-arrow-right me-1"></i>Chọn
        </button>
        <button type="button" id="btn_move_all_right" class="btn btn-outline-primary mb-4" style="width:150px;">
            <i class="fa fa-angle-double-right me-1"></i>Chọn tất cả
        </button>
        <button type="button" id="btn_move_left" class="btn btn-danger mb-2" style="width:150px;">
            <i class="fa fa-arrow-left me-1"></i>Bỏ
        </button>
        <button type="button" id="btn_move_all_left" class="btn btn-outline-danger" style="width:150px;">
            <i class="fa fa-angle-double-left me-1"></i>Bỏ tất cả
        </button>
    </div>

    <!-- Thí sinh đã chọn -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fa fa-check-circle me-2"></i>Thí sinh sẽ gắn (<span id="selected_count">0</span>)</h6>
            </div>
            <div class="card-body p-0">
                <div class="p-2 border-bottom">
                    <input type="text" id="search_selected" class="form-control form-control-sm"
                        placeholder="Tìm kiếm...">
                </div>
                <div class="list-group list-group-flush" id="selected_list" style="max-height:450px;overflow-y:auto;">
                    <div class="list-group-item text-muted text-center" id="empty_selected">Chưa chọn thí sinh nào</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nút Lưu -->
<div class="row mt-4">
    <div class="col-12 text-center">
        <button type="button" id="btn_save" class="btn btn-success btn-lg px-5">
            <i class="fa fa-save me-2"></i>Lưu thay đổi
        </button>
    </div>
</div>

<!-- Danh sách đã gắn trước đó -->
<?php if (!empty($assignedContestants)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-list me-2"></i>Thí sinh đã gắn vào vòng (<?php echo count($assignedContestants); ?>)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">STT</th>
                            <th style="width:80px">Ảnh</th>
                            <th>SBD</th>
                            <th>Họ tên</th>
                            <th>Đơn vị</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignedContestants as $idx => $c): ?>
                            <tr>
                                <td class="text-center"><?php echo $idx + 1; ?></td>
                                <td>
                                    <?php if (!empty($c['photo_portrait'])): ?>
                                        <img src="<?php echo CHtml::encode($c['photo_portrait']); ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                    <?php else: ?>
                                        <i class="fa fa-user text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo CHtml::encode($c['contestant_number']); ?></strong></td>
                                <td><?php echo CHtml::encode($c['contestant_name']); ?></td>
                                <td><?php echo CHtml::encode($c['property_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<input type="hidden" id="round_id" value="<?php echo $model->id; ?>">
<input type="hidden" id="assign_url" value="<?php echo $this->createUrl('assignContestants', array('id' => $model->id)); ?>">